<?php

namespace App\Http\Controllers;

use App\Models\UtkPayment;
use App\Models\UnitSelection;
use App\Models\NupRegistration;
use App\Models\PromoCode;
use App\Services\PaymentGatewayService;
use App\Services\WhatsAppGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UtkPaymentController extends Controller
{
    protected $paymentService;
    protected $whatsappService;

    public function __construct(PaymentGatewayService $paymentService, WhatsAppGatewayService $whatsappService)
    {
        $this->paymentService = $paymentService;
        $this->whatsappService = $whatsappService;
    }

    /**
     * Create UTK payment
     */
    public function create($unitSelectionId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $unitSelection = UnitSelection::with(['nupRegistration', 'housingUnit'])->findOrFail($unitSelectionId);

        if ($unitSelection->user_id !== Auth::id()) {
            abort(403);
        }

        // Jika skema Tunai: Bebas UTK (UTK dihapus)
        if ($unitSelection->isCash() || $unitSelection->isUtkWaived()) {
            return redirect()->route('units.show', $unitSelection->id)
                ->with('info', 'Khusus skema pembayaran Tunai, biaya UTK dihapuskan (Rp 0). Anda dapat langsung melanjutkan ke pembuatan SPR.');
        }

        // Jika skema Kredit Mitra Pintar: pastikan dokumen kerja sama telah disetujui operator
        if ($unitSelection->isMitraPintar() && !$unitSelection->isMitraApproved()) {
            return redirect()->route('units.show', $unitSelection->id)
                ->with('error', 'Dokumen kerja sama Mitra Pintar Anda masih menunggu verifikasi dari Operator. Pembayaran UTK baru dibuka setelah dokumen disetujui.');
        }

        // Jika skema KPR: pastikan berkas KPR sudah diajukan dan hasil verifikasi skor kredit (Pefindo/Bank) dinyatakan LAYAK
        if ($unitSelection->isKpr()) {
            $screening = $unitSelection->kprScreening ?? $unitSelection->nupRegistration?->kprScreening;
            if (!$screening) {
                return redirect()->route('kpr.create', $unitSelection->id)
                    ->with('info', 'Silakan lengkapi data pekerjaan dan upload berkas persyaratan KPR terlebih dahulu sebelum pembayaran UTK.');
            }
            if (!in_array($screening->status, ['approved', 'layak_bersyarat'], true)) {
                return redirect()->route('units.show', $unitSelection->id)
                    ->with('error', 'Pembayaran UTK hanya dapat dilakukan setelah hasil evaluasi skor kredit KPR (Pefindo/Bank) diverifikasi dan dinyatakan LAYAK.');
            }
        }

        if ($unitSelection->isUtkPaid()) {
            return back()->with('error', 'UTK sudah dibayar.');
        }

        if ($unitSelection->isUtkPaymentDeadlinePassed()) {
            return back()->with('error', 'Batas waktu pembayaran UTK telah berakhir.');
        }

        // Hitung rincian UTK: Pokok (diisi operator) + PPN 11% + VE 20%
        $housingProject = $unitSelection->housingUnit->housingProject;
        $baseUtk = (float) ($unitSelection->utk_base_amount ?? ($housingProject ? $housingProject->getUtkBaseAmount() : 5000000));
        $ppnUtk = (float) ($unitSelection->utk_ppn_amount ?? ($housingProject ? $housingProject->getUtkPpnAmount() : round($baseUtk * 0.11, 2)));
        $veUtk = (float) ($unitSelection->utk_ve_amount ?? ($housingProject ? $housingProject->getUtkVeAmount() : round($baseUtk * 0.20, 2)));
        $totalUtk = (float) ($unitSelection->utk_amount ?? ($housingProject ? $housingProject->getTotalUtkAmount() : ($baseUtk + $ppnUtk + $veUtk)));
        $utkAmount = $totalUtk;

        $company = $unitSelection->getCompany() ?? $housingProject?->company;
        $bankAccounts = $company ? $company->activeBankAccounts()->get() : collect();

        return view('utk.create', compact('unitSelection', 'utkAmount', 'baseUtk', 'ppnUtk', 'veUtk', 'totalUtk', 'company', 'bankAccounts'));
    }

    /**
     * Process UTK payment
     */
    public function process(Request $request, $unitSelectionId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $unitSelection = UnitSelection::with(['nupRegistration', 'housingUnit'])->findOrFail($unitSelectionId);

        if ($unitSelection->user_id !== Auth::id()) {
            abort(403);
        }

        // Jika skema Tunai: Bebas UTK
        if ($unitSelection->isCash() || $unitSelection->isUtkWaived()) {
            return redirect()->route('units.show', $unitSelection->id)
                ->with('info', 'Khusus skema pembayaran Tunai, biaya UTK dihapuskan (Rp 0). Anda dapat langsung melanjutkan ke pembuatan SPR.');
        }

        // Jika skema Kredit Mitra Pintar: pastikan dokumen kerja sama telah disetujui operator
        if ($unitSelection->isMitraPintar() && !$unitSelection->isMitraApproved()) {
            return redirect()->route('units.show', $unitSelection->id)
                ->with('error', 'Dokumen kerja sama Mitra Pintar Anda belum disetujui oleh Operator.');
        }

        // Jika skema KPR: pastikan sudah lolos verifikasi Pefindo/Bank
        if ($unitSelection->isKpr()) {
            $screening = $unitSelection->kprScreening ?? $unitSelection->nupRegistration?->kprScreening;
            if (!$screening || !in_array($screening->status, ['approved', 'layak_bersyarat'], true)) {
                return redirect()->route('units.show', $unitSelection->id)
                    ->with('error', 'Pembayaran UTK hanya dapat diproses setelah hasil evaluasi skor kredit KPR (Pefindo/Bank) Anda dinyatakan LAYAK.');
            }
        }

        // Sync UTK payment status first to verify if paid in background
        if ($unitSelection->utk_payment_status === 'pending') {
            $pendingUtkPayments = \App\Models\UtkPayment::where('unit_selection_id', $unitSelection->id)
                ->where('status', 'pending')
                ->get();

            foreach ($pendingUtkPayments as $utkPayment) {
                $isPaid = $this->paymentService->syncUtkPaymentStatus($utkPayment);
                if ($isPaid) {
                    $unitSelection->refresh();
                    break;
                }
            }
        }

        if ($unitSelection->isUtkPaid()) {
            return redirect()->route('units.show', $unitSelection->id)
                ->with('success', 'Pembayaran UTK Anda telah berhasil terverifikasi!');
        }

        $validator = Validator::make($request->all(), [
            'payment_method'            => 'required|in:manual_transfer,transfer,cash',
            'developer_bank_account_id' => 'required_if:payment_method,manual_transfer|nullable|exists:developer_bank_accounts,id',
            'proof_of_transfer'         => 'required_if:payment_method,manual_transfer|nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'sender_bank'               => 'required_if:payment_method,manual_transfer|nullable|string|max:50',
            'sender_name'               => 'required_if:payment_method,manual_transfer|nullable|string|max:100',
            'sender_account_number'     => 'nullable|string|max:50',
            'transfer_date'             => 'required_if:payment_method,manual_transfer|nullable|date',
            'promo_code'                => 'nullable|string|max:50',
        ], [
            'developer_bank_account_id.required_if' => 'Silakan pilih rekening bank tujuan transfer.',
            'proof_of_transfer.required_if'         => 'Wajib mengunggah foto/struk bukti transfer.',
            'sender_bank.required_if'               => 'Nama bank pengirim wajib diisi.',
            'sender_name.required_if'               => 'Nama pemilik rekening pengirim wajib diisi.',
            'transfer_date.required_if'             => 'Tanggal transfer wajib diisi.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $promoReserved = false;
        $promo = null;

        try {
            // Hitung UTK: Pokok (diatur operator) + PPN 11% + VE 20%
            $housingProject = $unitSelection->housingUnit->housingProject;
            $baseUtk = (float) ($unitSelection->utk_base_amount ?? ($housingProject ? $housingProject->getUtkBaseAmount() : 5000000));
            $ppnUtk = (float) ($unitSelection->utk_ppn_amount ?? ($housingProject ? $housingProject->getUtkPpnAmount() : round($baseUtk * 0.11, 2)));
            $veUtk = (float) ($unitSelection->utk_ve_amount ?? ($housingProject ? $housingProject->getUtkVeAmount() : round($baseUtk * 0.20, 2)));
            $utkAmount = (float) ($unitSelection->utk_amount ?? ($housingProject ? $housingProject->getTotalUtkAmount() : ($baseUtk + $ppnUtk + $veUtk)));

            $amount = (int) $utkAmount;

            // 1. Handle Pembayaran Transfer Manual ke Rekening Pengembang
            if ($request->payment_method === 'manual_transfer') {
                $proofPath = null;
                if ($request->hasFile('proof_of_transfer')) {
                    $proofPath = $request->file('proof_of_transfer')->store('proofs/utk', 'public');
                }

                // Cek apakah sudah ada pengajuan transfer manual pending untuk unit ini
                $existingPending = UtkPayment::where('unit_selection_id', $unitSelection->id)
                    ->where('status', 'pending')
                    ->where('payment_method', 'bank_transfer')
                    ->first();

                if ($existingPending) {
                    // Perbarui pengajuan yang ada alih-alih membuat nomor tagihan duplikat baru
                    $existingPending->update([
                        'amount'                    => $amount,
                        'utk_base_amount'           => $baseUtk,
                        'utk_ppn_amount'            => $ppnUtk,
                        'utk_ve_amount'             => $veUtk,
                        'developer_bank_account_id' => $request->developer_bank_account_id,
                        'proof_of_transfer'         => $proofPath ?? $existingPending->proof_of_transfer,
                        'sender_bank'               => strtoupper(trim($request->sender_bank)),
                        'sender_name'               => trim($request->sender_name),
                        'sender_account_number'     => trim($request->sender_account_number ?? ''),
                        'transfer_date'             => $request->transfer_date,
                    ]);

                    $unitSelection->update([
                        'utk_amount'         => $amount,
                        'utk_payment_status' => 'pending',
                    ]);

                    return redirect()->route('units.show', $unitSelection->id)
                        ->with('success', 'Bukti transfer UTK berhasil diperbarui! Menunggu verifikasi dari pengembang.');
                }

                $utkPayment = UtkPayment::create([
                    'unit_selection_id'         => $unitSelection->id,
                    'nup_registration_id'       => $unitSelection->nup_registration_id,
                    'user_id'                   => Auth::id(),
                    'payment_number'            => UtkPayment::generatePaymentNumber(),
                    'amount'                    => $amount,
                    'utk_base_amount'           => $baseUtk,
                    'utk_ppn_amount'            => $ppnUtk,
                    'utk_ve_amount'             => $veUtk,
                    'payment_method'            => 'bank_transfer',
                    'developer_bank_account_id' => $request->developer_bank_account_id,
                    'proof_of_transfer'         => $proofPath,
                    'sender_bank'               => strtoupper(trim($request->sender_bank)),
                    'sender_name'               => trim($request->sender_name),
                    'sender_account_number'     => trim($request->sender_account_number ?? ''),
                    'transfer_date'             => $request->transfer_date,
                    'status'                    => 'pending',
                    'expired_at'                => now()->addHours(48),
                ]);

                $unitSelection->update([
                    'utk_amount'         => $amount,
                    'utk_payment_status' => 'pending',
                ]);

                return redirect()->route('units.show', $unitSelection->id)
                    ->with('success', 'Bukti transfer UTK berhasil dikirim! Menunggu verifikasi dari pengembang.');
            }
            $promoCode = strtoupper(trim((string) $request->input('promo_code')));

            if ($request->payment_method === 'cash' && $promoCode !== '') {
                return back()->with('error', 'Kode promo hanya berlaku untuk pembayaran transfer melalui DOKU.')->withInput();
            }

            if ($request->payment_method === 'transfer' && $promoCode !== '') {
                $promo = PromoCode::query()
                    ->whereRaw('UPPER(code) = ?', [$promoCode])
                    ->where('is_active', true)
                    ->where(function ($query) {
                        $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->first();

                if (!$promo) {
                    return back()->with('error', 'Kode promo tidak valid, tidak aktif, atau sudah kedaluwarsa.')->withInput();
                }
                if (!in_array($promo->applicable_to ?? 'all', ['all', 'utk'], true)) {
                    return back()->with('error', 'Kode promo ini tidak berlaku untuk pembayaran UTK.')->withInput();
                }
                if ($promo->allowed_email && strcasecmp($promo->allowed_email, (string) Auth::user()->email) !== 0) {
                    return back()->with('error', 'Kode promo ini tidak berlaku untuk akun Anda.')->withInput();
                }
                if ($promo->payable_amount > $amount) {
                    return back()->with('error', 'Nominal promo tidak dapat melebihi biaya UTK.')->withInput();
                }

                $reserved = PromoCode::whereKey($promo->id)
                    ->where('is_active', true)
                    ->whereColumn('used_count', '<', 'usage_limit')
                    ->increment('used_count');
                if (!$reserved) {
                    return back()->with('error', 'Kuota kode promo sudah habis.')->withInput();
                }
                $promoReserved = true;
                $amount = $promo->payable_amount;
            }

            // Create UTK payment record dengan rincian Pokok, PPN 11%, VE 20%
            $utkPayment = UtkPayment::create([
                'unit_selection_id' => $unitSelection->id,
                'nup_registration_id' => $unitSelection->nup_registration_id,
                'user_id' => Auth::id(),
                'payment_number' => UtkPayment::generatePaymentNumber(),
                'amount' => $amount,
                'utk_base_amount' => $baseUtk,
                'utk_ppn_amount' => $ppnUtk,
                'utk_ve_amount' => $veUtk,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'metadata' => $promo ? [
                    'promo_code' => $promo->code,
                    'original_amount' => (int) $utkAmount,
                    'discounted_amount' => $amount,
                    'utk_base' => $baseUtk,
                    'utk_ppn_11' => $ppnUtk,
                    'utk_ve_20' => $veUtk,
                ] : [
                    'utk_base' => $baseUtk,
                    'utk_ppn_11' => $ppnUtk,
                    'utk_ve_20' => $veUtk,
                ],
                'expired_at' => now()->addHours(24),
            ]);

            // Process payment through gateway
            $result = $this->paymentService->createUtkPayment($utkPayment, $request->payment_method);

            if ($result['success']) {
                // Update unit selection with UTK amount
                $unitSelection->update(['utk_amount' => $amount]);

                if (!empty($result['free_payment'])) {
                    return redirect()->route('utk.show', $utkPayment->id)
                        ->with('success', 'Kode promo Rp 0 berhasil digunakan! Pembayaran UTK lunas.');
                }

                // Tunai di kantor: redirect ke halaman detail pembayaran
                if (!empty($result['cash_payment'])) {
                    return redirect()->route('utk.show', $utkPayment->id)
                        ->with('success', 'Pembayaran UTK tunai berhasil dicatat. Silakan datang ke kantor untuk melakukan pembayaran. Admin akan mengkonfirmasi setelah pembayaran diterima.');
                }

                // Check if it's mock mode
                if (isset($result['mock_mode']) && $result['mock_mode']) {
                    return view('utk.payment-mock', [
                        'transaction' => $result['transaction'],
                        'utkPayment' => $utkPayment,
                        'unitSelection' => $unitSelection,
                        'message' => $result['message'] ?? '',
                    ]);
                }

                return redirect()->away($result['checkout_url']);

            } else {
                if ($promoReserved && $promo) {
                    PromoCode::whereKey($promo->id)->where('used_count', '>', 0)->decrement('used_count');
                }
                return back()->with('error', $result['message'])->withInput();
            }

        } catch (\Exception $e) {
            if ($promoReserved && $promo) {
                PromoCode::whereKey($promo->id)->where('used_count', '>', 0)->decrement('used_count');
            }
            return back()->with('error', 'Gagal memproses pembayaran UTK: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Process mock UTK payment success
     */
    public function mockPaymentSuccess(Request $request, $utkPaymentId)
    {
        if ($this->paymentService->isConfigured()) {
            return redirect()->route('utk.show', $utkPaymentId)->with('error', 'Pembayaran simulasi dinonaktifkan saat DOKU aktif.');
        }
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $utkPayment = UtkPayment::findOrFail($utkPaymentId);

        if ($utkPayment->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            // Use payment_number from utkPayment if request payment_number is null
            $paymentNumber = $request->payment_number ?? $utkPayment->payment_number;
            
            if (!$paymentNumber) {
                return back()->with('error', 'Payment number tidak ditemukan.');
            }

            $result = $this->paymentService->processUtkMockPaymentSuccess($paymentNumber);

            if ($result['success']) {
                // Update UTK payment status
                $utkPayment->update([
                    'status' => 'paid',
                    'payment_date' => now(),
                    'payment_gateway_reference' => $result['reference'] ?? 'MOCK-' . time(),
                ]);

                // Update unit selection
                $unitSelection = $utkPayment->unitSelection;
                $unitSelection->update([
                    'status'                 => 'confirmed',
                    'utk_payment_status'     => 'paid',
                    'utk_payment_date'       => now(),
                    'utk_payment_reference'  => $result['reference'] ?? 'MOCK-' . time(),
                ]);

                // Send WhatsApp notification
                $this->whatsappService->sendUtkPaymentNotification(
                    $utkPayment->user,
                    $unitSelection->housingUnit->unit_code,
                    $utkPayment->amount
                );

                return redirect()->route('units.show', $unitSelection->id)
                    ->with('success', 'Pembayaran UTK berhasil diproses!');

            } else {
                return back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses mock payment: ' . $e->getMessage());
        }
    }

    /**
     * Show UTK payment details
     */
    public function show($utkPaymentId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $utkPayment = UtkPayment::with(['unitSelection', 'unitSelection.housingUnit.project', 'user'])->findOrFail($utkPaymentId);

        if ($utkPayment->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($utkPayment->user_id !== Auth::id()) {
            $companyId = Auth::user()->getOperatorCompanyId();
            if ($companyId && !$utkPayment->unitSelection?->housingUnit?->belongsToCompany($companyId)) {
                abort(403, 'Akses ditolak: Data UTK ini milik perusahaan developer lain.');
            }
        }

        // Jika masih pending & menggunakan transfer DOKU, aktif cek status ke DOKU API.
        // Ini menangani kasus di mana webhook belum/terlambat diterima saat user kembali
        // dari halaman checkout DOKU.
        if ($utkPayment->status === 'pending' && $utkPayment->payment_method === 'transfer') {
            $isPaid = $this->paymentService->syncUtkPaymentStatus($utkPayment);
            if ($isPaid) {
                $utkPayment->refresh();
            }
        }

        // Jika sudah lunas, langsung arahkan ke halaman detail unit untuk pilih skema rumah
        if ($utkPayment->status === 'paid') {
            return redirect()->route('units.show', $utkPayment->unitSelection->id)
                ->with('success', '🎉 Selamat! Pembayaran UTK Anda telah berhasil terverifikasi. Silakan pilih skema pembayaran rumah.');
        }

        return view('utk.show', compact('utkPayment'));
    }

    /**
     * Payment callback
     */
    public function paymentCallback(Request $request)
    {
        try {
            if (!app(\App\Services\DokuCheckoutService::class)->verifyNotification(
                $request->getContent(), [
                    'client-id' => $request->header('Client-Id'),
                    'request-id' => $request->header('Request-Id'),
                    'request-timestamp' => $request->header('Request-Timestamp'),
                    'signature' => $request->header('Signature'),
                ], '/utk/callback'
            )) {
                return response()->json(['status' => 'invalid signature'], 401);
            }
            $notificationData = $request->all();
            $success = $this->paymentService->handleUtkNotification($notificationData);

            if ($success) {
                return response()->json(['status' => 'success']);
            } else {
                return response()->json(['status' => 'failed'], 400);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin index
     */
    public function adminIndex()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $companyId = Auth::user()->getOperatorCompanyId();

        $utkQuery = UtkPayment::with(['unitSelection.housingUnit', 'user', 'nupRegistration'])
            ->orderBy('created_at', 'desc');

        if ($companyId) {
            $utkQuery->whereHas('unitSelection.housingUnit', function ($q) use ($companyId) {
                $q->forCompany($companyId);
            });
        }

        $utkPayments = $utkQuery->paginate(20);

        return view('admin.utk.index', compact('utkPayments'));
    }

    /**
     * Admin: Konfirmasi pembayaran tunai UTK di kantor
     */
    public function confirmCash($utkPaymentId)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $utkPayment = UtkPayment::with(['unitSelection.housingUnit.project', 'user'])->findOrFail($utkPaymentId);

        $companyId = Auth::user()->getOperatorCompanyId();
        if ($companyId && !$utkPayment->unitSelection?->housingUnit?->belongsToCompany($companyId)) {
            abort(403, 'Akses ditolak: Data UTK ini milik perusahaan developer lain.');
        }

        if ($utkPayment->payment_method !== 'cash' || $utkPayment->status !== 'pending') {
            return back()->with('error', 'Pembayaran tidak valid atau sudah diproses.');
        }

        if ($utkPayment->unitSelection && $utkPayment->unitSelection->status === 'cancelled') {
            return back()->with('error', 'Gagal: Pemilihan unit ini sudah dibatalkan oleh pembeli.');
        }

        $ref = 'CASH-OFFICE-' . now()->format('YmdHis');

        $utkPayment->update([
            'status'                    => 'paid',
            'payment_date'              => now(),
            'payment_gateway_reference' => $ref,
            'payment_gateway_response'  => [
                'method'        => 'cash_office',
                'confirmed_by'  => Auth::id(),
                'confirmed_at'  => now()->toIso8601String(),
            ],
        ]);

        // Update unit selection
        $unitSelection = $utkPayment->unitSelection;
        if ($unitSelection) {
            $unitSelection->update([
                'status'                => 'confirmed',
                'utk_payment_status'    => 'paid',
                'utk_payment_date'      => now(),
                'utk_payment_reference' => $ref,
            ]);

            // Batalkan otomatis semua tagihan UTK lain milik unit ini yang masih pending
            $otherPendingUtks = UtkPayment::where('unit_selection_id', $unitSelection->id)
                ->where('id', '!=', $utkPayment->id)
                ->where('status', 'pending')
                ->get();

            foreach ($otherPendingUtks as $otherUtk) {
                $otherUtk->update([
                    'status'                   => 'cancelled',
                    'payment_gateway_response' => [
                        'reason'            => 'Dibatalkan otomatis oleh sistem karena transaksi ' . $utkPayment->payment_number . ' telah disetujui lunas.',
                        'auto_cancelled_at' => now()->toIso8601String(),
                    ],
                ]);
            }

            // Kirim notif WhatsApp
            $this->whatsappService->sendUtkPaymentNotification(
                $utkPayment->user,
                $unitSelection->housingUnit->unit_code ?? '-',
                $utkPayment->amount
            );
        }

        return back()->with('success', 'Pembayaran UTK tunai berhasil dikonfirmasi.');
    }

    /**
     * Admin: Tolak pembayaran tunai UTK
     */
    public function rejectCash(Request $request, $utkPaymentId)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $utkPayment = UtkPayment::with('unitSelection.housingUnit.project')->findOrFail($utkPaymentId);

        $companyId = Auth::user()->getOperatorCompanyId();
        if ($companyId && !$utkPayment->unitSelection?->housingUnit?->belongsToCompany($companyId)) {
            abort(403, 'Akses ditolak: Data UTK ini milik perusahaan developer lain.');
        }

        if ($utkPayment->payment_method !== 'cash' || $utkPayment->status !== 'pending') {
            return back()->with('error', 'Pembayaran tidak valid atau sudah diproses.');
        }

        if ($utkPayment->unitSelection && $utkPayment->unitSelection->status === 'cancelled') {
            return back()->with('error', 'Gagal: Pemilihan unit ini sudah dibatalkan oleh pembeli.');
        }

        $utkPayment->update([
            'status'                   => 'failed',
            'payment_gateway_response' => [
                'method'       => 'cash_office',
                'rejected_by'  => Auth::id(),
                'rejected_at'  => now()->toIso8601String(),
                'reason'       => $request->input('reason', 'Ditolak oleh admin'),
            ],
        ]);

        if ($utkPayment->metadata && isset($utkPayment->metadata['promo_code'])) {
            $code = $utkPayment->metadata['promo_code'];
            PromoCode::where('code', $code)->where('used_count', '>', 0)->decrement('used_count');
        }

        return back()->with('success', 'Pembayaran UTK tunai berhasil ditolak.');
    }

    /**
     * Admin: Setujui pembayaran transfer bank UTK
     */
    public function approveTransfer($utkPaymentId)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $utkPayment = UtkPayment::with(['unitSelection.housingUnit.project', 'user', 'developerBankAccount'])->findOrFail($utkPaymentId);

        $companyId = Auth::user()->getOperatorCompanyId();
        if ($companyId && !$utkPayment->unitSelection?->housingUnit?->belongsToCompany($companyId)) {
            abort(403, 'Akses ditolak: Data UTK ini milik perusahaan developer lain.');
        }

        if ($utkPayment->status !== 'pending') {
            return back()->with('error', 'Pembayaran tidak valid atau sudah diproses.');
        }

        if ($utkPayment->unitSelection && $utkPayment->unitSelection->status === 'cancelled') {
            return back()->with('error', 'Gagal: Pemilihan unit ini sudah dibatalkan oleh pembeli.');
        }

        $bank = $utkPayment->developerBankAccount ? $utkPayment->developerBankAccount->bank_name : 'TRANSFER';
        $ref = 'TRF-' . $bank . '-' . now()->format('YmdHis');

        $utkPayment->update([
            'status'                    => 'paid',
            'payment_date'              => now(),
            'payment_gateway_reference' => $ref,
            'payment_gateway_response'  => [
                'method'        => 'manual_transfer',
                'confirmed_by'  => Auth::id(),
                'confirmed_at'  => now()->toIso8601String(),
            ],
        ]);

        $unitSelection = $utkPayment->unitSelection;
        if ($unitSelection) {
            $unitSelection->update([
                'status'                => 'confirmed',
                'utk_payment_status'    => 'paid',
                'utk_payment_date'      => now(),
                'utk_payment_reference' => $ref,
            ]);

            // Batalkan otomatis semua tagihan UTK lain milik unit ini yang masih pending
            $otherPendingUtks = UtkPayment::where('unit_selection_id', $unitSelection->id)
                ->where('id', '!=', $utkPayment->id)
                ->where('status', 'pending')
                ->get();

            foreach ($otherPendingUtks as $otherUtk) {
                $otherUtk->update([
                    'status'                   => 'cancelled',
                    'payment_gateway_response' => [
                        'reason'            => 'Dibatalkan otomatis oleh sistem karena transaksi ' . $utkPayment->payment_number . ' telah disetujui lunas.',
                        'auto_cancelled_at' => now()->toIso8601String(),
                    ],
                ]);
            }

            $this->whatsappService->sendUtkPaymentNotification(
                $utkPayment->user,
                $unitSelection->housingUnit->unit_code ?? '-',
                $utkPayment->amount
            );
        }

        return back()->with('success', 'Pembayaran transfer bank UTK berhasil disetujui!');
    }

    /**
     * Admin: Tolak pembayaran transfer bank UTK
     */
    public function rejectTransfer(Request $request, $utkPaymentId)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $utkPayment = UtkPayment::with('unitSelection.housingUnit.project')->findOrFail($utkPaymentId);

        $companyId = Auth::user()->getOperatorCompanyId();
        if ($companyId && !$utkPayment->unitSelection?->housingUnit?->belongsToCompany($companyId)) {
            abort(403, 'Akses ditolak: Data UTK ini milik perusahaan developer lain.');
        }

        if ($utkPayment->status !== 'pending') {
            return back()->with('error', 'Pembayaran tidak valid atau sudah diproses.');
        }

        if ($utkPayment->unitSelection && $utkPayment->unitSelection->status === 'cancelled') {
            return back()->with('error', 'Gagal: Pemilihan unit ini sudah dibatalkan oleh pembeli.');
        }

        $utkPayment->update([
            'status'                   => 'failed',
            'payment_gateway_response' => [
                'method'       => 'manual_transfer',
                'rejected_by'  => Auth::id(),
                'rejected_at'  => now()->toIso8601String(),
                'reason'       => $request->input('reason', 'Bukti transfer tidak valid atau dana belum masuk'),
            ],
        ]);

        return back()->with('success', 'Pembayaran transfer UTK berhasil ditolak.');
    }

    /**
     * Create UTK payment from screening
     */
    public function createFromScreening($screeningId)

    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = \App\Models\KprScreening::with(['unitSelection', 'nupRegistration'])->findOrFail($screeningId);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        if ($screening->status !== 'utk_pending') {
            return back()->with('error', 'Screening tidak dalam status pembayaran UTK.');
        }

        if ($screening->isUtkExpired()) {
            return back()->with('error', 'Batas waktu pembayaran UTK telah berakhir.');
        }

        $utkAmount = config('nup.utk_amount', 5000000);

        // Ensure amount is not null
        if (!$utkAmount || $utkAmount <= 0) {
            $utkAmount = 5000000; // Default 5 juta
        }

        return view('utk.create-from-screening', compact('screening', 'utkAmount'));
    }

    /**
     * Process UTK payment from screening
     */
    public function processFromScreening(Request $request, $screeningId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = \App\Models\KprScreening::with(['unitSelection', 'nupRegistration'])->findOrFail($screeningId);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        if ($screening->status !== 'utk_pending') {
            return back()->with('error', 'Screening tidak dalam status pembayaran UTK.');
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:cash,transfer',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $utkAmount = config('nup.utk_amount', 5000000);

            // Ensure amount is not null
            if (!$utkAmount || $utkAmount <= 0) {
                $utkAmount = 5000000; // Default 5 juta
            }

            // Create UTK payment record
            $utkPayment = UtkPayment::create([
                'unit_selection_id' => $screening->unit_selection_id,
                'nup_registration_id' => $screening->nup_registration_id,
                'user_id' => Auth::id(),
                'payment_number' => UtkPayment::generatePaymentNumber(),
                'amount' => $utkAmount,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'expired_at' => now()->addHours(24),
            ]);

            // Process payment through gateway
            $result = $this->paymentService->createUtkPayment($utkPayment, $request->payment_method);

            if ($result['success']) {
                // Check if it's mock mode
                if (isset($result['mock_mode']) && $result['mock_mode']) {
                    return view('utk.payment-mock', [
                        'transaction' => $result['transaction'],
                        'utkPayment' => $utkPayment,
                        'screening' => $screening,
                        'message' => $result['message'] ?? '',
                    ]);
                }

                return redirect()->away($result['checkout_url']);
            } else {
                return back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses pembayaran UTK: ' . $e->getMessage());
        }
    }
}
