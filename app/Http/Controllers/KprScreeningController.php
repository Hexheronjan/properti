<?php

namespace App\Http\Controllers;

use App\Models\KprScreening;
use App\Models\UnitSelection;
use App\Services\WhatsAppGatewayService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class KprScreeningController extends Controller
{
    protected $whatsappService;
    protected $paymentService;

    public function __construct(WhatsAppGatewayService $whatsappService, PaymentGatewayService $paymentService)
    {
        $this->whatsappService = $whatsappService;
        $this->paymentService = $paymentService;
    }

    /**
     * Create screening form
     */
    public function create($unitSelectionId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $unitSelection = UnitSelection::findOrFail($unitSelectionId);

        if ($unitSelection->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if screening already exists
        if ($unitSelection->kprScreening()->exists()) {
            return redirect()->route('kpr.show', $unitSelection->kprScreening->id);
        }

        $nupRegistration = $unitSelection->nupRegistration;
        $kprBanks = config('nup.kpr_banks');
        $user = Auth::user();
        $autoOccupation = $nupRegistration->occupation ?? $user->occupation;
        $autoOccupationOther = $nupRegistration->occupation_other;
        $autoEmploymentStatus = KprScreening::resolveEmploymentStatus($autoOccupation);
        $autoOccupationLabel = KprScreening::getOccupationLabel($autoOccupation, $autoOccupationOther);
        $defaultPlafon = $unitSelection->housingUnit->price;
        $preferredProject = $unitSelection->housingUnit->housingProject;

        return view('kpr.create', compact('unitSelection', 'nupRegistration', 'kprBanks', 'autoEmploymentStatus', 'autoOccupationLabel', 'preferredProject', 'defaultPlafon'));
    }

    /**
     * Create screening form directly from NUP Registration
     */
    public function createFromNup($nupRegistrationId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $nupRegistration = \App\Models\NupRegistration::findOrFail($nupRegistrationId);

        if ($nupRegistration->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$nupRegistration->isPaid()) {
            return redirect()->route('nup.show', $nupRegistration->id)
                ->with('error', 'Harap selesaikan pembayaran biaya pendaftaran NUP terlebih dahulu sebelum mengisi formulir screening KPR.');
        }

        if ($nupRegistration->kprScreening()->exists()) {
            return redirect()->route('kpr.show', $nupRegistration->kprScreening->id);
        }

        $kprBanks = config('nup.kpr_banks');
        $user = Auth::user();
        $autoOccupation = $nupRegistration->occupation ?? $user->occupation;
        $autoOccupationOther = $nupRegistration->occupation_other;
        $autoEmploymentStatus = KprScreening::resolveEmploymentStatus($autoOccupation);
        $autoOccupationLabel = KprScreening::getOccupationLabel($autoOccupation, $autoOccupationOther);

        // Ambil data harga rumah asli dari perumahan yang dipilih pada registrasi NUP
        $pref = $nupRegistration->housingPreferences->first();
        $preferredProject = null;
        $defaultPlafon = null;
        if ($pref && $pref->housing_name) {
            $preferredProject = \App\Models\HousingProject::where('name', $pref->housing_name)->with('housingUnits')->first();
            $availableUnit = $preferredProject?->housingUnits()->where('status', 'available')->first() 
                ?? $preferredProject?->housingUnits()->first();
            $defaultPlafon = $availableUnit?->price;
        }

        return view('kpr.create-from-nup', compact('nupRegistration', 'kprBanks', 'autoEmploymentStatus', 'autoOccupationLabel', 'preferredProject', 'defaultPlafon'));
    }

    /**
     * Store screening data
     */
    public function store(Request $request, $unitSelectionId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $unitSelection = UnitSelection::findOrFail($unitSelectionId);

        if ($unitSelection->user_id !== Auth::id()) {
            abort(403);
        }

        $user = Auth::user();
        $nupRegistration = $unitSelection->nupRegistration;
        $defaultEmploymentStatus = KprScreening::resolveEmploymentStatus($nupRegistration->occupation ?? $user->occupation);

        // Ambil file rekening koran dari salah satu input yang terisi
        $bankStatementFile = $request->file('bank_statement_file')
            ?? $request->file('bank_statement_employee')
            ?? $request->file('bank_statement_entrepreneur')
            ?? $request->file('bank_statement_other');

        $status = $request->input('employment_status', $defaultEmploymentStatus);

        $rules = [
            'employment_status' => 'required|in:employee,entrepreneur,professional,retired,other',
            'occupation_other' => 'nullable|string|max:150',
        ];

        // Validasi berkas persyaratan KPR sesuai profesi
        if ($status === 'employee') {
            $rules['sk_karyawan_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['salary_slip_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['bank_statement_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
        } elseif (in_array($status, ['entrepreneur', 'professional'])) {
            $rules['sku_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['financial_statement_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['bank_statement_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
        } elseif ($status === 'other') {
            $rules['occupation_other'] = 'required|string|max:150';
            $rules['bank_statement_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['additional_income_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
        }

        $messages = [
            'sk_karyawan_file.required' => 'SK Karyawan (SK Pengangkatan / Kontrak Kerja) wajib diunggah untuk ASN/Karyawan.',
            'salary_slip_file.required' => 'Slip Gaji 6 Bulan Terakhir wajib diunggah untuk ASN/Karyawan.',
            'bank_statement_file.required' => 'Rekening Koran 6 Bulan Terakhir wajib diunggah.',
            'sku_file.required' => 'Surat Keterangan Usaha (SKU / NIB OSS) wajib diunggah untuk Wiraswasta / Usaha.',
            'financial_statement_file.required' => 'Laporan Keuangan 6 Bulan Terakhir wajib diunggah untuk Wiraswasta / Usaha.',
            'additional_income_file.required' => 'Bukti Penghasilan / Dokumen Pendukung wajib diunggah.',
            'occupation_other.required' => 'Silakan tuliskan profesi/pekerjaan Anda secara mandiri.',
        ];

        $allData = $request->all();
        if ($bankStatementFile) {
            $allData['bank_statement_file'] = $bankStatementFile;
        }

        $validator = Validator::make($allData, $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $project = $unitSelection->housingUnit?->housingProject;
            $feeBase = (float) ($project?->screening_fee ?? 150000);
            if ($feeBase < 150000 && $feeBase > 0) $feeBase = 150000;
            $feeTotal = $feeBase + round($feeBase * 0.11, 2) + round($feeBase * 0.20, 2);

            $screening = KprScreening::create([
                'user_id' => Auth::id(),
                'nup_registration_id' => $unitSelection->nup_registration_id,
                'unit_selection_id' => $unitSelection->id,
                'screening_number' => KprScreening::generateScreeningNumber(),
                'bank' => $request->input('bank', 'btn'),
                'bank_other' => $request->input('bank_other'),
                'loan_amount' => $request->input('loan_amount', $unitSelection->housingUnit?->price ?? 0),
                'tenor_years' => $request->input('tenor_years', 15),
                'monthly_income' => $request->input('monthly_income', $unitSelection->nupRegistration->monthly_income ?? 0),
                'monthly_expense' => $request->input('monthly_expense', $unitSelection->nupRegistration->active_installment ?? 0),
                'employment_status' => $status,
                'employment_duration_years' => $request->input('employment_duration_years', 3),
                'employer_name' => $request->input('employer_name', $nupRegistration->full_name ?? Auth::user()->name),
                'screening_fee_amount' => $feeTotal,
                'screening_fee_status' => $feeTotal > 0 ? 'unpaid' : 'waived',
                'status' => 'pending',
                'submission_date' => now(),
            ]);

            // Simpan dokumen persyaratan KPR yang diunggah
            $docMap = [
                'sk_kerja' => $request->file('sk_karyawan_file'),
                'salary_slip' => $request->file('salary_slip_file'),
                'bank_statement' => $bankStatementFile ?? $request->file('bank_statement_file'),
                'sku_nib' => $request->file('sku_file'),
                'financial_statement' => $request->file('financial_statement_file'),
                'other' => $request->file('additional_income_file'),
            ];

            $nupUpdates = [];
            if ($request->filled('occupation_other')) {
                $nupUpdates['occupation_other'] = $request->occupation_other;
            }

            foreach ($docMap as $docType => $uploadedFile) {
                if (!$uploadedFile) continue;

                $fileName = time() . '_' . $uploadedFile->getClientOriginalName();
                $filePath = $uploadedFile->storeAs('documents/' . Auth::id(), $fileName, 'public');

                $screening->userDocuments()->create([
                    'user_id' => Auth::id(),
                    'document_type' => $docType,
                    'document_name' => $fileName,
                    'file_path' => $filePath,
                    'file_type' => $uploadedFile->getClientOriginalExtension() ?: 'pdf',
                    'file_size' => $uploadedFile->getSize(),
                    'status' => 'pending',
                    'uploaded_at' => now(),
                ]);

                if ($docType === 'sk_kerja') $nupUpdates['sk_karyawan_file'] = $filePath;
                if ($docType === 'salary_slip') $nupUpdates['salary_slip_file'] = $filePath;
                if ($docType === 'bank_statement') $nupUpdates['bank_statement_file'] = $filePath;
                if ($docType === 'sku_nib') $nupUpdates['sku_file'] = $filePath;
                if ($docType === 'financial_statement') $nupUpdates['financial_statement_file'] = $filePath;
                if ($docType === 'other') $nupUpdates['additional_income_file'] = $filePath;
            }

            if (!empty($nupUpdates) && $nupRegistration) {
                $nupRegistration->update($nupUpdates);
            }

            return redirect()->route('kpr.show', $screening->id)
                ->with('success', 'Data screening KPR dan berkas persyaratan berhasil disimpan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan data screening: ' . $e->getMessage());
        }
    }

    /**
     * Store screening directly from NUP Registration
     */
    public function storeFromNup(Request $request, $nupRegistrationId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $nupRegistration = \App\Models\NupRegistration::findOrFail($nupRegistrationId);

        if ($nupRegistration->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$nupRegistration->isPaid()) {
            return redirect()->route('nup.show', $nupRegistration->id)
                ->with('error', 'Harap selesaikan pembayaran biaya pendaftaran NUP terlebih dahulu sebelum mengirim formulir screening KPR.');
        }

        $user = Auth::user();
        $defaultEmploymentStatus = KprScreening::resolveEmploymentStatus($nupRegistration->occupation ?? $user->occupation);

        // Ambil file rekening koran dari salah satu input yang terisi
        $bankStatementFile = $request->file('bank_statement_file')
            ?? $request->file('bank_statement_employee')
            ?? $request->file('bank_statement_entrepreneur')
            ?? $request->file('bank_statement_other');

        $status = $request->input('employment_status', $defaultEmploymentStatus);

        $rules = [
            'employment_status' => 'required|in:employee,entrepreneur,professional,retired,other',
            'occupation_other' => 'nullable|string|max:150',
        ];

        // Validasi berkas persyaratan KPR sesuai profesi
        if ($status === 'employee') {
            $rules['sk_karyawan_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['salary_slip_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['bank_statement_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
        } elseif (in_array($status, ['entrepreneur', 'professional'])) {
            $rules['sku_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['financial_statement_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['bank_statement_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
        } elseif ($status === 'other') {
            $rules['occupation_other'] = 'required|string|max:150';
            $rules['bank_statement_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['additional_income_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
        }

        $messages = [
            'sk_karyawan_file.required' => 'SK Karyawan (SK Pengangkatan / Kontrak Kerja) wajib diunggah untuk ASN/Karyawan.',
            'salary_slip_file.required' => 'Slip Gaji 6 Bulan Terakhir wajib diunggah untuk ASN/Karyawan.',
            'bank_statement_file.required' => 'Rekening Koran 6 Bulan Terakhir wajib diunggah.',
            'sku_file.required' => 'Surat Keterangan Usaha (SKU / NIB OSS) wajib diunggah untuk Wiraswasta / Usaha.',
            'financial_statement_file.required' => 'Laporan Keuangan 6 Bulan Terakhir wajib diunggah untuk Wiraswasta / Usaha.',
            'additional_income_file.required' => 'Bukti Penghasilan / Dokumen Pendukung wajib diunggah.',
            'occupation_other.required' => 'Silakan tuliskan profesi/pekerjaan Anda secara mandiri.',
        ];

        $allData = $request->all();
        if ($bankStatementFile) {
            $allData['bank_statement_file'] = $bankStatementFile;
        }

        $validator = Validator::make($allData, $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $project = $nupRegistration->unitSelections()->latest()->first()?->housingUnit?->housingProject ?? \App\Models\HousingProject::first();
            $feeBase = (float) ($project?->screening_fee ?? 150000);
            if ($feeBase < 150000 && $feeBase > 0) $feeBase = 150000;
            $feeTotal = $feeBase + round($feeBase * 0.11, 2) + round($feeBase * 0.20, 2);

            $screening = KprScreening::create([
                'user_id' => Auth::id(),
                'nup_registration_id' => $nupRegistration->id,
                'unit_selection_id' => null,
                'screening_number' => KprScreening::generateScreeningNumber(),
                'bank' => $request->input('bank', null),
                'bank_other' => $request->input('bank_other'),
                'loan_amount' => $request->input('loan_amount', null),
                'tenor_years' => $request->input('tenor_years', null),
                'monthly_income' => $request->input('monthly_income', $nupRegistration->monthly_income ?? null),
                'monthly_expense' => $request->input('monthly_expense', $nupRegistration->active_installment ?? null),
                'employment_status' => $status,
                'employment_duration_years' => $request->input('employment_duration_years', null),
                'employer_name' => $request->input('employer_name', $nupRegistration->full_name ?? null),
                'screening_fee_amount' => $feeTotal,
                'screening_fee_status' => $feeTotal > 0 ? 'unpaid' : 'waived',
                'status' => 'pending',
                'submission_date' => now(),
            ]);

            // Simpan dokumen persyaratan KPR yang diunggah
            $docMap = [
                'sk_kerja' => $request->file('sk_karyawan_file'),
                'salary_slip' => $request->file('salary_slip_file'),
                'bank_statement' => $bankStatementFile ?? $request->file('bank_statement_file'),
                'sku_nib' => $request->file('sku_file'),
                'financial_statement' => $request->file('financial_statement_file'),
                'other' => $request->file('additional_income_file'),
            ];

            $nupUpdates = [];
            if ($request->filled('occupation_other')) {
                $nupUpdates['occupation_other'] = $request->occupation_other;
            }

            foreach ($docMap as $docType => $uploadedFile) {
                if (!$uploadedFile) continue;

                $fileName = time() . '_' . $uploadedFile->getClientOriginalName();
                $filePath = $uploadedFile->storeAs('documents/' . Auth::id(), $fileName, 'public');

                $screening->userDocuments()->create([
                    'user_id' => Auth::id(),
                    'document_type' => $docType,
                    'document_name' => $fileName,
                    'file_path' => $filePath,
                    'file_type' => $uploadedFile->getClientOriginalExtension() ?: 'pdf',
                    'file_size' => $uploadedFile->getSize(),
                    'status' => 'pending',
                    'uploaded_at' => now(),
                ]);

                if ($docType === 'sk_kerja') $nupUpdates['sk_karyawan_file'] = $filePath;
                if ($docType === 'salary_slip') $nupUpdates['salary_slip_file'] = $filePath;
                if ($docType === 'bank_statement') $nupUpdates['bank_statement_file'] = $filePath;
                if ($docType === 'sku_nib') $nupUpdates['sku_file'] = $filePath;
                if ($docType === 'financial_statement') $nupUpdates['financial_statement_file'] = $filePath;
                if ($docType === 'other') $nupUpdates['additional_income_file'] = $filePath;
            }

            if (!empty($nupUpdates) && $nupRegistration) {
                $nupRegistration->update($nupUpdates);
            }

            return redirect()->route('kpr.show', $screening->id)
                ->with('success', 'Data screening KPR dan berkas persyaratan berhasil disimpan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan data screening: ' . $e->getMessage());
        }
    }

    /**
     * Show screening details
     */
    public function show($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::with([
            'user',
            'unitSelection.housingUnit',
            'nupRegistration',
            'userDocuments'
        ])->findOrFail($id);

        if ($screening->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        // Sinkronkan berkas dokumen dari NUP jika ada yang belum tercatat
        $screening->syncNupDocuments();
        $screening->load('userDocuments');

        $kprBanks = config('nup.kpr_banks');
        $documentLabels = KprScreening::documentLabels();
        $requiredDocs = $screening->getRequiredDocuments();
        $feeBreakdown = $screening->getScreeningFeeBreakdown();
        if (!$screening->screening_fee_amount || $screening->screening_fee_amount == 0) {
            $screening->update(['screening_fee_amount' => $feeBreakdown['total']]);
        }
        $company = $screening->getHousingProject()?->company;
        $bankAccounts = $company ? $company->activeBankAccounts()->get() : \App\Models\DeveloperBankAccount::where('is_active', true)->get();

        return view('kpr.show', compact('screening', 'kprBanks', 'documentLabels', 'requiredDocs', 'feeBreakdown', 'bankAccounts', 'company'));
    }

    /**
     * Upload bukti pembayaran biaya administrasi screening manual KPR
     */
    public function uploadScreeningFeeProof(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($id);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'screening_fee_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'developer_bank_account_id' => 'nullable|exists:developer_bank_accounts,id',
            'sender_bank' => 'nullable|string|max:100',
            'sender_name' => 'nullable|string|max:150',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $proofPath = $request->file('screening_fee_proof')->store('screening_fee_proofs', 'public');
            $senderInfo = trim(($request->sender_name ?: Auth::user()->name) . ' (' . ($request->sender_bank ?: 'Bank') . ')');

            $updateData = [
                'screening_fee_proof' => $proofPath,
                'screening_fee_status' => 'pending_verification',
                'screening_fee_paid_at' => now(),
                'developer_bank_account_id' => $request->developer_bank_account_id,
                'screening_fee_notes' => 'Pengirim: ' . $senderInfo,
            ];

            // Cek apakah seluruh berkas persyaratan KPR sudah diunggah, jika ya langsung ajukan berkas (status: submitted)
            $requiredTypes = array_keys($screening->getRequiredDocuments());
            $uploadedTypes = $screening->userDocuments()
                ->whereIn('document_type', $requiredTypes)
                ->pluck('document_type')
                ->unique()
                ->toArray();

            $missing = array_diff($requiredTypes, $uploadedTypes);
            if (empty($missing)) {
                $updateData['status'] = 'submitted';
                $updateData['submission_date'] = now();
            }

            $screening->update($updateData);

            if (empty($missing)) {
                try {
                    $this->whatsappService->sendKprScreeningNotification(
                        $screening->user,
                        'submitted',
                        'Bukti pembayaran biaya screening dan seluruh berkas pengajuan KPR Anda telah berhasil dikirim dan sedang diverifikasi oleh tim analis.'
                    );
                } catch (\Exception $e) {
                    \Log::error('WA Notification KPR submit: ' . $e->getMessage());
                }

                return back()->with('success', 'Bukti transfer biaya screening dan seluruh berkas pengajuan KPR berhasil dikirim! Menunggu verifikasi petugas.');
            }

            return back()->with('success', 'Bukti pembayaran biaya screening berhasil diunggah! Lengkapi berkas persyaratan yang belum diunggah di bawah.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengunggah bukti pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Submit screening
     */
    public function submit($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($id);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        // Cek bukti biaya screening
        if ($screening->screening_fee_amount > 0 && $screening->screening_fee_status === 'unpaid') {
            return back()->with('error', 'Silakan unggah bukti transfer biaya administrasi screening terlebih dahulu.');
        }

        // Cek apakah seluruh berkas persyaratan sesuai profesi pemohon sudah diunggah
        $requiredTypes = array_keys($screening->getRequiredDocuments());
        $uploadedTypes = $screening->userDocuments()
            ->whereIn('document_type', $requiredTypes)
            ->pluck('document_type')
            ->unique()
            ->toArray();

        $missing = array_diff($requiredTypes, $uploadedTypes);
        if (!empty($missing)) {
            return back()->with('error', 'Harap unggah seluruh berkas persyaratan KPR yang diperlukan sebelum mengirim pengajuan.');
        }

        try {
            $screening->update([
                'status' => 'submitted',
                'submission_date' => now(),
            ]);

            $this->whatsappService->sendKprScreeningNotification(
                $screening->user,
                'submitted',
                'Screening Anda sedang diproses oleh bank.'
            );

            return back()->with('success', 'Screening berhasil dikirim untuk proses lebih lanjut.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim screening: ' . $e->getMessage());
        }
    }

    /**
     * Upload document (Karyawan / Wirausaha & Revisi Dokumen)
     */
    public function uploadDocument(Request $request, $screeningId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($screeningId);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'document_type' => 'required|string|max:100',
            'document' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ], [
            'document_type.required' => 'Pilih jenis dokumen yang diunggah.',
            'document.required' => 'Pilih file dokumen.',
            'document.max' => 'Ukuran file dokumen maksimal 10 MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $file = $request->file('document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents/' . Auth::id(), $fileName, 'public');

            // Cek apakah sebelumnya ada dokumen bertipe sama yang ditolak (Revisi dokumen)
            $existingDoc = $screening->userDocuments()
                ->where('document_type', $request->document_type)
                ->where('status', 'rejected')
                ->latest()
                ->first();

            if ($existingDoc) {
                // Perbarui dokumen yang direvisi
                $existingDoc->update([
                    'document_name' => $fileName,
                    'file_path' => $filePath,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'status' => 'pending',
                    'rejection_reason' => null,
                    'uploaded_at' => now(),
                ]);
            } else {
                $screening->userDocuments()->create([
                    'user_id' => Auth::id(),
                    'document_type' => $request->document_type,
                    'document_name' => $fileName,
                    'file_path' => $filePath,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'status' => 'pending',
                    'uploaded_at' => now(),
                ]);
            }

            // Jika bukti biaya screening sudah diunggah dan seluruh berkas sekarang telah lengkap, otomatis ajukan screening (status: submitted)
            if ($screening->screening_fee_proof && $screening->status === 'pending') {
                $requiredTypes = array_keys($screening->getRequiredDocuments());
                $uploadedTypes = $screening->userDocuments()
                    ->whereIn('document_type', $requiredTypes)
                    ->pluck('document_type')
                    ->unique()
                    ->toArray();

                $missing = array_diff($requiredTypes, $uploadedTypes);
                if (empty($missing)) {
                    $screening->update([
                        'status' => 'submitted',
                        'submission_date' => now(),
                    ]);
                    return back()->with('success', 'Dokumen berhasil diunggah dan seluruh berkas pengajuan KPR Anda telah dikirim untuk dianalisis!');
                }
            }

            return back()->with('success', 'Dokumen berhasil diunggah dan sedang ditinjau.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal upload dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Admin approve screening
     */
    public function approve(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $screening = KprScreening::findOrFail($id);

        try {
            $screening->update([
                'status' => 'approved',
                'approval_date' => now(),
                'approved_by' => Auth::user()->name,
            ]);

            $this->whatsappService->sendKprScreeningNotification(
                $screening->user,
                'approved',
                'Selamat! Screening KPR Anda telah disetujui.'
            );

            return back()->with('success', 'Screening berhasil disetujui.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui screening: ' . $e->getMessage());
        }
    }

    /**
     * Admin reject screening
     */
    public function reject(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $screening = KprScreening::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $screening->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
            ]);

            $this->whatsappService->sendKprScreeningNotification(
                $screening->user,
                'rejected',
                $request->rejection_reason
            );

            return back()->with('success', 'Screening berhasil ditolak.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak screening: ' . $e->getMessage());
        }
    }

    /**
     * Helper validasi hak akses operator terhadap berkas screening KPR
     */
    private function checkOperatorAccess(KprScreening $screening): void
    {
        $companyId = Auth::user()->getOperatorCompanyId();
        if (!$companyId) {
            return;
        }

        // 1. Cek unit selection langsung pada screening
        $unit = $screening->unitSelection?->housingUnit;
        if ($unit) {
            if ((int)$unit->company_id === (int)$companyId || (int)$unit->housingProject?->company_id === (int)$companyId) {
                return;
            }
        }

        // 2. Cek NUP Registration
        $nup = $screening->nupRegistration;
        if ($nup) {
            // 2a. Unit selection pada NUP
            $hasMatchingUnit = $nup->unitSelections()
                ->whereHas('housingUnit', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereHas('housingProject', fn($pq) => $pq->where('company_id', $companyId));
                })->exists();

            if ($hasMatchingUnit) {
                return;
            }

            // 2b. Preferensi perumahan pendaftar NUP (screening sebelum pilih unit)
            $hasMatchingPref = $nup->housingPreferences()
                ->whereHas('housingProject', fn($pq) => $pq->where('company_id', $companyId))
                ->exists();

            if ($hasMatchingPref) {
                return;
            }

            // 2c. Broker yang mendaftarkan NUP
            if ($nup->broker_id) {
                $broker = $nup->broker;
                if ($broker) {
                    if ((int)$broker->company_id === (int)$companyId) {
                        return;
                    }
                    if ($broker->user_id && \App\Models\Broker::where('user_id', $broker->user_id)->where('company_id', $companyId)->exists()) {
                        return;
                    }
                }
            }
        }

        // 3. Cek apakah konsumen memiliki riwayat unit/pesanan di perusahaan developer ini
        if ($screening->user_id) {
            $hasUserUnitInCompany = \App\Models\UnitSelection::where('user_id', $screening->user_id)
                ->whereHas('housingUnit', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereHas('housingProject', fn($pq) => $pq->where('company_id', $companyId));
                })->exists();

            if ($hasUserUnitInCompany) {
                return;
            }
        }

        abort(403, 'Akses ditolak: Data berkas screening KPR ini milik perusahaan developer lain.');
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

        // Show NUP registrations with documents (KTP, KK, etc.)
        $query = \App\Models\NupRegistration::with(['user', 'unitSelections.housingUnit', 'kprScreening'])
            ->where('payment_status', 'paid')
            ->where(function($query) {
                $query->whereNotNull('ktp_file')
                      ->orWhereNotNull('kk_file')
                      ->orWhereNotNull('npwp_file');
            });

        // Filter jika login sebagai Operator Mitra Developer
        if ($companyId) {
            $query->where(function ($mainQ) use ($companyId) {
                $mainQ->whereHas('unitSelections.housingUnit', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereHas('housingProject', fn($pq) => $pq->where('company_id', $companyId));
                })->orWhereHas('housingPreferences.housingProject', function ($hpq) use ($companyId) {
                    $hpq->where('company_id', $companyId);
                })->orWhereHas('broker', function ($bq) use ($companyId) {
                    $bq->where('company_id', $companyId);
                })->orWhereHas('user.unitSelections.housingUnit', function ($uq) use ($companyId) {
                    $uq->where('company_id', $companyId);
                });
            });
        }

        $registrations = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.kpr.index', compact('registrations'));
    }

    /**
     * Admin show screening details
     */
    public function adminShow($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $screening = KprScreening::with([
            'user',
            'unitSelection.housingUnit',
            'nupRegistration.unitSelections.housingUnit',
            'userDocuments'
        ])->findOrFail($id);

        $this->checkOperatorAccess($screening);

        $kprBanks = config('nup.kpr_banks');
        $documentLabels = KprScreening::documentLabels();
        $requiredDocs = $screening->getRequiredDocuments();
        $feeBreakdown = $screening->getScreeningFeeBreakdown();

        return view('admin.kpr.show', compact('screening', 'kprBanks', 'documentLabels', 'requiredDocs', 'feeBreakdown'));
    }

    /**
     * Operator verifikasi pembayaran biaya screening
     */
    public function verifyScreeningFee(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $screening = KprScreening::with(['unitSelection.housingUnit', 'nupRegistration.unitSelections.housingUnit'])->findOrFail($id);
        $this->checkOperatorAccess($screening);

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        if ($request->action === 'approve') {
            $screening->update([
                'screening_fee_status' => 'verified',
                'screening_fee_verified_at' => now(),
                'screening_fee_verified_by' => Auth::user()->name,
            ]);
            return back()->with('success', 'Pembayaran biaya screening Pefindo berhasil diverifikasi (LUNAS). Anda sekarang dapat memproses skor kredit Pefindo pemohon.');
        } else {
            $screening->update([
                'screening_fee_status' => 'unpaid',
                'screening_fee_notes' => $request->notes ?: 'Bukti transfer biaya screening ditolak oleh operator.',
            ]);
            return back()->with('warning', 'Bukti transfer ditolak. Status dikembalikan ke belum bayar agar user dapat mengunggah bukti yang valid.');
        }
    }

    /**
     * Operator / Admin verifikasi dokumen individual (Approve / Revisi)
     */
    public function verifyDocument(Request $request, $documentId)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $document = \App\Models\UserDocument::with(['kprScreening.unitSelection.housingUnit', 'kprScreening.nupRegistration.unitSelections.housingUnit'])->findOrFail($documentId);
        if ($document->kprScreening) {
            $this->checkOperatorAccess($document->kprScreening);
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        if ($request->action === 'approve') {
            $document->update([
                'status' => 'approved',
                'rejection_reason' => null,
                'verified_at' => now(),
                'verified_by' => Auth::user()->name,
            ]);
            $msg = 'Dokumen berhasil diverifikasi dan disetujui.';
        } else {
            $document->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'verified_at' => now(),
                'verified_by' => Auth::user()->name,
            ]);
            $msg = 'Dokumen ditandai perlu revisi.';
        }

        return back()->with('success', $msg);
    }

    /**
     * Operator Input Skor Kredit Pefindo & Keputusan Screening KPR
     */
    public function savePefindoScore(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $screening = KprScreening::with(['unitSelection.housingUnit', 'nupRegistration.unitSelections.housingUnit'])->findOrFail($id);
        $this->checkOperatorAccess($screening);

        if (!$screening->areAllRequiredDocumentsApproved()) {
            return back()->with('error', 'Semua berkas persyaratan screening harus disetujui (ACC) terlebih dahulu sebelum menginput skor Pefindo & Keputusan.');
        }

        if (!$screening->isScreeningFeeVerified()) {
            return back()->with('error', 'Biaya administrasi screening harus diverifikasi lunas terlebih dahulu sebelum menginput skor Pefindo & Keputusan.');
        }

        $validator = Validator::make($request->all(), [
            'pefindo_score' => 'required|string|max:50',
            'pefindo_grade' => 'required|string|max:100',
            'status' => 'required|in:approved,layak_bersyarat,rejected',
            'bank' => 'nullable|string|max:50',
            'bank_other' => 'nullable|string|max:100',
            'pefindo_notes' => 'nullable|string|max:1000',
            'pefindo_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = [
            'pefindo_score' => $request->pefindo_score,
            'pefindo_grade' => $request->pefindo_grade,
            'pefindo_notes' => $request->pefindo_notes,
            'status' => $request->status,
            'approval_date' => now(),
            'approved_by' => Auth::user()->name,
        ];

        if ($request->has('bank')) {
            $data['bank'] = $request->filled('bank') ? $request->bank : null;
            $data['bank_other'] = ($request->bank === 'other') ? $request->bank_other : null;
        }

        if ($request->hasFile('pefindo_file')) {
            $data['pefindo_file'] = $request->file('pefindo_file')->store('pefindo', 'public');
        }

        $screening->update($data);

        // Kirim WhatsApp notification ke pemohon
        try {
            if ($screening->user && $screening->user->phone) {
                $statusLabel = match($request->status) {
                    'approved' => 'LAYAK PROSES KPR (HIJAU)',
                    'layak_bersyarat' => 'LAYAK BERSYARAT (KUNING)',
                    'rejected' => 'TIDAK LAYAK KPR (MERAH)',
                    default => $request->status
                };
                $msg = "Halo {$screening->user->name},\n\nHasil pengecekan Skor Pefindo & Screening KPR Anda:\nSkor: {$request->pefindo_score} ({$request->pefindo_grade})\nStatus: {$statusLabel}\n\nSilakan buka aplikasi untuk melihat rincian dan menentukan langkah selanjutnya (Lanjut KPR / Alih ke Tunai atau In-House / Batal).";
                $this->whatsappService->sendNotification($screening->user->phone, $msg, 'pefindo_result', $screening->user->id);
            }
        } catch (\Exception $e) {
            \Log::error('WA Notification Pefindo: ' . $e->getMessage());
        }

        return back()->with('success', 'Hasil Skor Kredit Pefindo & Status Screening berhasil disimpan!');
    }

    /**
     * Download / View document
     */
    public function downloadDocument($documentId)
    {
        try {
            if (!Auth::check()) {
                abort(403);
            }

            $document = \App\Models\UserDocument::findOrFail($documentId);

            // Check if user has access (admin or document owner)
            if (!Auth::user()->isAdmin() && $document->user_id !== Auth::id()) {
                abort(403);
            }

            // 1. Cek di disk public (baik nup_documents maupun documents)
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($document->file_path)) {
                $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($document->file_path);
                $mimeType = @mime_content_type($filePath) ?: 'application/octet-stream';
                return response()->file($filePath, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="' . $document->document_name . '"',
                ]);
            }

            // 2. Cek di disk local (storage/app/...)
            if (\Illuminate\Support\Facades\Storage::disk('local')->exists($document->file_path)) {
                $filePath = \Illuminate\Support\Facades\Storage::disk('local')->path($document->file_path);
                $mimeType = @mime_content_type($filePath) ?: 'application/octet-stream';
                return response()->file($filePath, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="' . $document->document_name . '"',
                ]);
            }

            // 3. Cek di path fisik storage/app/public/
            $publicPath = storage_path('app/public/' . $document->file_path);
            if (file_exists($publicPath)) {
                $mimeType = @mime_content_type($publicPath) ?: 'application/octet-stream';
                return response()->file($publicPath, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="' . $document->document_name . '"',
                ]);
            }

            // 4. Cek di path fisik storage/app/
            $appPath = storage_path('app/' . $document->file_path);
            if (file_exists($appPath)) {
                $mimeType = @mime_content_type($appPath) ?: 'application/octet-stream';
                return response()->file($appPath, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="' . $document->document_name . '"',
                ]);
            }

            // 5. Cek di public_path('storage/...')
            $webPublicPath = public_path('storage/' . $document->file_path);
            if (file_exists($webPublicPath)) {
                $mimeType = @mime_content_type($webPublicPath) ?: 'application/octet-stream';
                return response()->file($webPublicPath, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="' . $document->document_name . '"',
                ]);
            }

            \Log::error('File not found for download', [
                'document_id' => $documentId,
                'file_path' => $document->file_path,
            ]);
            abort(404, 'File dokumen tidak ditemukan di server.');

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Download document error', [
                'document_id' => $documentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Terjadi kesalahan saat mengunduh dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Process screening (admin)
     */
    public function process($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $screening = KprScreening::findOrFail($id);

        try {
            $screening->update(['status' => 'processing']);

            $this->whatsappService->sendKprScreeningNotification(
                $screening->user,
                'processing',
                'Screening Anda sedang dalam proses verifikasi.'
            );

            return back()->with('success', 'Screening status updated.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal update status: ' . $e->getMessage());
        }
    }

    /**
     * Handle screening outcome - User selects option after screening result
     */
    public function handleOutcome(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::with(['unitSelection', 'nupRegistration'])->findOrFail($id);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        // Only allow outcome handling if screening is evaluated (approved, layak_bersyarat, or rejected)
        if (!in_array($screening->status, ['approved', 'layak_bersyarat', 'rejected'])) {
            return back()->with('error', 'Screening harus telah dievaluasi untuk melanjutkan.');
        }

        if ($screening->status === 'rejected' && in_array($request->outcome, ['select_unit', 'agree_utk'], true)) {
            return back()->with('error', 'Pengajuan KPR Anda ditolak oleh perbankan. Silakan pilih beralih ke skema In-House atau Tunai.');
        }

        $validator = Validator::make($request->all(), [
            'outcome' => 'required|in:select_unit,agree_utk,switch_to_inhouse,switch_to_cash,not_agree',
            'agreed_to_spr_terms' => 'nullable',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $nupRegistration = $screening->nupRegistration;

            $decisionValue = in_array($request->outcome, ['select_unit', 'agree_utk']) ? 'select_unit' : $request->outcome;
            $screening->recordParticipantDecision($decisionValue);

            switch ($request->outcome) {
                case 'select_unit':
                case 'agree_utk':
                    if ($screening->unitSelection) {
                        if (!$screening->unitSelection->isUtkPaid() && !$screening->unitSelection->isCash()) {
                            return redirect()->route('utk.create', $screening->unitSelection->id)
                                ->with('success', 'Hasil verifikasi skor kredit KPR Anda dinyatakan LAYAK! Silakan lanjutkan pembayaran Uang Tanda Komitmen (UTK) untuk mengunci unit kavling Anda.');
                        }
                        return redirect()->route('spr.create', $screening->unitSelection->id)
                            ->with('info', 'Silakan lanjutkan pembuatan & penandatanganan SPR.');
                    }
                    if ($nupRegistration) {
                        return redirect()->route('units.create', $nupRegistration->id)
                            ->with('info', 'Silakan pilih unit dari stok yang tersedia.');
                    }
                    return redirect()->route('units.index');

                case 'switch_to_inhouse':
                    $screening->update([
                        'participant_decision' => 'switch_to_inhouse',
                        'decision_made_at' => now(),
                    ]);

                    if ($nupRegistration) {
                        $nupRegistration->update([
                            'payment_method' => 'inhouse',
                            'original_payment_method' => $nupRegistration->original_payment_method ?: $nupRegistration->payment_method,
                            'payment_method_switched_at' => now(),
                            'payment_method_switch_reason' => 'Beralih ke In-House Developer setelah hasil Pefindo',
                            'kpr_switched' => true,
                            'kpr_switch_notes' => 'Beralih ke In-House Developer setelah hasil Pefindo',
                        ]);
                    }

                    if ($screening->unitSelection) {
                        $screening->unitSelection->update([
                            'payment_scheme' => 'inhouse',
                        ]);
                        return redirect()->route('units.show', $screening->unitSelection->id)
                            ->with('success', 'Berhasil beralih ke skema In-House (Cicilan Pengembang)! Unit Anda tetap terkunci, silakan selesaikan pembayaran UTK untuk mengonfirmasi unit.');
                    }

                    if ($nupRegistration) {
                        return redirect()->route('units.create', $nupRegistration->id)
                            ->with('success', 'Berhasil beralih ke skema In-House (Cicilan Pengembang). Silakan pilih unit.');
                    }
                    return redirect()->route('units.index');

                case 'switch_to_cash':
                    $screening->update([
                        'participant_decision' => 'switch_to_cash',
                        'decision_made_at' => now(),
                    ]);

                    if ($nupRegistration) {
                        $nupRegistration->update([
                            'payment_method' => 'cash',
                            'original_payment_method' => $nupRegistration->original_payment_method ?: $nupRegistration->payment_method,
                            'payment_method_switched_at' => now(),
                            'payment_method_switch_reason' => 'Beralih ke Tunai setelah hasil Pefindo',
                            'kpr_switched' => true,
                            'kpr_switch_notes' => 'Beralih ke Tunai setelah hasil Pefindo',
                        ]);
                    }

                    if ($screening->unitSelection) {
                        $screening->unitSelection->update([
                            'payment_scheme' => 'cash',
                            'status' => 'confirmed',
                            'utk_payment_status' => 'paid',
                            'utk_payment_date' => now(),
                        ]);
                        return redirect()->route('units.show', $screening->unitSelection->id)
                            ->with('success', 'Berhasil beralih ke skema Pembayaran Tunai! Khusus skema Tunai, biaya UTK dihapuskan (Rp 0). Anda dapat langsung melanjutkan ke pembuatan SPR.');
                    }

                    if ($nupRegistration) {
                        return redirect()->route('units.create', $nupRegistration->id)
                            ->with('success', 'Berhasil beralih ke skema Tunai. Silakan pilih unit.');
                    }
                    return redirect()->route('units.index');

                case 'not_agree':
                    $screening->update([
                        'status' => 'rejected',
                        'participant_decision' => 'not_agree',
                        'decision_made_at' => now(),
                        'rejection_reason' => 'User membatalkan pengajuan KPR',
                    ]);

                    if ($nupRegistration) {
                        $nupRegistration->markAsForfeited('Pembeli membatalkan pengajuan KPR secara mandiri');
                    }

                    return redirect()->route('dashboard')
                        ->with('info', 'Pengajuan KPR dan antrean NUP Anda telah resmi dibatalkan.');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses pilihan: ' . $e->getMessage());
        }
    }

    /**
     * Show screening outcome selection page
     */
    public function showOutcome($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::with(['unitSelection.housingUnit', 'nupRegistration'])->findOrFail($id);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        // Allow outcome page if screening is evaluated (approved, layak_bersyarat, or rejected)
        if (!in_array($screening->status, ['approved', 'layak_bersyarat', 'rejected'])) {
            return back()->with('error', 'Screening harus telah dievaluasi untuk melihat opsi lanjutan.');
        }

        // Get UTK amount from config or default
        $utkAmount = config('nup.utk_amount', 5000000);

        return view('kpr.outcome', compact('screening', 'utkAmount'));
    }

    /**
     * Make decision when KPR screening is not eligible
     */
    public function makeDecision(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($id);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'decision' => 'required|in:request_refund,switch_to_cash,switch_to_mitra_pintar',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            switch ($request->decision) {
                case 'request_refund':
                    // Process refund request
                    $screening->nupRegistration->update([
                        'refund_status' => 'requested',
                        'refund_requested_at' => now(),
                        'refund_notes' => $request->notes,
                    ]);

                    $this->whatsappService->sendKprScreeningNotification(
                        $screening->user,
                        'refund_requested',
                        'Request refund NUP Anda sedang diproses.'
                    );

                    return redirect()->route('dashboard')
                        ->with('success', 'Request refund NUP berhasil diajukan.');

                case 'switch_to_cash':
                    // Switch to cash payment
                    $screening->nupRegistration->update([
                        'payment_method' => 'tunai',
                        'kpr_switched' => true,
                        'kpr_switch_notes' => $request->notes,
                    ]);

                    $this->whatsappService->sendKprScreeningNotification(
                        $screening->user,
                        'switched_to_cash',
                        'Anda beralih ke pembayaran tunai.'
                    );

                    return redirect()->route('dashboard')
                        ->with('success', 'Berhasil beralih ke pembayaran tunai.');

                case 'switch_to_mitra_pintar':
                    // Switch to Mitra Pintar
                    $screening->nupRegistration->update([
                        'payment_method' => 'tunai_mitra_pintar',
                        'kpr_switched' => true,
                        'kpr_switch_notes' => $request->notes,
                    ]);

                    $this->whatsappService->sendKprScreeningNotification(
                        $screening->user,
                        'switched_to_mitra_pintar',
                        'Anda beralih ke Mitra Pintar.'
                    );

                    return redirect()->route('dashboard')
                        ->with('success', 'Berhasil beralih ke Mitra Pintar.');
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses keputusan: ' . $e->getMessage());
        }
    }

    /**
     * List KPR payments for user
     */
    public function paymentList()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screenings = KprScreening::with(['unitSelection.housingUnit', 'nupRegistration'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['approved', 'layak_bersyarat'])
            ->whereHas('kprPayments')
            ->get();

        return view('kpr.payment-list', compact('screenings'));
    }

    /**
     * Show KPR payment details
     */
    public function paymentShow($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::with([
            'unitSelection.housingUnit',
            'nupRegistration',
            'kprPayments'
        ])->findOrFail($id);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        return view('kpr.payment-show', compact('screening'));
    }

    /**
     * Process KPR payment
     */
    public function processPayment(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($id);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $payment = $screening->kprPayments()->create([
                'payment_number' => \App\Models\KprPayment::generatePaymentNumber(),
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'user_id' => Auth::id(),
                'payment_type' => 'installment',
            ]);

            $checkout = $this->paymentService->createDokuCheckout(
                $payment->payment_number,
                (int) $payment->amount,
                Auth::user(),
                route('kpr.show', $screening->id),
                $payment->payment_method
            );
            $payment->update([
                'transaction_id' => $checkout['request_id'],
                'payment_response' => $checkout['response'],
            ]);

            $this->whatsappService->sendKprScreeningNotification(
                $screening->user,
                'payment_submitted',
                'Silakan selesaikan pembayaran KPR melalui halaman DOKU.'
            );
            return redirect()->away($checkout['checkout_url']);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }
}
