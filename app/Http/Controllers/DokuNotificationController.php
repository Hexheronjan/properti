<?php

namespace App\Http\Controllers;

use App\Services\DokuCheckoutService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DokuNotificationController extends Controller
{
    public function __invoke(Request $request, DokuCheckoutService $doku, PaymentGatewayService $payments)
    {
        $headers = [
            'client-id'         => $request->header('Client-Id'),
            'request-id'        => $request->header('Request-Id'),
            'request-timestamp' => $request->header('Request-Timestamp'),
            'signature'         => $request->header('Signature'),
        ];

        $requestTarget = $request->getRequestUri() ?: '/doku/notifications';

        Log::info('DOKU webhook callback received', [
            'ip'         => $request->ip(),
            'request_id' => $headers['request-id'],
            'timestamp'  => $headers['request-timestamp'],
            'target'     => $requestTarget,
        ]);

        // Verify signature with current target URI or well-known fallback paths
        $isSignatureValid = $doku->verifyNotification($request->getContent(), $headers, $requestTarget)
            || $doku->verifyNotification($request->getContent(), $headers, '/doku/notifications')
            || $doku->verifyNotification($request->getContent(), $headers, '/nup/payment/callback')
            || $doku->verifyNotification($request->getContent(), $headers, '/utk/callback');

        if (!$isSignatureValid) {
            Log::warning('Rejected DOKU notification with invalid signature', [
                'request_id' => $headers['request-id'],
                'target'     => $requestTarget,
            ]);
            return response()->json(['status' => 'invalid signature'], 401);
        }

        $data = $request->all();
        $invoice = (string) data_get($data, 'order.invoice_number');
        $status  = strtoupper((string) data_get($data, 'transaction.status'));

        Log::info('DOKU webhook verified successfully', [
            'invoice' => $invoice,
            'status'  => $status,
        ]);

        if (!$payments->handleDokuNotification($data)) {
            Log::warning('DOKU notification could not be processed', [
                'invoice' => $invoice,
                'status'  => $status,
                'reason'  => 'Transaction not found or amount mismatch',
            ]);
            return response()->json(['status' => 'transaction not found or invalid amount'], 404);
        }

        Log::info('DOKU notification processed and database synchronized successfully', [
            'invoice' => $invoice,
            'status'  => $status,
        ]);

        return response()->json(['status' => 'success']);
    }
}
