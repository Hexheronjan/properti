<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE kpr_payments MODIFY payment_method ENUM('midtrans','doku','transfer','cash') NOT NULL DEFAULT 'doku'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE kpr_payments MODIFY payment_method ENUM('midtrans','transfer','cash') NOT NULL DEFAULT 'midtrans'");
        }
    }
};
