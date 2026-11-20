<?php

namespace App\Http\Controllers;

use App\Models\SprDocument;
use App\Models\UnitSelection;
use App\Services\WhatsAppGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class SprDocumentController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppGatewayService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Create SPR form (Participant / Redirect)
     */
    public function create($unitSelectionId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.spr.create', $unitSelectionId);
        }

        $unitSelection = UnitSelection::with(['housingUnit', 'nupRegistration', 'kprScreening', 'sprDocuments'])
            ->findOrFail($unitSelectionId);

        if ($unitSelection->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if SPR already exists
        $existingSpr = $unitSelection->sprDocuments()->whereNotIn('status', ['cancelled'])->first();
        if ($existingSpr) {
            return redirect()->route('spr.show', $existingSpr->id);
        }

        // Model Opsi A: Operator yang menerbitkan SPR
        return redirect()->route('units.show', $unitSelection->id)
            ->with('info', 'Uang Tanda Komitmen (UTK) Anda telah tercatat. Tim pengembang sedang menyiapkan Surat Pemesanan Rumah (SPR) resmi. Anda akan mendapatkan notifikasi setelah dokumen siap ditinjau.');
    }

    /**
     * Store SPR data
     */
    public function store(Request $request, $unitSelectionId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $unitSelection = UnitSelection::with(['housingUnit', 'nupRegistration'])
            ->findOrFail($unitSelectionId);

        if ($unitSelection->user_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'payment_scheme'   => 'required|in:cash,kpr,installment,tunai_bertahap,inhouse',
            'down_payment'     => 'required|numeric|min:0',
            'payment_schedule' => 'nullable|string|max:100',
            'terms_conditions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $unitPrice     = (float) $unitSelection->housingUnit->price;
            $paymentScheme = $request->payment_scheme;

            // Khusus KPR: Tidak ada DP (DP = 0, tanda jadi menggunakan UTK yang sudah lunas)
            if ($paymentScheme === 'kpr') {
                $downPayment = 0;
            } else {
                $downPayment = (float) $request->down_payment;
                // Down payment tidak boleh melebihi harga unit
                if ($downPayment > $unitPrice) {
                    return back()->with('error', 'Down payment tidak boleh melebihi harga unit.');
                }
            }

            $remainingAmount = max(0, $unitPrice - $downPayment);

            // Ambil KPR screening terbaru (jika ada)
            $kprScreening = $unitSelection->kprScreening()->latest()->first() ?? $unitSelection->nupRegistration?->kprScreening;

            // Cek duplikasi SPR aktif
            $existingActive = \App\Models\SprDocument::where('unit_selection_id', $unitSelection->id)
                ->whereNotIn('status', ['cancelled'])
                ->first();

            if ($existingActive) {
                return redirect()->route('spr.show', $existingActive->id)
                    ->with('info', 'SPR sudah pernah dibuat sebelumnya.');
            }

            $company = $unitSelection->housingUnit?->housingProject?->company;
            $companyCode = $company?->company_initials ?: ($company ? \Illuminate\Support\Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $company->company_name), 0, 4) : 'PT');

            $project = $unitSelection->housingUnit?->housingProject;
            $tenorMonths = null;
            $dueDate = null;
            $penaltyPercent = 0;

            if ($paymentScheme === 'tunai_bertahap') {
                $days = (int) ($request->tunai_bertahap_days ?: ($project?->cash_due_days ?? 30));
                $dueDate = now()->addDays($days)->toDateString();
                $penaltyPercent = (float) ($project?->cash_penalty_percent ?? 1.00);
            } elseif ($paymentScheme === 'inhouse' || $paymentScheme === 'installment') {
                $tenorMonths = (int) ($request->inhouse_tenor ?: ($project?->inhouse_max_tenor_months ?? 12));
                $penaltyPercent = (float) ($project?->inhouse_penalty_percent ?? 1.00);
                $dueDay = (int) ($project?->inhouse_due_day ?? 10);
                $firstDueDate = now()->copy()->addMonth()->setDay(min(28, max(1, $dueDay)));
                $dueDate = $firstDueDate->toDateString();
            }

            $isKpr = ($paymentScheme === 'kpr');
            $buyerName = $unitSelection->nupRegistration?->full_name ?: Auth::user()->name;
            $buyerNik = $unitSelection->nupRegistration?->nik ?: Auth::user()->nik;
            $buyerPhone = $unitSelection->nupRegistration?->phone ?: Auth::user()->phone;
            $buyerAddress = $unitSelection->nupRegistration?->address ?: Auth::user()->address;

            $spr = SprDocument::create([
                'user_id'             => Auth::id(),
                'buyer_name'          => $buyerName,
                'buyer_nik'           => $buyerNik,
                'buyer_phone'         => $buyerPhone,
                'buyer_address'       => $buyerAddress,
                'nup_registration_id' => $unitSelection->nup_registration_id,
                'unit_selection_id'   => $unitSelection->id,
                'kpr_screening_id'    => $kprScreening?->id,
                'spr_number'          => SprDocument::generateSprNumber($companyCode),
                'spr_date'            => now()->toDateString(),
                'unit_price'          => $unitPrice,
                'down_payment'        => $downPayment,
                'remaining_amount'    => $remainingAmount,
                'payment_scheme'      => $paymentScheme,
                'payment_schedule'    => $request->payment_schedule,
                'due_date'            => $dueDate,
                'tenor_months'        => $tenorMonths,
                'penalty_percent'     => $penaltyPercent,
                'terms_conditions'    => $request->terms_conditions,
                'status'              => $isKpr ? 'issued' : 'draft',
                'issued_date'         => $isKpr ? now() : null,
                'issued_by'           => $isKpr ? Auth::user()->name : null,
                'bank_submission_status' => $isKpr ? 'submitted_to_bank' : 'pending',
                'argo_deadline'       => $isKpr ? now()->addDays(7) : null,
            ]);

            $successMsg = $isKpr 
                ? 'Dokumen SPR KPR resmi diterbitkan! Pengurusan berkas ke bank rekanan telah dimulai dan Argo 7 Hari berjalan.' 
                : 'Draft SPR berhasil dibuat. Silakan review dan konfirmasi.';

            // Jika pembeli langsung mengunggah bukti transfer DP di form ini
            if ($request->hasFile('proof_of_transfer') && $downPayment > 0) {
                $proofPath = $request->file('proof_of_transfer')->store('proofs/kpr', 'public');
                \App\Models\KprPayment::create([
                    'spr_document_id'           => $spr->id,
                    'user_id'                   => Auth::id(),
                    'payment_number'            => \App\Models\KprPayment::generatePaymentNumber(),
                    'amount'                    => $downPayment,
                    'payment_type'              => 'dp',
                    'installment_number'        => null,
                    'principal_amount'          => $downPayment,
                    'interest_amount'           => 0,
                    'penalty_amount'            => 0,
                    'payment_method'            => 'transfer',
                    'developer_bank_account_id' => $request->developer_bank_account_id,
                    'proof_of_transfer'         => $proofPath,
                    'sender_bank'               => strtoupper(trim($request->sender_bank ?? 'BANK')),
                    'sender_name'               => trim($request->sender_name ?? Auth::user()->name),
                    'sender_account_number'     => trim($request->sender_account_number ?? ''),
                    'transfer_date'             => $request->transfer_date ?? now()->toDateString(),
                    'notes'                     => 'Pembayaran Uang Muka (DP) saat penerbitan draft SPR',
                    'status'                    => 'pending',
                ]);
                $successMsg = 'Draft SPR berhasil dibuat dan Bukti Transfer Uang Muka (DP) berhasil diunggah! Menunggu verifikasi pengembang.';
            }

            return redirect()->route('spr.show', $spr->id)
                ->with('success', $successMsg);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat draft SPR: ' . $e->getMessage());
        }
    }

    /**
     * Show SPR details
     */
    public function show($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $spr = SprDocument::with([
            'user',
            'unitSelection.housingUnit.housingProject.company',
            'unitSelection.housingUnit.housingProject.sprClauses',
            'nupRegistration',
            'kprScreening'
        ])->findOrFail($id);

        if ($spr->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($spr->user_id !== Auth::id()) {
            $this->ensureOperatorCanAccessSpr($spr);
        }

        // Model 2: Dokumen SPR hanya dapat diakses konsumen setelah DP lunas
        if (!Auth::user()->isAdmin() && $spr->down_payment > 0 && !$spr->isDpPaid()) {
            return redirect()->route('kpr-payment.list')
                ->with('info', 'Dokumen SPR resmi akan dapat dilihat dan diunduh setelah pembayaran Uang Muka (DP) berhasil diverifikasi.');
        }

        return view('spr.show', compact('spr'));
    }

    /**
     * Generate SPR PDF
     */
    public function generatePdf($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $spr = SprDocument::with([
            'user',
            'unitSelection.housingUnit.housingProject.company',
            'unitSelection.housingUnit.housingProject.sprClauses',
            'nupRegistration',
            'kprScreening'
        ])->findOrFail($id);

        if ($spr->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($spr->user_id !== Auth::id()) {
            $this->ensureOperatorCanAccessSpr($spr);
        }

        if (!Auth::user()->isAdmin() && $spr->down_payment > 0 && !$spr->isDpPaid()) {
            return redirect()->route('kpr-payment.list')
                ->with('error', 'Dokumen PDF SPR hanya dapat diunduh setelah pembayaran Uang Muka (DP) lunas.');
        }

        $safeNumber = str_replace(['/', '\\'], '-', $spr->spr_number);
        $fileName = 'SPR-' . $safeNumber . '.pdf';

        $pdf = Pdf::loadView('spr.pdf', compact('spr'));

        if (request()->has('download')) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    /**
     * Issue SPR (admin only)
     */
    public function issue($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $spr = SprDocument::with('unitSelection.housingUnit.project')->findOrFail($id);
        $this->ensureOperatorCanAccessSpr($spr);

        try {
            $spr->update([
                'status' => 'issued',
                'issued_date' => now(),
                'issued_by' => Auth::user()->name,
            ]);

            // Generate PDF document
            $safeNumber = str_replace(['/', '\\'], '-', $spr->spr_number);
            $fileName = 'SPR-' . $safeNumber . '.pdf';

            $sprDir = storage_path('app/public/spr');
            if (!file_exists($sprDir)) {
                mkdir($sprDir, 0755, true);
            }

            $pdf = Pdf::loadView('spr.pdf', compact('spr'));
            $filePath = $pdf->save($sprDir . '/' . $fileName);

            $spr->update(['document_file' => 'spr/' . $fileName]);

            // Send WhatsApp notification
            $this->whatsappService->sendSprNotification(
                $spr->user,
                $spr->spr_number,
                $spr->spr_date
            );

            return back()->with('success', 'SPR berhasil diterbitkan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menerbitkan SPR: ' . $e->getMessage());
        }
    }

    /**
     * Cancel SPR (admin only)
     */
    public function cancel($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $spr = SprDocument::with('unitSelection.housingUnit.project')->findOrFail($id);
        $this->ensureOperatorCanAccessSpr($spr);

        try {
            $spr->update(['status' => 'cancelled']);

            return back()->with('success', 'SPR berhasil dibatalkan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan SPR: ' . $e->getMessage());
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

        $sprQuery = SprDocument::with(['user', 'unitSelection.housingUnit'])
            ->orderBy('created_at', 'desc');

        if ($companyId) {
            $sprQuery->whereHas('unitSelection.housingUnit', function ($q) use ($companyId) {
                $q->forCompany($companyId);
            });
        }

        $sprDocuments = $sprQuery->paginate(20);

        // Unit selections yang UTK-nya sudah lunas / bebas UTK tapi belum dibuatkan SPR
        $readyQuery = UnitSelection::with(['user', 'housingUnit.housingProject', 'nupRegistration', 'utkPayments'])
            ->whereIn('status', ['confirmed', 'selected'])
            ->whereDoesntHave('sprDocuments', function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            });

        if ($companyId) {
            $readyQuery->whereHas('housingUnit', function ($q) use ($companyId) {
                $q->forCompany($companyId);
            });
        }

        $readySelections = $readyQuery->get()
            ->filter(function ($selection) {
                return $selection->isCash() || $selection->isUtkWaived() || $selection->isUtkPaid();
            });

        return view('admin.spr.index', compact('sprDocuments', 'readySelections'));
    }

    /**
     * Operator Create SPR Form
     */
    public function adminCreate($unitSelectionId)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $unitSelection = UnitSelection::with([
            'housingUnit.housingProject.company',
            'nupRegistration.kprScreening',
            'user',
            'kprScreening',
            'sprDocuments',
            'utkPayments',
        ])->findOrFail($unitSelectionId);

        $this->ensureOperatorCanAccessUnitSelection($unitSelection);

        // Check if an active SPR already exists
        $existingSpr = $unitSelection->sprDocuments()->whereNotIn('status', ['cancelled'])->first();
        if ($existingSpr) {
            return redirect()->route('spr.show', $existingSpr->id)
                ->with('info', 'Dokumen SPR untuk unit ini sudah pernah diterbitkan.');
        }

        $isPaid = $unitSelection->isCash() || $unitSelection->isUtkWaived() || $unitSelection->isUtkPaid();
        if (!$isPaid) {
            return redirect()->route('admin.spr.index')
                ->with('error', 'Biaya UTK untuk unit ini belum lunas. SPR hanya dapat diterbitkan setelah UTK lunas atau menggunakan skema Tunai.');
        }

        $company = $unitSelection->housingUnit?->housingProject?->company ?? $unitSelection->housingUnit?->company;
        $companyCode = $company?->company_initials ?: ($company ? \Illuminate\Support\Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $company->company_name), 0, 4) : 'PT');
        $suggestedSprNumber = SprDocument::generateSprNumber($companyCode);

        $bankAccounts = $company ? $company->activeBankAccounts()->get() : collect();
        if ($bankAccounts->isEmpty() && $company) {
            $bankAccounts = $company->bankAccounts()->get();
        }

        $unitPrice = (float) ($unitSelection->housingUnit->price ?? 0);
        $project = $unitSelection->housingUnit?->housingProject;

        // Default payment scheme from unit selection or NUP
        $rawScheme = $unitSelection->payment_scheme ?: ($unitSelection->nupRegistration?->payment_method ?: 'inhouse');
        if (in_array($rawScheme, ['kpr', 'kpr_bank', 'kpr_bank_flpp', 'kpr_syariah'], true)) {
            $scheme = 'kpr';
        } elseif (in_array($rawScheme, ['inhouse', 'installment', 'cicilan_developer'], true)) {
            $scheme = 'inhouse';
        } elseif (in_array($rawScheme, ['tunai', 'cash', 'tunai_keras', 'cash_keras'], true)) {
            $scheme = 'cash';
        } elseif (in_array($rawScheme, ['tunai_bertahap'], true)) {
            $scheme = 'tunai_bertahap';
        } elseif (in_array($rawScheme, ['mitra_pintar', 'kredit_mitra_pintar', 'tunai_mitra_pintar'], true)) {
            $scheme = 'mitra_pintar';
        } else {
            $scheme = 'inhouse';
        }

        return view('admin.spr.create', compact(
            'unitSelection',
            'suggestedSprNumber',
            'bankAccounts',
            'unitPrice',
            'project',
            'scheme'
        ));
    }

    /**
     * Operator Store and Issue SPR
     */
    public function adminStore(Request $request, $unitSelectionId)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $unitSelection = UnitSelection::with(['housingUnit.housingProject.company', 'nupRegistration', 'user'])
            ->findOrFail($unitSelectionId);

        $this->ensureOperatorCanAccessUnitSelection($unitSelection);

        $validator = Validator::make($request->all(), [
            'spr_number' => 'required|string|max:100|unique:spr_documents,spr_number',
            'spr_date' => 'required|date',
            'payment_scheme' => 'required|in:cash,kpr,installment,tunai_bertahap,inhouse,mitra_pintar',
            'down_payment' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'tenor_months' => 'nullable|integer|min:1|max:120',
            'developer_bank_account_id' => 'nullable|exists:developer_bank_accounts,id',
            'terms_conditions' => 'nullable|string',
            'status' => 'required|in:issued,draft',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $unitPrice = (float) $unitSelection->housingUnit->price;
            $paymentScheme = $request->payment_scheme;
            $downPayment = (float) $request->down_payment;

            if ($paymentScheme === 'kpr' || $paymentScheme === 'mitra_pintar') {
                $downPayment = 0; // KPR / Mitra Pintar DP handled by bank / UTK (Bebas DP)
            }

            if ($downPayment > $unitPrice) {
                return back()->with('error', 'Down Payment tidak boleh melebihi harga unit rumah.')->withInput();
            }

            $remainingAmount = max(0, $unitPrice - $downPayment);
            $kprScreening = $unitSelection->kprScreening()->latest()->first() ?? $unitSelection->nupRegistration?->kprScreening;

            $buyerName = $unitSelection->nupRegistration?->full_name ?: $unitSelection->user->name;
            $buyerNik = $unitSelection->nupRegistration?->nik ?: $unitSelection->user->nik;
            $buyerPhone = $unitSelection->nupRegistration?->phone ?: $unitSelection->user->phone;
            $buyerAddress = $unitSelection->nupRegistration?->address ?: $unitSelection->user->address;

            $project = $unitSelection->housingUnit?->housingProject;
            $penaltyPercent = 0;
            $tenorMonths = null;
            $dueDate = $request->due_date;

            if ($paymentScheme === 'tunai_bertahap') {
                $penaltyPercent = (float) ($project?->cash_penalty_percent ?? 1.00);
            } elseif (in_array($paymentScheme, ['inhouse', 'installment', 'mitra_pintar'], true)) {
                $tenorMonths = $request->tenor_months ? (int) $request->tenor_months : null;
                $penaltyPercent = (float) ($project?->inhouse_penalty_percent ?? 1.00);
            }

            $isKpr = ($paymentScheme === 'kpr');
            $status = $request->status ?: 'issued';

            $spr = SprDocument::create([
                'user_id' => $unitSelection->user_id,
                'buyer_name' => $buyerName,
                'buyer_nik' => $buyerNik,
                'buyer_phone' => $buyerPhone,
                'buyer_address' => $buyerAddress,
                'nup_registration_id' => $unitSelection->nup_registration_id,
                'unit_selection_id' => $unitSelection->id,
                'kpr_screening_id' => $kprScreening?->id,
                'spr_number' => $request->spr_number,
                'spr_date' => $request->spr_date ?: now()->toDateString(),
                'unit_price' => $unitPrice,
                'down_payment' => $downPayment,
                'remaining_amount' => $remainingAmount,
                'payment_scheme' => $paymentScheme,
                'payment_schedule' => $request->payment_schedule,
                'due_date' => $dueDate,
                'tenor_months' => $tenorMonths,
                'penalty_percent' => $penaltyPercent,
                'terms_conditions' => $request->terms_conditions,
                'status' => $status,
                'issued_date' => ($status === 'issued') ? now() : null,
                'issued_by' => ($status === 'issued') ? Auth::user()->name : null,
                'bank_submission_status' => $isKpr ? 'submitted_to_bank' : 'pending',
                'argo_deadline' => $isKpr ? now()->addDays(7) : null,
            ]);

            // Sync payment_scheme back to unit_selection and nup_registration
            $unitSelection->update([
                'payment_scheme' => $paymentScheme,
                'status' => 'confirmed',
            ]);

            // Kirim notifikasi WA ke pembeli
            try {
                if ($unitSelection->user && $unitSelection->user->phone) {
                    $schemeLabel = match($paymentScheme) {
                        'inhouse', 'installment' => 'In-House Developer',
                        'cash' => 'Tunai Lunas',
                        'tunai_bertahap' => 'Tunai Bertahap',
                        'kpr' => 'KPR Bank',
                        'mitra_pintar' => 'Kredit Mitra Pintar (Bebas DP)',
                        default => strtoupper($paymentScheme)
                    };
                    $msg = "Halo {$buyerName},\n\nDokumen Surat Pemesanan Rumah (SPR) untuk unit {$unitSelection->housingUnit->full_identifier} telah RESMI DITERBITKAN oleh pengembang dengan skema {$schemeLabel}.\nNo. SPR: {$spr->spr_number}\n\nSilakan buka aplikasi untuk memeriksa dokumen dan menandatanganinya secara digital.";
                    $this->whatsappService->sendNotification($unitSelection->user->phone, $msg, 'spr_issued', $unitSelection->user->id);
                }
            } catch (\Exception $e) {
                \Log::error('WA Notification SPR Issue: ' . $e->getMessage());
            }

            return redirect()->route('spr.show', $spr->id)
                ->with('success', "Dokumen SPR {$spr->spr_number} berhasil diterbitkan dan siap ditinjau oleh pemohon!");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menerbitkan SPR: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show pending SPRs
     */
    public function pending()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $companyId = Auth::user()->getOperatorCompanyId();

        $pendingQuery = SprDocument::with(['user', 'unitSelection.housingUnit'])
            ->where('status', 'draft')
            ->orderBy('created_at', 'desc');

        if ($companyId) {
            $pendingQuery->whereHas('unitSelection.housingUnit', function ($q) use ($companyId) {
                $q->forCompany($companyId);
            });
        }

        $pendingSprs = $pendingQuery->get();

        return view('admin.spr.pending', compact('pendingSprs'));
    }

    /**
     * Update Bank Submission & Jalankan Argo KPR 7 Hari (Operator)
     */
    public function updateBankSubmission(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $spr = SprDocument::with('unitSelection.housingUnit.project')->findOrFail($id);
        $this->ensureOperatorCanAccessSpr($spr);

        $validator = Validator::make($request->all(), [
            'bank_submission_status' => 'nullable|in:pending,submitted_to_bank,sp3k_approved,sp3k_rejected',
            'argo_days' => 'nullable|integer|min:1|max:60',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $status = $request->bank_submission_status ?: 'submitted_to_bank';
        $data = [
            'bank_submission_status' => $status,
        ];

        // Jika diajukan ke bank, jalankan argo countdown (default 7 hari)
        if ($status === 'submitted_to_bank') {
            $days = (int) ($request->filled('argo_days') ? $request->argo_days : 7);
            if ($days <= 0) {
                $days = 7;
            }
            $data['argo_deadline'] = now()->addDays($days);
        }

        $spr->update($data);

        return back()->with('success', "Durasi argo KPR bank berhasil diperbarui ({$days} hari ke depan)!");
    }

    /**
     * Simpan SP3K dari Bank (Operator)
     */
    public function saveSp3k(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $spr = SprDocument::with('unitSelection.housingUnit.project')->findOrFail($id);
        $this->ensureOperatorCanAccessSpr($spr);

        $validator = Validator::make($request->all(), [
            'bank_submission_status' => 'required|in:sp3k_approved,sp3k_rejected',
            'sp3k_number' => 'nullable|string|max:100',
            'sp3k_approved_amount' => 'nullable|numeric|min:0',
            'sp3k_notes' => 'nullable|string|max:1000',
            'sp3k_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = [
            'bank_submission_status' => $request->bank_submission_status,
            'sp3k_number' => $request->sp3k_number,
            'sp3k_approved_amount' => $request->sp3k_approved_amount,
            'sp3k_notes' => $request->sp3k_notes,
        ];

        if ($request->hasFile('sp3k_file')) {
            $data['sp3k_file'] = $request->file('sp3k_file')->store('sp3k', 'public');
        }

        $spr->update($data);

        // Notifikasi WA
        try {
            if ($spr->user && $spr->user->phone) {
                if ($request->bank_submission_status === 'sp3k_approved') {
                    $nominal = number_format($request->sp3k_approved_amount, 0, ',', '.');
                    $msg = "Halo {$spr->user->name},\n\nSelamat! Pengajuan KPR Anda telah DISETUJUI BANK (SP3K Terbit).\nNo. SP3K: {$request->sp3k_number}\nPlafon ACC: Rp {$nominal}\nSilakan cek jadwal akad kredit dan checklist berkas di dashboard.";
                } else {
                    $msg = "Halo {$spr->user->name},\n\nMohon maaf, pengajuan KPR Anda belum disetujui oleh bank rekanan.\nCatatan: {$request->sp3k_notes}\nAnda dapat memilih opsi beralih ke skema In-House atau Tunai melalui dashboard.";
                }
                $this->whatsappService->sendNotification($spr->user->phone, $msg, 'kpr_result', $spr->user->id);
            }
        } catch (\Exception $e) {
            \Log::error('WA Notification SP3K: ' . $e->getMessage());
        }

        return back()->with('success', 'Hasil keputusan KPR / SP3K berhasil disimpan!');
    }

    /**
     * Input & Simpan Jadwal Akad Kredit (Operator)
     */
    public function saveAkad(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $spr = SprDocument::with('unitSelection.housingUnit.project')->findOrFail($id);
        $this->ensureOperatorCanAccessSpr($spr);

        $validator = Validator::make($request->all(), [
            'akad_date' => 'required|date',
            'akad_location' => 'required|string|max:255',
            'akad_notary' => 'nullable|string|max:255',
            'akad_notes' => 'nullable|string|max:1000',
            'akad_checklist' => 'nullable|array',
            'akad_custom_docs' => 'nullable|string|max:500',
            'is_akad_completed' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $checklist = (array) $request->input('akad_checklist', []);
        if ($request->filled('akad_custom_docs')) {
            $customLines = preg_split('/[\r\n]+/', $request->akad_custom_docs);
            foreach ($customLines as $line) {
                $line = trim($line);
                if (!empty($line) && !in_array($line, $checklist, true)) {
                    $checklist[] = $line;
                }
            }
        }

        $isAkadCompleted = $request->boolean('is_akad_completed') || $request->has('is_akad_completed');

        $updateData = [
            'akad_date' => $request->akad_date,
            'akad_location' => $request->akad_location,
            'akad_notary' => $request->akad_notary,
            'akad_notes' => $request->akad_notes,
            'akad_checklist' => !empty($checklist) ? array_values($checklist) : null,
            'is_akad_completed' => $isAkadCompleted,
        ];

        // Opsi 1: Otomatis ubah status SPR menjadi 'paid' (Lunas) saat akad kredit ditandai selesai
        if ($isAkadCompleted) {
            $updateData['status'] = 'paid';
        } elseif ($spr->status === 'paid' && $spr->payment_scheme === 'kpr') {
            $updateData['status'] = 'issued';
        }

        $spr->update($updateData);

        // Notifikasi Jadwal Akad ke User
        try {
            if ($spr->user && $spr->user->phone) {
                $tgl = \Carbon\Carbon::parse($request->akad_date)->translatedFormat('l, d F Y H:i');
                $msg = "Halo {$spr->user->name},\n\nJadwal Akad Kredit Anda telah ditentukan:\nTanggal: {$tgl}\nTempat: {$request->akad_location}\nNotaris: " . ($request->akad_notary ?? '-') . "\n\nHarap membawa dokumen asli (KTP Asli Suami/Istri, KK Asli, Buku Nikah Asli, NPWP Asli).";
                $this->whatsappService->sendNotification($spr->user->phone, $msg, 'akad_schedule', $spr->user->id);
            }
        } catch (\Exception $e) {
            \Log::error('WA Notification Akad: ' . $e->getMessage());
        }

        return back()->with('success', 'Jadwal Akad Kredit berhasil diperbarui.');
    }

    /**
     * Konfirmasi Penyelesaian Akad Kredit (Operator - Sendiri / Sekali Klik)
     */
    public function completeAkad(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $spr = SprDocument::with(['unitSelection.housingUnit.project', 'user'])->findOrFail($id);
        $this->ensureOperatorCanAccessSpr($spr);

        $isCompleted = $request->boolean('is_completed', true);

        $updateData = [
            'is_akad_completed' => $isCompleted,
        ];

        // Otomatis tandai status SPR menjadi 'paid' (Lunas 100%) jika akad telah selesai
        if ($isCompleted) {
            $updateData['status'] = 'paid';
        } elseif ($spr->status === 'paid' && $spr->payment_scheme === 'kpr') {
            $updateData['status'] = 'issued';
        }

        $spr->update($updateData);

        // Notifikasi WA bila akad selesai
        if ($isCompleted) {
            try {
                if ($spr->user && $spr->user->phone) {
                    $unitIdentifier = $spr->unitSelection?->housingUnit?->full_identifier ?? 'Unit Rumah';
                    $msg = "Halo {$spr->buyer_name},\n\nSelamat! Proses penandatanganan Akad Kredit untuk unit {$unitIdentifier} telah resmi dinyatakan SELESAI.\n\nStatus pembayaran SPR Anda kini LUNAS 100%. Tim pengembang akan segera menghubungi Anda untuk penjadwalan Serah Terima Kunci (BAST).";
                    $this->whatsappService->sendNotification($spr->user->phone, $msg, 'akad_completed', $spr->user->id);
                }
            } catch (\Exception $e) {
                \Log::error('WA Notification Akad Completed: ' . $e->getMessage());
            }
        }

        $msg = $isCompleted
            ? 'Proses Akad Kredit berhasil ditandai SELESAI! Dokumen SPR resmi Lunas 100% dan siap lanjut ke Serah Terima Kunci (BAST).'
            : 'Status Akad Kredit berhasil dikembalikan menjadi belum selesai.';

        return back()->with('success', $msg);
    }

    /**
     * Opsi Penyelamatan Peserta Jika KPR Ditolak (Alih ke In-House / Tunai / Batal)
     */
    public function rescueSwitch(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $spr = SprDocument::findOrFail($id);

        if ($spr->user_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:switch_inhouse,switch_cash,cancel',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        if ($request->action === 'switch_inhouse') {
            $spr->update([
                'payment_scheme' => 'installment',
                'bank_submission_status' => 'pending',
                'terms_conditions' => ($spr->terms_conditions ? $spr->terms_conditions . "\n" : '') . "Dialihkan ke In-House Developer pada " . now()->format('d/m/Y H:i'),
            ]);
            return back()->with('success', 'Skema pembayaran SPR berhasil dialihkan ke In-House (Cicilan Pengembang)!');
        } elseif ($request->action === 'switch_cash') {
            $spr->update([
                'payment_scheme' => 'cash',
                'bank_submission_status' => 'pending',
                'terms_conditions' => ($spr->terms_conditions ? $spr->terms_conditions . "\n" : '') . "Dialihkan ke Tunai / Bertahap pada " . now()->format('d/m/Y H:i'),
            ]);
            return back()->with('success', 'Skema pembayaran SPR berhasil dialihkan ke Tunai!');
        } else {
            // 1. Batalkan Dokumen SPR
            $spr->update([
                'status' => 'cancelled',
                'terms_conditions' => ($spr->terms_conditions ? $spr->terms_conditions . "\n" : '') . "Dibatalkan secara mandiri oleh pemohon pada " . now()->format('d/m/Y H:i') . " (Biaya NUP & UTK dinyatakan hangus).",
            ]);

            // 2. Batalkan Unit Selection & Lepaskan Unit Rumah Kavling
            $unitSelection = $spr->unitSelection;
            if ($unitSelection) {
                $unitSelection->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => $request->input('reason', 'Dibatalkan secara mandiri oleh pemesan pada tahap SPR (KPR tidak disetujui / Batal pemesanan). Seluruh biaya pendaftaran NUP & UTK dinyatakan hangus.'),
                    'cancelled_at' => now(),
                ]);

                if ($unitSelection->housingUnit) {
                    $unitSelection->housingUnit->update([
                        'status' => 'available',
                    ]);
                }
            }

            // 3. Nyatakan NUP Hangus / Selesai
            $nup = $spr->nupRegistration ?? $unitSelection?->nupRegistration;
            if ($nup) {
                $nup->update([
                    'is_forfeited' => true,
                    'forfeited_at' => now(),
                    'forfeit_reason' => 'Pemesan membatalkan pemesanan unit secara mandiri pada tahap SPR. Sesuai ketentuan resmi, seluruh biaya pendaftaran NUP & UTK dinyatakan hangus (non-refundable).',
                ]);
            }

            return redirect()->route('dashboard')
                ->with('info', 'Pemesanan unit telah dibatalkan. Sesuai ketentuan, biaya pendaftaran NUP & UTK dinyatakan hangus dan unit kavling telah dilepas kembali ke stok tersedia.');
        }
    }

    /**
     * Konfirmasi Pencairan Dana Pelunasan oleh Lembaga Mitra Pintar (Operator)
     */
    public function confirmMitraDisbursement(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $spr = SprDocument::with(['unitSelection.housingUnit.project', 'user'])->findOrFail($id);
        $this->ensureOperatorCanAccessSpr($spr);

        $validator = Validator::make($request->all(), [
            'disbursement_amount'       => 'required|numeric|min:1',
            'transfer_date'             => 'required|date',
            'developer_bank_account_id' => 'nullable|exists:developer_bank_accounts,id',
            'sender_name'               => 'nullable|string|max:255',
            'reference_number'          => 'nullable|string|max:100',
            'proof_file'                => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes'                     => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $proofPath = null;
            if ($request->hasFile('proof_file')) {
                $proofPath = $request->file('proof_file')->store('proofs/mitra_disbursement', 'public');
            }

            $senderName = $request->sender_name
                ?: ($spr->unitSelection?->mitra_institution_name ?: 'Lembaga Mitra Pintar');

            // Catat transaksi pelunasan di KprPayment
            \App\Models\KprPayment::create([
                'spr_document_id'           => $spr->id,
                'user_id'                   => $spr->user_id,
                'payment_number'            => \App\Models\KprPayment::generatePaymentNumber(),
                'amount'                    => $request->disbursement_amount,
                'payment_type'              => 'full_payment',
                'installment_number'        => null,
                'principal_amount'          => $request->disbursement_amount,
                'interest_amount'           => 0,
                'penalty_amount'            => 0,
                'payment_method'            => 'transfer',
                'developer_bank_account_id' => $request->developer_bank_account_id,
                'proof_of_transfer'         => $proofPath,
                'sender_bank'               => 'MITRA PINTAR',
                'sender_name'               => $senderName,
                'sender_account_number'     => $request->reference_number ?? '',
                'transfer_date'             => $request->transfer_date,
                'payment_date'              => \Carbon\Carbon::parse($request->transfer_date),
                'notes'                     => 'Pencairan Pelunasan Unit oleh Mitra Pintar (' . $senderName . ')' . ($request->notes ? ' - ' . $request->notes : ''),
                'status'                    => 'success',
            ]);

            // Update status SPR menjadi paid (Lunas)
            $updateNotes = "\n[Pencairan Mitra Pintar] Dana pelunasan sebesar Rp " . number_format($request->disbursement_amount, 0, ',', '.') . " diterima pada " . $request->transfer_date . " dari " . $senderName . " (Dikonfirmasi oleh " . Auth::user()->name . ")";
            $spr->update([
                'status' => 'paid',
                'terms_conditions' => ($spr->terms_conditions ?? '') . $updateNotes,
            ]);

            if ($spr->unitSelection?->housingUnit) {
                $spr->unitSelection->housingUnit->update(['status' => 'sold']);
            }

            // Kirim notifikasi WA ke pembeli
            try {
                if ($spr->user && $spr->user->phone) {
                    $unitIdentifier = $spr->unitSelection?->housingUnit?->full_identifier ?? 'Unit Rumah';
                    $nominal = number_format($request->disbursement_amount, 0, ',', '.');
                    $msg = "Halo {$spr->buyer_name},\n\nKabar gembira! Pembayaran pelunasan unit {$unitIdentifier} sebesar Rp {$nominal} telah berhasil DICAIRKAN oleh Lembaga Mitra Pintar ({$senderName}) ke rekening pengembang.\n\nStatus pemesanan Anda kini RESMI LUNAS (100%).\nTahap selanjutnya adalah Penjadwalan Serah Terima Kunci dan Penandatanganan Berita Acara Serah Terima (BAST).";
                    $this->whatsappService->sendNotification($spr->user->phone, $msg, 'mitra_disbursement_confirmed', $spr->user->id);
                }
            } catch (\Exception $e) {
                \Log::error('WA Notification Mitra Disbursement: ' . $e->getMessage());
            }

            return back()->with('success', 'Dana pelunasan dari Lembaga Mitra Pintar berhasil diverifikasi. Dokumen SPR resmi LUNAS 100%!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses konfirmasi pencairan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Jadwalkan & Simpan BAST / Serah Terima Kunci (Operator)
     */
    public function saveBast(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $spr = SprDocument::with(['unitSelection.housingUnit.project', 'user'])->findOrFail($id);
        $this->ensureOperatorCanAccessSpr($spr);

        $validator = Validator::make($request->all(), [
            'bast_date'     => 'required|date',
            'bast_location' => 'nullable|string|max:255',
            'bast_notes'    => 'nullable|string|max:1000',
            'bast_file'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = [
                'bast_date'     => $request->bast_date,
                'bast_location' => $request->bast_location ?: $spr->bast_location,
                'bast_notes'    => $request->bast_notes ?? $spr->bast_notes,
            ];

            if ($request->hasFile('bast_file')) {
                $data['bast_file'] = $request->file('bast_file')->store('bast', 'public');
            }

            $spr->update($data);

            // Kirim notifikasi WA ke pemohon
            try {
                if ($spr->user && $spr->user->phone) {
                    $tgl = \Carbon\Carbon::parse($request->bast_date)->translatedFormat('l, d F Y H:i');
                    $unitIdentifier = $spr->unitSelection?->housingUnit?->full_identifier ?? 'Unit Rumah';

                    if ($request->hasFile('bast_file')) {
                        $msg = "Halo {$spr->buyer_name},\n\nDokumen Berita Acara Serah Terima (BAST) untuk unit {$unitIdentifier} telah resmi diunggah dan proses Serah Terima Kunci dinyatakan SELESAI.\n\nSelamat menikmati hunian baru Anda!";
                    } else {
                        $msg = "Halo {$spr->buyer_name},\n\nJadwal Serah Terima Kunci & Pengecekan Fisik Unit {$unitIdentifier} telah ditetapkan:\nTanggal & Waktu: {$tgl} WIB\n\nHarap hadir tepat waktu untuk pengecekan fisik unit bersama tim pengembang dan penandatanganan Berita Acara Serah Terima (BAST).";
                    }
                    $this->whatsappService->sendNotification($spr->user->phone, $msg, 'bast_schedule', $spr->user->id);
                }
            } catch (\Exception $e) {
                \Log::error('WA Notification BAST: ' . $e->getMessage());
            }

            $successMsg = $request->hasFile('bast_file')
                ? 'Dokumen BAST dan Serah Terima Kunci berhasil diselesaikan!'
                : 'Jadwal Serah Terima Kunci berhasil ditetapkan.';

            return back()->with('success', $successMsg);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui BAST: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Pastikan Operator Developer hanya dapat mengakses SPR milik perusahaannya sendiri.
     */
    private function ensureOperatorCanAccessSpr(SprDocument $spr): void
    {
        $companyId = Auth::user()?->getOperatorCompanyId();
        if (!$companyId) {
            return;
        }

        $unit = $spr->unitSelection?->housingUnit;
        if (!$unit || !$unit->belongsToCompany($companyId)) {
            abort(403, 'Akses ditolak: Dokumen SPR ini milik perusahaan developer lain.');
        }
    }

    /**
     * Pastikan Operator Developer hanya dapat mengakses Pemilihan Unit milik perusahaannya sendiri.
     */
    private function ensureOperatorCanAccessUnitSelection(UnitSelection $unitSelection): void
    {
        $companyId = Auth::user()?->getOperatorCompanyId();
        if (!$companyId) {
            return;
        }

        $unit = $unitSelection->housingUnit;
        if (!$unit || !$unit->belongsToCompany($companyId)) {
            abort(403, 'Akses ditolak: Unit ini milik perusahaan developer lain.');
        }
    }
}