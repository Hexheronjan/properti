<?php

namespace App\Http\Controllers;

use App\Models\KprPayment;
use App\Models\SprDocument;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KprPaymentController extends Controller
{
    protected $payment;

    public function __construct(PaymentGatewayService $payment)
    {
        $this->payment = $payment;
    }

    /**
     * List all SPR payments for the logged-in user (all schemes)
     */
    public function list()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Ambil SPR aktif/lunas terbaru milik user (semua skema pembayaran)
        $sprs = SprDocument::where('user_id', Auth::id())
            ->whereIn('status', ['issued', 'paid'])
            ->with(['kprPayments' => function($query) {
                $query->orderBy('created_at', 'desc');
            }, 'unitSelection.housingUnit'])
            ->orderBy('created_at', 'desc')
            ->take(1)
            ->get();

        return view('kpr-payment.list', compact('sprs'));
    }

    /**
     * Show KPR payment page for an SPR
     */
    public function index($sprId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $spr = SprDocument::with(['kprPayments' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($sprId);

        if ($spr->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($spr->payment_scheme !== 'kpr') {
            return back()->with('error', 'Pembayaran KPR hanya tersedia untuk skema pembayaran KPR.');
        }

        $totalPaid = $spr->getTotalPaid();
        $remainingAmount = $spr->getRemainingAmount();
        $paymentProgress = $spr->getPaymentProgress();
        $isFullyPaid = $spr->isKprFullyPaid();

        return view('kpr-payment.index', compact(
            'spr',
            'totalPaid',
            'remainingAmount',
            'paymentProgress',
            'isFullyPaid'
        ));
    }

    /**
     * Show payment form for installment
     */
    public function create($sprId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $spr = SprDocument::findOrFail($sprId);

        if ($spr->user_id !== Auth::id()) {
            abort(403);
        }

        if ($spr->isFullyPaid()) {
            return back()->with('success', 'Pembayaran rumah sudah lunas.');
        }

        if ($spr->payment_scheme === 'mitra_pintar') {
            return redirect()->route('spr.show', $spr->id)
                ->with('info', 'Untuk skema Kredit Mitra Pintar (Bebas DP), pencairan pelunasan unit dilakukan langsung oleh Lembaga Mitra Pintar ke rekening pengembang.');
        }

        $remainingAmount = $spr->getSisaPembayaran();
        $lastInstallment = $spr->kprPayments()->where('payment_type', 'installment')->max('installment_number') ?? 0;

        $company = $spr->getCompany();
        $bankAccounts = $company ? $company->activeBankAccounts()->get() : collect();
        $overdueDetails = $spr->getOverduePenaltyDetails();
        $isDpPaid = $spr->isDpPaid();

        return view('kpr-payment.create', compact('spr', 'remainingAmount', 'lastInstallment', 'company', 'bankAccounts', 'overdueDetails', 'isDpPaid'));
    }

    /**
     * Process KPR payment
     */
    public function store(Request $request, $sprId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Check if user has unpaid unit selection (UTK belum lunas)
        $unpaidSelection = \App\Models\UnitSelection::where('user_id', Auth::id())
            ->whereIn('status', ['selected', 'confirmed'])
            ->get()
            ->first(function($sel) {
                return !$sel->isCash() && !$sel->isUtkWaived() && !$sel->isUtkPaid();
            });

        if ($unpaidSelection) {
            return redirect()->route('units.show', $unpaidSelection->id)
                ->with('error', 'Anda harus melunasi pembayaran UTK unit terpilih terlebih dahulu sebelum membayar uang muka atau cicilan rumah.');
        }

        $spr = SprDocument::findOrFail($sprId);

        if ($spr->user_id !== Auth::id()) {
            abort(403);
        }

        if ($spr->isFullyPaid()) {
            return back()->with('success', 'Pembayaran rumah sudah lunas.');
        }

        if ($spr->payment_scheme === 'mitra_pintar') {
            return redirect()->route('spr.show', $spr->id)
                ->with('info', 'Untuk skema Kredit Mitra Pintar (Bebas DP), pencairan pelunasan unit dilakukan langsung oleh Lembaga Mitra Pintar ke rekening pengembang.');
        }

        $isDpPaid = $spr->isDpPaid();
        if (!$isDpPaid && $request->payment_type !== 'dp') {
            return back()->with('error', 'Anda harus membayar dan melunasi Uang Muka (DP) terlebih dahulu sebelum dapat membayar cicilan atau pelunasan.');
        }

        $request->validate([
            'amount'                    => 'required|numeric|min:100000',
            'payment_type'              => 'required|in:dp,installment,full_payment',
            'payment_method'            => 'required|in:manual_transfer,transfer,cash',
            'developer_bank_account_id' => 'required_if:payment_method,manual_transfer|nullable|exists:developer_bank_accounts,id',
            'proof_of_transfer'         => 'required_if:payment_method,manual_transfer|nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'sender_bank'               => 'required_if:payment_method,manual_transfer|nullable|string|max:50',
            'sender_name'               => 'required_if:payment_method,manual_transfer|nullable|string|max:100',
            'transfer_date'             => 'required_if:payment_method,manual_transfer|nullable|date',
            'notes'                     => 'nullable|string|max:500',
        ], [
            'developer_bank_account_id.required_if' => 'Silakan pilih rekening bank tujuan transfer.',
            'proof_of_transfer.required_if'         => 'Wajib mengunggah foto / struk bukti transfer.',
            'sender_bank.required_if'               => 'Nama bank pengirim wajib diisi.',
            'sender_name.required_if'               => 'Nama pemilik rekening pengirim wajib diisi.',
            'transfer_date.required_if'             => 'Tanggal transfer wajib diisi.',
        ]);

        $amount = (float) $request->amount;
        $remainingAmount = $spr->getSisaPembayaran();

        // Validasi ketepatan nominal cicilan bulanan in-house agar user tidak bisa mengubah/mengurangi nominal
        if ($request->payment_type === 'installment' && in_array($spr->payment_scheme, ['inhouse', 'installment'], true)) {
            $expectedMonthly = (float) $spr->getMonthlyInstallmentAmount();
            if ($expectedMonthly > 0 && abs($amount - $expectedMonthly) > 1.0) {
                return back()->with('error', 'Nominal cicilan bulanan terkunci sebesar Rp ' . number_format($expectedMonthly, 0, ',', '.') . '. Nominal tidak boleh diubah manual.')
                    ->withInput();
            }
        }

        // Validasi ketepatan nominal DP agar user tidak bisa mengubah/mengurangi nominal DP
        if ($request->payment_type === 'dp') {
            $expectedDp = (float) $spr->down_payment;
            if ($expectedDp > 0 && abs($amount - $expectedDp) > 1.0) {
                return back()->with('error', 'Nominal Uang Muka (DP) terkunci sebesar Rp ' . number_format($expectedDp, 0, ',', '.') . '. Nominal tidak boleh diubah manual.')
                    ->withInput();
            }
        }

        // Validasi kebijakan developer jika skema Tunai Keras tidak mengizinkan termin bertahap
        if ($spr->payment_scheme === 'cash') {
            $project = $spr->getProject();
            $allowCashTermin = (bool) ($project?->cash_allow_installments ?? true);
            if ($request->payment_type === 'installment' && !$allowCashTermin) {
                return back()->with('error', 'Kebijakan pengembang untuk perumahan ini mewajibkan pelunasan sekaligus (pembayaran termin bertahap dinonaktifkan).')
                    ->withInput();
            }
        }

        // Hitung denda otomatis jika melewati tanggal jatuh tempo
        $overdue = $spr->getOverduePenaltyDetails($amount, $request->payment_type);
        $penaltyAmount = $overdue['is_overdue'] ? $overdue['penalty_amount'] : 0;
        $maxAllowed = $remainingAmount + $penaltyAmount;

        if ($amount > $maxAllowed) {
            return back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan yang harus dibayar (Rp ' . number_format($maxAllowed, 0, ',', '.') . ').');
        }

        try {
            $notes = $request->notes;
            if ($overdue['is_overdue'] && $penaltyAmount > 0) {
                $notes = trim("[Terlambat {$overdue['days_overdue']} hari - Denda otomatis: Rp " . number_format($penaltyAmount, 0, ',', '.') . "] " . ($notes ?? ''));
            }

            // 1. Handle Pembayaran Transfer Manual ke Rekening Pengembang
            if ($request->payment_method === 'manual_transfer') {
                $proofPath = null;
                if ($request->hasFile('proof_of_transfer')) {
                    $proofPath = $request->file('proof_of_transfer')->store('proofs/kpr', 'public');
                }

                $lastInstallment = $spr->kprPayments()->where('payment_type', 'installment')->max('installment_number') ?? 0;

                $payment = KprPayment::create([
                    'spr_document_id'           => $sprId,
                    'user_id'                   => Auth::id(),
                    'payment_number'            => KprPayment::generatePaymentNumber(),
                    'amount'                    => $amount,
                    'payment_type'              => $request->payment_type,
                    'installment_number'        => $request->payment_type === 'installment' ? ($lastInstallment + 1) : null,
                    'principal_amount'          => $request->payment_type === 'installment' ? ($amount * 0.7) : $amount,
                    'interest_amount'           => $request->payment_type === 'installment' ? ($amount * 0.3) : 0,
                    'penalty_amount'            => $penaltyAmount,
                    'payment_method'            => 'transfer',
                    'developer_bank_account_id' => $request->developer_bank_account_id,
                    'proof_of_transfer'         => $proofPath,
                    'sender_bank'               => strtoupper(trim($request->sender_bank)),
                    'sender_name'               => trim($request->sender_name),
                    'sender_account_number'     => trim($request->sender_account_number ?? ''),
                    'transfer_date'             => $request->transfer_date,
                    'due_date'                  => $overdue['due_date'],
                    'notes'                     => $notes,
                    'status'                    => 'pending',
                ]);

                return redirect()->route('kpr-payment.list')
                    ->with('success', 'Bukti transfer pembayaran berhasil diunggah! Menunggu verifikasi dari tim pengembang.');
            }

            // Create payment record untuk gateway
            $payment = KprPayment::create([
                'spr_document_id' => $sprId,
                'user_id' => Auth::id(),
                'payment_number' => KprPayment::generatePaymentNumber(),
                'amount' => $amount,
                'payment_type' => $request->payment_type,
                'payment_method' => $request->payment_method,
                'penalty_amount' => $penaltyAmount,
                'due_date' => $overdue['due_date'],
                'notes' => $notes,
                'status' => 'pending',
            ]);

            // If payment type is installment, calculate installment details
            if ($request->payment_type === 'installment') {
                $lastInstallment = $spr->kprPayments()->where('payment_type', 'installment')->max('installment_number') ?? 0;
                $payment->update([
                    'installment_number' => $lastInstallment + 1,
                    'principal_amount' => $amount * 0.7, // 70% principal
                    'interest_amount' => $amount * 0.3, // 30% interest
                ]);
            } else {
                $payment->update([
                    'principal_amount' => $amount,
                    'interest_amount'  => 0,
                ]);
            }

            // All customer payment options are created through DOKU Checkout.
            if (in_array($request->payment_method, ['transfer', 'cash'], true)) {
                $checkoutResult = $this->processDokuPayment($payment, $spr);

                if ($checkoutResult['success']) {
                    $payment->update([
                        'transaction_id' => $checkoutResult['transaction_id'] ?? null,
                        'payment_response' => $checkoutResult['response'] ?? null,
                    ]);

                    return redirect()->away($checkoutResult['checkout_url']);
                } else {
                    $payment->update(['status' => 'failed']);
                    return back()->with('error', 'Gagal memproses pembayaran DOKU: ' . ($checkoutResult['message'] ?? 'Unknown error'));
                }
            }

            $payment->update(['status' => 'failed']);
            return back()->with('error', 'Pilih Transfer atau Tunai melalui DOKU.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Show payment details
     */
    public function show($paymentId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $payment = KprPayment::with('sprDocument')->findOrFail($paymentId);

        if ($payment->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        // Admin otomatis diarahkan ke halaman admin untuk approve/reject
        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.kpr-payments.show', $paymentId);
        }

        return view('kpr-payment.show', compact('payment'));
    }

    /**
     * Mock payment success – hanya digunakan jika Midtrans belum dikonfigurasi
     * (fallback untuk environment development/local)
     */
    public function mockSuccess($paymentId)
    {
        $payment = KprPayment::with('sprDocument')->findOrFail($paymentId);

        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        // Hanya izinkan mock jika Midtrans belum dikonfigurasi
        if ($this->payment->isConfigured()) {
            return redirect()->route('kpr-payment.list')
                ->with('error', 'Fitur simulasi tidak tersedia di mode produksi.');
        }

        $payment->update([
            'status'            => 'success',
            'payment_date'      => now(),
            'remaining_principal' => $payment->sprDocument->getSisaPembayaran(),
        ]);

        if ($payment->sprDocument->getSisaPembayaran() <= 0) {
            $payment->sprDocument->update([
                'status' => 'paid',
                'paid_date' => now(),
            ]);

            if ($payment->sprDocument->unitSelection?->housingUnit) {
                $payment->sprDocument->unitSelection->housingUnit->update(['status' => 'sold']);
            }
        }

        return redirect()->route('kpr-payment.list')
            ->with('success', '✅ Pembayaran berhasil! Sisa: Rp ' . number_format($payment->sprDocument->getSisaPembayaran() - $payment->amount, 0, ',', '.'));
    }

    /**
     * Create a DOKU Checkout payment.
     */
    protected function processDokuPayment(KprPayment $payment, SprDocument $spr): array
    {
        try {
            $user = $spr->user;
            $checkout = $this->payment->createDokuCheckout(
                $payment->payment_number,
                (int) $payment->amount,
                $user,
                route('kpr-payment.show', $payment->id),
                $payment->payment_method
            );

            return [
                'success'        => true,
                'checkout_url'   => $checkout['checkout_url'],
                'transaction_id' => $checkout['request_id'],
                'response'       => $checkout['response'],
            ];

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DOKU KPR payment error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Admin: List all KPR payments
     */
    public function adminIndex()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $companyId = Auth::user()->getOperatorCompanyId();

        $paymentsQuery = KprPayment::with(['sprDocument', 'user', 'sprDocument.unitSelection.housingUnit'])
            ->orderBy('created_at', 'desc');

        if ($companyId) {
            $paymentsQuery->whereHas('sprDocument.unitSelection.housingUnit', function ($q) use ($companyId) {
                $q->forCompany($companyId);
            });
        }

        $payments = $paymentsQuery->paginate(20);

        // Calculate statistics untuk pembayaran Tunai Bertahap & Pelunasan Tunai ke Developer
        // (Dokumen KPR tidak dihitung di sini karena cicilan KPR dibayarkan langsung ke Bank)
        $sprsQuery = SprDocument::where('payment_scheme', '!=', 'kpr')
            ->whereIn('status', ['issued', 'paid'])
            ->with(['kprPayments', 'user', 'unitSelection.housingUnit']);

        if ($companyId) {
            $sprsQuery->whereHas('unitSelection.housingUnit', function ($q) use ($companyId) {
                $q->forCompany($companyId);
            });
        }

        $sprs = $sprsQuery->get();

        $totalBilled = $sprs->sum(function($spr) {
            return (float) ($spr->unit_price ?: $spr->house_price);
        });
        $totalPaid = $sprs->sum(function($spr) {
            return $spr->getTotalSudahDibayar();
        });
        $totalRemaining = max(0, $totalBilled - $totalPaid);
        $paymentProgress = $totalBilled > 0 ? ($totalPaid / $totalBilled) * 100 : 0;

        $stats = [
            'total_billed' => $totalBilled,
            'total_paid' => $totalPaid,
            'total_remaining' => $totalRemaining,
            'payment_progress' => $paymentProgress,
        ];

        // Build SPR summaries
        $sprSummaries = $sprs->map(function($spr) {
            return [
                'id' => $spr->id,
                'spr_number' => $spr->spr_number ?? ('SPR #' . $spr->id),
                'payment_scheme' => $spr->payment_scheme,
                'user_name' => $spr->user?->name ?? 'User',
                'unit_identifier' => $spr->unitSelection?->housingUnit?->full_identifier ?? '-',
                'total_price' => (float) ($spr->unit_price ?: $spr->house_price),
                'total_paid' => $spr->getTotalSudahDibayar(),
                'remaining' => $spr->getSisaPembayaran(),
                'progress' => $spr->getTotalPaymentProgress(),
                'is_fully_paid' => $spr->isFullyPaid(),
            ];
        });

        return view('admin.kpr-payments.index', compact('payments', 'stats', 'sprSummaries'));
    }

    /**
     * Admin: Show payment details
     */
    public function adminShow($paymentId)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $payment = KprPayment::with(['sprDocument', 'user', 'sprDocument.unitSelection.housingUnit.project'])
            ->findOrFail($paymentId);

        $companyId = Auth::user()->getOperatorCompanyId();
        if ($companyId && !$payment->sprDocument?->unitSelection?->housingUnit?->belongsToCompany($companyId)) {
            abort(403, 'Akses ditolak: Data pembayaran ini milik perusahaan developer lain.');
        }

        return view('admin.kpr-payments.show', compact('payment'));
    }

    /**
     * Admin: Approve payment
     */
    public function adminApprove(Request $request, $paymentId)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $payment = KprPayment::with('sprDocument.unitSelection.housingUnit.project')->findOrFail($paymentId);

        $companyId = Auth::user()->getOperatorCompanyId();
        if ($companyId && !$payment->sprDocument?->unitSelection?->housingUnit?->belongsToCompany($companyId)) {
            abort(403, 'Akses ditolak: Data pembayaran ini milik perusahaan developer lain.');
        }

        if ($payment->status === 'success') {
            return back()->with('error', 'Pembayaran sudah disetujui.');
        }

        $payment->update([
            'status' => 'success',
            'payment_date' => now(),
            'remaining_principal' => $payment->sprDocument->getRemainingAmount(),
        ]);

        // Otomatis terbitkan Dokumen SPR resmi (status: issued) jika statusnya masih draft
        if ($payment->sprDocument && $payment->sprDocument->status === 'draft') {
            $payment->sprDocument->update([
                'status'      => 'issued',
                'issued_date' => now(),
                'issued_by'   => Auth::user()->name ?? 'Admin Developer',
            ]);
        }

        // Check if SPR is now fully paid and update status
        if ($payment->sprDocument->getSisaPembayaran() <= 0) {
            $payment->sprDocument->update([
                'status' => 'paid',
                'paid_date' => now(),
            ]);

            // Update status unit rumah menjadi terjual (sold)
            if ($payment->sprDocument->unitSelection?->housingUnit) {
                $payment->sprDocument->unitSelection->housingUnit->update(['status' => 'sold']);
            }

            // Otomatis batalkan semua transaksi lain yang masih pending untuk SPR ini
            KprPayment::where('spr_document_id', $payment->spr_document_id)
                ->where('id', '!=', $payment->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled',
                    'notes' => 'Dibatalkan otomatis oleh sistem karena unit rumah telah dinyatakan LUNAS pada pembayaran #' . $payment->payment_number,
                ]);
        }

        return back()->with('success', 'Pembayaran berhasil disetujui.');
    }

    /**
     * Admin: Reject payment
     */
    public function adminReject(Request $request, $paymentId)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $payment = KprPayment::with('sprDocument.unitSelection.housingUnit.project')->findOrFail($paymentId);

        $companyId = Auth::user()->getOperatorCompanyId();
        if ($companyId && !$payment->sprDocument?->unitSelection?->housingUnit?->belongsToCompany($companyId)) {
            abort(403, 'Akses ditolak: Data pembayaran ini milik perusahaan developer lain.');
        }

        if ($payment->status === 'failed') {
            return back()->with('error', 'Pembayaran sudah ditolak.');
        }

        $payment->update([
            'status' => 'failed',
            'notes' => $request->notes ?? 'Ditolak oleh admin',
        ]);

        return back()->with('success', 'Pembayaran berhasil ditolak.');
    }

    /**
     * Handle Midtrans notification callback for KPR payments
     */
    public function handleNotification(Request $request)
    {
        try {
            $notification = new \Midtrans\Notification();

            $transactionId = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status;
            $paymentType = $notification->payment_type;

            $payment = KprPayment::where('payment_number', $transactionId)->first();

            if (!$payment) {
                \Illuminate\Support\Facades\Log::error('KPR Payment not found: ' . $transactionId);
                return response()->json(['status' => 'error', 'message' => 'Payment not found'], 404);
            }

            // Update payment status based on notification
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $payment->update([
                        'status' => 'pending',
                        'payment_response' => $request->all(),
                    ]);
                } else if ($fraudStatus == 'accept') {
                    $this->processSuccessfulPayment($payment, $request->all());
                }
            } else if ($transactionStatus == 'settlement') {
                $this->processSuccessfulPayment($payment, $request->all());
            } else if ($transactionStatus == 'cancel') {
                $payment->update([
                    'status' => 'failed',
                    'payment_response' => $request->all(),
                ]);
            } else if ($transactionStatus == 'deny') {
                $payment->update([
                    'status' => 'failed',
                    'payment_response' => $request->all(),
                ]);
            } else if ($transactionStatus == 'expire') {
                $payment->update([
                    'status' => 'expired',
                    'payment_response' => $request->all(),
                ]);
            } else if ($transactionStatus == 'pending') {
                $payment->update([
                    'status' => 'pending',
                    'payment_response' => $request->all(),
                ]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('KPR Payment notification error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Process successful KPR payment
     */
    protected function processSuccessfulPayment(KprPayment $payment, array $notificationData): void
    {
        DB::transaction(function () use ($payment, $notificationData) {
            $payment->refresh();
            if ($payment->status === 'success') {
                return;
            }

            if (!$payment->sprDocument) {
                $payment->update([
                    'status' => 'success',
                    'payment_date' => now(),
                    'payment_response' => $notificationData,
                ]);
                return;
            }

            $payment->update([
                'status' => 'success',
                'payment_date' => now(),
                'payment_response' => $notificationData,
                'remaining_principal' => $payment->sprDocument->getRemainingAmount(),
            ]);

            // Check if SPR is now fully paid and update status
            if ($payment->sprDocument->getSisaPembayaran() <= 0) {
                $payment->sprDocument->update([
                    'status' => 'paid',
                    'paid_date' => now(),
                ]);

                // Update status unit rumah menjadi terjual (sold)
                if ($payment->sprDocument->unitSelection?->housingUnit) {
                    $payment->sprDocument->unitSelection->housingUnit->update(['status' => 'sold']);
                }

                // Otomatis batalkan semua transaksi lain yang masih pending untuk SPR ini
                KprPayment::where('spr_document_id', $payment->spr_document_id)
                    ->where('id', '!=', $payment->id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'cancelled',
                        'notes' => 'Dibatalkan otomatis oleh sistem karena unit rumah telah dinyatakan LUNAS pada pembayaran #' . $payment->payment_number,
                    ]);
            }
        });
    }

    public function handleDokuNotification(array $data): bool
    {
        $payment = KprPayment::with('sprDocument')->where(
            'payment_number', data_get($data, 'order.invoice_number')
        )->first();
        if (!$payment || (int) data_get($data, 'order.amount') !== (int) $payment->amount) {
            return false;
        }

        $status = strtoupper((string) data_get($data, 'transaction.status'));
        if ($status === 'SUCCESS' && $payment->status !== 'success') {
            $this->processSuccessfulPayment($payment, $data);
        } elseif ($payment->status === 'pending' && in_array($status, ['FAILED', 'EXPIRED', 'CANCELLED', 'TIMEOUT'], true)) {
            $payment->update(['status' => 'failed', 'payment_response' => $data]);
        }
        return true;
    }
}
