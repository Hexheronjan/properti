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
        if (Schema::hasTable('spr_documents')) {
            Schema::table('spr_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('spr_documents', 'due_date')) {
                    $table->date('due_date')->nullable()->after('payment_schedule')->comment('Jatuh tempo pembayaran');
                }
                if (!Schema::hasColumn('spr_documents', 'tenor_months')) {
                    $table->integer('tenor_months')->nullable()->after('due_date')->comment('Tenor cicilan inhouse dalam bulan');
                }
                if (!Schema::hasColumn('spr_documents', 'penalty_percent')) {
                    $table->decimal('penalty_percent', 5, 2)->nullable()->after('tenor_months')->comment('Persentase denda per bulan');
                }
            });
        }

        if (Schema::hasTable('kpr_payments')) {
            Schema::table('kpr_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('kpr_payments', 'penalty_amount')) {
                    $table->decimal('penalty_amount', 15, 2)->default(0)->after('interest_amount')->comment('Nominal denda keterlambatan');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('spr_documents')) {
            Schema::table('spr_documents', function (Blueprint $table) {
                if (Schema::hasColumn('spr_documents', 'penalty_percent')) {
                    $table->dropColumn('penalty_percent');
                }
                if (Schema::hasColumn('spr_documents', 'tenor_months')) {
                    $table->dropColumn('tenor_months');
                }
                if (Schema::hasColumn('spr_documents', 'due_date')) {
                    $table->dropColumn('due_date');
                }
            });
        }

        if (Schema::hasTable('kpr_payments')) {
            Schema::table('kpr_payments', function (Blueprint $table) {
                if (Schema::hasColumn('kpr_payments', 'penalty_amount')) {
                    $table->dropColumn('penalty_amount');
                }
            });
        }
    }
};
