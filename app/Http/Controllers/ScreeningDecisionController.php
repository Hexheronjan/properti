<?php

namespace App\Http\Controllers;

use App\Models\KprScreening;
use App\Models\HousingProject;
use App\Models\HousingUnit;
use App\Models\NupRegistration;
use App\Models\CommitmentFeePayment;
use App\Models\UnitSelection;
use App\Models\SprDocument;
use App\Services\PaymentGatewayService;
use App\Services\EmateraiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScreeningDecisionController extends Controller
{
    protected $payment;
    protected $ematerai;

    public function __construct(PaymentGatewayService $payment, EmateraiService $ematerai)
    {
        $this->payment = $payment;
        $this->ematerai = $ematerai;
    }

    /**
     * Index page - show all screenings for current user
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screenings = KprScreening::where('user_id', Auth::id())
            ->with(['unitSelection.housingUnit', 'nupRegistration', 'userDocuments'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Cari pemilihan unit aktif berskema KPR yang belum memiliki screening
        $unitSelection = UnitSelection::where('user_id', Auth::id())
            ->whereIn('payment_scheme', ['kpr', 'kpr_bank', 'kpr_bank_flpp'])
            ->whereIn('status', ['confirmed', 'selected'])
            ->whereDoesntHave('kprScreening')
            ->with(['housingUnit', 'utkPayments'])
            ->latest()
            ->first();

        return view('screening.index', compact('screenings', 'unitSelection'));
    }

    /**
     * Show screening decision page
     */
    public function showDecision($screeningId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($screeningId);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        return redirect()->route('kpr.outcome', $screeningId);
    }

    /**
     * Process screening decision
     */
    public function processDecision(Request $request, $screeningId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($screeningId);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'decision' => 'required|in:agree,disagree',
            'notes' => 'nullable|string',
        ]);

        if ($request->decision === 'disagree') {
            // User disagrees - mark as rejected, NUP not refunded
            $screening->update([
                'screening_decision' => 'rejected',
                'decision_date' => now(),
                'decision_notes' => $request->notes,
                'refund_nup' => false,
            ]);

            return redirect()->route('screening.result', $screeningId)
                ->with('info', 'Anda memilih untuk tidak melanjutkan. NUP tidak dapat direfund.');
        }

        // User agrees - redirect to unit selection
        $screening->update([
            'screening_decision' => 'approved',
            'decision_date' => now(),
            'decision_notes' => $request->notes,
        ]);

        return redirect()->route('screening.select-unit', $screeningId);
    }

    /**
     * Show unit selection page
     */
    public function showUnitSelection($screeningId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($screeningId);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        if ($screening->screening_decision !== 'approved') {
            return redirect()->route('screening.decision', $screeningId);
        }

        // Check if user has any unpaid KPR (block unit selection)
        if (Auth::user()->hasUnpaidKpr()) {
            return back()->with('error', 'Silakan lunasi KPR yang sedang berjalan terlebih dahulu sebelum memilih unit baru.');
        }

        // Get housing project from user's preference
        $nupRegistration = $screening->nupRegistration;
        $housingPreference = $nupRegistration->housingPreferences()->first();

        // If no housing preference, show all available housing projects
        if (!$housingPreference) {
            $housingProjects = HousingProject::active()->get();
            return view('screening.select-project', compact('screening', 'housingProjects'));
        }

        $housingProject = HousingProject::where('name', $housingPreference->housing_name)->first();

        if (!$housingProject) {
            return back()->with('error', 'Perumahan tidak ditemukan.');
        }

        // Get available units for this project
        $availableUnits = HousingUnit::where('project_id', $housingProject->id)
            ->where('status', 'available')
            ->get();

        return view('screening.select-unit', compact('screening', 'housingProject', 'availableUnits'));
    }

    /**
     * Show units for selected project
     */
    public function showProjectUnits($screeningId, $projectId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($screeningId);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        if ($screening->screening_decision !== 'approved') {
            return redirect()->route('screening.decision', $screeningId);
        }

        // Check if user has any unpaid KPR (block unit selection)
        if (Auth::user()->hasUnpaidKpr()) {
            return back()->with('error', 'Silakan lunasi KPR yang sedang berjalan terlebih dahulu sebelum memilih unit baru.');
        }

        $housingProject = HousingProject::findOrFail($projectId);

        // Get available units for this project
        $availableUnits = HousingUnit::where('project_id', $housingProject->id)
            ->where('status', 'available')
            ->get();

        return view('screening.select-unit', compact('screening', 'housingProject', 'availableUnits'));
    }

    /**
     * Show SPR clauses page
     */
    public function showSprClauses($screeningId, $unitId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($screeningId);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if user has any unpaid KPR (block unit selection)
        if (Auth::user()->hasUnpaidKpr()) {
            return back()->with('error', 'Silakan lunasi KPR yang sedang berjalan terlebih dahulu sebelum memilih unit baru.');
        }

        $unit = HousingUnit::findOrFail($unitId);

        if ($unit->status !== 'available') {
            return back()->with('error', 'Unit tidak tersedia.');
        }

        // Get housing project
        $housingProject = $unit->project;

        // Get SPR clauses for this project
        $sprClauses = $housingProject->sprClauses()->active()->ordered()->get();

        return view('screening.spr-clauses', compact('screening', 'unit', 'housingProject', 'sprClauses'));
    }

    /**
     * Process SPR agreement and redirect to payment
     */
    public function processSprAgreement(Request $request, $screeningId, $unitId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($screeningId);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'spr_agreement' => 'required|in:agree,disagree',
            'nup_agreement' => 'required|in:agree,disagree',
        ]);

        // If user disagrees with SPR clauses
        if ($request->spr_agreement === 'disagree') {
            return back()->with('error', 'Proses pembelian dibatalkan karena Anda tidak menyetujui klausul SPR.');
        }

        // If user disagrees with Not Refund NUP
        if ($request->nup_agreement === 'disagree') {
            return back()->with('error', 'Proses tidak dapat dilanjutkan karena Anda tidak menyetujui ketentuan Not Refund NUP.');
        }

        $unit = HousingUnit::findOrFail($unitId);

        if ($unit->status !== 'available') {
            return back()->with('error', 'Unit tidak tersedia.');
        }

        // Store selected unit in session for payment
        session([
            'selected_unit_id' => $unitId,
            'screening_id' => $screeningId,
        ]);

        return redirect()->route('screening.commitment-payment', $screeningId);
    }

    /**
     * Show commitment payment page
     */
    public function showCommitmentPayment($screeningId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($screeningId);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if user has any unpaid house (block unit selection for all schemes)
        if (Auth::user()->hasUnpaidHouse()) {
            return back()->with('error', 'Anda masih memiliki rumah yang belum lunas. Silakan lunasi pembayaran rumah terlebih dahulu sebelum melakukan pembelian atau pengajuan KPR untuk unit rumah berikutnya.');
        }

        if (!session('selected_unit_id')) {
            return redirect()->route('screening.select-unit', $screeningId);
        }

        $unit = HousingUnit::findOrFail(session('selected_unit_id'));
        $housingProject = $unit->project;

        // Check if payment already exists
        $existingPayment = CommitmentFeePayment::where('kpr_screening_id', $screeningId)
            ->where('housing_unit_id', $unit->id)
            ->first();

        if ($existingPayment && $existingPayment->isSuccessful()) {
            return redirect()->route('screening.result', $screeningId)
                ->with('success', 'Pembayaran uang tanda komitmen sudah berhasil.');
        }

        return view('screening.commitment-payment', compact('screening', 'unit', 'housingProject', 'existingPayment'));
    }

    /**
     * Process commitment payment
     */
    public function processCommitmentPayment(Request $request, $screeningId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($screeningId);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        if (!session('selected_unit_id')) {
            return redirect()->route('screening.select-unit', $screeningId);
        }

        $unit = HousingUnit::findOrFail(session('selected_unit_id'));
        $housingProject = $unit->project;

        $request->validate([
            'payment_method' => 'required|in:cash,transfer',
        ]);

        // Create payment record
        $payment = CommitmentFeePayment::create([
            'user_id' => Auth::id(),
            'kpr_screening_id' => $screeningId,
            'housing_unit_id' => $unit->id,
            'housing_project_id' => $housingProject->id,
            'payment_number' => CommitmentFeePayment::generatePaymentNumber(),
            'amount' => $housingProject->commitment_fee,
            'payment_gateway' => 'doku',
            'payment_method' => $request->payment_method,
            'status' => 'pending',
            'deadline' => now()->addHours(48), // Fixed 48 hours (2x24 jam) sesuai requirement
        ]);

        // Create a DOKU Checkout session.
        try {
            $checkout = $this->payment->createDokuCheckout(
                $payment->payment_number,
                (int) $payment->amount,
                Auth::user(),
                route('screening.result', $screeningId),
                $payment->payment_method
            );
            $payment->update([
                'transaction_id' => $checkout['request_id'],
                'payment_response' => $checkout['response'],
            ]);
            return redirect()->away($checkout['checkout_url']);
        } catch (\Exception $e) {
            $payment->delete();
            return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }
    /**
     * Handle payment callback from Midtrans
     */
    public function paymentCallback(Request $request)
    {
        try {
            $headers = [
                'client-id' => $request->header('Client-Id'),
                'request-id' => $request->header('Request-Id'),
                'request-timestamp' => $request->header('Request-Timestamp'),
                'signature' => $request->header('Signature'),
            ];
            if (!app(\App\Services\DokuCheckoutService::class)->verifyNotification(
                $request->getContent(), $headers, '/screening/payment-callback'
            )) {
                return response()->json(['status' => 'invalid signature'], 401);
            }
            if (!$this->handleDokuNotification($request->all())) {
                return response()->json(['status' => 'invalid transaction'], 404);
            }
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function handleDokuNotification(array $data): bool
    {
        $payment = CommitmentFeePayment::where(
            'payment_number', data_get($data, 'order.invoice_number')
        )->first();
        if (!$payment || (int) data_get($data, 'order.amount') !== (int) $payment->amount) {
            return false;
        }

        $payment->update(['payment_response' => $data]);
        $status = strtoupper((string) data_get($data, 'transaction.status'));
        if ($status === 'SUCCESS' && $payment->status !== 'success') {
            $this->handleSuccessfulPayment($payment);
        } elseif ($payment->status === 'pending' && in_array($status, ['FAILED', 'EXPIRED', 'CANCELLED', 'TIMEOUT'], true)) {
            $payment->update(['status' => 'failed']);
        }
        return true;
    }

    /**
     * Handle successful payment
     */
    protected function handleSuccessfulPayment(CommitmentFeePayment $payment)
    {
        DB::transaction(function () use ($payment) {
            $payment->refresh();
            if ($payment->status === 'success') {
                return;
            }

            // Update payment status
            $payment->update([
                'status' => 'success',
                'payment_date' => now(),
                'payment_response' => $payment->payment_response,
            ]);

            // Update unit status
            $payment->housingUnit->update(['status' => 'booked']);

            // Create unit selection
            $unitSelection = UnitSelection::create([
                'nup_registration_id' => $payment->kprScreening->nup_registration_id,
                'housing_unit_id' => $payment->housing_unit_id,
                'user_id' => $payment->user_id,
                'selection_order' => 1,
                'selection_date' => now(),
                'status' => 'confirmed',
                'utk_payment_status' => 'paid',
            ]);

            // Update KPR screening with unit selection
            $payment->kprScreening->update([
                'unit_selection_id' => $unitSelection->id,
            ]);

            // Create SPR document
            $spr = SprDocument::create([
                'user_id' => $payment->user_id,
                'nup_registration_id' => $payment->kprScreening->nup_registration_id,
                'unit_selection_id' => $unitSelection->id,
                'kpr_screening_id' => $payment->kprScreening->id,
                'spr_number' => SprDocument::generateSprNumber(),
                'spr_date' => now()->toDateString(),
                'unit_price' => $payment->housingUnit->price,
                'down_payment' => $payment->housingUnit->price * 0.3,
                'remaining_amount' => $payment->housingUnit->price * 0.7,
                'payment_scheme' => 'kpr',
                'status' => 'issued',
                'issued_date' => now(),
                'issued_by' => 'System',
                'ematerai_status' => 'pending',
            ]);

            // Process E-Materai integration
            try {
                if (config('services.ematerai.enabled', false)) {
                    // Generate PDF first
                    $pdfPath = $this->generateSprPdf($spr);
                    $spr->update(['document_file' => $pdfPath]);
                    
                    // Stamp with E-Materai
                    $this->ematerai->stampDocument($spr);
                } else {
                    // Mock E-Materai for development
                    $this->ematerai->mockStampDocument($spr);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the payment process
                logger()->error('E-Materai stamping failed: ' . $e->getMessage());
            }
        });

        // Clear session
        session()->forget(['selected_unit_id', 'screening_id']);
    }

    /**
     * Mock payment success for testing
     */
    public function mockPaymentSuccess($paymentId)
    {
        $payment = CommitmentFeePayment::findOrFail($paymentId);

        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        // Hanya izinkan mock jika Midtrans belum dikonfigurasi
        if ($this->payment->isConfigured()) {
            return redirect()->route('screening.index')
                ->with('error', 'Fitur simulasi tidak tersedia di mode produksi.');
        }

        $this->handleSuccessfulPayment($payment);

        return redirect()->route('screening.result', $payment->kpr_screening_id)
            ->with('success', '✅ Pembayaran uang tanda komitmen berhasil! SPR sedang diproses.');
    }

    /**
     * Show decision result page
     */
    public function showResult($screeningId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $screening = KprScreening::findOrFail($screeningId);

        if ($screening->user_id !== Auth::id()) {
            abort(403);
        }

        return view('screening.result', compact('screening'));
    }

    /**
     * Generate SPR PDF document
     */
    protected function generateSprPdf(SprDocument $spr)
    {
        // This is a placeholder for PDF generation
        // In production, you would use a proper PDF library like DomPDF or TCPDF
        
        $fileName = 'spr_' . $spr->spr_number . '.pdf';
        $filePath = 'documents/spr/' . $fileName;
        
        // For now, just create a placeholder file
        // In production, this would generate a proper PDF with all the SPR details
        $content = "SPR DOCUMENT\n";
        $content .= "SPR Number: " . $spr->spr_number . "\n";
        $content .= "Date: " . $spr->spr_date . "\n";
        $content .= "Unit Price: " . $spr->unit_price . "\n";
        $content .= "Down Payment: " . $spr->down_payment . "\n";
        $content .= "Remaining Amount: " . $spr->remaining_amount . "\n";
        
        Storage::put($filePath, $content);
        
        return $filePath;
    }
}
