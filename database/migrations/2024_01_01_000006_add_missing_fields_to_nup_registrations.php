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
        Schema::table('nup_registrations', function (Blueprint $table) {
            // Unit selection deadline (check if exists first)
            if (!Schema::hasColumn('nup_registrations', 'unit_selection_deadline')) {
                $table->timestamp('unit_selection_deadline')->nullable()->after('payment_gateway_response');
            }
            
            // Expiry and forfeiture
            if (!Schema::hasColumn('nup_registrations', 'is_expired')) {
                $table->boolean('is_expired')->default(false)->after('unit_selection_deadline');
            }
            if (!Schema::hasColumn('nup_registrations', 'is_forfeited')) {
                $table->boolean('is_forfeited')->default(false)->after('is_expired');
            }
            if (!Schema::hasColumn('nup_registrations', 'forfeited_at')) {
                $table->timestamp('forfeited_at')->nullable()->after('is_forfeited');
            }
            if (!Schema::hasColumn('nup_registrations', 'forfeit_reason')) {
                $table->text('forfeit_reason')->nullable()->after('forfeited_at');
            }
            
            // Refund fields
            if (!Schema::hasColumn('nup_registrations', 'refund_requested')) {
                $table->boolean('refund_requested')->default(false)->after('forfeit_reason');
            }
            if (!Schema::hasColumn('nup_registrations', 'refund_processed')) {
                $table->boolean('refund_processed')->default(false)->after('refund_requested');
            }
            if (!Schema::hasColumn('nup_registrations', 'refund_amount')) {
                $table->decimal('refund_amount', 15, 2)->nullable()->after('refund_processed');
            }
            if (!Schema::hasColumn('nup_registrations', 'refund_processed_at')) {
                $table->timestamp('refund_processed_at')->nullable()->after('refund_amount');
            }
            if (!Schema::hasColumn('nup_registrations', 'refund_reason')) {
                $table->text('refund_reason')->nullable()->after('refund_processed_at');
            }
            
            // Payment method switch
            if (!Schema::hasColumn('nup_registrations', 'original_payment_method')) {
                $table->string('original_payment_method')->nullable()->after('refund_reason');
            }
            if (!Schema::hasColumn('nup_registrations', 'payment_method_switched_at')) {
                $table->timestamp('payment_method_switched_at')->nullable()->after('original_payment_method');
            }
            if (!Schema::hasColumn('nup_registrations', 'payment_method_switch_reason')) {
                $table->text('payment_method_switch_reason')->nullable()->after('payment_method_switched_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nup_registrations', function (Blueprint $table) {
            $columnsToDrop = [
                'unit_selection_deadline',
                'is_expired',
                'is_forfeited',
                'forfeited_at',
                'forfeit_reason',
                'refund_requested',
                'refund_processed',
                'refund_amount',
                'refund_processed_at',
                'refund_reason',
                'original_payment_method',
                'payment_method_switched_at',
                'payment_method_switch_reason',
            ];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('nup_registrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
