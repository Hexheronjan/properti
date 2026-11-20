<?php

namespace App\Http\Controllers;

use App\Models\NupRegistration;
use App\Models\User;
use App\Services\PaymentGatewayService;
use App\Services\WhatsAppGatewayService;
use App\Services\NupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class NupRegistrationController extends Controller
{
    protected $paymentService;
    protected $whatsappService;
    protected $nupService;

    public function __construct(PaymentGatewayService $paymentService, WhatsAppGatewayService $whatsappService, NupService $nupService)
    {
        $this->paymentService = $paymentService;
        $this->whatsappService = $whatsappService;
        $this->nupService = $nupService;
    }

    /** Download an uploaded NUP document for an administrator. */
    public function downloadDocument(NupRegistration $registration, string $type)
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $companyId = Auth::user()->getOperatorCompanyId();
        if ($companyId) {
            abort_unless($this->hasOperatorAccess($registration, $companyId), 403, 'Akses ditolak: Dokumen ini milik konsumen dari perusahaan developer lain.');
        }

        $documentFields = [
            'ktp' => 'ktp_file',
            'kk' => 'kk_file',
            'npwp' => 'npwp_file',
            'spouse_ktp' => 'spouse_ktp_file',
            'spouse_kk' => 'spouse_kk_file',
            'marriage_certificate' => 'marriage_certificate_file',
            'divorce_death_certificate' => 'divorce_death_certificate_file',
            'sk_karyawan' => 'sk_karyawan_file',
            'salary_slip' => 'salary_slip_file',
            'bank_statement' => 'bank_statement_file',
            'sku' => 'sku_file',
            'financial_statement' => 'financial_statement_file',
            'additional_income' => 'additional_income_file',
        ];

        abort_unless(isset($documentFields[$type]), 404);

        $path = $registration->{$documentFields[$type]};
        abort_unless($path && Storage::disk('public')->exists($path), 404, 'Dokumen tidak ditemukan');

        return Storage::disk('public')->download($path, basename($path));
    }

    /**
     * Display registration form
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // User tetap diizinkan membuka formulir Daftar NUP
        $hasUnpaidHouse = Auth::user()->hasUnpaidHouse();

        $paymentMethods = config('nup.payment_methods');
        $nupFee = config('nup.nup_fee');
        $housingProjects = \App\Models\HousingProject::active()->hasAvailableUnits()->with(['company', 'broker'])->get();
        $brokers = \App\Models\Broker::active()->get();

        return view('nup.register', compact('paymentMethods', 'nupFee', 'housingProjects', 'brokers', 'hasUnpaidHouse'));
    }

    /**
     * Store new registration
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Pengecekan rumah belum lunas saat formulir di-submit
        if (Auth::user()->hasUnpaidHouse()) {
            return back()->withInput()
                ->with('error', 'Pendaftaran NUP baru tidak dapat diproses karena Anda masih memiliki pembayaran rumah yang belum lunas. Silakan selesaikan pelunasan rumah Anda terlebih dahulu.');
        }

        $rules = [
            'full_name' => 'required|string|max:255',
            'address' => 'required|string',
            'domicile_address' => 'nullable|string',
            'phone' => 'required|string|max:20',
            'occupation' => 'required|string|max:100',
            'occupation_other' => 'nullable|string|max:150',
            'marital_status' => 'required|in:menikah,belum_menikah,cerai_hidup,cerai_meninggal',
            'monthly_income' => 'required|numeric|min:0',
            'active_installment' => 'nullable|numeric|min:0',
            'nik' => 'required|string|max:16|unique:users,nik,' . Auth::id(),
            'birth_date' => 'required|date',
            'ktp_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'kk_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'npwp_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'spouse_ktp_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'spouse_kk_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'marriage_certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'divorce_death_certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'agreement_accepted' => 'required|accepted',
            'housing_preferences' => 'nullable|array',
            'housing_preferences.*.housing_name' => 'nullable|string|max:255',
            'housing_preferences.*.broker_name' => 'nullable|string|max:255',
            'housing_preferences.*.marketing_name' => 'nullable|string|max:255',
            'housing_preferences.*.marketing_contact' => 'nullable|string|max:20',
            'housing_preferences.*.purchase_plan' => 'nullable|in:kpr_bank_flpp,inhouse,tunai,kredit_mitra_pintar',
        ];

        // Validasi profesi lainnya
        if ($request->occupation === 'lainnya') {
            $rules['occupation_other'] = 'required|string|max:150';
        }

        // Conditional validation based on marital status
        if ($request->marital_status === 'menikah') {
            $rules['spouse_ktp_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['marriage_certificate_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
        } elseif (in_array($request->marital_status, ['cerai_hidup', 'cerai_meninggal'])) {
            $rules['divorce_death_certificate_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
        }

        $messages = [
            'occupation_other.required' => 'Silakan tuliskan profesi/pekerjaan Anda secara manual pada kolom yang tersedia.',
            'spouse_ktp_file.required' => 'KTP Pasangan wajib diunggah untuk status menikah.',
            'marriage_certificate_file.required' => 'Buku Nikah / Surat Nikah wajib diunggah untuk status menikah.',
            'divorce_death_certificate_file.required' => 'Surat Cerai / Surat Kematian wajib diunggah.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Handle file uploads
            $ktpPath = null;
            $kkPath = null;
            $npwpPath = null;
            $spouseKtpPath = null;
            $spouseKkPath = null;
            $marriageCertificatePath = null;
            $divorceDeathCertificatePath = null;
            $skKaryawanPath = null;
            $salarySlipPath = null;
            $bankStatementPath = null;
            $skuPath = null;
            $financialStatementPath = null;
            $additionalIncomePath = null;

            if ($request->hasFile('ktp_file')) {
                $ktpFile = $request->file('ktp_file');
                $ktpPath = $ktpFile->store('nup_documents/ktp', 'public');
            }

            if ($request->hasFile('kk_file')) {
                $kkFile = $request->file('kk_file');
                $kkPath = $kkFile->store('nup_documents/kk', 'public');
            }

            if ($request->hasFile('npwp_file')) {
                $npwpFile = $request->file('npwp_file');
                $npwpPath = $npwpFile->store('nup_documents/npwp', 'public');
            }

            if ($request->hasFile('spouse_ktp_file')) {
                $spouseKtpFile = $request->file('spouse_ktp_file');
                $spouseKtpPath = $spouseKtpFile->store('nup_documents/spouse_ktp', 'public');
            }

            if ($request->hasFile('spouse_kk_file')) {
                $spouseKkFile = $request->file('spouse_kk_file');
                $spouseKkPath = $spouseKkFile->store('nup_documents/spouse_kk', 'public');
            }

            if ($request->hasFile('marriage_certificate_file')) {
                $marriageCertificateFile = $request->file('marriage_certificate_file');
                $marriageCertificatePath = $marriageCertificateFile->store('nup_documents/marriage_certificate', 'public');
            }

            if ($request->hasFile('divorce_death_certificate_file')) {
                $divorceDeathCertificateFile = $request->file('divorce_death_certificate_file');
                $divorceDeathCertificatePath = $divorceDeathCertificateFile->store('nup_documents/divorce_death_certificate', 'public');
            }

            if ($request->hasFile('sk_karyawan_file')) {
                $skKaryawanPath = $request->file('sk_karyawan_file')->store('nup_documents/sk_karyawan', 'public');
            }

            if ($request->hasFile('salary_slip_file')) {
                $salarySlipPath = $request->file('salary_slip_file')->store('nup_documents/salary_slip', 'public');
            }

            if ($request->hasFile('bank_statement_file')) {
                $bankStatementPath = $request->file('bank_statement_file')->store('nup_documents/bank_statement', 'public');
            }

            if ($request->hasFile('sku_file')) {
                $skuPath = $request->file('sku_file')->store('nup_documents/sku', 'public');
            }

            if ($request->hasFile('financial_statement_file')) {
                $financialStatementPath = $request->file('financial_statement_file')->store('nup_documents/financial_statement', 'public');
            }

            if ($request->hasFile('additional_income_file')) {
                $additionalIncomePath = $request->file('additional_income_file')->store('nup_documents/additional_income', 'public');
            }

            // Update user profile hanya jika data akun masih kosong (tidak menimpa nama akun utama)
            $user = Auth::user();
            $userDataToUpdate = [];
            if (empty($user->name)) $userDataToUpdate['name'] = $request->full_name;
            if (empty($user->phone)) $userDataToUpdate['phone'] = $request->phone;
            if (empty($user->nik)) $userDataToUpdate['nik'] = $request->nik;
            if (empty($user->address)) $userDataToUpdate['address'] = $request->address;
            if (empty($user->birth_date)) $userDataToUpdate['birth_date'] = $request->birth_date;
            if (empty($user->occupation)) $userDataToUpdate['occupation'] = $request->occupation;
            if (!empty($userDataToUpdate)) {
                $user->update($userDataToUpdate);
            }

            // Check if user already has an active unused NUP registration or pending payment
            // (1 NUP = 1 Unit Rumah: NUP lama yang sudah digunakan untuk unit atau NUP yang sudah hangus/batal tidak memblokir pendaftaran NUP baru)
            $existingActiveNup = NupRegistration::where('user_id', $user->id)
                ->where('is_forfeited', false)
                ->where('is_expired', false)
                ->where(function ($query) {
                    $query->where('payment_status', 'pending')
                        ->orWhere(function ($q) {
                            $q->where('payment_status', 'paid')
                              ->whereDoesntHave('unitSelections', function ($sq) {
                                  $sq->where('status', '!=', 'cancelled');
                              });
                        });
                })
                ->where(function ($query) {
                    // Kecualikan jika NUP KPR yang screening-nya sudah ditolak atau dibatalkan tanpa alih skema
                    $query->where('payment_method', '!=', 'kpr_bank_flpp')
                        ->orWhereDoesntHave('kprScreening', function ($sq) {
                            $sq->where('status', 'rejected')
                                ->orWhere('participant_decision', 'not_agree');
                        });
                })
                ->first();

            if ($existingActiveNup) {
                if ($existingActiveNup->payment_status === 'pending') {
                    return back()->withInput()->with('error', 'Anda masih memiliki pendaftaran NUP (' . $existingActiveNup->registration_number . ') yang belum dibayar. Silakan selesaikan pembayaran NUP tersebut terlebih dahulu.');
                }

                return back()->withInput()->with('error', 'Anda sudah memiliki tiket NUP aktif (' . ($existingActiveNup->queue_number ?: $existingActiveNup->registration_number) . ') yang belum digunakan. Silakan gunakan tiket tersebut untuk memilih unit di menu Pilih Unit.');
            }

            // Create NUP registration (Data pemesan disimpan independen per registrasi unit rumah)
            $registration = NupRegistration::create([
                'user_id' => $user->id,
                'full_name' => $request->full_name ?: $user->name,
                'nik' => $request->nik ?: $user->nik,
                'phone' => $request->phone ?: $user->phone,
                'address' => $request->address ?: $user->address,
                'birth_date' => $request->birth_date ?: $user->birth_date,
                'occupation' => $request->occupation ?: $user->occupation,
                'occupation_other' => $request->occupation === 'lainnya' ? $request->occupation_other : null,
                'registration_number' => NupRegistration::generateRegistrationNumber(),
                'payment_method' => 'tunai_mitra_pintar', // Default payment method
                'payment_status' => 'pending',
                'nup_fee' => config('nup.nup_fee'),
                'domicile_address' => $request->domicile_address,
                'marital_status' => $request->marital_status,
                'monthly_income' => $request->monthly_income,
                'active_installment' => $request->active_installment,
                'ktp_file' => $ktpPath,
                'kk_file' => $kkPath,
                'npwp_file' => $npwpPath,
                'sk_karyawan_file' => $skKaryawanPath,
                'salary_slip_file' => $salarySlipPath,
                'bank_statement_file' => $bankStatementPath,
                'sku_file' => $skuPath,
                'financial_statement_file' => $financialStatementPath,
                'additional_income_file' => $additionalIncomePath,
                'spouse_ktp_file' => $spouseKtpPath,
                'spouse_kk_file' => $spouseKkPath,
                'marriage_certificate_file' => $marriageCertificatePath,
                'divorce_death_certificate_file' => $divorceDeathCertificatePath,
                'agreement_accepted' => $request->has('agreement_accepted'),
            ]);

            // Handle housing preferences
            if ($request->has('housing_preferences') && is_array($request->housing_preferences)) {
                foreach ($request->housing_preferences as $index => $preference) {
                    $brokerName = $preference['broker_name'] ?? null;
                    if ($brokerName === 'none') {
                        $brokerName = null;
                    }

                    if (!empty($preference['housing_name']) || !empty($brokerName) ||
                        !empty($preference['marketing_name']) || !empty($preference['purchase_plan'])) {
                        \App\Models\HousingPreference::create([
                            'nup_registration_id' => $registration->id,
                            'housing_name' => $preference['housing_name'] ?? null,
                            'broker_name' => $brokerName,
                            'marketing_name' => $preference['marketing_name'] ?? null,
                            'marketing_contact' => $preference['marketing_contact'] ?? null,
                            'purchase_plan' => $preference['purchase_plan'] ?? null,
                            'priority_order' => $index + 1,
                        ]);
                    }
                }

                // Otomatis sinkronkan data broker & marketing pilihan utama ke pendaftaran NUP
                $firstPref = $request->input('housing_preferences.0');
                if ($firstPref) {
                    $brokerId = $firstPref['broker_id'] ?? null;
                    $brokerName = $firstPref['broker_name'] ?? null;
                    if ($brokerName === 'none') {
                        $brokerId = null;
                        $brokerName = null;
                    }
                    $mktName = $firstPref['marketing_name'] ?? $brokerName;
                    $mktContact = $firstPref['marketing_contact'] ?? null;

                    if (!$brokerId && $brokerName) {
                        $matchedBroker = \App\Models\Broker::where('name', $brokerName)->first();
                        $brokerId = $matchedBroker?->id;
                    }

                    $registration->update([
                        'broker_id' => $brokerId,
                        'agent_name' => $mktName,
                        'agent_contact' => $mktContact,
                    ]);
                }
            }

            // WhatsApp notification (dinonaktifkan sementara)
            // $this->whatsappService->sendRegistrationNotification($user, $registration->registration_number);

            // Periksa apakah rencana pembelian adalah KPR
            $isKprPlan = ($firstPref['purchase_plan'] ?? null) === 'kpr_bank_flpp'
                         || $request->payment_method === 'kpr_bank_flpp';

            if ($isKprPlan) {
                $registration->update(['payment_method' => 'kpr_bank_flpp']);
            }

            return redirect()->route('nup.show', $registration->id)
                ->with('success', 'Registrasi NUP berhasil! Silakan selesaikan pembayaran biaya pendaftaran NUP.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal melakukan registrasi: ' . $e->getMessage());
        }
    }

    /**
     * Show registration details
     */
    public function show(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $registration = NupRegistration::with('user')->findOrFail($id);

        if ($registration->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($registration->payment_status === 'pending') {
            $transactionId = $request->query('transaction_id');
            $orderId = $request->query('order_id');

            $pendingTransactions = \App\Models\PaymentTransaction::where('nup_registration_id', $registration->id)
                ->where('status', 'pending')
                ->get();

            foreach ($pendingTransactions as $transaction) {
                $txId = null;
                if ($orderId && $transaction->transaction_number === $orderId) {
                    $txId = $transactionId;
                }
                $isPaid = $this->paymentService->syncPaymentStatus($transaction, $txId);
                if ($isPaid) {
                    $registration->refresh();
                    break;
                }
            }
        }

        if ($registration->isPaid() && $registration->user_id === Auth::id() && $registration->unitSelections()->where('status', '!=', 'cancelled')->count() === 0) {
            if ($registration->payment_method === 'kpr_bank_flpp') {
                if (!$registration->kprScreening) {
                    return redirect()->route('kpr.create-from-nup', $registration->id)
                        ->with('success', 'Pembayaran NUP berhasil! Silakan isi formulir pengajuan & screening KPR (Pefindo).');
                }
            } else {
                return redirect()->route('units.create', $registration->id)
                    ->with('success', 'Pembayaran NUP berhasil! Silakan pilih unit perumahan.');
            }
        }

        $latestPaymentTransaction = $registration->paymentTransactions()->latest()->first();
        return view('nup.show', compact('registration', 'latestPaymentTransaction'));
    }

    /**
     * Process payment
     */
    public function processPayment(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $registration = NupRegistration::with('user')->findOrFail($id);

        if ($registration->user_id !== Auth::id()) {
            abort(403);
        }

        // Sync payment status first to verify if paid in background
        if ($registration->payment_status === 'pending') {
            $pendingTransactions = \App\Models\PaymentTransaction::where('nup_registration_id', $registration->id)
                ->where('status', 'pending')
                ->get();

            foreach ($pendingTransactions as $transaction) {
                $isPaid = $this->paymentService->syncPaymentStatus($transaction);
                if ($isPaid) {
                    $registration->refresh();
                    break;
                }
            }
        }

        if ($registration->payment_status === 'paid') {
            if ($registration->payment_method === 'kpr_bank_flpp') {
                return redirect()->route('kpr.create-from-nup', $registration->id)
                    ->with('success', 'Pembayaran NUP berhasil! Silakan isi formulir pengajuan & screening KPR (Pefindo).');
            }
            return redirect()->route('units.create', $registration->id)
                ->with('success', 'Pembayaran NUP berhasil! Silakan pilih unit perumahan.');
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:transfer',
            'promo_code' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $result = $this->paymentService->createPayment($registration, $request->payment_method, $request->input('promo_code'));

            if ($result['success']) {
                if (!empty($result['free_payment'])) {
                    return redirect()->route('units.create', $registration->id)
                        ->with('success', 'Kode promo Rp 0 berhasil digunakan! Pembayaran NUP lunas. Silakan pilih unit perumahan.');
                }

                if (!empty($result['cash_payment'])) {
                    return redirect()->route('nup.show', $registration->id)
                        ->with('success', 'Pilihan bayar tunai tersimpan. Silakan datang ke kantor untuk membayar; status akan diperbarui setelah petugas mengonfirmasi pembayaran.');
                }

                // Check if it's mock mode
                if (isset($result['mock_mode']) && $result['mock_mode']) {
                    return view('nup.payment-mock', [
                        'transaction' => $result['transaction'],
                        'registration' => $registration,
                        'message' => $result['message'] ?? '',
                    ]);
                }

                return redirect()->away($result['checkout_url']);
            } else {
                return back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Process mock payment success (for testing without credentials)
     */
    public function mockPaymentSuccess(Request $request, $id)
    {
        if ($this->paymentService->isConfigured()) {
            return redirect()->route('dashboard')->with('error', 'Pembayaran simulasi dinonaktifkan saat DOKU aktif.');
        }
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $registration = NupRegistration::findOrFail($id);

        if ($registration->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $result = $this->paymentService->processMockPaymentSuccess($request->transaction_number);

            if ($result['success']) {
                // Set unit selection deadline after payment
                $registration->setUnitSelectionDeadline();

                // Jika skema KPR, arahkan ke formulir screening KPR terlebih dahulu
                if ($registration->payment_method === 'kpr_bank_flpp') {
                    return redirect()->route('kpr.create-from-nup', $registration->id)
                        ->with('success', 'Pembayaran NUP berhasil! Silakan isi formulir pengajuan & screening KPR (Pefindo).');
                }

                // Redirect langsung ke halaman pemilihan unit setelah pembayaran sukses
                return redirect()->route('units.create', $registration->id)
                    ->with('success', 'Pembayaran NUP berhasil! Silakan pilih unit perumahan.');
            } else {
                return back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses mock payment: ' . $e->getMessage());
        }
    }

    /**
     * Request NUP refund
     */
    public function requestRefund(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $registration = NupRegistration::findOrFail($id);

        if ($registration->user_id !== Auth::id()) {
            abort(403);
        }

        if ($registration->isRefundRequested()) {
            return back()->with('error', 'Refund sudah pernah diminta.');
        }

        if ($registration->isForfeited()) {
            return back()->with('error', 'NUP telah hangus dan tidak dapat direfund.');
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            // Use NupService to process refund
            $this->nupService->processRefund($registration, $request->notes);

            return back()->with('success', 'Permintaan refund berhasil diajukan. Menunggu persetujuan admin.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengajukan refund: ' . $e->getMessage());
        }
    }

    /**
     * Switch payment method (alih jalur)
     */
    public function switchPaymentMethod(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $registration = NupRegistration::findOrFail($id);

        if ($registration->user_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'new_payment_method' => 'required|in:tunai_mitra_pintar,kpr_bank_flpp',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        if ($registration->payment_method === $request->new_payment_method) {
            return back()->with('error', 'Metode pembayaran sama dengan yang sekarang.');
        }

        try {
            $registration->switchPaymentMethod($request->new_payment_method, $request->reason);

            // Update deadline based on new payment method
            $registration->setUnitSelectionDeadline();

            return back()->with('success', 'Metode pembayaran berhasil diubah.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah metode pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Payment callback
     */
    public function paymentCallback(Request $request)
    {
        return app(DokuNotificationController::class)(
            $request,
            app(\App\Services\DokuCheckoutService::class),
            $this->paymentService
        );
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $registration = NupRegistration::findOrFail($id);

        if ($registration->user_id !== Auth::id()) {
            abort(403);
        }

        if ($registration->payment_status === 'pending') {
            $transactionId = $request->query('transaction_id');
            $orderId = $request->query('order_id');

            $pendingTransactions = \App\Models\PaymentTransaction::where('nup_registration_id', $registration->id)
                ->where('status', 'pending')
                ->get();

            foreach ($pendingTransactions as $transaction) {
                $txId = null;
                if ($orderId && $transaction->transaction_number === $orderId) {
                    $txId = $transactionId;
                }
                $isPaid = $this->paymentService->syncPaymentStatus($transaction, $txId);
                if ($isPaid) {
                    $registration->refresh();
                    break;
                }
            }
        }

        return response()->json([
            'payment_status' => $registration->payment_status,
            'queue_number' => $registration->queue_number,
            'queue_issue_date' => $registration->queue_issue_date ? $registration->queue_issue_date->format('Y-m-d H:i:s') : null,
        ]);
    }

    /**
     * Dashboard for user
     */
    public function dashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Jika akun operator/admin tanpa registrasi pribadi, arahkan ke dashboard admin
        if ($user->isAdmin() && $user->role !== 'member' && !NupRegistration::where('user_id', $user->id)->exists()) {
            return redirect()->route('admin.dashboard');
        }

        // Ambil seluruh registrasi NUP milik user (termasuk yang lama)
        $registrations = NupRegistration::where('user_id', $user->id)
            ->with([
                'unitSelections.housingUnit',
                'kprScreening',
                'sprDocuments',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Load semua dokumen SPR resmi milik user (termasuk unit rumah yang sudah lunas 100%)
        $sprDocuments = \App\Models\SprDocument::where('user_id', $user->id)
            ->whereIn('status', ['issued', 'paid', 'completed'])
            ->with([
                'kprPayments' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'unitSelection.housingUnit',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Flag apakah member masih punya rumah Belum Lunas
        $hasUnpaidHouse = $user->hasUnpaidHouse();

        return view('nup.dashboard', compact('registrations', 'user', 'sprDocuments', 'hasUnpaidHouse'));
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

        $query = NupRegistration::with([
                'user',
                'unitSelections.housingUnit.housingProject',
                'paymentTransactions' => fn ($query) => $query
                    ->where('payment_gateway', 'cash_office')
                    ->where('payment_method', 'cash')
                    ->where('status', 'pending')
                    ->latest(),
            ]);

        // Jika Operator Developer: tampilkan NUP yang terikat dengan perusahaannya
        if ($companyId) {
            $query->where(function ($mainQ) use ($companyId) {
                $mainQ->whereHas('unitSelections.housingUnit', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)
                      ->orWhereHas('housingProject', fn($pq) => $pq->where('company_id', $companyId));
                })->orWhereHas('housingPreferences.housingProject', function ($pq) use ($companyId) {
                    $pq->where('company_id', $companyId);
                })->orWhereHas('broker', function ($bq) use ($companyId) {
                    $bq->where('company_id', $companyId);
                })->orWhereHas('user.unitSelections.housingUnit', function ($uq) use ($companyId) {
                    $uq->where('company_id', $companyId);
                });
            });
        }

        $registrations = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.nup.index', compact('registrations'));
    }

    /**
     * Admin show
     */
    public function adminShow($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $companyId = Auth::user()->getOperatorCompanyId();

        $registration = NupRegistration::with([
            'user',
            'unitSelections.housingUnit.housingProject',
            'kprScreening',
            'sprDocuments',
            'paymentTransactions'
        ])->findOrFail($id);

        // Jika Operator Developer: pastikan data ini milik perusahaannya
        if ($companyId) {
            if (!$this->hasOperatorAccess($registration, $companyId)) {
                abort(403, 'Akses ditolak: Pendaftar NUP ini bukan konsumen dari unit perumahan Anda.');
            }
        }

        return view('admin.nup.show', compact('registration'));
    }

    /**
     * Validasi apakah NUP terkait dengan perusahaan developer operator
     */
    private function hasOperatorAccess(NupRegistration $registration, int $companyId): bool
    {
        // 1. Cek unit selection
        if ($registration->unitSelections()->whereHas('housingUnit', function ($q) use ($companyId) {
            $q->where('company_id', $companyId)->orWhereHas('housingProject', fn($pq) => $pq->where('company_id', $companyId));
        })->exists()) {
            return true;
        }

        // 2. Cek preferensi perumahan
        if ($registration->housingPreferences()->whereHas('housingProject', fn($pq) => $pq->where('company_id', $companyId))->exists()) {
            return true;
        }

        // 3. Cek broker
        if ($registration->broker_id) {
            $broker = $registration->broker;
            if ($broker) {
                if ((int)$broker->company_id === (int)$companyId) {
                    return true;
                }
                if ($broker->user_id && \App\Models\Broker::where('user_id', $broker->user_id)->where('company_id', $companyId)->exists()) {
                    return true;
                }
            }
        }

        // 4. Cek user konsumen
        if ($registration->user_id) {
            if (\App\Models\UnitSelection::where('user_id', $registration->user_id)
                ->whereHas('housingUnit', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)->orWhereHas('housingProject', fn($pq) => $pq->where('company_id', $companyId));
                })->exists()) {
                return true;
            }
        }

        return false;
    }

    public function confirmCashOfficePayment($transactionId)
    {
        $transaction = \App\Models\PaymentTransaction::with('nupRegistration')->findOrFail($transactionId);
        if (!$this->paymentService->confirmCashOfficePayment($transaction, (int) Auth::id())) {
            return back()->with('error', 'Transaksi tunai tidak valid, sudah diproses, atau registrasi sudah lunas.');
        }

        return back()->with('success', 'Pembayaran tunai kantor dikonfirmasi dan registrasi ditandai lunas.');
    }

    public function rejectCashOfficePayment(Request $request, $transactionId)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $rejected = DB::transaction(function () use ($transactionId, $request) {
            $transaction = \App\Models\PaymentTransaction::with('nupRegistration')
                ->lockForUpdate()
                ->findOrFail($transactionId);

            if ($transaction->payment_gateway !== 'cash_office'
                || $transaction->payment_method !== 'cash'
                || $transaction->status !== 'pending'
                || !$transaction->nupRegistration
                || $transaction->nupRegistration->payment_status === 'paid') {
                return false;
            }

            $transaction->update([
                'status' => 'failed',
                'gateway_response_payload' => [
                    'status' => 'REJECTED_BY_OPERATOR',
                    'reason' => $request->input('rejection_reason'),
                    'rejected_by' => Auth::id(),
                    'rejected_at' => now()->toIso8601String(),
                ],
            ]);

            return true;
        });

        if (!$rejected) {
            return back()->with('error', 'Transaksi tunai tidak valid, sudah diproses, atau registrasi sudah lunas.');
        }

        return back()->with('success', 'Pengajuan pembayaran tunai ditolak. Peserta dapat memilih metode pembayaran dan mencoba kembali.');
    }
}
