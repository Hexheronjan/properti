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
        // Ubah enum payment_scheme menjadi varchar(50) agar mendukung 'cash', 'tunai_bertahap', 'inhouse', 'installment', 'kpr', 'mitra_pintar'
        DB::statement("ALTER TABLE spr_documents MODIFY COLUMN payment_scheme VARCHAR(50) NOT NULL DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE spr_documents MODIFY COLUMN payment_scheme ENUM('cash', 'kpr', 'installment') NOT NULL");
    }
};
