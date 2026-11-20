<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\NupRegistration;
use App\Models\UtkPayment;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use App\Models\KprPayment;
use App\Models\CommitmentFeePayment;
use Illuminate\Support\Facades\DB;
use App\Models\PromoCode;

class PaymentGatewayService
{
    protected DokuCheckoutService $doku;

    public function __construct()
    {
        $this->doku = app(DokuCheckoutService::class);
    }

    /**
     * Check if payment gateway is properly configured
     */
    public function isConfigured(): bool
    {
        return $this->doku->isConfigured();
    }

    public function createDokuCheckout(string $invoice, int $amount, $user, string $returnUrl, string $paymentOption): array
    {
        return $this->doku->createPayment($invoice, $amount, [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? null,
        ], $returnUrl, $paymentOption);
    }

    /**
     * Configure Midtrans settings
     */
    protected function configureMidtrans()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.environment') === 'production';
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create payment transaction
     */
    public function createPayment(NupRegistration $nupRegistration, string $paymentMethod, ?string $promoCode = null): array
    {
        $promoReserved = false;
        $promo = null;
        try {
            $amount = (int) $nupRegistration->nup_fee;
            $promoCode = strtoupper(trim((string) $promoCode));

            if ($paymentMethod === 'cash' && $promoCode !== '') {
                return ['success' => false, 'message' => 'Kode promo hanya berlaku untuk pembayaran transfer melalui DOKU.'];
            }

            if ($paymentMethod === 'transfer' && $promoCode !== '') {
                $promo = PromoCode::query()
                    ->whereRaw('UPPER(code) = ?', [$promoCode])
                    ->where('is_active', true)
                    ->where(function ($query) {
                        $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->first();

                if (!$promo) {
                    return ['success' => false, 'message' => 'Kode promo tidak valid, tidak aktif, atau sudah kedaluwarsa.'];
                }
                if (!in_array($promo->applicable_to ?? 'all', ['all', 'nup'], true)) {
                    return ['success' => false, 'message' => 'Kode promo ini tidak berlaku untuk pembayaran NUP.'];
                }
                if ($promo->allowed_email && strcasecmp($promo->allowed_email, (string) $nupRegistration->user->email) !== 0) {
                    return ['success' => false, 'message' => 'Kode promo ini tidak berlaku untuk akun Anda.'];
                }
                if ($promo->payable_amount > $amount) {
                    return ['success' => false, 'message' => 'Nominal promo tidak dapat melebihi biaya NUP.'];
                }

                $reserved = PromoCode::whereKey($promo->id)
                    ->where('is_active', true)
                    ->whereColumn('used_count', '<', 'usage_limit')
                    ->increment('used_count');
                if (!$reserved) {
                    return ['success' => false, 'message' => 'Kuota kode promo sudah habis.'];
                }
                $promoReserved = true;
                $amount = (int) $promo->payable_amount;
            }

            // Promo Rp 0 (Bebas Biaya / Testing): Langsung proses lunas tanpa melalui payment gateway
            if ($amount === 0) {
                $invoice = PaymentTransaction::generateTransactionNumber();
                $paymentTransaction = PaymentTransaction::create([
                    'user_id' => $nupRegistration->user_id,
                    'nup_registration_id' => $nupRegistration->id,
                    'transaction_number' => $invoice,
                    'payment_gateway' => 'promo_free',
                    'payment_method' => $paymentMethod,
                    'amount' => 0,
                    'status' => 'pending',
                    'gateway_request_payload' => ['promo_code' => $promo?->code, 'free_testing' => true],
                    'gateway_transaction_id' => $invoice,
                    'metadata' => [
                        'promo_code' => $promo?->code,
                        'original_amount' => (int) $nupRegistration->nup_fee,
                        'discounted_amount' => 0,
                    ],
                    'transaction_date' => now(),
                    'expiry_date' => now()->addHours(24),
                ]);

                $this->processSuccessfulPayment($paymentTransaction, [
                    'transaction_id' => $invoice,
                    'payment_method' => 'promo_free',
                    'status' => 'success',
                ]);

                return [
                    'success' => true,
                    'free_payment' => true,
                    'cash_payment' => false,
                    'checkout_url' => null,
                    'transaction' => $paymentTransaction,
                ];
            }

            $invoice = PaymentTransaction::generateTransactionNumber();
            $checkout = null;
            if ($paymentMethod === 'transfer') {
                $checkout = $this->doku->createPayment($invoice, $amount, [
                    'name' => $nupRegistration->user->name,
                    'email' => $nupRegistration->user->email,
                    'phone' => $nupRegistration->user->phone,
                ], route('nup.show', $nupRegistration->id), 'transfer');
            }

            // Create payment transaction record
            $paymentTransaction = PaymentTransaction::create([
                'user_id' => $nupRegistration->user_id,
                'nup_registration_id' => $nupRegistration->id,
                'transaction_number' => $invoice,
                'payment_gateway' => $paymentMethod === 'cash' ? 'cash_office' : 'doku',
                'payment_method' => $paymentMethod,
                'amount' => $amount,
                'status' => 'pending',
                'gateway_request_payload' => $checkout['response'] ?? ['manual' => true, 'location' => 'office'],
                'gateway_transaction_id' => $checkout['request_id'] ?? null,
                'metadata' => $promo ? [
                    'promo_code' => $promo->code,
                    'original_amount' => (int) $nupRegistration->nup_fee,
                    'discounted_amount' => $amount,
                ] : null,
                'transaction_date' => now(),
                'expiry_date' => now()->addHours(24),
            ]);

            return [
                'success' => true,
                'cash_payment' => $paymentMethod === 'cash',
                'checkout_url' => $checkout['checkout_url'] ?? null,
                'transaction' => $paymentTransaction,
            ];

        } catch (\Exception $e) {
            if ($promoReserved && $promo) {
                PromoCode::whereKey($promo->id)->where('used_count', '>', 0)->decrement('used_count');
            }
            Log::error('Payment creation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat pembayaran: ' . $e->getMessage(),
            ];
        }
    }

    public function confirmCashOfficePayment(PaymentTransaction $paymentTransaction, int $adminId): bool
    {
        if ($paymentTransaction->payment_gateway !== 'cash_office'
            || $paymentTransaction->payment_method !== 'cash'
            || $paymentTransaction->status !== 'pending'
            || !$paymentTransaction->nupRegistration
            || $paymentTransaction->nupRegistration->payment_status === 'paid') {
            return false;
        }

        $this->processSuccessfulPayment($paymentTransaction, [
            'payment_type' => 'cash_office',
            'confirmed_by' => $adminId,
        ]);

        return true;
    }

    /**
     * Create mock payment for testing without credentials
     */
    protected function createMockPayment(NupRegistration $nupRegistration, string $paymentMethod): array
    {
        try {
            $transactionNumber = PaymentTransaction::generateTransactionNumber();

            // Create mock payment transaction
            $paymentTransaction = PaymentTransaction::create([
                'user_id' => $nupRegistration->user_id,
                'nup_registration_id' => $nupRegistration->id,
                'transaction_number' => $transactionNumber,
                'payment_gateway' => 'mock',
                'payment_method' => $paymentMethod,
                'amount' => $nupRegistration->nup_fee,
                'status' => 'pending',
                'gateway_request_payload' => ['mock' => true, 'payment_method' => $paymentMethod],
                'transaction_date' => now(),
                'expiry_date' => now()->addHours(24),
            ]);

            return [
                'success' => true,
                'mock_mode' => true,
                'transaction' => $paymentTransaction,
                'message' => 'Payment gateway belum dikonfigurasi. Menggunakan mode mock untuk testing.',
            ];

        } catch (\Exception $e) {
            Log::error('Mock payment creation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat mock payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process mock payment success (for testing)
     */
    public function processMockPaymentSuccess(string $transactionNumber): array
    {
        try {
            $paymentTransaction = PaymentTransaction::where('transaction_number', $transactionNumber)->first();

            if (!$paymentTransaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction not found',
                ];
            }

            // Update transaction to success
            $paymentTransaction->update([
                'status' => 'success',
                'gateway_response_payload' => ['mock' => true, 'status' => 'success'],
                'transaction_date' => now(),
            ]);

            // Process success payment logic
            $this->processSuccessfulPayment($paymentTransaction, ['mock' => true, 'status' => 'success']);

            return [
                'success' => true,
                'message' => 'Mock payment processed successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Mock payment processing error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memproses mock payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle payment notification
     */
    public function handleNotification(array $notificationData): bool
    {
        try {
            $transactionId = (string) data_get($notificationData, 'order.invoice_number');
            $transactionStatus = strtoupper((string) data_get($notificationData, 'transaction.status'));

            Log::info('Processing payment notification', [
                'invoice' => $transactionId,
                'status'  => $transactionStatus,
            ]);

            $paymentTransaction = PaymentTransaction::where('transaction_number', $transactionId)->first();

            if (!$paymentTransaction) {
                Log::error('Payment notification failed: transaction not found in database', [
                    'invoice' => $transactionId,
                ]);
                return false;
            }

            Log::info('Payment transaction found', [
                'transaction_id'      => $paymentTransaction->id,
                'invoice'             => $paymentTransaction->transaction_number,
                'nup_registration_id' => $paymentTransaction->nup_registration_id,
                'current_status'      => $paymentTransaction->status,
            ]);

            // Check if already processed (Idempotent handling)
            if ($transactionStatus === 'SUCCESS' && $paymentTransaction->status === 'success' && $paymentTransaction->nupRegistration?->payment_status === 'paid') {
                Log::info('Payment notification already processed previously (Idempotent)', [
                    'invoice'             => $transactionId,
                    'nup_registration_id' => $paymentTransaction->nup_registration_id,
                ]);
                return true;
            }

            // Validate amount strictly
            $notifAmount = (int) data_get($notificationData, 'order.amount');
            $txAmount    = (int) $paymentTransaction->amount;
            if ($notifAmount !== $txAmount) {
                Log::error('DOKU NUP notification rejected due to amount mismatch', [
                    'invoice'   => $transactionId,
                    'notif_amt' => $notifAmount,
                    'db_amt'    => $txAmount,
                ]);
                return false;
            }

            if ($transactionStatus === 'SUCCESS') {
                $this->processSuccessfulPayment($paymentTransaction, $notificationData);
                Log::info('Payment notification marked transaction and NUP as SUCCESS/PAID', [
                    'invoice'             => $transactionId,
                    'nup_registration_id' => $paymentTransaction->nup_registration_id,
                ]);
            } elseif ($paymentTransaction->status === 'pending' && in_array($transactionStatus, ['FAILED', 'EXPIRED', 'CANCELLED', 'TIMEOUT'], true)) {
                $paymentTransaction->update([
                    'status' => in_array($transactionStatus, ['EXPIRED', 'TIMEOUT'], true) ? 'expired' : 'failed',
                    'gateway_response_payload' => $notificationData,
                ]);
                Log::info('Payment notification updated transaction status to non-success', [
                    'invoice' => $transactionId,
                    'status'  => $paymentTransaction->status,
                ]);
            } else {
                $paymentTransaction->update(['gateway_response_payload' => $notificationData]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Payment notification handling error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return false;
        }
    }

    public function handleDokuNotification(array $data): bool
    {
        $invoice = (string) data_get($data, 'order.invoice_number', '');
        if ($invoice === '') {
            Log::warning('DOKU notification received without invoice number');
            return false;
        }

        Log::info('Handling DOKU notification callback', [
            'invoice' => $invoice,
            'status'  => data_get($data, 'transaction.status'),
        ]);

        if (PaymentTransaction::where('transaction_number', $invoice)->exists()) {
            return $this->handleNotification($data);
        }
        if (UtkPayment::where('payment_number', $invoice)->exists()) {
            return $this->handleUtkNotification($data);
        }
        if (KprPayment::where('payment_number', $invoice)->exists()) {
            return app(KprPaymentController::class)->handleDokuNotification($data);
        }
        if (CommitmentFeePayment::where('payment_number', $invoice)->exists()) {
            return app(ScreeningDecisionController::class)->handleDokuNotification($data);
        }

        Log::warning('DOKU notification received but invoice not found in any payment table', [
            'invoice' => $invoice,
        ]);
        return false;
    }

    /**
     * Process successful payment
     */
    protected function processSuccessfulPayment(PaymentTransaction $paymentTransaction, array $notificationData): void
    {
        DB::transaction(function () use ($paymentTransaction, $notificationData) {
            $lockedTx = PaymentTransaction::where('id', $paymentTransaction->id)->lockForUpdate()->first();
            if (!$lockedTx) {
                return;
            }
            $wasAlreadySuccessful = $lockedTx->status === 'success';

            $ref = data_get($notificationData, 'transaction.original_request_id')
                ?? data_get($notificationData, 'virtual_account_payment.reference_number')
                ?? data_get($notificationData, 'transaction_id')
                ?? $lockedTx->gateway_transaction_id
                ?? $lockedTx->transaction_number;

            $updateData = [
                'status'                   => 'success',
                'gateway_transaction_id'   => $ref,
                'gateway_response_payload' => $notificationData,
            ];

            if (!$wasAlreadySuccessful || !$lockedTx->transaction_date) {
                $updateData['transaction_date'] = now();
            }

            $lockedTx->update($updateData);

            // Update NUP registration status with row locking
            $nupRegistration = $lockedTx->nup_registration_id 
                ? NupRegistration::where('id', $lockedTx->nup_registration_id)->lockForUpdate()->first() 
                : null;

            if ($nupRegistration) {
                $wasAlreadyPaid = $nupRegistration->payment_status === 'paid';
                $queueNumber = $nupRegistration->queue_number ?: NupRegistration::generateQueueNumber();

                $nupRegistration->update([
                    'payment_status'            => 'paid',
                    'payment_date'              => $nupRegistration->payment_date ?: now(),
                    'queue_number'              => $queueNumber,
                    'queue_issue_date'          => $nupRegistration->queue_issue_date ?: now(),
                    'payment_gateway_reference' => $ref,
                ]);
                $nupRegistration->setUnitSelectionDeadline();

                Log::info('NUP registration successfully marked as PAID', [
                    'nup_registration_id' => $nupRegistration->id,
                    'registration_number' => $nupRegistration->registration_number,
                    'queue_number'        => $queueNumber,
                    'invoice'             => $lockedTx->transaction_number,
                ]);

                // Send WhatsApp notification only on first success
                if (!$wasAlreadyPaid) {
                    $this->sendPaymentSuccessNotification($nupRegistration);
                }
            } else {
                Log::warning('PaymentTransaction has no associated NupRegistration', [
                    'transaction_id' => $lockedTx->id,
                    'invoice'        => $lockedTx->transaction_number,
                ]);
            }
        });
    }

    /**
     * Send payment success notification
     */
    protected function sendPaymentSuccessNotification(NupRegistration $nupRegistration): void
    {
        try {
            $whatsappService = app(WhatsAppGatewayService::class);

            // Get payment method from the latest transaction
            $paymentTransaction = PaymentTransaction::where('nup_registration_id', $nupRegistration->id)
                ->where('status', 'success')
                ->latest()
                ->first();

            $paymentMethodText = 'Tidak diketahui';
            if ($paymentTransaction) {
                $paymentMethodText = $this->getPaymentMethodText($paymentTransaction->payment_method, $paymentTransaction->payment_channel);
            }

            $message = "Halo {$nupRegistration->user->name},\n\n" .
                      "Pembayaran NUP Anda telah berhasil!\n\n" .
                      "Nomor Registrasi: {$nupRegistration->registration_number}\n" .
                      "Nomor Antrian: {$nupRegistration->queue_number}\n" .
                      "Tanggal Pembayaran: {$nupRegistration->payment_date->format('d-m-Y H:i')}\n" .
                      "Metode Pembayaran: {$paymentMethodText}\n" .
                      "Nominal: Rp " . number_format($nupRegistration->nup_fee, 0, ',', '.') . "\n\n" .
                      "Simpan nomor antrian Anda untuk pemilihan unit.\n" .
                      "Terima kasih!";

            if ($nupRegistration->user->phone) {
                $whatsappService->sendMessage(
                    $nupRegistration->user->phone,
                    $message,
                    'payment',
                    $nupRegistration->user_id
                );
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp notification error: ' . $e->getMessage());
        }
    }

    /**
     * Get user-friendly payment method text
     */
    protected function getPaymentMethodText(string $paymentMethod, ?string $paymentChannel = null): string
    {
        $methodText = [
            'transfer' => 'Transfer bank via DOKU',
            'cash' => 'Tunai via DOKU (Alfamart/Indomaret)',
            'bank_transfer' => 'Transfer Bank',
            'echannel' => 'Mandiri Bill',
            'qris' => 'QRIS',
            'credit_card' => 'Kartu Kredit',
            'gopay' => 'GoPay',
            'dana' => 'DANA',
            'ovo' => 'OVO',
            'shopeepay' => 'ShopeePay',
            'linkaja' => 'LinkAja',
            'bca_klikpay' => 'BCA KlikPay',
            'bri_epay' => 'BRI E-Pay',
            'cimb_clicks' => 'CIMB Clicks',
            'danamon_online' => 'Danamon Online',
            'bca_va' => 'BCA Virtual Account',
            'bri_va' => 'BRI Virtual Account',
            'bni_va' => 'BNI Virtual Account',
            'mandiri_va' => 'Mandiri Virtual Account',
            'permata_va' => 'Permata Virtual Account',
            'cimb_va' => 'CIMB Virtual Account',
            'other_va' => 'Virtual Account',
        ];

        // If payment channel is provided, use it for more specific info
        if ($paymentChannel) {
            $channelText = [
                'bca' => 'BCA',
                'bri' => 'BRI',
                'bni' => 'BNI',
                'mandiri' => 'Mandiri',
                'permata' => 'Permata',
                'cimb' => 'CIMB',
                'danamon' => 'Danamon',
                'gopay' => 'GoPay',
                'dana' => 'DANA',
                'ovo' => 'OVO',
                'shopeepay' => 'ShopeePay',
                'linkaja' => 'LinkAja',
            ];

            $channelName = $channelText[strtolower($paymentChannel)] ?? strtoupper($paymentChannel);

            if ($paymentMethod === 'bank_transfer' || $paymentMethod === 'echannel') {
                return $methodText[$paymentMethod] . " - {$channelName}";
            }

            if (isset($methodText[strtolower($paymentChannel)])) {
                return $methodText[strtolower($paymentChannel)];
            }
        }

        return $methodText[$paymentMethod] ?? $paymentMethod;
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(string $transactionNumber): ?PaymentTransaction
    {
        return PaymentTransaction::where('transaction_number', $transactionNumber)->first();
    }

    /**
     * Sync payment status with Midtrans API directly (helpful for localhost/cPanel fallback)
     */
    public function syncPaymentStatus(PaymentTransaction $paymentTransaction, ?string $transactionId = null): bool
    {
        try {
            if ($paymentTransaction->payment_gateway === 'doku') {
                Log::info('Initiating DOKU active status sync', [
                    'transaction_number'  => $paymentTransaction->transaction_number,
                    'nup_registration_id' => $paymentTransaction->nup_registration_id,
                    'current_status'      => $paymentTransaction->status,
                ]);

                // Sync status from DOKU Order Status API
                $status = $this->doku->checkTransactionStatus($paymentTransaction->transaction_number);
                if ($status === 'SUCCESS') {
                    $this->processSuccessfulPayment($paymentTransaction, [
                        'source'  => 'doku_status_check',
                        'status'  => 'SUCCESS',
                        'invoice' => $paymentTransaction->transaction_number,
                    ]);
                    Log::info('DOKU active status sync completed successfully: SUCCESS', [
                        'transaction_number'  => $paymentTransaction->transaction_number,
                        'nup_registration_id' => $paymentTransaction->nup_registration_id,
                    ]);
                    return true;
                } elseif (in_array($status, ['FAILED', 'EXPIRED', 'CANCELLED', 'TIMEOUT'], true)) {
                    $newStatus = in_array($status, ['EXPIRED', 'TIMEOUT'], true) ? 'expired' : 'failed';
                    $paymentTransaction->update([
                        'status' => $newStatus,
                    ]);
                    Log::info('DOKU active status sync updated transaction to: ' . $newStatus, [
                        'transaction_number' => $paymentTransaction->transaction_number,
                    ]);
                } else {
                    Log::info('DOKU active status sync result: ' . ($status ?: 'UNKNOWN/PENDING'), [
                        'transaction_number' => $paymentTransaction->transaction_number,
                    ]);
                }
                return false;
            }

            if ($paymentTransaction->payment_gateway !== 'midtrans') {
                // cash_office dikonfirmasi manual oleh admin.
                return false;
            }
            if (!config('services.midtrans.server_key')) return false;
            $this->configureMidtrans();
            
            // If explicit transaction ID is provided, save it to the transaction model
            if ($transactionId && $paymentTransaction->gateway_transaction_id !== $transactionId) {
                $paymentTransaction->update(['gateway_transaction_id' => $transactionId]);
            }
            
            $queryId = $paymentTransaction->gateway_transaction_id ?: $paymentTransaction->transaction_number;
            
            // Query Midtrans API using transaction ID or number (order ID)
            $statusResponse = \Midtrans\Transaction::status($queryId);
            
            if ($statusResponse) {
                $transactionStatus = $statusResponse->transaction_status ?? null;
                $fraudStatus = $statusResponse->fraud_status ?? null;
                $statusResponseArray = json_decode(json_encode($statusResponse), true);
                
                // Keep gateway_transaction_id updated
                if (isset($statusResponseArray['transaction_id'])) {
                    $paymentTransaction->update(['gateway_transaction_id' => $statusResponseArray['transaction_id']]);
                }

                if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                    if ($transactionStatus == 'capture' && $fraudStatus == 'challenge') {
                        // challenged, do nothing
                    } else {
                        // Successful payment!
                        $this->processSuccessfulPayment($paymentTransaction, $statusResponseArray);
                        return true;
                    }
                } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                    $paymentTransaction->update([
                        'status' => 'failed',
                        'gateway_response_payload' => $statusResponseArray,
                    ]);
                }
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Error syncing payment status from Midtrans: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync UTK payment status by querying DOKU Order Status API directly.
     * Called when user returns from DOKU checkout page to /utk/show/{id}.
     */
    public function syncUtkPaymentStatus(UtkPayment $utkPayment, ?string $transactionId = null): bool
    {
        try {
            if (!$this->isConfigured()) return false;

            Log::info('Initiating DOKU active status sync for UTK payment', [
                'payment_number' => $utkPayment->payment_number,
                'utk_payment_id' => $utkPayment->id,
                'current_status' => $utkPayment->status,
            ]);

            // Query DOKU Order Status API using the payment_number as invoice
            $status = $this->doku->checkTransactionStatus($utkPayment->payment_number);

            if ($status === 'SUCCESS') {
                $this->processSuccessfulUtkPayment($utkPayment, [
                    'source'  => 'doku_status_check',
                    'status'  => 'SUCCESS',
                    'invoice' => $utkPayment->payment_number,
                ]);
                Log::info('DOKU active status sync for UTK: SUCCESS → payment marked as paid', [
                    'payment_number' => $utkPayment->payment_number,
                    'utk_payment_id' => $utkPayment->id,
                ]);
                return true;
            } elseif (in_array($status, ['FAILED', 'EXPIRED', 'CANCELLED', 'TIMEOUT'], true)) {
                $newStatus = in_array($status, ['EXPIRED', 'TIMEOUT'], true) ? 'expired' : 'failed';
                $utkPayment->update(['status' => $newStatus]);
                Log::info('DOKU active status sync for UTK: updated to ' . $newStatus, [
                    'payment_number' => $utkPayment->payment_number,
                ]);
            } else {
                Log::info('DOKU active status sync for UTK: still ' . ($status ?: 'PENDING/UNKNOWN'), [
                    'payment_number' => $utkPayment->payment_number,
                ]);
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Error syncing UTK payment status from DOKU: ' . $e->getMessage(), [
                'payment_number' => $utkPayment->payment_number ?? null,
            ]);
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Legacy Midtrans sync stub (no longer used – kept to avoid breaking callers)
    // -------------------------------------------------------------------------
    private function syncUtkPaymentStatusMidtrans(UtkPayment $utkPayment): bool
    {
        try {
            $this->configureMidtrans();
            
            // If explicit transaction ID is provided, save it
            if ($transactionId && $utkPayment->payment_gateway_reference !== $transactionId) {
                $utkPayment->update(['payment_gateway_reference' => $transactionId]);
            }
            
            $queryId = $utkPayment->payment_gateway_reference ?: $utkPayment->payment_number;
            
            // Query Midtrans API using transaction ID or number
            $statusResponse = \Midtrans\Transaction::status($queryId);
            
            if ($statusResponse) {
                $transactionStatus = $statusResponse->transaction_status ?? null;
                $fraudStatus = $statusResponse->fraud_status ?? null;
                $statusResponseArray = json_decode(json_encode($statusResponse), true);
                
                // Keep reference updated
                if (isset($statusResponseArray['transaction_id'])) {
                    $utkPayment->update(['payment_gateway_reference' => $statusResponseArray['transaction_id']]);
                }

                if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                    if ($transactionStatus == 'capture' && $fraudStatus == 'challenge') {
                        // challenged, do nothing
                    } else {
                        // Successful UTK payment!
                        $this->processSuccessfulUtkPayment($utkPayment, $statusResponseArray);
                        return true;
                    }
                } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                    $utkPayment->update([
                        'status' => 'failed',
                        'payment_gateway_response' => $statusResponseArray,
                    ]);
                }
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Error syncing UTK payment status from Midtrans: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get payment methods
     */
    public function getPaymentMethods(): array
    {
        return [
            'transfer' => 'Transfer bank melalui DOKU',
            'cash' => 'Tunai di Alfamart atau Indomaret melalui DOKU',
        ];
    }

    /**
     * Create UTK payment transaction
     */
    public function createUtkPayment(UtkPayment $utkPayment, string $paymentMethod): array
    {
        // Promo Rp 0 (Bebas Biaya / Testing): Langsung proses lunas tanpa melalui payment gateway
        if ((int) $utkPayment->amount === 0) {
            $this->processSuccessfulUtkPayment($utkPayment, [
                'transaction_id' => $utkPayment->payment_number,
                'status' => 'success',
                'promo_free' => true,
            ]);

            return [
                'success'      => true,
                'free_payment' => true,
                'cash_payment' => false,
                'checkout_url' => null,
                'transaction'  => $utkPayment,
            ];
        }

        // Tunai di kantor: tidak perlu DOKU, langsung simpan dan tunggu konfirmasi admin
        if ($paymentMethod === 'cash') {
            return [
                'success'      => true,
                'cash_payment' => true,
                'transaction'  => $utkPayment,
            ];
        }

        try {
            $checkout = $this->doku->createPayment($utkPayment->payment_number, (int) $utkPayment->amount, [
                'name' => $utkPayment->user->name,
                'email' => $utkPayment->user->email,
                'phone' => $utkPayment->user->phone,
            ], route('utk.show', $utkPayment->id), $paymentMethod);
            $utkPayment->update([
                'snap_token' => $checkout['checkout_url'],
                'payment_gateway_reference' => $checkout['request_id'],
                'payment_gateway_response' => $checkout['response'],
            ]);

            return [
                'success' => true,
                'checkout_url' => $checkout['checkout_url'],
                'transaction' => $utkPayment,
            ];

        } catch (\Exception $e) {
            Log::error('UTK payment creation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat pembayaran UTK: ' . $e->getMessage(),
            ];
        }
    }


    /**
     * Create mock UTK payment for testing without credentials
     */
    protected function createMockUtkPayment(UtkPayment $utkPayment, string $paymentMethod): array
    {
        try {
            return [
                'success' => true,
                'mock_mode' => true,
                'transaction' => $utkPayment,
                'message' => 'Payment gateway belum dikonfigurasi. Menggunakan mode mock untuk testing.',
            ];

        } catch (\Exception $e) {
            Log::error('Mock UTK payment creation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat mock UTK payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process mock UTK payment success (for testing)
     */
    public function processUtkMockPaymentSuccess(string $paymentNumber): array
    {
        try {
            $utkPayment = UtkPayment::where('payment_number', $paymentNumber)->first();

            if (!$utkPayment) {
                return [
                    'success' => false,
                    'message' => 'UTK Payment not found',
                ];
            }

            // Update UTK payment to success
            $utkPayment->update([
                'status' => 'paid',
                'payment_gateway_response' => ['mock' => true, 'status' => 'success'],
                'payment_date' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Mock UTK payment processed successfully',
                'reference' => 'MOCK-' . $paymentNumber,
            ];

        } catch (\Exception $e) {
            Log::error('Mock UTK payment processing error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memproses mock UTK payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle UTK payment notification from DOKU webhook
     */
    public function handleUtkNotification(array $notificationData): bool
    {
        try {
            $transactionId     = (string) data_get($notificationData, 'order.invoice_number', '');
            $transactionStatus = strtoupper((string) data_get($notificationData, 'transaction.status'));

            Log::info('Processing UTK payment notification', [
                'invoice' => $transactionId,
                'status'  => $transactionStatus,
            ]);

            $utkPayment = UtkPayment::where('payment_number', $transactionId)->first();

            if (!$utkPayment) {
                Log::error('UTK notification failed: payment_number not found in utk_payments table', [
                    'invoice' => $transactionId,
                ]);
                return false;
            }

            Log::info('UTK payment record found', [
                'utk_payment_id'   => $utkPayment->id,
                'payment_number'   => $utkPayment->payment_number,
                'current_status'   => $utkPayment->status,
                'doku_status'      => $transactionStatus,
            ]);

            // Idempotency: if already paid, return true without re-processing
            if ($transactionStatus === 'SUCCESS' && $utkPayment->status === 'paid') {
                Log::info('UTK notification already processed previously (Idempotent)', [
                    'invoice'       => $transactionId,
                    'utk_payment_id' => $utkPayment->id,
                ]);
                return true;
            }

            // Validate amount strictly
            $notifAmount = (int) data_get($notificationData, 'order.amount');
            $dbAmount    = (int) $utkPayment->amount;

            if ($notifAmount !== $dbAmount) {
                Log::error('DOKU UTK notification rejected due to amount mismatch', [
                    'invoice'   => $transactionId,
                    'notif_amt' => $notifAmount,
                    'db_amt'    => $dbAmount,
                ]);
                return false;
            }

            if ($transactionStatus === 'SUCCESS') {
                $this->processSuccessfulUtkPayment($utkPayment, $notificationData);
                Log::info('UTK notification processed: payment marked as PAID', [
                    'invoice'       => $transactionId,
                    'utk_payment_id' => $utkPayment->id,
                ]);
            } elseif ($utkPayment->status === 'pending' && in_array($transactionStatus, ['FAILED', 'EXPIRED', 'CANCELLED', 'TIMEOUT'], true)) {
                $utkPayment->update([
                    'status'                   => in_array($transactionStatus, ['EXPIRED', 'TIMEOUT'], true) ? 'expired' : 'failed',
                    'payment_gateway_response' => $notificationData,
                ]);
                Log::info('UTK notification updated status to non-success', [
                    'invoice' => $transactionId,
                    'status'  => $utkPayment->status,
                ]);
            } else {
                $utkPayment->update(['payment_gateway_response' => $notificationData]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('UTK payment notification handling error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return false;
        }
    }

    /**
     * Process successful UTK payment — wrapped in DB transaction for atomicity and idempotency.
     */
    protected function processSuccessfulUtkPayment(UtkPayment $utkPayment, array $notificationData): void
    {
        DB::transaction(function () use ($utkPayment, $notificationData) {
            $utkPayment->refresh();

            // Idempotency re-check inside transaction
            if ($utkPayment->status === 'paid') {
                Log::info('processSuccessfulUtkPayment: already paid, skipping (idempotent)', [
                    'utk_payment_id' => $utkPayment->id,
                    'payment_number' => $utkPayment->payment_number,
                ]);
                return;
            }

            $ref = data_get($notificationData, 'transaction.original_request_id')
                ?? data_get($notificationData, 'virtual_account_payment.reference_number')
                ?? data_get($notificationData, 'transaction_id')
                ?? $notificationData['invoice'] // from doku_status_check synthetic payload
                ?? $utkPayment->payment_gateway_reference
                ?? $utkPayment->payment_number;

            $utkPayment->update([
                'status'                   => 'paid',
                'payment_gateway_response' => $notificationData,
                'payment_date'             => now(),
                'payment_gateway_reference'=> $ref,
            ]);

            Log::info('UTK payment status updated to PAID in database', [
                'utk_payment_id' => $utkPayment->id,
                'payment_number' => $utkPayment->payment_number,
                'reference'      => $ref,
            ]);

            // Update unit selection status
            $unitSelection = $utkPayment->unitSelection;
            if ($unitSelection) {
                $unitSelection->update([
                    'status'                => 'confirmed',
                    'utk_payment_status'    => 'paid',
                    'utk_payment_date'      => now(),
                    'utk_payment_reference' => $ref,
                ]);

                Log::info('UnitSelection status updated to confirmed after UTK payment', [
                    'unit_selection_id' => $unitSelection->id,
                    'utk_payment_id'    => $utkPayment->id,
                ]);

                // Send WhatsApp notification (non-blocking, outside transaction is fine but wrapped in try-catch)
                try {
                    $this->sendUtkPaymentSuccessNotification($utkPayment);
                } catch (\Exception $e) {
                    Log::error('WhatsApp UTK notification error (non-critical): ' . $e->getMessage());
                }
            } else {
                Log::warning('processSuccessfulUtkPayment: no UnitSelection linked', [
                    'utk_payment_id' => $utkPayment->id,
                ]);
            }
        });
    }

    /**
     * Send UTK payment success notification
     */
    protected function sendUtkPaymentSuccessNotification(UtkPayment $utkPayment): void
    {
        try {
            $whatsappService = app(WhatsAppGatewayService::class);
            $unitSelection = $utkPayment->unitSelection;
            $housingUnit = $unitSelection->housingUnit;
            
            $message = "Halo {$utkPayment->user->name},\n\n" .
                      "Pembayaran UTK (Uang Tanda Jadi) Anda telah berhasil!\n\n" .
                      "Unit: {$housingUnit->unit_code} - Blok {$housingUnit->block} No. {$housingUnit->number}\n" .
                      "Nomor Pembayaran: {$utkPayment->payment_number}\n" .
                      "Tanggal Pembayaran: {$utkPayment->payment_date->format('d-m-Y H:i')}\n" .
                      "Nominal: Rp " . number_format($utkPayment->amount, 0, ',', '.') . "\n\n" .
                      "SPR (Surat Pemesanan Rumah) akan segera diterbitkan.\n" .
                      "Terima kasih!";

            if ($utkPayment->user->phone) {
                $whatsappService->sendMessage(
                    $utkPayment->user->phone,
                    $message,
                    'utk_payment',
                    $utkPayment->user_id
                );
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp UTK notification error: ' . $e->getMessage());
        }
    }
}
