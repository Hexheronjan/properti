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
            // UTK (Uang Tanda Kunjungan) payment fields
            if (!Schema::hasColumn('unit_selections', 'utk_amount')) {
                $table->decimal('utk_amount', 15, 2)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('unit_selections', 'utk_payment_status')) {
                $table->enum('utk_payment_status', ['pending', 'paid', 'failed', 'expired'])->nullable()->after('utk_amount');
            }
            if (!Schema::hasColumn('unit_selections', 'utk_payment_date')) {
                $table->timestamp('utk_payment_date')->nullable()->after('utk_payment_status');
            }
            if (!Schema::hasColumn('unit_selections', 'utk_payment_reference')) {
                $table->string('utk_payment_reference')->nullable()->after('utk_payment_date');
            }
            if (!Schema::hasColumn('unit_selections', 'utk_payment_gateway_response')) {
                $table->text('utk_payment_gateway_response')->nullable()->after('utk_payment_reference');
            }
            if (!Schema::hasColumn('unit_selections', 'utk_payment_deadline')) {
                $table->timestamp('utk_payment_deadline')->nullable()->after('utk_payment_gateway_response');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_selections', function (Blueprint $table) {
            $columnsToDrop = [
                'utk_amount',
                'utk_payment_status',
                'utk_payment_date',
                'utk_payment_reference',
                'utk_payment_gateway_response',
                'utk_payment_deadline',
            ];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('unit_selections', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
