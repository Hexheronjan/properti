<?php

namespace App\Services;

use App\Models\WhatsappNotification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppGatewayService
{
    protected $gatewayUrl;
    protected $apiKey;
    protected $senderNumber;

    public function __construct()
    {
        $this->gatewayUrl = config('services.whatsapp.gateway_url');
        $this->apiKey = config('services.whatsapp.api_key');
        $this->senderNumber = config('services.whatsapp.sender_number');
    }

    /**
     * Check if WhatsApp gateway is properly configured
     */
    public function isConfigured(): bool
    {
        return config('services.whatsapp.notification_enabled') &&
               $this->gatewayUrl &&
               $this->apiKey;
    }

    /**
     * Create skipped notification record
     */
    protected function createSkippedNotification(string $phoneNumber, string $message, string $notificationType, ?int $userId): void
    {
        try {
            $formattedPhone = $this->formatPhoneNumber($phoneNumber);
            
            WhatsappNotification::create([
                'user_id' => $userId,
                'phone_number' => $formattedPhone,
                'notification_type' => $notificationType,
                'message' => $message,
                'status' => 'pending',
                'gateway_response' => json_encode(['skipped' => true, 'reason' => 'WhatsApp gateway not configured']),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create skipped notification: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp message
     */
    public function sendMessage(string $phoneNumber, string $message, string $notificationType, ?int $userId = null): array
    {
        // Check if WhatsApp gateway is configured
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'skipped' => true,
                'message' => 'WhatsApp gateway dinonaktifkan.',
            ];
        }

        // Format phone number (remove country code prefix if needed)
        $formattedPhone = $this->formatPhoneNumber($phoneNumber);

        try {
            // Create notification record
            $notification = WhatsappNotification::create([
                'user_id' => $userId,
                'phone_number' => $formattedPhone,
                'notification_type' => $notificationType,
                'message' => $message,
                'status' => 'pending',
            ]);

            // Send message via gateway
            $response = $this->sendToGateway($formattedPhone, $message);

            if ($response['success']) {
                $notification->update([
                    'status' => 'sent',
                    'gateway_message_id' => $response['message_id'] ?? null,
                    'gateway_response' => json_encode($response),
                    'sent_at' => now(),
                ]);
            } else {
                $notification->update([
                    'status' => 'failed',
                    'gateway_response' => json_encode($response),
                    'retry_count' => 1,
                ]);

                // Schedule retry
                $this->scheduleRetry($notification);
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('WhatsApp sending error: ' . $e->getMessage());

            if (isset($notification)) {
                $notification->update([
                    'status' => 'failed',
                    'gateway_response' => $e->getMessage(),
                    'retry_count' => 1,
                ]);
            }

            return [
                'success' => false,
                'message' => 'Gagal mengirim pesan: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * General notification send method fallback
     */
    public function sendNotification(string $phoneNumber, string $message, string $notificationType = 'general', ?int $userId = null): array
    {
        return $this->sendMessage($phoneNumber, $message, $notificationType, $userId);
    }


    /**
     * Send message to WhatsApp gateway
     * Supports CloudWA (cloudwa.my.id) API format
     */
    protected function sendToGateway(string $phoneNumber, string $message): array
    {
        try {
            // Format phone: CloudWA pakai format 628xxxx (dengan 62)
            $formattedForApi = $this->formatPhoneForApi($phoneNumber);

            // CloudWA API payload matches Meta's WhatsApp Cloud API structure
            $payload = [
                'recipient_type' => 'individual',
                'to'             => $formattedForApi,
                'type'           => 'text',
                'text'           => [
                    'body' => $message,
                ],
            ];

            Log::info('CloudWA request', [
                'url'     => $this->gatewayUrl,
                'phone'   => $formattedForApi,
                'payload' => $payload,
            ]);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $this->apiKey,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])
                ->post($this->gatewayUrl, $payload);

            $responseData = $response->json() ?? [];

            Log::info('CloudWA response', [
                'status'   => $response->status(),
                'body'     => $response->body(),
                'decoded'  => $responseData,
            ]);

            if ($response->successful()) {
                // CloudWA success: code is 200, status/success can be true
                $code = $responseData['code'] ?? null;
                $success = ($code === 200) || filter_var(
                    $responseData['status'] ?? $responseData['success'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );

                if (!$success) {
                    Log::warning('CloudWA send failed', [
                        'phone'    => $formattedForApi,
                        'response' => $responseData,
                    ]);
                }

                // Get message ID from the messages array or data object
                $messageId = $responseData['messages'][0]['id'] ?? $responseData['data']['id'] ?? $responseData['id'] ?? null;

                return [
                    'success'    => $success,
                    'message_id' => $messageId,
                    'data'       => $responseData,
                    'message'    => $this->parseGatewayMessage($responseData, $success),
                ];
            }

            // HTTP error (4xx/5xx) — tampilkan body asli untuk debug
            $errorMsg = !empty($responseData)
                ? $this->parseGatewayMessage($responseData, false)
                : $response->body();

            return [
                'success'     => false,
                'message'     => 'Gateway error (' . $response->status() . '): ' . $errorMsg,
                'status_code' => $response->status(),
                'data'        => $responseData,
                'raw_body'    => $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('CloudWA connection error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format phone number for API call: CloudWA needs 628xx format
     */
    protected function formatPhoneForApi(string $phoneNumber): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Already has country code 62
        if (str_starts_with($phone, '62')) {
            return $phone;
        }

        // Local format 08xx → 628xx
        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }

        // Starts with 8 → 628xx
        if (str_starts_with($phone, '8')) {
            return '62' . $phone;
        }

        return $phone;
    }

    /**
     * Parse CloudWA/gateway API response into user-friendly message
     */
    protected function parseGatewayMessage(array $responseData, bool $success): string
    {
        if ($success) {
            return $responseData['message'] ?? $responseData['detail'] ?? 'Pesan terkirim';
        }

        $reason = $responseData['message']
            ?? $responseData['reason']
            ?? $responseData['detail']
            ?? $responseData['error']
            ?? 'Gagal mengirim pesan';

        $reasonLower = strtolower($reason);

        if (str_contains($reasonLower, 'disconnected')) {
            return 'Perangkat WhatsApp belum terhubung. Silakan hubungkan perangkat di dashboard CloudWA Anda.';
        }
        if (str_contains($reasonLower, 'invalid') && str_contains($reasonLower, 'token')) {
            return 'API Key tidak valid. Periksa WHATSAPP_GATEWAY_API_KEY di .env.';
        }
        if (str_contains($reasonLower, 'quota') || str_contains($reasonLower, 'limit')) {
            return 'Kuota pesan habis. Silakan top up paket di dashboard CloudWA.';
        }
        if (str_contains($reasonLower, 'phone') || str_contains($reasonLower, 'number')) {
            return 'Nomor WhatsApp tujuan tidak valid.';
        }
        if (str_contains($reasonLower, 'unauthorized') || str_contains($reasonLower, '401')) {
            return 'Unauthorized: API Key tidak valid atau tidak memiliki akses.';
        }

        return $reason;
    }

    /**
     * Format phone number for Fonnte (local 08xx format; countryCode handles +62)
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (str_starts_with($phone, '62')) {
            return '0' . substr($phone, 2);
        }

        if (str_starts_with($phone, '8')) {
            return '0' . $phone;
        }

        return $phone;
    }

    /**
     * Schedule retry for failed notification
     */
    protected function scheduleRetry(WhatsappNotification $notification): void
    {
        if ($notification->retry_count < 3) {
            // This would typically use Laravel's job queue
            // For now, we'll implement simple retry logic
            $delayMinutes = [5, 15, 30];
            $delay = $delayMinutes[$notification->retry_count];

            // Schedule retry (implement with Laravel queue jobs)
            // SendWhatsAppRetryJob::dispatch($notification->id)->delay(now()->addMinutes($delay));
        }
    }

    /**
     * Send registration notification
     */
    public function sendRegistrationNotification(User $user, string $registrationNumber): void
    {
        $message = "Halo {$user->name},\n\n" .
                  "Selamat! Registrasi Anda telah berhasil.\n\n" .
                  "Nomor Registrasi: {$registrationNumber}\n" .
                  "Tanggal: " . now()->format('d-m-Y H:i') . "\n\n" .
                  "Silakan lanjutkan ke pembayaran NUP untuk mendapatkan nomor antrian.\n" .
                  "Terima kasih!";

        if ($user->phone) {
            $this->sendMessage($user->phone, $message, 'registration', $user->id);
        }
    }

    /**
     * Send queue number notification
     */
    public function sendQueueNumberNotification(User $user, string $queueNumber, string $selectionDate): void
    {
        $message = "Halo {$user->name},\n\n" .
                  "Nomor Antrian Anda telah diterbitkan!\n\n" .
                  "Nomor Antrian: {$queueNumber}\n" .
                  "Tanggal Pemilihan: {$selectionDate}\n\n" .
                  "Harap datang tepat waktu untuk pemilihan unit.\n" .
                  "Terima kasih!";

        if ($user->phone) {
            $this->sendMessage($user->phone, $message, 'queue_number', $user->id);
        }
    }

    /**
     * Send unit selection notification
     */
    public function sendUnitSelectionNotification(User $user, string $unitCode, string $block, string $number): void
    {
        $message = "Halo {$user->name},\n\n" .
                  "Selamat! Anda telah berhasil memilih unit.\n\n" .
                  "Unit: Blok {$block} No. {$number}\n" .
                  "Kode Unit: {$unitCode}\n" .
                  "Tanggal: " . now()->format('d-m-Y H:i') . "\n\n" .
                  "Lanjutkan ke proses selanjutnya.\n" .
                  "Terima kasih!";

        if ($user->phone) {
            $this->sendMessage($user->phone, $message, 'selection', $user->id);
        }
    }

    /**
     * Send KPR screening notification
     */
    public function sendKprScreeningNotification(User $user, string $status, ?string $notes = null): void
    {
        $statusText = match($status) {
            'pending' => 'Sedang diproses',
            'processing' => 'Dalam proses verifikasi',
            'approved' => 'Disetujui (Layak Proses)',
            'layak_bersyarat' => 'Layak Bersyarat',
            'rejected' => 'Tidak Layak',
            'requires_document' => 'Membutuhkan dokumen tambahan',
            default => $status
        };

        $message = "Halo {$user->name},\n\n" .
                  "Status Screening KPR Anda: {$statusText}\n\n" .
                  "Tanggal: " . now()->format('d-m-Y H:i');

        if ($notes) {
            $message .= "\n\nCatatan: {$notes}";
        }

        $message .= "\n\nTerima kasih!";

        if ($user->phone) {
            $this->sendMessage($user->phone, $message, 'screening', $user->id);
        }
    }

    /**
     * Send SPR notification
     */
    public function sendSprNotification(User $user, string $sprNumber, string $sprDate): void
    {
        $message = "Halo {$user->name},\n\n" .
                  "Surat Pemesanan Rumah (SPR) telah diterbitkan!\n\n" .
                  "Nomor SPR: {$sprNumber}\n" .
                  "Tanggal SPR: {$sprDate}\n\n" .
                  "Silakan unduh dokumen SPR dari aplikasi.\n" .
                  "Terima kasih!";

        if ($user->phone) {
            $this->sendMessage($user->phone, $message, 'spr', $user->id);
        }
    }

    /**
     * Send reminder notification
     */
    public function sendReminderNotification(User $user, string $reminderType, string $date): void
    {
        $reminderMessages = [
            'payment' => "Pengingat pembayaran NUP",
            'selection' => "Pengingat pemilihan unit",
            'document' => "Pengingat pengunggahan dokumen",
        ];

        $title = $reminderMessages[$reminderType] ?? 'Pengingat';

        $message = "Halo {$user->name},\n\n" .
                  "{$title}\n\n" .
                  "Tanggal: {$date}\n\n" .
                  "Harap selesaikan proses tersebut sebelum batas waktu.\n" .
                  "Terima kasih!";

        if ($user->phone) {
            $this->sendMessage($user->phone, $message, 'reminder', $user->id);
        }
    }

    /**
     * Check notification status
     */
    public function getNotificationStatus(int $notificationId): ?WhatsappNotification
    {
        return WhatsappNotification::find($notificationId);
    }

    /**
     * Retry failed notifications
     */
    public function retryFailedNotifications(): int
    {
        $failedNotifications = WhatsappNotification::where('status', 'failed')
            ->where('retry_count', '<', 3)
            ->get();

        $retryCount = 0;

        foreach ($failedNotifications as $notification) {
            $response = $this->sendToGateway($notification->phone_number, $notification->message);

            if ($response['success']) {
                $notification->update([
                    'status' => 'sent',
                    'gateway_message_id' => $response['message_id'] ?? null,
                    'gateway_response' => json_encode($response),
                    'sent_at' => now(),
                ]);
                $retryCount++;
            } else {
                $notification->increment('retry_count');
            }
        }

        return $retryCount;
    }

    /**
     * Send refund notification
     */
    public function sendRefundNotification(User $user, string $status, ?string $notes = null): void
    {
        $statusText = match($status) {
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'processed' => 'Diproses',
            default => $status
        };

        $message = "Halo {$user->name},\n\n" .
                  "Status Permintaan Refund NUP: {$statusText}\n\n" .
                  "Tanggal: " . now()->format('d-m-Y H:i');

        if ($notes) {
            $message .= "\n\nCatatan: {$notes}";
        }

        if ($status === 'approved') {
            $message .= "\n\nRefund sedang dalam proses. Mohon tunggu konfirmasi selanjutnya.";
        } elseif ($status === 'rejected') {
            $message .= "\n\nMohon hubungi admin untuk informasi lebih lanjut.";
        }

        $message .= "\n\nTerima kasih!";

        if ($user->phone) {
            $this->sendMessage($user->phone, $message, 'refund', $user->id);
        }
    }

    /**
     * Send UTK payment notification
     */
    public function sendUtkPaymentNotification(User $user, string $unitCode, float $amount): void
    {
        $message = "Halo {$user->name},\n\n" .
                  "Pembayaran UTK (Uang Tanda Jadi) berhasil!\n\n" .
                  "Unit: {$unitCode}\n" .
                  "Nominal: Rp " . number_format($amount, 0, ',', '.') . "\n" .
                  "Tanggal: " . now()->format('d-m-Y H:i') . "\n\n" .
                  "SPR akan segera diterbitkan setelah verifikasi.\n" .
                  "Terima kasih!";

        if ($user->phone) {
            $this->sendMessage($user->phone, $message, 'utk_payment', $user->id);
        }
    }

    /**
     * Send NUP forfeit notification
     */
    public function sendNupForfeitNotification(User $user, string $reason): void
    {
        $message = "Halo {$user->name},\n\n" .
                  "Mohon maaf, NUP Anda telah hangus.\n\n" .
                  "Alasan: {$reason}\n" .
                  "Tanggal: " . now()->format('d-m-Y H:i') . "\n\n" .
                  "Jika Anda ingin mengajukan refund atau alih jalur pembayaran,\n" .
                  "silakan hubungi admin.\n" .
                  "Terima kasih.";

        if ($user->phone) {
            $this->sendMessage($user->phone, $message, 'forfeit', $user->id);
        }
    }

    /**
     * Send payment method switch notification
     */
    public function sendPaymentMethodSwitchNotification(User $user, string $oldMethod, string $newMethod): void
    {
        $methodText = [
            'tunai_mitra_pintar' => 'Tunai / Mitra Pintar',
            'kpr_bank_flpp' => 'KPR Bank / FLPP',
        ];

        $message = "Halo {$user->name},\n\n" .
                  "Metode pembayaran Anda telah diubah.\n\n" .
                  "Dari: {$methodText[$oldMethod]}\n" .
                  "Ke: {$methodText[$newMethod]}\n" .
                  "Tanggal: " . now()->format('d-m-Y H:i') . "\n\n" .
                  "Silakan lanjutkan proses sesuai metode pembayaran baru.\n" .
                  "Terima kasih!";

        if ($user->phone) {
            $this->sendMessage($user->phone, $message, 'payment_switch', $user->id);
        }
    }

    /**
     * Send deadline reminder notification
     */
    public function sendDeadlineReminder(User $user, string $deadlineType, string $deadlineDate, string $action): void
    {
        $typeText = [
            'unit_selection' => 'Pemilihan Unit',
            'utk_payment' => 'Pembayaran UTK',
            'kpr_decision' => 'Keputusan Screening KPR',
        ];

        $message = "Halo {$user->name},\n\n" .
                  "PENGINGAT PENTING!\n\n" .
                  "Batas waktu {$typeText[$deadlineType]}:\n" .
                  "{$deadlineDate}\n\n" .
                  "Silakan {$action} sebelum batas waktu berakhir.\n\n" .
                  "Jika melewati batas waktu, NUP Anda dapat hangus.\n" .
                  "Terima kasih!";

        if ($user->phone) {
            $this->sendMessage($user->phone, $message, 'deadline_reminder', $user->id);
        }
    }
}