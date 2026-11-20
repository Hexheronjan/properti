<?php

namespace App\Console\Commands;

use App\Models\PaymentTransaction;
use App\Models\UtkPayment;
use App\Services\PaymentGatewayService;
use Illuminate\Console\Command;

class SyncPendingDokuPayments extends Command
{
    protected $signature = 'doku:sync-pending';
    protected $description = 'Rekonsiliasi berkala transaksi DOKU yang masih berstatus pending dengan Order Status API DOKU';

    public function handle(PaymentGatewayService $paymentService): int
    {
        $this->info('Memulai sinkronisasi transaksi DOKU pending...');

        // 1. Sinkronisasi NUP Payment Transactions (5 menit s/d 48 jam yang lalu)
        $pendingNup = PaymentTransaction::where('payment_gateway', 'doku')
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(5))
            ->where('created_at', '>', now()->subDays(2))
            ->get();

        $nupSuccessCount = 0;
        foreach ($pendingNup as $tx) {
            $this->line("Memeriksa NUP TRX: {$tx->transaction_number}...");
            $isPaid = $paymentService->syncPaymentStatus($tx);
            if ($isPaid) {
                $nupSuccessCount++;
                $this->info("✓ NUP TRX {$tx->transaction_number} berhasil dinyatakan LUNAS.");
            }
        }

        // 2. Sinkronisasi UTK Payments (5 menit s/d 48 jam yang lalu)
        $pendingUtk = UtkPayment::where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(5))
            ->where('created_at', '>', now()->subDays(2))
            ->get();

        $utkSuccessCount = 0;
        foreach ($pendingUtk as $utk) {
            $this->line("Memeriksa UTK: {$utk->payment_number}...");
            $isPaid = $paymentService->syncUtkPaymentStatus($utk);
            if ($isPaid) {
                $utkSuccessCount++;
                $this->info("✓ UTK {$utk->payment_number} berhasil dinyatakan LUNAS.");
            }
        }

        $this->info("Selesai. {$nupSuccessCount} NUP dan {$utkSuccessCount} UTK diperbarui menjadi lunas.");
        return Command::SUCCESS;
    }
}
