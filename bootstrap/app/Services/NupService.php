<?php

namespace App\Services;

use App\Models\KprScreening;
use App\Models\NupRegistration;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;

class NupService
{
    /**
     * Handle NUP forfeiture when deadline is passed
     */
    public function handleForfeiture(NupRegistration $registration, string $reason): void
    {
        DB::transaction(function () use ($registration, $reason) {
            $registration->markAsForfeited($reason);
            
            // Log the forfeiture
            $this->logForfeiture($registration, $reason);
            
            // Send notification
            $this->sendForfeitureNotification($registration);
        });
    }

    /**
     * Process refund request for NUP
     */
    public function processRefund(NupRegistration $registration, string $reason): void
    {
        DB::transaction(function () use ($registration, $reason) {
            $registration->update([
                'refund_requested' => true,
                'refund_reason' => $reason,
            ]);
            
            // Create refund transaction
            $this->createRefundTransaction($registration, $reason);
            
            // Send notification
            $this->sendRefundRequestNotification($registration);
        });
    }

    /**
     * Process refund payment (admin action)
     */
    public function processRefundPayment(NupRegistration $registration): void
    {
        DB::transaction(function () use ($registration) {
            $registration->update([
                'refund_processed' => true,
                'refund_processed_at' => now(),
                'refund_amount' => $registration->nup_fee,
            ]);
            
            // Update payment transaction
            $this->updateRefundTransaction($registration);
            
            // Send notification
            $this->sendRefundProcessedNotification($registration);
        });
    }

    /**
     * Switch payment method (KPR to Cash/Mitra Pintar)
     */
    public function switchPaymentMethod(NupRegistration $registration, string $newMethod, string $reason): void
    {
        DB::transaction(function () use ($registration, $newMethod, $reason) {
            $registration->switchPaymentMethod($newMethod, $reason);
            
            // Reset KPR screening if exists
            if ($registration->kprScreening) {
                $registration->kprScreening->update([
                    'status' => 'cancelled',
                    'notes' => 'Payment method switched to ' . $newMethod,
                ]);
            }
            
            // Set new deadline based on new payment method
            $registration->setUnitSelectionDeadline();
            
            // Send notification
            $this->sendPaymentMethodSwitchNotification($registration, $newMethod);
        });
    }

    /**
     * Handle KPR screening result and set appropriate actions
     */
    public function handleScreeningResult(KprScreening $screening): void
    {
        DB::transaction(function () use ($screening) {
            $registration = $screening->nupRegistration;
            
            if ($screening->isLayakProses()) {
                // Layak Proses: Set 48-hour deadline for unit selection
                $screening->setUnitSelectionDeadline();
                $registration->update([
                    'unit_selection_deadline' => $screening->unit_selection_deadline,
                ]);
                
                // NUP becomes PT Anugrah's property
                $this->sendLayakProsesNotification($screening);
                
            } elseif ($screening->isLayakBersyarat()) {
                // Layak Bersyarat: Set 48-hour deadline, allow refund or continue
                $screening->setUnitSelectionDeadline();
                $registration->update([
                    'unit_selection_deadline' => $screening->unit_selection_deadline,
                ]);
                
                $this->sendLayakBersyaratNotification($screening);
                
            } elseif ($screening->isTidakLayak()) {
                // Tidak Layak: Allow refund or switch payment method
                $this->sendTidakLayakNotification($screening);
            }
        });
    }

    /**
     * Record participant decision after screening
     */
    public function recordParticipantDecision(KprScreening $screening, string $decision, ?string $notes = null): void
    {
        DB::transaction(function () use ($screening, $decision, $notes) {
            $screening->recordParticipantDecision($decision, $notes);
            
            $registration = $screening->nupRegistration;
            
            switch ($decision) {
                case 'select_unit':
                    // Participant chooses to select unit
                    // No additional action needed, deadline already set
                    break;
                    
                case 'request_refund':
                    // Process refund request
                    $this->processRefund($registration, 'Refund requested after screening result: ' . $screening->status);
                    break;
                    
                case 'switch_to_cash':
                    // Switch to cash payment method
                    $this->switchPaymentMethod($registration, 'tunai_mitra_pintar', 'Switched from KPR due to tidak_layak status');
                    break;
                    
                case 'switch_to_mitra_pintar':
                    // Switch to mitra pintar payment method
                    $this->switchPaymentMethod($registration, 'tunai_mitra_pintar', 'Switched from KPR due to tidak_layak status');
                    break;
            }
            
            $this->sendDecisionRecordedNotification($screening, $decision);
        });
    }

    /**
     * Check and handle expired registrations
     */
    public function checkAndHandleExpiredRegistrations(): void
    {
        $expiredRegistrations = NupRegistration::where('payment_status', 'paid')
            ->where('is_forfeited', false)
            ->where('unit_selection_deadline', '<', now())
            ->whereDoesntHave('unitSelections')
            ->get();
        
        foreach ($expiredRegistrations as $registration) {
            $this->handleForfeiture($registration, 'Unit selection deadline passed');
        }
    }

    /**
     * Check and handle KPR screening deadline passed
     */
    public function checkAndHandleScreeningDeadlines(): void
    {
        $expiredScreenings = KprScreening::whereIn('status', ['layak_proses', 'layak_bersyarat'])
            ->where('unit_selection_deadline', '<', now())
            ->whereNull('participant_decision')
            ->get();
        
        foreach ($expiredScreenings as $screening) {
            $registration = $screening->nupRegistration;
            $this->handleForfeiture($registration, 'Unit selection deadline passed after screening');
        }
    }

    /**
     * Log forfeiture
     */
    private function logForfeiture(NupRegistration $registration, string $reason): void
    {
        // Implementation can be added for logging
    }

