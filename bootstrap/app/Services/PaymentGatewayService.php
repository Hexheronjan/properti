<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\NupRegistration;
use App\Models\UtkPayment;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class PaymentGatewayService
{
    public function __construct()
    {
        // Only configure if payment is enabled and credentials are set
        if (config('services.midtrans.payment_enabled') && config('services.midtrans.server_key')) {
            $this->configureMidtrans();
        }
    }

    /**
     * Check if payment gateway is properly configured
     */
    public function isConfigured(): bool
    {
        return config('services.midtrans.payment_enabled') &&
               config('services.midtrans.server_key') &&
               config('services.midtrans.client_key');
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
    public function createPayment(NupRegistration $nupRegistration, string $paymentMethod): array
    {
        try {
            // Check if payment gateway is configured
            if (!$this->isConfigured()) {
                return $this->createMockPayment($nupRegistration, $paymentMethod);
            }

            $transactionDetails = [
                'transaction_details' => [
                    'order_id' => PaymentTransaction::generateTransactionNumber(),
                    'gross_amount' => (int) $nupRegistration->nup_fee,
                ],
                'customer_details' => [
                    'first_name' => $nupRegistration->user->name,
                    'email' => $nupRegistration->user->email,
                    'phone' => $nupRegistration->user->phone ?? '',
                ],
                'item_details' => [
                    [
                        'id' => 'NUP-' . $nupRegistration->id,
                        'price' => (int) $nupRegistration->nup_fee,
                        'quantity' => 1,
                        'name' => 'Pembayaran NUP',
                    ]
                ],
            ];

            // Add payment method specific settings
            if ($paymentMethod === 'bank_transfer') {
                $transactionDetails['payment_type'] = 'bank_transfer';
                $transactionDetails['bank_transfer'] = [
                    'bank' => 'bca',
                ];
            } elseif ($paymentMethod === 'echannel') {
                $transactionDetails['payment_type'] = 'echannel';
            } elseif ($paymentMethod === 'qris') {
                $transactionDetails['payment_type'] = 'qris';
            } elseif ($paymentMethod === 'credit_card') {
                $transactionDetails['payment_type'] = 'credit_card';
            }

            $snapToken = Snap::getSnapToken($transactionDetails);

            // Create payment transaction record
            $paymentTransaction = PaymentTransaction::create([
                'user_id' => $nupRegistration->user_id,
                'nup_registration_id' => $nupRegistration->id,
                'transaction_number' => $transactionDetails['transaction_details']['order_id'],
                'payment_gateway' => 'midtrans',
                'payment_method' => $paymentMethod,
                'amount' => $nupRegistration->nup_fee,
                'status' => 'pending',
                'gateway_request_payload' => $transactionDetails,
                'transaction_date' => now(),
                'expiry_date' => now()->addHours(24),
            ]);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'transaction' => $paymentTransaction,
                'client_key' => Config::$clientKey,
            ];

        } catch (\Exception $e) {
            Log::error('Payment creation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat pembayaran: ' . $e->getMessage(),
            ];
        }
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
            $notification = new Notification();

            $transactionId = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status;
            $paymentType = $notification->payment_type;

            $paymentTransaction = PaymentTransaction::where('transaction_number', $transactionId)->first();

            if (!$paymentTransaction) {
                Log::error('Transaction not found: ' . $transactionId);
                return false;
            }

            // Update transaction status based on notification
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $paymentTransaction->update([
                        'status' => 'pending',
                        'gateway_response_payload' => $notificationData,
                    ]);
                } else if ($fraudStatus == 'accept') {
                    $this->processSuccessfulPayment($paymentTransaction, $notificationData);
                }
            } else if ($transactionStatus == 'settlement') {
                $this->processSuccessfulPayment($paymentTransaction, $notificationData);
            } else if ($transactionStatus == 'cancel') {
                $paymentTransaction->update([
                    'status' => 'cancelled',
                    'gateway_response_payload' => $notificationData,
                ]);
            } else if ($transactionStatus == 'deny') {
                $paymentTransaction->update([
                    'status' => 'failed',
                    'gateway_response_payload' => $notificationData,
                ]);
            } else if ($transactionStatus == 'expire') {
                $paymentTransaction->update([
                    'status' => 'expired',
                    'gateway_response_payload' => $notificationData,
                ]);
            } else if ($transactionStatus == 'pending') {
                $paymentTransaction->update([
                    'status' => 'pending',
                    'gateway_response_payload' => $notificationData,
                ]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Payment notification handling error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Process successful payment
     */
    protected function processSuccessfulPayment(PaymentTransaction $paymentTransaction, array $notificationData): void
    {
        $updateData = [
            'status' => 'success',
            'gateway_response_payload' => $notificationData,
            'transaction_date' => now(),
        ];

        if (isset($notificationData['transaction_id'])) {
            $updateData['gateway_transaction_id'] = $notificationData['transaction_id'];
        }

        $paymentTransaction->update($updateData);

        // Update NUP registration status
        $nupRegistration = $paymentTransaction->nupRegistration;
        if ($nupRegistration) {
            $queueNumber = NupRegistration::generateQueueNumber();
            $ref = $paymentTransaction->gateway_transaction_id ?: $paymentTransaction->transaction_number;
            $nupRegistration->update([
                'payment_status' => 'paid',
                'payment_date' => now(),
                'queue_number' => $queueNumber,
                'queue_issue_date' => now(),
                'payment_gateway_reference' => $ref,
            ]);

            // Send WhatsApp notification
            $this->sendPaymentSuccessNotification($nupRegistration);
        }
    }

    /**
     * Send payment success notification
     */
    protected function sendPaymentSuccessNotification(NupRegistration $nupRegistration): void
    {
        try {
            $whatsappService = app(WhatsAppGatewayService::class);
            $message = "Halo {$nupRegistration->user->name},\n\n" .
                      "Pembayaran NUP Anda telah berhasil!\n\n" .
                      "Nomor Registrasi: {$nupRegistration->registration_number}\n" .
                      "Nomor Antrian: {$nupRegistration->queue_number}\n" .
                      "Tanggal Pembayaran: {$nupRegistration->payment_date->format('d-m-Y H:i')}\n" .
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
            if (!$this->isConfigured()) {
                return false;
            }

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
     * Sync UTK payment status with Midtrans API directly
     */
    public function syncUtkPaymentStatus(UtkPayment $utkPayment, ?string $transactionId = null): bool
    {
        try {
            if (!$this->isConfigured()) {
                return false;
            }

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
            'bank_transfer' => 'Transfer Bank',
            'echannel' => 'Mandiri Bill',
            'qris' => 'QRIS',
            'credit_card' => 'Kartu Kredit',
        ];
    }

    /**
     * Create UTK payment transaction
     */
    public function createUtkPayment(UtkPayment $utkPayment, string $paymentMethod): array
    {
        try {
            // Check if payment gateway is configured
            if (!$this->isConfigured()) {
                return $this->createMockUtkPayment($utkPayment, $paymentMethod);
            }

            $transactionDetails = [
                'transaction_details' => [
                    'order_id' => $utkPayment->payment_number,
                    'gross_amount' => (int) $utkPayment->amount,
                ],
                'customer_details' => [
                    'first_name' => $utkPayment->user->name,
                    'email' => $utkPayment->user->email,
                    'phone' => $utkPayment->user->phone ?? '',
                ],
                'item_details' => [
                    [
                        'id' => 'UTK-' . $utkPayment->id,
                        'price' => (int) $utkPayment->amount,
                        'quantity' => 1,
                        'name' => 'Pembayaran UTK (Uang Tanda Jadi)',
                    ]
                ],
            ];

            // Add payment method specific settings
            if ($paymentMethod === 'bank_transfer') {
                $transactionDetails['payment_type'] = 'bank_transfer';
                $transactionDetails['bank_transfer'] = [
                    'bank' => 'bca',
                ];
            } elseif ($paymentMethod === 'echannel') {
                $transactionDetails['payment_type'] = 'echannel';
            } elseif ($paymentMethod === 'qris') {
                $transactionDetails['payment_type'] = 'qris';
            } elseif ($paymentMethod === 'credit_card') {
                $transactionDetails['payment_type'] = 'credit_card';
            }

            $snapToken = Snap::getSnapToken($transactionDetails);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'transaction' => $utkPayment,
                'client_key' => Config::$clientKey,
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
     * Handle UTK payment notification
     */
    public function handleUtkNotification(array $notificationData): bool
    {
        try {
            $notification = new Notification();

            $transactionId = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status;

            $utkPayment = UtkPayment::where('payment_number', $transactionId)->first();

            if (!$utkPayment) {
                Log::error('UTK Payment not found: ' . $transactionId);
                return false;
            }

            // Update transaction status based on notification
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $utkPayment->update([
                        'status' => 'pending',
                        'payment_gateway_response' => $notificationData,
                    ]);
                } else if ($fraudStatus == 'accept') {
                    $this->processSuccessfulUtkPayment($utkPayment, $notificationData);
                }
            } else if ($transactionStatus == 'settlement') {
                $this->processSuccessfulUtkPayment($utkPayment, $notificationData);
            } else if ($transactionStatus == 'cancel') {
                $utkPayment->update([
                    'status' => 'failed',
                    'payment_gateway_response' => $notificationData,
                ]);
            } else if ($transactionStatus == 'deny') {
                $utkPayment->update([
                    'status' => 'failed',
                    'payment_gateway_response' => $notificationData,
                ]);
            } else if ($transactionStatus == 'expire') {
                $utkPayment->update([
                    'status' => 'expired',
                    'payment_gateway_response' => $notificationData,
                ]);
            } else if ($transactionStatus == 'pending') {
                $utkPayment->update([
                    'status' => 'pending',
                    'payment_gateway_response' => $notificationData,
                ]);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('UTK payment notification handling error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Process successful UTK payment
     */
    protected function processSuccessfulUtkPayment(UtkPayment $utkPayment, array $notificationData): void
    {
        $ref = $notificationData['transaction_id'] ?? $utkPayment->payment_gateway_reference ?? $utkPayment->payment_number;
        $utkPayment->update([
            'status' => 'paid',
            'payment_gateway_response' => $notificationData,
            'payment_date' => now(),
            'payment_gateway_reference' => $ref,
        ]);

        // Update unit selection status
        $unitSelection = $utkPayment->unitSelection;
        if ($unitSelection) {
            $unitSelection->update([
                'utk_payment_status' => 'paid',
                'utk_payment_date' => now(),
                'utk_payment_reference' => $ref,
            ]);

            // Send WhatsApp notification
            $this->sendUtkPaymentSuccessNotification($utkPayment);
        }
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