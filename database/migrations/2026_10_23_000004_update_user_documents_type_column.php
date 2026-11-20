<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah document_type menjadi VARCHAR agar fleksibel untuk dokumen Usaha & Karyawan
        DB::statement("ALTER TABLE user_documents MODIFY COLUMN document_type VARCHAR(100) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE user_documents MODIFY COLUMN document_type ENUM('ktp','kk','npwp','salary_slip','bank_statement','employment_letter','marriage_certificate','other') NOT NULL");
    }
};
