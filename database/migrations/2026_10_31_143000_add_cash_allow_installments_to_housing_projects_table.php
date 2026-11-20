<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('housing_projects', function (Blueprint $table) {
            if (!Schema::hasColumn('housing_projects', 'cash_allow_installments')) {
                $table->boolean('cash_allow_installments')->default(true)->after('cash_penalty_percent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housing_projects', function (Blueprint $table) {
            if (Schema::hasColumn('housing_projects', 'cash_allow_installments')) {
                $table->dropColumn('cash_allow_installments');
            }
        });
    }
};