    /**
     * Create refund transaction
     */
    private function createRefundTransaction(NupRegistration $registration, string $reason): void
    {
        PaymentTransaction::create([
            'user_id' => $registration->user_id,
            'nup_registration_id' => $registration->id,
            'transaction_number' => 'REF-' . now()->format('YmdHis') . '-' . $registration->id,
            'amount' => $registration->nup_fee,
            'type' => 'refund',
            'status' => 'pending',
            'description' => $reason,
        ]);
    }

    /**
     * Update refund transaction
     */
    private function updateRefundTransaction(NupRegistration $registration): void
    {
        $transaction = PaymentTransaction::where('nup_registration_id', $registration->id)
            ->where('type', 'refund')
            ->where('status', 'pending')
            ->latest()
            ->first();
        
        if ($transaction) {
            $transaction->update([
                'status' => 'success',
                'processed_at' => now(),
            ]);
        }
    }

    /**
     * Send forfeiture notification
     */
    private function sendForfeitureNotification(NupRegistration $registration): void
    {
        // Implementation using WhatsAppGatewayService
        try {
            $whatsappService = new WhatsAppGatewayService();
            $message = "NUP Anda telah hangus. Alasan: {$registration->forfeit_reason}. " .
                      "Nomor Registrasi: {$registration->registration_number}";
            $whatsappService->sendNotification($registration->user->phone, $message);
        } catch (\Exception $e) {
            // Log error but don't fail the transaction
        }
    }

    /**
     * Send refund request notification
     */
    private function sendRefundRequestNotification(NupRegistration $registration): void
    {
        try {
            $whatsappService = new WhatsAppGatewayService();
            $message = "Permintaan refund NUP Anda telah diterima. " .
                      "Nomor Registrasi: {$registration->registration_number}. " .
                      "Jumlah: Rp " . number_format($registration->nup_fee, 0, ',', '.');
            $whatsappService->sendNotification($registration->user->phone, $message);
        } catch (\Exception $e) {
            // Log error but don't fail the transaction
        }
    }

    /**
     * Send refund processed notification
     */
    private function sendRefundProcessedNotification(NupRegistration $registration): void
    {
        try {
            $whatsappService = new WhatsAppGatewayService();
            $message = "Refund NUP Anda telah diproses. " .
                      "Nomor Registrasi: {$registration->registration_number}. " .
                      "Jumlah: Rp " . number_format($registration->refund_amount, 0, ',', '.');
            $whatsappService->sendNotification($registration->user->phone, $message);
        } catch (\Exception $e) {
            // Log error but don't fail the transaction
        }
    }

    /**
     * Send payment method switch notification
     */
    private function sendPaymentMethodSwitchNotification(NupRegistration $registration, string $newMethod): void
    {
        try {
            $whatsappService = new WhatsAppGatewayService();
            $message = "Metode pembayaran NUP Anda telah diubah. " .
                      "Nomor Registrasi: {$registration->registration_number}. " .
                      "Metode baru: " . config('nup.payment_methods.' . $newMethod);
            $whatsappService->sendNotification($registration->user->phone, $message);
        } catch (\Exception $e) {
            // Log error but don't fail the transaction
        }
    }

    /**
     * Send layak_proses notification
     */
    private function sendLayakProsesNotification(KprScreening $screening): void
    {
        try {
            $whatsappService = new WhatsAppGatewayService();
            $registration = $screening->nupRegistration;
            $message = "Selamat! Screening KPR Anda dinyatakan LAYAK PROSES. " .
                      "Silakan pilih unit dalam 48 jam. " .
                      "Deadline: {$screening->unit_selection_deadline->format('d-m-Y H:i')}";
            $whatsappService->sendNotification($registration->user->phone, $message);
        } catch (\Exception $e) {
            // Log error but don't fail the transaction
        }
    }

    /**
     * Send layak_bersyarat notification
     */
    private function sendLayakBersyaratNotification(KprScreening $screening): void
    {
        try {
            $whatsappService = new WhatsAppGatewayService();
            $registration = $screening->nupRegistration;
            $message = "Screening KPR Anda dinyatakan LAYAK BERSYARAT. " .
                      "Anda dapat memilih unit dalam 48 jam atau request refund 100%. " .
                      "Deadline: {$screening->unit_selection_deadline->format('d-m-Y H:i')}";
            $whatsappService->sendNotification($registration->user->phone, $message);
        } catch (\Exception $e) {
            // Log error but don't fail the transaction
        }
    }

    /**
     * Send tidak_layak notification
     */
    private function sendTidakLayakNotification(KprScreening $screening): void
    {
        try {
            $whatsappService = new WhatsAppGatewayService();
            $registration = $screening->nupRegistration;
            $message = "Mohon maaf, screening KPR Anda dinyatakan TIDAK LAYAK. " .
                      "Anda dapat memilih: Refund 100%, Alih ke Tunai, atau Alih ke Mitra Pintar.";
            $whatsappService->sendNotification($registration->user->phone, $message);
        } catch (\Exception $e) {
            // Log error but don't fail the transaction
        }
    }

    /**
     * Send decision recorded notification
     */
    private function sendDecisionRecordedNotification(KprScreening $screening, string $decision): void
    {
        try {
            $whatsappService = new WhatsAppGatewayService();
            $registration = $screening->nupRegistration;
            $decisionText = config('nup.participant_decisions.' . $decision);
            $message = "Keputusan Anda telah dicatat: {$decisionText}. " .
                      "Nomor Registrasi: {$registration->registration_number}";
            $whatsappService->sendNotification($registration->user->phone, $message);
        } catch (\Exception $e) {
            // Log error but don't fail the transaction
        }
    }
}
