<?php

namespace App\Http\Controllers;

use App\Models\HousingUnit;
use App\Models\NupRegistration;
use App\Models\UnitSelection;
use App\Services\PaymentGatewayService;
use App\Services\WhatsAppGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UnitSelectionController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppGatewayService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Display available units
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $availableUnits = HousingUnit::available()->with(['project.company'])->orderBy('block')->orderBy('number')->get();
        $unitTypes = config('nup.unit_types');
        $availableNup = Auth::user()->getAvailableNupForSelection();
        $hasUnpaidHouse = Auth::user()->hasUnpaidHouse();

        return view('units.index', compact('availableUnits', 'unitTypes', 'availableNup', 'hasUnpaidHouse'));
    }

    /**
     * Display unit selection page
     */
    public function create($nupRegistrationId, Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $registration = NupRegistration::findOrFail($nupRegistrationId);

        if ($registration->user_id !== Auth::id()) {
            abort(403);
        }

        // Sync payment status dari Midtrans jika ada query parameters
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
                $paymentService = app(\App\Services\PaymentGatewayService::class);
                $isPaid = $paymentService->syncPaymentStatus($transaction, $txId);
                if ($isPaid) {
                    $registration->refresh();
                    break;
                }
            }
        }

        // 1 NUP = 1 Unit Rumah:
        // Cek apakah nomor NUP ini sudah pernah digunakan untuk memilih unit yang aktif (bukan cancelled)
        $existingSelection = $registration->unitSelections()->where('status', '!=', 'cancelled')->latest()->first();
        if ($existingSelection) {
            $unitName = $existingSelection->housingUnit?->full_identifier ?? 'unit kavling';
            return redirect()->route('units.show', $existingSelection->id)
                ->with('success', "Unit {$unitName} sudah berhasil dipilih dan dikunci untuk tiket NUP ini.");
        }

        // Cek apakah user masih memiliki unit rumah lain yang belum lunas
        if (Auth::user()->hasUnpaidHouse()) {
            return redirect()->route('dashboard')->with('error', 'Anda masih memiliki unit rumah aktif yang belum lunas. Silakan selesaikan pembayaran rumah sebelumnya terlebih dahulu sebelum memilih unit berikutnya.');
        }

        if (!$registration->isPaid()) {
            return redirect()->route('dashboard')->with('error', 'Silakan selesaikan pembayaran biaya NUP terlebih dahulu sebelum memilih unit.');
        }

        // Khusus KPR: Wajib melalui dan lulus screening Pefindo terlebih dahulu sebelum memilih unit
        if ($registration->payment_method === 'kpr_bank_flpp') {
            $screening = $registration->kprScreening;
            if (!$screening) {
                return redirect()->route('kpr.create-from-nup', $registration->id)
                    ->with('info', 'Untuk skema KPR, silakan isi formulir pengajuan & screening Pefindo terlebih dahulu.');
            }
            if (!in_array($screening->status, ['approved', 'layak_bersyarat'])) {
                return redirect()->route('kpr.show', $screening->id)
                    ->with('info', 'Pengajuan KPR Anda sedang dalam proses verifikasi Pefindo. Tunggu hasil skor dari analis sebelum memilih unit.');
            }
            if (!$screening->hasParticipantDecision()) {
                return redirect()->route('kpr.outcome', $screening->id)
                    ->with('info', 'Silakan lihat hasil skor Pefindo dan tentukan langkah Anda terlebih dahulu sebelum memilih unit.');
            }
        }

        $availableUnits = HousingUnit::available()->get();
        $unitTypes = config('nup.unit_types');

        return view('units.select', compact('registration', 'availableUnits', 'unitTypes'));
    }

    /**
     * Store unit selection
     */
    public function store(Request $request, $nupRegistrationId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $registration = NupRegistration::findOrFail($nupRegistrationId);

        if ($registration->user_id !== Auth::id()) {
            abort(403);
        }

        // 1 NUP = 1 Unit Rumah:
        // Cek apakah nomor NUP ini sudah pernah digunakan untuk memilih unit yang aktif (bukan cancelled)
        $existingSelection = $registration->unitSelections()->where('status', '!=', 'cancelled')->latest()->first();
        if ($existingSelection) {
            $unitName = $existingSelection->housingUnit?->full_identifier ?? 'unit kavling';
            return redirect()->route('units.show', $existingSelection->id)
                ->with('success', "Unit {$unitName} sudah berhasil dipilih sebelumnya.");
        }

        // Cek apakah user masih memiliki unit rumah lain yang belum lunas
        if (Auth::user()->hasUnpaidHouse()) {
            return redirect()->route('dashboard')->with('error', 'Anda masih memiliki unit rumah aktif yang belum lunas. Silakan selesaikan pembayaran rumah sebelumnya terlebih dahulu.');
        }

        if (!$registration->isPaid()) {
            return redirect()->route('dashboard')->with('error', 'Silakan selesaikan pembayaran biaya NUP terlebih dahulu.');
        }

        // Khusus KPR: Wajib melalui dan lulus screening Pefindo terlebih dahulu sebelum memilih unit
        if ($registration->payment_method === 'kpr_bank_flpp') {
            $screening = $registration->kprScreening;
            if (!$screening || !in_array($screening->status, ['approved', 'layak_bersyarat'])) {
                return redirect()->route('kpr.create-from-nup', $registration->id)
                    ->with('error', 'Untuk skema KPR, Anda harus menyelesaikan verifikasi skor Pefindo terlebih dahulu sebelum memilih unit.');
            }
        }

        $validator = Validator::make($request->all(), [
            'housing_unit_id' => 'required|exists:housing_units,id',
            'payment_scheme' => 'nullable|string',
            'mitra_institution_name' => 'required_if:payment_scheme,mitra_pintar|nullable|string|max:255',
            'mitra_document' => 'required_if:payment_scheme,mitra_pintar|nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes' => 'nullable|string|max:500',
        ], [
            'mitra_institution_name.required_if' => 'Nama instansi/perusahaan mitra wajib diisi untuk skema Kredit Mitra Pintar.',
            'mitra_document.required_if' => 'Dokumen bukti kerja sama mitra pintar (PDF/Foto) wajib diunggah.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $unit = HousingUnit::with('housingProject')->findOrFail($request->housing_unit_id);

            if (!$unit->isAvailable()) {
                return back()->with('error', 'Unit yang dipilih tidak tersedia.');
            }

            // Tentukan skema pembayaran (Tunai vs KPR / In-House / Mitra Pintar)
            $scheme = $request->input('payment_scheme');
            if (!$scheme) {
                $scheme = in_array($registration->payment_method, ['tunai', 'cash', 'tunai_mitra_pintar', 'tunai_keras'], true) ? 'cash' : 'kpr_bank';
            }

            $project = $unit->housingProject;
            $isCash = in_array($scheme, ['cash', 'tunai', 'tunai_keras'], true);
            $isMitra = ($scheme === 'mitra_pintar');

            if ($isCash) {
                // Khusus skema Tunai: UTK dihapuskan (Rp 0), status otomatis confirmed & bebas UTK
                $utkBase = 0;
                $utkPpn = 0;
                $utkVe = 0;
                $utkTotal = 0;
                $selectionStatus = 'confirmed';
                $utkStatus = 'paid';
                $utkDeadline = null;
            } else {
                // Skema KPR / In-House / Mitra Pintar: UTK diisi operator + 11% PPN + 20% VE
                $utkBase = $project ? $project->getUtkBaseAmount() : 5000000;
                $utkPpn = $project ? $project->getUtkPpnAmount() : round($utkBase * 0.11, 2);
                $utkVe = $project ? $project->getUtkVeAmount() : round($utkBase * 0.20, 2);
                $utkTotal = $project ? $project->getTotalUtkAmount() : ($utkBase + $utkPpn + $utkVe);
                $selectionStatus = 'selected';
                $utkStatus = 'pending';
                $deadlineHours = $project->commitment_deadline_hours ?? 48;
                $utkDeadline = now()->addHours($deadlineHours);
            }

            // Upload dokumen Mitra Pintar jika memilih skema mitra_pintar
            $mitraDocPath = null;
            if ($isMitra && $request->hasFile('mitra_document')) {
                $mitraDocPath = $request->file('mitra_document')->store('mitra_documents', 'public');
            }

            // Calculate selection order based on queue number
            $selectionOrder = NupRegistration::where('payment_status', 'paid')
                ->where('id', '<=', $registration->id)
                ->count();

            // Create unit selection
            $selection = UnitSelection::create([
                'nup_registration_id' => $registration->id,
                'housing_unit_id' => $unit->id,
                'payment_scheme' => $scheme,
                'mitra_institution_name' => $isMitra ? $request->mitra_institution_name : null,
                'mitra_document' => $isMitra ? $mitraDocPath : null,
                'mitra_status' => $isMitra ? 'pending' : null,
                'user_id' => Auth::id(),
                'selection_order' => $selectionOrder,
                'selection_date' => now(),
                'status' => $selectionStatus,
                'notes' => $request->notes,
                'utk_amount' => $utkTotal,
                'utk_base_amount' => $utkBase,
                'utk_ppn_amount' => $utkPpn,
                'utk_ve_amount' => $utkVe,
                'utk_payment_status' => $utkStatus,
                'utk_payment_deadline' => $utkDeadline,
                'utk_payment_date' => $isCash ? now() : null,
            ]);

            // Update unit status
            $unit->update(['status' => 'booked']);

            // Sinkronkan metode pembayaran di data NUP dengan skema unit yang dipilih
            $syncNupMethod = match(true) {
                in_array($scheme, ['kpr', 'kpr_bank', 'kpr_bank_flpp'], true) => 'kpr_bank_flpp',
                in_array($scheme, ['cash', 'tunai', 'tunai_keras'], true) => 'tunai',
                in_array($scheme, ['inhouse', 'installment'], true) => 'inhouse',
                $scheme === 'mitra_pintar' => 'kredit_mitra_pintar',
                default => null,
            };
            if ($syncNupMethod && $registration->payment_method !== $syncNupMethod) {
                $registration->update(['payment_method' => $syncNupMethod]);
            }

            // Send WhatsApp notification
            $this->whatsappService->sendUnitSelectionNotification(
                Auth::user(),
                $unit->unit_code,
                $unit->block,
                $unit->number
            );

            if ($isCash) {
                return redirect()->route('units.show', $selection->id)
                    ->with('success', 'Unit berhasil dipilih! Khusus skema Tunai, biaya UTK dihapuskan (Rp 0). Anda dapat langsung melanjutkan ke pembuatan SPR.');
            }

            if ($isMitra) {
                return redirect()->route('units.show', $selection->id)
                    ->with('success', 'Unit berhasil dipilih dengan skema Kredit Mitra Pintar! Dokumen kerja sama Anda telah diunggah dan sedang menunggu verifikasi oleh Operator.');
            }

            if (in_array($scheme, ['kpr', 'kpr_bank', 'kpr_bank_flpp'], true)) {
                $screening = $selection->kprScreening ?? $registration->kprScreening;
                if (!$screening) {
                    return redirect()->route('kpr.create', $selection->id)
                        ->with('success', 'Unit kavling berhasil dipilih sementara! Karena Anda memilih skema KPR, silakan lengkapi data pekerjaan dan upload berkas persyaratan KPR Anda untuk proses verifikasi skor kredit (Pefindo/Bank). Pembayaran UTK baru dibuka setelah Anda dinyatakan LAYAK.');
                }
                if (!in_array($screening->status, ['approved', 'layak_bersyarat'], true)) {
                    return redirect()->route('units.show', $selection->id)
                        ->with('info', 'Unit berhasil dipilih sementara! Berkas KPR Anda sedang dalam proses verifikasi (Pefindo/Bank). Pembayaran UTK akan dibuka setelah dinyatakan LAYAK.');
                }
            }

            return redirect()->route('units.show', $selection->id)
                ->with('success', 'Unit berhasil dipilih! Silakan selesaikan pembayaran UTK untuk mengunci unit.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memilih unit: ' . $e->getMessage());
        }
    }

    /**
     * Show unit selection details
     */
    public function show($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $selection = UnitSelection::with([
            'nupRegistration',
            'housingUnit.project',
            'user',
            'sprDocuments',
            'utkPayments',
        ])->findOrFail($id);

        if ($selection->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($selection->user_id !== Auth::id()) {
            $companyId = Auth::user()->getOperatorCompanyId();
            if ($companyId && !$selection->housingUnit?->belongsToCompany($companyId)) {
                abort(403, 'Akses ditolak: Data pemilihan unit ini milik perusahaan developer lain.');
            }
        }

        $validUtkPayment = $selection->utkPayments()
            ->where('nup_registration_id', $selection->nup_registration_id)
            ->where('status', 'paid')
            ->first();

        $utkPaid = $selection->isUtkWaived() || $validUtkPayment !== null;

        if ($utkPaid && $selection->status === 'selected') {
            $selection->update([
                'status' => 'confirmed',
                'utk_payment_status' => 'paid',
                'utk_payment_date' => $selection->utk_payment_date ?: ($validUtkPayment?->payment_date ?: now()),
            ]);
            $selection->refresh();
        } elseif (!$utkPaid && !$selection->isCash() && $selection->utk_payment_status === 'paid') {
            $selection->update([
                'status' => 'selected',
                'utk_payment_status' => 'pending',
                'utk_payment_date' => null,
            ]);
            $selection->refresh();
        }

        return view('units.show', compact('selection', 'utkPaid'));
    }

    /**
     * Confirm unit selection (admin only)
     */
    public function confirm($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $selection = UnitSelection::with('housingUnit.project')->findOrFail($id);

        $companyId = Auth::user()->getOperatorCompanyId();
        if ($companyId && !$selection->housingUnit?->belongsToCompany($companyId)) {
            abort(403, 'Akses ditolak: Data pemilihan unit ini milik perusahaan developer lain.');
        }

        try {
            $selection->update(['status' => 'confirmed']);

            // Update unit status to sold
            $selection->housingUnit->update(['status' => 'sold']);

            return back()->with('success', 'Unit selection confirmed.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal konfirmasi pemilihan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel unit selection
     */
    public function cancel(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $selection = UnitSelection::with('housingUnit.project')->findOrFail($id);

        if ($selection->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Anda tidak berhak membatalkan pemilihan unit ini.');
        }

        if ($selection->user_id !== Auth::id()) {
            $companyId = Auth::user()->getOperatorCompanyId();
            if ($companyId && !$selection->housingUnit?->belongsToCompany($companyId)) {
                abort(403, 'Akses ditolak: Data pemilihan unit ini milik perusahaan developer lain.');
            }
        }

        try {
            // Make unit available again if it was selected or confirmed
            if ($selection->status === 'selected' || $selection->status === 'confirmed') {
                $selection->housingUnit?->update(['status' => 'available']);
            }

            $updateData = [
                'status' => 'cancelled',
                'utk_payment_status' => 'cancelled',
            ];
            if ($request->filled('reason')) {
                $updateData['notes'] = trim(($selection->notes ? $selection->notes . ' | ' : '') . 'Alasan Batal: ' . $request->reason);
            }

            $selection->update($updateData);

            // Otomatis batalkan semua tagihan UTK yang masih pending untuk unit ini
            $selection->utkPayments()->where('status', 'pending')->update([
                'status' => 'cancelled',
                'payment_gateway_response' => [
                    'cancelled_at' => now()->toIso8601String(),
                    'reason' => 'Pemilihan unit dibatalkan oleh pengguna'
                ]
            ]);

            $unitCode = $selection->housingUnit->unit_code ?? ('#' . $selection->housing_unit_id);
            $msg = $selection->isUtkPaid()
                ? "Pemilihan unit {$unitCode} berhasil dibatalkan. Sesuai ketentuan pembatalan sepihak, pembayaran UTK dinyatakan hangus dan unit telah dibuka kembali."
                : "Pemilihan unit {$unitCode} berhasil dibatalkan. Karena pembayaran UTK belum dilakukan, tidak ada biaya UTK yang hangus dan unit telah dibuka kembali.";

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan pemilihan: ' . $e->getMessage());
        }
    }

    /**
     * Restore cancelled unit selection (Tidak Jadi Batal)
     */
    public function restore($id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Hanya operator / admin yang dapat memulihkan pemesanan.');
        }

        $selection = UnitSelection::with(['housingUnit.housingProject', 'utkPayments'])->findOrFail($id);

        $companyId = Auth::user()->getOperatorCompanyId();
        if ($companyId && !$selection->housingUnit?->belongsToCompany($companyId)) {
            abort(403, 'Akses ditolak: Data pemilihan unit ini milik perusahaan developer lain.');
        }

        if ($selection->status !== 'cancelled') {
            return back()->with('error', 'Pemesanan ini tidak berstatus batal.');
        }

        $unit = $selection->housingUnit;
        if (!$unit) {
            return back()->with('error', 'Data unit rumah tidak ditemukan.');
        }

        // PENTING: Cek apakah unit masih available!
        if ($unit->status !== 'available') {
            return back()->with('error', "Gagal memulihkan pemesanan: Unit {$unit->unit_code} saat ini berstatus '{$unit->status}' (sudah dipesan atau dibeli oleh konsumen lain).");
        }

        // Cek status UTK untuk menentukan status akhir
        $isPaid = $selection->isUtkWaived() || $selection->isUtkPaid();
        $targetStatus = $isPaid ? 'confirmed' : 'selected';

        // Kunci kembali unit menjadi booked
        $unit->update(['status' => 'booked']);

        // Data update
        $project = $unit->housingProject;
        $deadlineHours = $project->commitment_deadline_hours ?? 48;

        $updateData = [
            'status' => $targetStatus,
            'notes' => trim(($selection->notes ? $selection->notes . ' | ' : '') . 'Dipulihkan (Tidak Jadi Batal) oleh Operator pada ' . now()->format('d/m/Y H:i')),
        ];

        // Jika UTK belum dibayar, berikan perpanjangan batas waktu bayar UTK
        if (!$isPaid) {
            $updateData['utk_payment_deadline'] = now()->addHours($deadlineHours);
        }

        $selection->update($updateData);

        $unitCode = $unit->unit_code ?? ('#' . $unit->id);
        return back()->with('success', "Pemesanan unit {$unitCode} (#{$selection->id}) berhasil dipulihkan (Tidak Jadi Batal). Status unit kini kembali DIPESAN (Booked)" . (!$isPaid ? " dengan batas waktu pembayaran UTK diperpanjang {$deadlineHours} jam." : "."));
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
        $query = UnitSelection::with(['user', 'housingUnit.housingProject', 'nupRegistration', 'sprDocuments'])
            ->latest();

        // Jika Operator Developer: hanya tampilkan unit perumahan miliknya
        if ($companyId) {
            $query->whereHas('housingUnit', function ($q) use ($companyId) {
                $q->forCompany($companyId);
            });
        }

        $unitSelections = $query->paginate(20);

        return view('admin.units.index', compact('unitSelections'));
    }

    /**
     * Setujui dokumen Mitra Pintar (Admin / Operator)
     */
    public function approveMitra($id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $selection = UnitSelection::with(['user', 'housingUnit.housingProject'])->findOrFail($id);

        $companyId = Auth::user()->getOperatorCompanyId();
        if ($companyId && !$selection->housingUnit?->belongsToCompany($companyId)) {
            abort(403, 'Akses ditolak: Data pemilihan unit ini milik perusahaan developer lain.');
        }

        $deadlineHours = $selection->housingUnit?->housingProject?->commitment_deadline_hours ?? 48;

        $selection->update([
            'mitra_status'           => 'approved',
            'mitra_rejection_reason' => null,
            'mitra_verified_at'      => now(),
            'mitra_verified_by'      => Auth::id(),
            'utk_payment_deadline'   => now()->addHours($deadlineHours),
        ]);

        try {
            if ($selection->user && $selection->user->phone) {
                $unitCode = $selection->housingUnit ? $selection->housingUnit->unit_code : 'Unit';
                $msg = "Halo {$selection->user->name}, Dokumen Kerja Sama Mitra Pintar Anda untuk {$unitCode} telah DISETUJUI oleh Developer. Silakan buka aplikasi untuk menyelesaikan pembayaran UTK.";
                $this->whatsappService->sendMessage($selection->user->phone, $msg, 'mitra_approval', $selection->user_id);
            }
        } catch (\Throwable $e) {
            \Log::error('Gagal mengirim WhatsApp persetujuan mitra: ' . $e->getMessage());
        }

        return back()->with('success', 'Dokumen Mitra Pintar berhasil DISETUJUI. Calon pembeli dapat melanjutkan ke pembayaran UTK.');
    }

    /**
     * Tolak dokumen Mitra Pintar (Admin / Operator)
     */
    public function rejectMitra(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $selection = UnitSelection::with(['user', 'housingUnit.project'])->findOrFail($id);

        $companyId = Auth::user()->getOperatorCompanyId();
        if ($companyId && !$selection->housingUnit?->belongsToCompany($companyId)) {
            abort(403, 'Akses ditolak: Data pemilihan unit ini milik perusahaan developer lain.');
        }

        $selection->update([
            'mitra_status' => 'rejected',
            'mitra_rejection_reason' => $request->rejection_reason,
            'mitra_verified_at' => now(),
            'mitra_verified_by' => Auth::id(),
        ]);

        try {
            if ($selection->user && $selection->user->phone) {
                $unitCode = $selection->housingUnit ? $selection->housingUnit->unit_code : 'Unit';
                $msg = "Halo {$selection->user->name}, Mohon maaf dokumen kerja sama Mitra Pintar Anda untuk {$unitCode} DITOLAK dengan catatan: \"{$request->rejection_reason}\". Silakan cek aplikasi untuk mengunggah ulang dokumen atau memilih skema pembayaran lain (KPR Bank / In-House / Tunai).";
                $this->whatsappService->sendMessage($selection->user->phone, $msg, 'mitra_rejection', $selection->user_id);
            }
        } catch (\Throwable $e) {
            \Log::error('Gagal mengirim WhatsApp penolakan mitra: ' . $e->getMessage());
        }

        return back()->with('success', 'Dokumen Mitra Pintar DITOLAK. Notifikasi dan opsi pengalihan telah dikirimkan ke calon pembeli.');
    }

    /**
     * Upload ulang dokumen Mitra Pintar oleh calon pembeli
     */
    public function reuploadMitra(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $selection = UnitSelection::findOrFail($id);
        if ($selection->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'mitra_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'mitra_institution_name' => 'nullable|string|max:255',
        ]);

        $path = $request->file('mitra_document')->store('mitra_documents', 'public');

        $selection->update([
            'mitra_document' => $path,
            'mitra_institution_name' => $request->mitra_institution_name ?: $selection->mitra_institution_name,
            'mitra_status' => 'pending',
            'mitra_rejection_reason' => null,
        ]);

        return back()->with('success', 'Dokumen Mitra Pintar berhasil diunggah ulang dan sedang menunggu verifikasi ulang oleh Operator.');
    }

    /**
     * Alihkan skema pembayaran jika pengajuan Mitra Pintar ditolak
     */
    public function switchSchemeFromMitra(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $selection = UnitSelection::with('housingUnit.housingProject')->findOrFail($id);
        if ($selection->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'target_scheme' => 'required|in:cash,kpr_bank,installment',
        ]);

        $newScheme = $request->target_scheme;
        $project = $selection->housingUnit->housingProject;

        if ($newScheme === 'cash') {
            $selection->update([
                'payment_scheme' => 'cash',
                'status' => 'confirmed',
                'utk_amount' => 0,
                'utk_base_amount' => 0,
                'utk_ppn_amount' => 0,
                'utk_ve_amount' => 0,
                'utk_payment_status' => 'paid',
                'utk_payment_date' => now(),
                'mitra_status' => null,
                'mitra_rejection_reason' => null,
            ]);
            if ($selection->nupRegistration) {
                $selection->nupRegistration->update(['payment_method' => 'tunai']);
            }
            return redirect()->route('units.show', $selection->id)
                ->with('success', 'Skema berhasil dialihkan ke Tunai Keras (Bebas UTK). Anda dapat langsung melanjutkan ke pembuatan SPR.');
        } else {
            $base = $project ? $project->getUtkBaseAmount() : 5000000;
            $ppn = $project ? $project->getUtkPpnAmount() : round($base * 0.11, 2);
            $ve = $project ? $project->getUtkVeAmount() : round($base * 0.20, 2);
            $total = $project ? $project->getTotalUtkAmount() : ($base + $ppn + $ve);

            $selection->update([
                'payment_scheme' => $newScheme,
                'status' => 'selected',
                'utk_amount' => $total,
                'utk_base_amount' => $base,
                'utk_ppn_amount' => $ppn,
                'utk_ve_amount' => $ve,
                'utk_payment_status' => 'pending',
                'mitra_status' => null,
                'mitra_rejection_reason' => null,
            ]);
            if ($selection->nupRegistration) {
                $selection->nupRegistration->update(['payment_method' => $newScheme === 'kpr_bank' ? 'kpr_bank_flpp' : 'inhouse']);
            }

            return redirect()->route('units.show', $selection->id)
                ->with('success', 'Skema berhasil dialihkan ke ' . ($newScheme === 'installment' ? 'In-House Developer' : 'KPR Bank') . '. Silakan lanjutkan pembayaran UTK.');
        }
    }

    /**
     * Show selection queue
     */
    public function queue()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Get paid registrations with their selection status
        $queue = NupRegistration::where('payment_status', 'paid')
            ->with(['user', 'unitSelections.housingUnit'])
            ->orderBy('queue_issue_date')
            ->get();

        return view('units.queue', compact('queue'));
    }
}