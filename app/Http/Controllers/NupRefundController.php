<?php

namespace App\Http\Controllers;

use App\Models\NupRefund;
use App\Models\NupRegistration;
use App\Services\WhatsAppGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NupRefundController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppGatewayService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Admin index - list all refunds
     */
    public function adminIndex()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $refunds = NupRefund::with(['nupRegistration.user', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.refunds.index', compact('refunds'));
    }

    /**
     * Admin approve refund
     */
    public function approve(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $refund = NupRefund::with('nupRegistration')->findOrFail($id);

        if ($refund->status !== 'pending') {
            return back()->with('error', 'Refund tidak dalam status pending.');
        }

        try {
            $refund->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::user()->name,
            ]);

            // Update registration
            $refund->nupRegistration->update([
                'refund_processed' => true,
                'refund_processed_at' => now(),
            ]);

            // Send WhatsApp notification
            $this->whatsappService->sendRefundNotification(
                $refund->user,
                'approved',
                'Permintaan refund Anda telah disetujui.'
            );

            return back()->with('success', 'Refund berhasil disetujui.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui refund: ' . $e->getMessage());
        }
    }

    /**
     * Admin reject refund
     */
    public function reject(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $refund = NupRefund::findOrFail($id);

        if ($refund->status !== 'pending') {
            return back()->with('error', 'Refund tidak dalam status pending.');
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $refund->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => Auth::user()->name,
                'rejection_reason' => $request->rejection_reason,
            ]);

            // Update registration
            $refund->nupRegistration->update([
                'refund_requested' => false,
                'refund_amount' => null,
                'refund_reason' => null,
            ]);

            // Send WhatsApp notification
            $this->whatsappService->sendRefundNotification(
                $refund->user,
                'rejected',
                $request->rejection_reason
            );

            return back()->with('success', 'Refund berhasil ditolak.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak refund: ' . $e->getMessage());
        }
    }

    /**
     * Admin process refund (mark as processed after payment)
     */
    public function process(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $refund = NupRefund::findOrFail($id);

        if ($refund->status !== 'approved') {
            return back()->with('error', 'Refund belum disetujui.');
        }

        $validator = Validator::make($request->all(), [
            'payment_reference' => 'required|string|max:200',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $refund->update([
                'status' => 'processed',
                'processed_at' => now(),
                'payment_reference' => $request->payment_reference,
            ]);

            return back()->with('success', 'Refund berhasil diproses.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses refund: ' . $e->getMessage());
        }
    }

    /**
     * Show refund details
     */
    public function show($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $refund = NupRefund::with(['nupRegistration', 'user'])->findOrFail($id);

        if ($refund->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        return view('refunds.show', compact('refund'));
    }
}
