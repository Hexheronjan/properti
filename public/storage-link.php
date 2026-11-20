<?php
/**
 * Skrip pembantu untuk cPanel tanpa Terminal (SSH)
 * Jalankan sekali di browser: https://app.anugrahpropertindo.com/storage-link.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "<h2>Pemeriksaan & Konfigurasi cPanel</h2>";

try {
    // 1. Jalankan storage:link
    Artisan::call('storage:link');
    echo "<p style='color:green;'>✓ Storage link berhasil dibuat: " . nl2br(Artisan::output()) . "</p>";
} catch (\Throwable $e) {
    echo "<p style='color:orange;'>Info storage link: " . $e->getMessage() . "</p>";
}

try {
    // 2. Clear cache konfigurasi & view agar terbaca fresh
    Artisan::call('optimize:clear');
    echo "<p style='color:green;'>✓ Cache berhasil dibersihkan: " . nl2br(Artisan::output()) . "</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red;'>Gagal clear cache: " . $e->getMessage() . "</p>";
}

echo "<hr><p><b>Selesai!</b> Silakan hapus berkas <code>public/storage-link.php</code> ini setelah selesai demi keamanan.</p>";
