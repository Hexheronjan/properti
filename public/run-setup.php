<?php

/**
 * Laravel Setup Helper untuk cPanel
 * Jalankan script ini lewat browser setelah mengubah file .env ke database MySQL Anda.
 * Format akses: https://domain-anda.com/run-setup.php?key=setup123
 */

// Pengaman sederhana agar tidak dijalankan sembarang orang
if (!isset($_GET['key']) || $_GET['key'] !== 'setup123') {
    die('Akses ditolak. Silakan gunakan parameter key yang benar. Contoh: run-setup.php?key=setup123');
}

// Load Composer Autoloader dan Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

use Illuminate\Support\Facades\Artisan;

// Inisialisasi kernel untuk bootstrap class Laravel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <title>APL NUP - Setup Helper</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; color: #1f2937; padding: 40px; }
        .card { background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 30px; max-width: 800px; margin: 0 auto; }
        h1 { color: #2563eb; margin-bottom: 20px; }
        .step { margin-bottom: 20px; padding: 15px; border-radius: 6px; background: #f9fafb; border-left: 4px solid #d1d5db; }
        .success { border-left-color: #10b981; background: #ecfdf5; }
        .error { border-left-color: #ef4444; background: #fef2f2; }
        pre { background: #1f2937; color: #f9fafb; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 14px; }
    </style>
</head>
<body>
    <div class='card'>
        <h1>🛠️ Laravel Setup Helper</h1>
        <p>Menjalankan konfigurasi awal database MySQL pada cPanel...</p>";

// 1. Jalankan Migrasi Database
echo "<div class='step'>";
try {
    echo "<strong>1. Menjalankan Migrasi Database...</strong><br>";
    Artisan::call('migrate', ['--force' => true]);
    echo "<span style='color: #10b981;'>✓ Selesai. Output:</span>";
    echo "<pre>" . (Artisan::output() ?: "Tidak ada tabel baru yang dimigrasi.") . "</pre>";
} catch (\Exception $e) {
    echo "<span style='color: #ef4444;'>✗ Gagal: " . $e->getMessage() . "</span>";
}
echo "</div>";

// 2. Jalankan Seeder
echo "<div class='step'>";
try {
    echo "<strong>2. Mengisi Data Awal (Database Seeding)...</strong><br>";
    Artisan::call('db:seed', ['--force' => true]);
    echo "<span style='color: #10b981;'>✓ Selesai. Output:</span>";
    echo "<pre>" . (Artisan::output() ?: "Data berhasil di-seed.") . "</pre>";
} catch (\Exception $e) {
    echo "<span style='color: #ef4444;'>✗ Gagal: " . $e->getMessage() . "</span>";
}
echo "</div>";

// 3. Buat Storage Link
echo "<div class='step'>";
try {
    echo "<strong>3. Membuat Symlink Folder Storage...</strong><br>";
    Artisan::call('storage:link');
    echo "<span style='color: #10b981;'>✓ Selesai. Output:</span>";
    echo "<pre>" . (Artisan::output() ?: "Link storage telah dibuat.") . "</pre>";
} catch (\Exception $e) {
    echo "<span style='color: #ef4444;'>✗ Catatan: " . $e->getMessage() . " (Kemungkinan link sudah ada)</span>";
}
echo "</div>";

// 4. Bersihkan Cache
echo "<div class='step'>";
try {
    echo "<strong>4. Membersihkan Cache Sistem...</strong><br>";
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    echo "<span style='color: #10b981;'>✓ Sukses membersihkan cache config, route, dan view.</span>";
} catch (\Exception $e) {
    echo "<span style='color: #ef4444;'>✗ Gagal: " . $e->getMessage() . "</span>";
}
echo "</div>";

echo "<hr style='border: 0; border-top: 1px solid #e5e7eb; margin: 30px 0;'>";
echo "<h3 style='color: #10b981;'>🎉 Proses setup selesai! Silakan hapus file <code>public/run-setup.php</code> ini dari File Manager demi alasan keamanan.</h3>";
echo "</div>
</body>
</html>";
