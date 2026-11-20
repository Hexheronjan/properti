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
            $table->string('mitra_institution_name')->nullable()->after('payment_scheme');
            $table->string('mitra_document')->nullable()->after('mitra_institution_name');
            $table->string('mitra_status', 30)->nullable()->after('mitra_document'); // pending, approved, rejected
            $table->text('mitra_rejection_reason')->nullable()->after('mitra_status');
            $table->timestamp('mitra_verified_at')->nullable()->after('mitra_rejection_reason');
            $table->foreignId('mitra_verified_by')->nullable()->constrained('users')->nullOnDelete()->after('mitra_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_selections', function (Blueprint $table) {
            $table->dropForeign(['mitra_verified_by']);
            $table->dropColumn([
                'mitra_institution_name',
                'mitra_document',
                'mitra_status',
                'mitra_rejection_reason',
                'mitra_verified_at',
                'mitra_verified_by',
            ]);
        });
    }
};
