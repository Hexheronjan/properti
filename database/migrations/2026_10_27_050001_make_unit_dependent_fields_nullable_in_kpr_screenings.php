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
        // Use raw ALTER TABLE to safely modify columns to nullable in MySQL
        DB::statement("ALTER TABLE `kpr_screenings` 
            MODIFY COLUMN `bank` varchar(50) NULL,
            MODIFY COLUMN `loan_amount` decimal(15,2) NULL,
            MODIFY COLUMN `tenor_years` int NULL,
            MODIFY COLUMN `monthly_income` decimal(15,2) NULL,
            MODIFY COLUMN `monthly_expense` decimal(15,2) NULL,
            MODIFY COLUMN `employer_name` varchar(191) NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op or revert if necessary
    }
};
