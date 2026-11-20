<?php

namespace App\Http\Controllers;

use App\Models\NupRegistration;
use App\Models\User;
use App\Services\PaymentGatewayService;
use App\Services\WhatsAppGatewayService;
use App\Services\NupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

    /**
     * Display registration form
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $paymentMethods = config('nup.payment_methods');
        $nupFee = config('nup.nup_fee');

        return view('nup.register', compact('paymentMethods', 'nupFee'));
    }

    /**
     * Store new registration
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:tunai_mitra_pintar,kpr_bank_flpp',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'nik' => 'required|string|max:16|unique:users,nik',
            'birth_date' => 'required|date',
            'occupation' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Update user information
            $user = Auth::user();
            $user->update([
                'phone' => $request->phone,
                'address' => $request->address,
                'nik' => $request->nik,
                'birth_date' => $request->birth_date,
                'occupation' => $request->occupation,
            ]);

            // Check if user already has an active registration
            $existingRegistration = NupRegistration::where('user_id', $user->id)
                ->whereIn('payment_status', ['pending', 'paid'])
                ->first();

            if ($existingRegistration) {
                return back()->with('error', 'Anda sudah memiliki registrasi yang aktif.');
            }

            // Create NUP registration
            $registration = NupRegistration::create([
                'user_id' => $user->id,
                'registration_number' => NupRegistration::generateRegistrationNumber(),
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'nup_fee' => config('nup.nup_fee'),
            ]);

            // Send WhatsApp notification
            $this->whatsappService->sendRegistrationNotification($user, $registration->registration_number);

            if ($request->payment_method === 'kpr_bank_flpp') {
                return redirect()->route('kpr.create-from-nup', $registration->id)
                    ->with('success', 'Registrasi berhasil! Silakan isi data screening KPR Anda.');
            }

            return redirect()->route('nup.show', $registration->id)
                ->with('success', 'Registrasi berhasil! Silakan lanjutkan ke pembayaran.');

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

        return view('nup.show', compact('registration'));
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
            return redirect()->route('nup.show', $registration->id)
                ->with('success', 'Pembayaran Anda telah berhasil terverifikasi!');
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:bank_transfer,echannel,qris,credit_card',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $result = $this->paymentService->createPayment($registration, $request->payment_method);

            if ($result['success']) {
                // Check if it's mock mode
                if (isset($result['mock_mode']) && $result['mock_mode']) {
                    return view('nup.payment-mock', [
                        'transaction' => $result['transaction'],
                        'registration' => $registration,
                        'message' => $result['message'] ?? '',
                    ]);
                }

                return view('nup.payment', [
                    'snap_token' => $result['snap_token'],
                    'client_key' => $result['client_key'],
                    'transaction' => $result['transaction'],
                    'registration' => $registration,
                ]);
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

                return redirect()->route('nup.show', $registration->id)
                    ->with('success', 'Pembayaran mock berhasil diproses!');
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
        try {
            $notificationData = $request->all();
            $success = $this->paymentService->handleNotification($notificationData);

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
        $registrations = NupRegistration::where('user_id', $user->id)
            ->with('unitSelections.housingUnit')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('nup.dashboard', compact('registrations', 'user'));
    }

    /**
     * Admin index
     */
    public function adminIndex()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $registrations = NupRegistration::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

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

        $registration = NupRegistration::with([
            'user',
            'unitSelections.housingUnit',
            'kprScreening',
            'sprDocuments',
            'paymentTransactions'
        ])->findOrFail($id);

        return view('admin.nup.show', compact('registration'));
    }
}