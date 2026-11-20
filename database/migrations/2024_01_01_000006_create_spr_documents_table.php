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
        Schema::create('spr_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('nup_registration_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_selection_id')->constrained()->onDelete('cascade');
            $table->foreignId('kpr_screening_id')->nullable()->constrained()->onDelete('set null');
            $table->string('spr_number')->unique();
            $table->date('spr_date');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('down_payment', 15, 2);
            $table->decimal('remaining_amount', 15, 2);
            $table->enum('payment_scheme', ['cash', 'kpr', 'installment']);
            $table->string('payment_schedule')->nullable();
            $table->enum('status', ['draft', 'issued', 'cancelled'])->default('draft');
            $table->text('terms_conditions')->nullable();
            $table->string('document_file')->nullable();
            $table->timestamp('issued_date')->nullable();
            $table->string('issued_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spr_documents');
    }
};