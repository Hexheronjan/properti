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
     * Create SPR form
     */
    public function create($unitSelectionId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $unitSelection = UnitSelection::with(['housingUnit', 'nupRegistration', 'kprScreening'])
            ->findOrFail($unitSelectionId);

        if ($unitSelection->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$unitSelection->isConfirmed()) {
            return back()->with('error', 'Unit selection harus dikonfirmasi terlebih dahulu.');
        }

        // Check if KPR screening is required and approved
        $nupRegistration = $unitSelection->nupRegistration;
        if ($nupRegistration->payment_method === 'kpr_bank_flpp') {
            $kprScreening = $unitSelection->kprScreening;
            if (!$kprScreening || !$kprScreening->isApproved()) {
                return back()->with('error', 'Screening KPR harus disetujui terlebih dahulu.');
            }
        }

        // Check if SPR already exists
        if ($unitSelection->sprDocuments()->exists()) {
            return redirect()->route('spr.show', $unitSelection->sprDocuments()->first()->id);
        }

        $paymentSchemes = ['cash', 'kpr', 'installment'];

        return view('spr.create', compact('unitSelection', 'nupRegistration', 'paymentSchemes'));
    }

    /**
     * Store SPR data
     */
    public function store(Request $request, $unitSelectionId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $unitSelection = UnitSelection::with(['housingUnit', 'nupRegistration', 'kprScreening'])
            ->findOrFail($unitSelectionId);

        if ($unitSelection->user_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'payment_scheme' => 'required|in:cash,kpr,installment',
            'down_payment' => 'required|numeric|min:0',
            'terms_conditions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $unitPrice = $unitSelection->housingUnit->price;
            $downPayment = $request->down_payment;
            $remainingAmount = $unitPrice - $downPayment;

            // Validate down payment
            if ($downPayment > $unitPrice) {
                return back()->with('error', 'Down payment tidak boleh melebihi harga unit.');
            }

            if ($downPayment < ($unitPrice * 0.3)) {
                return back()->with('error', 'Down payment minimum 30% dari harga unit.');
            }

            $spr = SprDocument::create([
                'user_id' => Auth::id(),
                'nup_registration_id' => $unitSelection->nup_registration_id,
                'unit_selection_id' => $unitSelection->id,
                'kpr_screening_id' => $unitSelection->kprScreening->id ?? null,
                'spr_number' => SprDocument::generateSprNumber(),
                'spr_date' => now()->toDateString(),
                'unit_price' => $unitPrice,
                'down_payment' => $downPayment,
                'remaining_amount' => $remainingAmount,
                'payment_scheme' => $request->payment_scheme,
                'terms_conditions' => $request->terms_conditions,
                'status' => 'draft',
            ]);

            return redirect()->route('spr.show', $spr->id)
                ->with('success', 'Draft SPR berhasil dibuat. Silakan review dan konfirmasi.');

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
            'unitSelection.housingUnit',
            'nupRegistration',
            'kprScreening'
        ])->findOrFail($id);

        if ($spr->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
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
            'unitSelection.housingUnit',
            'nupRegistration'
        ])->findOrFail($id);

        if ($spr->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $pdf = Pdf::loadView('spr.pdf', compact('spr'));
        return $pdf->download('SPR-' . $spr->spr_number . '.pdf');
    }

    /**
     * Issue SPR (admin only)
     */
    public function issue($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $spr = SprDocument::findOrFail($id);

        // Check if UTK payment is completed
        if (!$spr->canBeIssued()) {
            return back()->with('error', 'SPR tidak dapat diterbitkan. Pembayaran UTK belum selesai.');
        }

        try {
            $spr->update([
                'status' => 'issued',
                'issued_date' => now(),
                'issued_by' => Auth::user()->name,
            ]);

            // Generate PDF document
            $pdf = Pdf::loadView('spr.pdf', compact('spr'));
            $fileName = 'SPR-' . $spr->spr_number . '.pdf';
            $filePath = $pdf->save(storage_path('app/public/spr/' . $fileName));

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

        $spr = SprDocument::findOrFail($id);

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

        $sprDocuments = SprDocument::with(['user', 'unitSelection.housingUnit'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.spr.index', compact('sprDocuments'));
    }

    /**
     * Show pending SPRs
     */
    public function pending()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $pendingSprs = SprDocument::with(['user', 'unitSelection.housingUnit'])
            ->where('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.spr.pending', compact('pendingSprs'));
    }
}