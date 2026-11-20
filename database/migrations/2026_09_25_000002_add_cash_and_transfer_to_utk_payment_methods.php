<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE utk_payments MODIFY payment_method ENUM('cash','transfer','bank_transfer','echannel','qris','credit_card') NOT NULL");
        }
    }

    public function down(): void
    {
        // Keep both values accepted so existing payment rows remain valid.
    }
};
