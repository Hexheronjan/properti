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
        Schema::create('utk_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_selection_id')->constrained()->onDelete('cascade');
            $table->foreignId('nup_registration_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('payment_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['bank_transfer', 'echannel', 'qris', 'credit_card']);
            $table->enum('status', ['pending', 'paid', 'failed', 'expired', 'refunded'])->default('pending');
            $table->string('payment_gateway_reference')->nullable();
            $table->text('payment_gateway_response')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->string('snap_token')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utk_payments');
    }
};
