<?php
/**
 * Script Pembersih Cache Laravel via Browser (Tanpa Terminal / SSH)
 * Akses di browser: https://app.anugrahpropertindo.com/clear_cache.php
 */

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Proses Pembersihan Cache APRO...</h2>";

$baseDir = __DIR__ . '/..';

// 1. Coba via Laravel Artisan
$artisanSuccess = false;
try {
    if (file_exists($baseDir . '/vendor/autoload.php') && file_exists($baseDir . '/bootstrap/app.php')) {
        require $baseDir . '/vendor/autoload.php';
        $app = require_once $baseDir . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        $output = \Illuminate\Support\Facades\Artisan::output();
        echo "<pre style='background:#f4f4f4; padding:12px; border-radius:6px; color:#1e3a8a;'>" . htmlspecialchars($output) . "</pre>";
        echo "<p style='color:green; font-weight:bold;'>✓ Cache berhasil dibersihkan via Artisan!</p>";
        $artisanSuccess = true;
    }
} catch (\Throwable $e) {
    echo "<p style='color:orange;'>Info Artisan: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 2. Fallback manual jika Artisan tidak jalan
if (!$artisanSuccess) {
    $bootstrapCache = [
        $baseDir . '/bootstrap/cache/config.php',
        $baseDir . '/bootstrap/cache/routes-v7.php',
        $baseDir . '/bootstrap/cache/services.php',
        $baseDir . '/bootstrap/cache/packages.php',
    ];

    foreach ($bootstrapCache as $file) {
        if (file_exists($file)) {
            @unlink($file);
            echo "<p style='color:green;'>✓ File cache dihapus: " . basename($file) . "</p>";
        }
    }
    echo "<p style='color:green; font-weight:bold;'>✓ Pembersihan file cache bootstrap selesai!</p>";
}

echo "<hr><p style='color:#b91c1c; font-weight:bold;'>PENTING: Setelah selesai, hapus file <code>clear_cache.php</code> ini dari file manager cPanel demi keamanan.</p>";
