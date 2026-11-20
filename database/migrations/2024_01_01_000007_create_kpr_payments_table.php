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
        Schema::create('kpr_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('spr_document_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('payment_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->enum('payment_type', ['dp', 'installment', 'full_payment'])->default('installment');
            $table->integer('installment_number')->nullable()->comment('Urutan cicilan ke-');
            $table->integer('total_installments')->nullable()->comment('Total cicilan');
            $table->decimal('principal_amount', 15, 2)->nullable()->comment('Pokok cicilan');
            $table->decimal('interest_amount', 15, 2)->nullable()->comment('Bunga cicilan');
            $table->decimal('remaining_principal', 15, 2)->nullable()->comment('Sisa pokok setelah bayar');
            $table->enum('payment_method', ['midtrans', 'transfer', 'cash'])->default('midtrans');
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'cancelled'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->text('payment_response')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('due_date')->nullable()->comment('Jatuh tempo cicilan');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('spr_document_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpr_payments');
    }
};
