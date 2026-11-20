<?php

namespace App\Http\Controllers;

use App\Models\KprScreening;
use App\Models\UnitSelection;
use App\Models\NupRegistration;
use App\Services\WhatsAppGatewayService;
use App\Services\NupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class KprScreeningController extends Controller
{
    protected $whatsappService;
    protected $nupService;

    public function __construct(WhatsAppGatewayService $whatsappService, NupService $nupService)
    {
        $this->whatsappService = $whatsappService;
        $this->nupService = $nupService;
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

        if (!$unitSelection->isConfirmed()) {
            return back()->with('error', 'Unit selection harus dikonfirmasi terlebih dahulu.');
        }

        // Check if screening already exists
        if ($unitSelection->kprScreening()->exists()) {
            return redirect()->route('kpr.show', $unitSelection->kprScreening->id);
        }

        $nupRegistration = $unitSelection->nupRegistration;
        $kprBanks = config('nup.kpr_banks');

        return view('kpr.create', compact('unitSelection', 'nupRegistration', 'kprBanks'));
    }

    /**
     * Create screening form from NUP registration (before unit selection)
     */
    public function createFromNup($nupRegistrationId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $nupRegistration = NupRegistration::findOrFail($nupRegistrationId);

        if ($nupRegistration->user_id !== Auth::id()) {
            abort(403);
        }

        if ($nupRegistration->payment_method !== 'kpr_bank_flpp') {
            return back()->with('error', 'Screening KPR hanya untuk jalur KPR Bank/FLPP.');
        }

        // Check if screening already exists
        if ($nupRegistration->kprScreening()->exists()) {
            return redirect()->route('kpr.show', $nupRegistration->kprScreening->id);
        }

        $kprBanks = config('nup.kpr_banks');

        return view('kpr.create-from-nup', compact('nupRegistration', 'kprBanks'));
    }

    /**
     * Store screening data from NUP registration
     */
    public function storeFromNup(Request $request, $nupRegistrationId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $nupRegistration = NupRegistration::findOrFail($nupRegistrationId);

        if ($nupRegistration->user_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'bank' => 'required|in:bca,bni,bri,btn,mandiri,cimb,uob,maybank,other',
            'bank_other' => 'required_if:bank,other|string|max:100',
            'loan_amount' => 'required|numeric|min:100000000',
            'tenor_years' => 'required|integer|min:5|max:30',
            'monthly_income' => 'required|numeric|min:0',
            'monthly_expense' => 'required|numeric|min:0',
            'employment_status' => 'required|in:employee,entrepreneur,professional,retired,other',
            'employment_duration_years' => 'nullable|integer|min:0|max:50',
            'employer_name' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $screening = KprScreening::create([
                'user_id' => Auth::id(),
                'nup_registration_id' => $nupRegistration->id,
                'unit_selection_id' => null, // Will be set after unit selection
                'screening_number' => KprScreening::generateScreeningNumber(),
                'bank' => $request->bank,
                'bank_other' => $request->bank_other,
                'loan_amount' => $request->loan_amount,
                'tenor_years' => $request->tenor_years,
                'monthly_income' => $request->monthly_income,
                'monthly_expense' => $request->monthly_expense,
                'employment_status' => $request->employment_status,
                'employment_duration_years' => $request->employment_duration_years,
                'employer_name' => $request->employer_name,
                'status' => 'pending',
                'submission_date' => now(),
            ]);

            return redirect()->route('kpr.show', $screening->id)
                ->with('success', 'Data screening KPR berhasil disimpan. Silakan upload dokumen yang diperlukan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan data screening: ' . $e->getMessage());
        }
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

        $validator = Validator::make($request->all(), [
            'bank' => 'required|in:bca,bni,bri,btn,mandiri,cimb,uob,maybank,other',
            'bank_other' => 'required_if:bank,other|string|max:100',
            'loan_amount' => 'required|numeric|min:100000000',
            'tenor_years' => 'required|integer|min:5|max:30',
            'monthly_income' => 'required|numeric|min:0',
            'monthly_expense' => 'required|numeric|min:0',
            'employment_status' => 'required|in:employee,entrepreneur,professional,retired,other',
            'employment_duration_years' => 'nullable|integer|min:0|max:50',
            'employer_name' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $screening = KprScreening::create([
                'user_id' => Auth::id(),
                'nup_registration_id' => $unitSelection->nup_registration_id,
                'unit_selection_id' => $unitSelection->id,
                'screening_number' => KprScreening::generateScreeningNumber(),
                'bank' => $request->bank,
                'bank_other' => $request->bank_other,
                'loan_amount' => $request->loan_amount,
                'tenor_years' => $request->tenor_years,
                'monthly_income' => $request->monthly_income,
                'monthly_expense' => $request->monthly_expense,
                'employment_status' => $request->employment_status,
                'employment_duration_years' => $request->employment_duration_years,
                'employer_name' => $request->employer_name,
                'status' => 'pending',
                'submission_date' => now(),
            ]);

            return redirect()->route('kpr.show', $screening->id)
                ->with('success', 'Data screening KPR berhasil disimpan. Silakan upload dokumen yang diperlukan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan data screening: ' . $e->getMessage());
        }
    }

    /**
     * Update screening to link with unit selection after unit is selected
     */
    public function linkUnitSelection($screeningId, $unitSelectionId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($screeningId);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $screening->update([
                'unit_selection_id' => $unitSelectionId,
            ]);

            return back()->with('success', 'Screening berhasil dihubungkan dengan unit selection.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghubungkan screening: ' . $e->getMessage());
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

        $kprBanks = config('nup.kpr_banks');
        $documentTypes = ['ktp', 'kk', 'npwp', 'salary_slip', 'bank_statement', 'employment_letter', 'marriage_certificate', 'other'];

        return view('kpr.show', compact('screening', 'kprBanks', 'documentTypes'));
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

        // Check if required documents are uploaded
        $requiredDocuments = ['ktp', 'kk', 'salary_slip', 'bank_statement'];
        $uploadedDocuments = $screening->userDocuments()
            ->whereIn('document_type', $requiredDocuments)
            ->where('status', 'approved')
            ->count();

        if ($uploadedDocuments < count($requiredDocuments)) {
            return back()->with('error', 'Harap upload semua dokumen yang diperlukan dan tunggu verifikasi.');
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
     * Upload document
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
            'document_type' => 'required|in:ktp,kk,npwp,salary_slip,bank_statement,employment_letter,marriage_certificate,other',
            'document' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $file = $request->file('document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents/' . Auth::id(), $fileName, 'public');

            $screening->userDocuments()->create([
                'document_type' => $request->document_type,
                'document_name' => $fileName,
                'file_path' => $filePath,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'status' => 'pending',
                'uploaded_at' => now(),
            ]);

            return back()->with('success', 'Dokumen berhasil diupload.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal upload dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Admin approve screening (layak_proses)
     */
    public function approve(Request $request, $id)
    {
        if (!Auth::user()->isPihakBank() && !Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $screening = KprScreening::findOrFail($id);

        try {
            $screening->update([
                'status' => 'layak_proses',
                'approval_date' => now(),
                'approved_by' => Auth::user()->name,
            ]);

            // Use NupService to handle screening result
            $this->nupService->handleScreeningResult($screening);

            return back()->with('success', 'Screening berhasil disetujui (Layak Proses).');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui screening: ' . $e->getMessage());
        }
    }

    /**
     * Admin approve screening with condition (layak bersyarat)
     */
    public function approveWithCondition(Request $request, $id)
    {
        if (!Auth::user()->isPihakBank() && !Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $screening = KprScreening::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'notes' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $screening->update([
                'status' => 'layak_bersyarat',
                'approval_date' => now(),
                'approved_by' => Auth::user()->name,
                'notes' => $request->notes,
            ]);

            // Use NupService to handle screening result
            $this->nupService->handleScreeningResult($screening);

            return back()->with('success', 'Screening disetujui dengan syarat (Layak Bersyarat).');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui screening: ' . $e->getMessage());
        }
    }

    /**
     * Participant request refund after screening result
     */
    public function requestRefund(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($id);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($screening->status, ['layak_bersyarat', 'tidak_layak'])) {
            return back()->with('error', 'Refund hanya dapat diajukan untuk status Layak Bersyarat atau Tidak Layak.');
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $registration = $screening->nupRegistration;
            $this->nupService->processRefund($registration, $request->notes);

            return back()->with('success', 'Permintaan refund berhasil diajukan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengajukan refund: ' . $e->getMessage());
        }
    }

    /**
     * Participant make decision after screening result
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

        if (!in_array($screening->status, ['layak_bersyarat', 'tidak_layak'])) {
            return back()->with('error', 'Keputusan hanya dapat dibuat untuk status Layak Bersyarat atau Tidak Layak.');
        }

        if ($screening->hasParticipantDecision()) {
            return back()->with('error', 'Anda sudah membuat keputusan.');
        }

        $validator = Validator::make($request->all(), [
            'decision' => 'required|in:select_unit,request_refund,switch_to_cash,switch_to_mitra_pintar',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            // Use NupService to handle participant decision
            $this->nupService->recordParticipantDecision($screening, $request->decision, $request->notes);

            return back()->with('success', 'Keputusan berhasil disimpan.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan keputusan: ' . $e->getMessage());
        }
    }

    /**
     * Admin reject screening (tidak_layak)
     */
    public function reject(Request $request, $id)
    {
        if (!Auth::user()->isPihakBank() && !Auth::user()->isSuperAdmin()) {
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
                'status' => 'tidak_layak',
                'rejection_reason' => $request->rejection_reason,
            ]);

            // Use NupService to handle screening result
            $this->nupService->handleScreeningResult($screening);

            return back()->with('success', 'Screening ditolak (Tidak Layak).');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak screening: ' . $e->getMessage());
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

        $screenings = KprScreening::with(['user', 'unitSelection.housingUnit'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.kpr.index', compact('screenings'));
    }

    /**
     * Process screening (admin)
     */
    public function process($id)
    {
        if (!Auth::user()->isPihakBank() && !Auth::user()->isSuperAdmin()) {
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
}