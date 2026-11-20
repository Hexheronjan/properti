<?php

/**
 * Script manual untuk menjalankan migration tanpa terminal
 * Upload file ini ke root directory aplikasi Laravel
 * Buka di browser: https://app.anugrahpropertindo.com/migrate_manual.php
 * Setelah selesai, HAPUS file ini untuk keamanan!
 */

// Database configuration
$host = 'localhost';
$dbname = 'aplikasi_perumahan';
$username = 'aplikasi_perumahan'; // Ganti dengan username database cPanel Anda
$password = 'password_database'; // Ganti dengan password database cPanel Anda

// Update dengan credentials dari .env Anda
// Buka file .env di cPanel untuk mendapatkan DB_DATABASE, DB_USERNAME, DB_PASSWORD

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Run migration SQL
    $sql = "ALTER TABLE spr_documents MODIFY COLUMN status ENUM('draft', 'issued', 'cancelled', 'paid') DEFAULT 'draft'";
    
    $pdo->exec($sql);
    
    echo "<h2>✅ Migration Berhasil!</h2>";
    echo "<p>Status kolom 'status' di tabel 'spr_documents' telah diperbarui.</p>";
    echo "<p>Status sekarang: draft, issued, cancelled, paid</p>";
    echo "<p style='color: red; font-weight: bold; margin-top: 20px;'>PENTING: Hapus file migrate_manual.php setelah selesai!</p>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<p><strong>Periksa:</strong></p>";
    echo "<ul>";
    echo "<li>Username dan password database benar (lihat di file .env)</li>";
    echo "<li>Nama database benar (lihat di file .env)</li>";
    echo "</ul>";
}
