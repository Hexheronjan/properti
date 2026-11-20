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
        Schema::table('kpr_screenings', function (Blueprint $table) {
            if (!Schema::hasColumn('kpr_screenings', 'screening_fee_amount')) {
                $table->decimal('screening_fee_amount', 15, 2)->nullable()->default(0)->after('employer_name');
            }
            if (!Schema::hasColumn('kpr_screenings', 'screening_fee_status')) {
                $table->string('screening_fee_status', 30)->default('unpaid')->after('screening_fee_amount');
            }
            if (!Schema::hasColumn('kpr_screenings', 'screening_fee_proof')) {
                $table->string('screening_fee_proof')->nullable()->after('screening_fee_status');
            }
            if (!Schema::hasColumn('kpr_screenings', 'screening_fee_paid_at')) {
                $table->timestamp('screening_fee_paid_at')->nullable()->after('screening_fee_proof');
            }
            if (!Schema::hasColumn('kpr_screenings', 'screening_fee_verified_at')) {
                $table->timestamp('screening_fee_verified_at')->nullable()->after('screening_fee_paid_at');
            }
            if (!Schema::hasColumn('kpr_screenings', 'screening_fee_verified_by')) {
                $table->string('screening_fee_verified_by')->nullable()->after('screening_fee_verified_at');
            }
            if (!Schema::hasColumn('kpr_screenings', 'screening_fee_notes')) {
                $table->text('screening_fee_notes')->nullable()->after('screening_fee_verified_by');
            }
            if (!Schema::hasColumn('kpr_screenings', 'developer_bank_account_id')) {
                $table->foreignId('developer_bank_account_id')->nullable()->after('screening_fee_notes')->constrained('developer_bank_accounts')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpr_screenings', function (Blueprint $table) {
            $cols = [
                'developer_bank_account_id',
                'screening_fee_notes',
                'screening_fee_verified_by',
                'screening_fee_verified_at',
                'screening_fee_paid_at',
                'screening_fee_proof',
                'screening_fee_status',
                'screening_fee_amount',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('kpr_screenings', $col)) {
                    if ($col === 'developer_bank_account_id') {
                        $table->dropForeign(['developer_bank_account_id']);
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};
