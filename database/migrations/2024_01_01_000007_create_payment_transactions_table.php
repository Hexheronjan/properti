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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('nup_registration_id')->nullable()->constrained()->onDelete('set null');
            $table->string('transaction_number')->unique();
            $table->string('payment_gateway'); // midtrans, xendit, stripe, etc.
            $table->string('payment_method'); // va, credit_card, ewallet, etc.
            $table->string('payment_channel')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'cancelled', 'expired'])->default('pending');
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->text('gateway_request_payload')->nullable();
            $table->text('gateway_response_payload')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};