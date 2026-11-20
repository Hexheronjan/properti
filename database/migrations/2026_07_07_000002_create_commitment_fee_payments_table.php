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
        Schema::create('commitment_fee_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('kpr_screening_id')->nullable();
            $table->unsignedBigInteger('housing_unit_id')->nullable();
            $table->unsignedBigInteger('housing_project_id')->nullable();
            $table->string('payment_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->string('payment_gateway')->default('midtrans');
            $table->string('payment_method');
            $table->enum('status', ['pending', 'success', 'failed', 'expired'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->json('payment_response')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['kpr_screening_id', 'status']);
            $table->index(['housing_unit_id', 'status']);
            $table->index('payment_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commitment_fee_payments');
    }
};
