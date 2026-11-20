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
        Schema::table('unit_selections', function (Blueprint $table) {
            if (!Schema::hasColumn('unit_selections', 'payment_scheme')) {
                $table->string('payment_scheme', 50)->nullable()->after('housing_unit_id');
            }
            if (!Schema::hasColumn('unit_selections', 'utk_base_amount')) {
                $table->decimal('utk_base_amount', 15, 2)->nullable()->after('utk_amount');
            }
            if (!Schema::hasColumn('unit_selections', 'utk_ppn_amount')) {
                $table->decimal('utk_ppn_amount', 15, 2)->nullable()->after('utk_base_amount');
            }
            if (!Schema::hasColumn('unit_selections', 'utk_ve_amount')) {
                $table->decimal('utk_ve_amount', 15, 2)->nullable()->after('utk_ppn_amount');
            }
        });

        Schema::table('utk_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('utk_payments', 'utk_base_amount')) {
                $table->decimal('utk_base_amount', 15, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('utk_payments', 'utk_ppn_amount')) {
                $table->decimal('utk_ppn_amount', 15, 2)->nullable()->after('utk_base_amount');
            }
            if (!Schema::hasColumn('utk_payments', 'utk_ve_amount')) {
                $table->decimal('utk_ve_amount', 15, 2)->nullable()->after('utk_ppn_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_selections', function (Blueprint $table) {
            $table->dropColumn(['payment_scheme', 'utk_base_amount', 'utk_ppn_amount', 'utk_ve_amount']);
        });

        Schema::table('utk_payments', function (Blueprint $table) {
            $table->dropColumn(['utk_base_amount', 'utk_ppn_amount', 'utk_ve_amount']);
        });
    }
};
