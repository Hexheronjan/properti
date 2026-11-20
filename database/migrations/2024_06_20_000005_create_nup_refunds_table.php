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
        Schema::create('nup_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nup_registration_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('refund_number')->unique();
            $table->decimal('refund_amount', 15, 2);
            $table->enum('refund_reason', ['layak_bersyarat', 'tidak_layak', 'expired', 'cancelled_by_user', 'admin_decision'])->default('cancelled_by_user');
            $table->text('refund_notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'processed'])->default('pending');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('rejected_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('payment_gateway_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nup_refunds');
    }
};
