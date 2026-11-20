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
        Schema::create('user_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('kpr_screening_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('document_type', ['ktp', 'kk', 'npwp', 'salary_slip', 'bank_statement', 'employment_letter', 'marriage_certificate', 'other']);
            $table->string('document_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->integer('file_size');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('uploaded_at');
            $table->timestamp('verified_at')->nullable();
            $table->string('verified_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_documents');
    }
};