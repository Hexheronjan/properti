<?php

namespace App\Http\Controllers;

use App\Models\UtkPayment;
use App\Models\UnitSelection;
use App\Models\NupRegistration;
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

        if ($unitSelection->isUtkPaid()) {
            return back()->with('error', 'UTK sudah dibayar.');
        }

        if ($unitSelection->isUtkPaymentDeadlinePassed()) {
            return back()->with('error', 'Batas waktu pembayaran UTK telah berakhir.');
        }

        // Get UTK amount from config or unit price (e.g., 5% of unit price)
        $utkAmount = $unitSelection->housingUnit->price * 0.05; // 5% of unit price

        return view('utk.create', compact('unitSelection', 'utkAmount'));
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
            'payment_method' => 'required|in:bank_transfer,echannel,qris,credit_card',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $utkAmount = $unitSelection->housingUnit->price * 0.05; // 5% of unit price

            // Create UTK payment record
            $utkPayment = UtkPayment::create([
                'unit_selection_id' => $unitSelection->id,
                'nup_registration_id' => $unitSelection->nup_registration_id,
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
                // Update unit selection with UTK amount
                $unitSelection->update(['utk_amount' => $utkAmount]);

                // Check if it's mock mode
                if (isset($result['mock_mode']) && $result['mock_mode']) {
                    return view('utk.payment-mock', [
                        'transaction' => $result['transaction'],
                        'utkPayment' => $utkPayment,
                        'unitSelection' => $unitSelection,
                        'message' => $result['message'] ?? '',
                    ]);
                }

                return view('utk.payment', [
                    'snap_token' => $result['snap_token'],
                    'client_key' => $result['client_key'],
                    'transaction' => $result['transaction'],
                    'utkPayment' => $utkPayment,
                    'unitSelection' => $unitSelection,
                ]);
            } else {
                return back()->with('error', $result['message']);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses pembayaran UTK: ' . $e->getMessage());
        }
    }

    /**
     * Process mock UTK payment success
     */
    public function mockPaymentSuccess(Request $request, $utkPaymentId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $utkPayment = UtkPayment::findOrFail($utkPaymentId);

        if ($utkPayment->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $result = $this->paymentService->processUtkMockPaymentSuccess($request->payment_number);

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
                    'utk_payment_status' => 'paid',
                    'utk_payment_date' => now(),
                    'utk_payment_reference' => $result['reference'] ?? 'MOCK-' . time(),
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
     * Payment callback
     */
    public function paymentCallback(Request $request)
    {
        try {
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

        $utkPayments = UtkPayment::with(['unitSelection.housingUnit', 'user', 'nupRegistration'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.utk.index', compact('utkPayments'));
    }
}
