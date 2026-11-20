<?php

namespace App\Http\Controllers;

use App\Models\HousingUnit;
use App\Models\NupRegistration;
use App\Models\UnitSelection;
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

        $availableUnits = HousingUnit::available()->orderBy('block')->orderBy('number')->get();
        $unitTypes = config('nup.unit_types');

        return view('units.index', compact('availableUnits', 'unitTypes'));
    }

    /**
     * Display unit selection page
     */
    public function create($nupRegistrationId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $registration = NupRegistration::findOrFail($nupRegistrationId);

        if ($registration->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$registration->isPaid()) {
            return back()->with('error', 'Silakan selesaikan pembayaran NUP terlebih dahulu.');
        }

        if ($registration->isForfeited()) {
            return back()->with('error', 'NUP Anda telah hangus.');
        }

        if ($registration->isExpired()) {
            return back()->with('error', 'Batas waktu pemilihan unit telah berakhir.');
        }

        if ($registration->unitSelections()->where('status', '!=', 'cancelled')->exists()) {
            return back()->with('error', 'Anda sudah memilih unit.');
        }

        // Check KPR screening deadline
        if ($registration->payment_method === 'kpr_bank_flpp') {
            $kprScreening = $registration->kprScreening;
            if ($kprScreening && $kprScreening->isUnitSelectionDeadlinePassed()) {
                return back()->with('error', 'Batas waktu pemilihan unit (2x24 jam) telah berakhir. NUP akan hangus.');
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

        if (!$registration->isPaid()) {
            return back()->with('error', 'Silakan selesaikan pembayaran NUP terlebih dahulu.');
        }

        $validator = Validator::make($request->all(), [
            'housing_unit_id' => 'required|exists:housing_units,id',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $unit = HousingUnit::findOrFail($request->housing_unit_id);

            if (!$unit->isAvailable()) {
                return back()->with('error', 'Unit yang dipilih tidak tersedia.');
            }

            // Calculate selection order based on queue number
            $selectionOrder = NupRegistration::where('payment_status', 'paid')
                ->where('id', '<=', $registration->id)
                ->count();

            // Create unit selection
            $selection = UnitSelection::create([
                'nup_registration_id' => $registration->id,
                'housing_unit_id' => $unit->id,
                'user_id' => Auth::id(),
                'selection_order' => $selectionOrder,
                'selection_date' => now(),
                'status' => 'selected',
                'notes' => $request->notes,
            ]);

            // Update unit status
            $unit->update(['status' => 'booked']);

            // Set UTK payment deadline (7 days)
            $selection->setUtkPaymentDeadline();

            // Send WhatsApp notification
            $this->whatsappService->sendUnitSelectionNotification(
                Auth::user(),
                $unit->unit_code,
                $unit->block,
                $unit->number
            );

            return redirect()->route('units.show', $selection->id)
                ->with('success', 'Unit berhasil dipilih! Silakan selesaikan pembayaran UTK dalam 7 hari.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memilih unit: ' . $e->getMessage());
        }
    }

    /**
     * Show unit selection details
     */
    public function show(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $selection = UnitSelection::with([
            'nupRegistration',
            'housingUnit',
            'user'
        ])->findOrFail($id);

        if ($selection->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        // Sync UTK payment status if it is pending
        if ($selection->utk_payment_status === 'pending') {
            $transactionId = $request->query('transaction_id');
            $orderId = $request->query('order_id');

            $pendingUtkPayments = \App\Models\UtkPayment::where('unit_selection_id', $selection->id)
                ->where('status', 'pending')
                ->get();

            foreach ($pendingUtkPayments as $utkPayment) {
                $txId = null;
                if ($orderId && $utkPayment->payment_number === $orderId) {
                    $txId = $transactionId;
                }
                $paymentService = app(\App\Services\PaymentGatewayService::class);
                $isPaid = $paymentService->syncUtkPaymentStatus($utkPayment, $txId);
                if ($isPaid) {
                    $selection->refresh();
                    break;
                }
            }
        }

        return view('units.show', compact('selection'));
    }

    /**
     * Confirm unit selection (admin only)
     */
    public function confirm($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $selection = UnitSelection::findOrFail($id);

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
    public function cancel($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $selection = UnitSelection::findOrFail($id);

        if ($selection->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        try {
            $selection->update(['status' => 'cancelled']);

            // Make unit available again
            if ($selection->status === 'selected') {
                $selection->housingUnit->update(['status' => 'available']);
            }

            return back()->with('success', 'Unit selection cancelled.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan pemilihan: ' . $e->getMessage());
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

        $unitSelections = UnitSelection::with(['user', 'housingUnit', 'nupRegistration'])
            ->orderBy('selection_order')
            ->paginate(20);

        return view('admin.units.index', compact('unitSelections'));
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