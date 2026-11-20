<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class DokuCheckoutService
{
    private const ENDPOINT = '/checkout/v1/payment';

    public function isConfigured(): bool
    {
        return (bool) (config('services.doku.client_id') && config('services.doku.secret_key'));
    }

    public function createPayment(string $invoice, int $amount, array $customer, string $returnUrl, string $paymentOption): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('DOKU belum dikonfigurasi. Isi DOKU_CLIENT_ID dan DOKU_SECRET_KEY.');
        }
        if ($amount < 1 || strlen($invoice) > 64) {
            throw new RuntimeException('Nominal atau nomor invoice DOKU tidak valid.');
        }

        $channels = match ($paymentOption) {
            'transfer' => [
                'VIRTUAL_ACCOUNT_BCA', 'VIRTUAL_ACCOUNT_BANK_MANDIRI', 'VIRTUAL_ACCOUNT_BRI',
                'VIRTUAL_ACCOUNT_BNI', 'VIRTUAL_ACCOUNT_BANK_PERMATA', 'VIRTUAL_ACCOUNT_BANK_CIMB',
                'VIRTUAL_ACCOUNT_BANK_DANAMON', 'VIRTUAL_ACCOUNT_BANK_SYARIAH_MANDIRI',
                'VIRTUAL_ACCOUNT_BNC', 'VIRTUAL_ACCOUNT_BTN', 'VIRTUAL_ACCOUNT_DOKU',
            ],
            'cash' => ['ONLINE_TO_OFFLINE_ALFA', 'ONLINE_TO_OFFLINE_INDOMARET'],
            default => throw new RuntimeException('Pilihan pembayaran DOKU tidak valid.'),
        };
        // BRI and BSI Virtual Accounts support low-value transactions such as a test promo.
        if ($paymentOption === 'transfer' && $amount < 10000) {
            $channels = ['VIRTUAL_ACCOUNT_BRI', 'VIRTUAL_ACCOUNT_BANK_SYARIAH_MANDIRI'];
        }

        $phone = preg_replace('/\D+/', '', (string) ($customer['phone'] ?? ''));
        if (str_starts_with($phone, '0')) {
            $phone = '62' . ltrim($phone, '0');
        }

        $body = [
            'order' => [
                'amount' => $amount,
                'invoice_number' => $invoice,
                'currency' => 'IDR',
                'callback_url_result' => $returnUrl,
                'auto_redirect' => true,
            ],
            'payment' => [
                'payment_due_date' => (int) config('services.doku.payment_due_minutes', 1440),
                'payment_method_types' => $channels,
            ],
            'customer' => array_filter([
                'name' => $customer['name'] ?? null,
                'email' => $customer['email'] ?? null,
                'phone' => $phone ?: null,
                'country' => 'ID',
            ]),
        ];
        $notificationUrl = config('services.doku.notification_url');
        if ($notificationUrl) {
            $body['additional_info'] = ['override_notification_url' => $notificationUrl];
        }

        $json = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $requestId = (string) Str::uuid();
        $timestamp = now('UTC')->format('Y-m-d\TH:i:s\Z');
        $digest = base64_encode(hash('sha256', $json, true));
        $signature = $this->signature($requestId, $timestamp, self::ENDPOINT, $digest);
        $base = rtrim(config('services.doku.base_url'), '/');

        $response = Http::timeout(15)->connectTimeout(5)->withHeaders([
            'Client-Id' => config('services.doku.client_id'),
            'Request-Id' => $requestId,
            'Request-Timestamp' => $timestamp,
            'Signature' => $signature,
            'Content-Type' => 'application/json',
        ])->withBody($json, 'application/json')->send('POST', $base . self::ENDPOINT);

        if (!$response->successful()) {
            $error = $response->json();
            $details = data_get($error, 'error.message') ?? data_get($error, 'message');
            if (is_array($details)) {
                $details = implode('; ', array_filter($details, 'is_string'));
            }
            $details = is_string($details) ? substr($details, 0, 300) : null;

            Log::warning('DOKU Checkout rejected payment creation', [
                'http_status' => $response->status(),
                'request_id' => $requestId,
                'error_code' => data_get($error, 'error.code'),
                'error_message' => $details,
            ]);

            throw new RuntimeException('DOKU menolak pembuatan pembayaran (HTTP ' . $response->status() . ')' . ($details ? ': ' . $details : '.'));
        }

        $data = $response->json();
        $url = data_get($data, 'response.payment.url');
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Respons DOKU tidak berisi URL checkout yang valid.');
        }

        return ['checkout_url' => $url, 'request_id' => $requestId, 'response' => $data];
    }

    /**
     * Check transaction status from DOKU API (ORDER STATUS API)
     * Returns 'SUCCESS', 'PENDING', 'FAILED', or null on error
     */
    public function checkTransactionStatus(string $invoiceNumber): ?string
    {
        if (!$this->isConfigured()) return null;

        try {
            $endpoint  = '/orders/v1/status/' . $invoiceNumber;
            $requestId = (string) Str::uuid();
            $timestamp = now('UTC')->format('Y-m-d\TH:i:s\Z');
            // For GET requests, DOKU specification requires NO Digest component
            $signature = $this->signature($requestId, $timestamp, $endpoint, null);
            $base      = rtrim(config('services.doku.base_url'), '/');

            $response = Http::timeout(15)->connectTimeout(5)->withHeaders([
                'Client-Id'         => config('services.doku.client_id'),
                'Request-Id'        => $requestId,
                'Request-Timestamp' => $timestamp,
                'Signature'         => $signature,
            ])->get($base . $endpoint);

            if (!$response->successful()) {
                Log::warning('DOKU checkTransactionStatus non-success response', [
                    'invoice'     => $invoiceNumber,
                    'http_status' => $response->status(),
                    'error'       => $response->json('error.message') ?? $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            $status = strtoupper((string) data_get($data, 'transaction.status', '')) ?: null;

            Log::info('DOKU checkTransactionStatus retrieved status', [
                'invoice' => $invoiceNumber,
                'status'  => $status,
            ]);

            return $status;

        } catch (\Exception $e) {
            Log::error('DOKU checkTransactionStatus error: ' . $e->getMessage(), [
                'invoice' => $invoiceNumber,
            ]);
            return null;
        }
    }

    public function verifyNotification(string $rawBody, array $headers, string $requestTarget): bool
    {
        $clientId = $headers['client-id'] ?? '';
        $requestId = $headers['request-id'] ?? '';
        $timestamp = $headers['request-timestamp'] ?? '';
        $received = $headers['signature'] ?? '';
        if (!$this->isConfigured() || !hash_equals((string) config('services.doku.client_id'), (string) $clientId)
            || !$requestId || !$timestamp || !$received) {
            return false;
        }

        $digest = base64_encode(hash('sha256', $rawBody, true));

        // Test with provided target path
        if (hash_equals($this->signature($requestId, $timestamp, $requestTarget, $digest), $received)) {
            return true;
        }

        // Test with alternative paths (e.g. without or with trailing slash)
        $altTarget = str_ends_with($requestTarget, '/') ? rtrim($requestTarget, '/') : $requestTarget . '/';
        if (hash_equals($this->signature($requestId, $timestamp, $altTarget, $digest), $received)) {
            return true;
        }

        return false;
    }

    public function signature(string $requestId, string $timestamp, string $target, ?string $digest = null): string
    {
        $component = "Client-Id:" . config('services.doku.client_id') . "\n"
            . "Request-Id:$requestId\nRequest-Timestamp:$timestamp\nRequest-Target:$target";

        // Digest is only included in POST/PUT requests with body; omitted in GET requests
        if ($digest !== null && $digest !== '') {
            $component .= "\nDigest:$digest";
        }

        return 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $component, config('services.doku.secret_key'), true));
    }
}
