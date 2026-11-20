<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support modifying columns, so we need to recreate the table
        // Get existing data
        $existingData = DB::table('kpr_screenings')->get();

        // Drop the table
        Schema::dropIfExists('kpr_screenings');

        // Recreate with updated schema
        Schema::create('kpr_screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('nup_registration_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_selection_id')->nullable()->constrained()->onDelete('set null');
            $table->string('screening_number')->unique();
            $table->enum('bank', ['bca', 'bni', 'bri', 'btn', 'mandiri', 'cimb', 'uob', 'maybank', 'other']);
            $table->string('bank_other')->nullable();
            $table->decimal('loan_amount', 15, 2);
            $table->integer('tenor_years');
            $table->decimal('monthly_income', 15, 2);
            $table->decimal('monthly_expense', 15, 2);
            $table->enum('employment_status', ['employee', 'entrepreneur', 'professional', 'retired', 'other']);
            $table->integer('employment_duration_years')->nullable();
            $table->string('employer_name')->nullable();
            $table->enum('status', ['pending', 'submitted', 'processing', 'approved', 'layak_bersyarat', 'rejected', 'requires_document'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submission_date')->nullable();
            $table->timestamp('approval_date')->nullable();
            $table->string('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Restore data if exists
        if ($existingData->isNotEmpty()) {
            foreach ($existingData as $data) {
                DB::table('kpr_screenings')->insert((array) $data);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get existing data
        $existingData = DB::table('kpr_screenings')->get();

        // Drop the table
        Schema::dropIfExists('kpr_screenings');

        // Recreate with original schema
        Schema::create('kpr_screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('nup_registration_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_selection_id')->nullable()->constrained()->onDelete('set null');
            $table->string('screening_number')->unique();
            $table->enum('bank', ['bca', 'bni', 'bri', 'btn', 'mandiri', 'cimb', 'uob', 'maybank', 'other']);
            $table->string('bank_other')->nullable();
            $table->decimal('loan_amount', 15, 2);
            $table->integer('tenor_years');
            $table->decimal('monthly_income', 15, 2);
            $table->decimal('monthly_expense', 15, 2);
            $table->enum('employment_status', ['employee', 'entrepreneur', 'professional', 'retired', 'other']);
            $table->integer('employment_duration_years')->nullable();
            $table->string('employer_name')->nullable();
            $table->enum('status', ['pending', 'submitted', 'processing', 'approved', 'rejected', 'requires_document'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submission_date')->nullable();
            $table->timestamp('approval_date')->nullable();
            $table->string('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Restore data if exists
        if ($existingData->isNotEmpty()) {
            foreach ($existingData as $data) {
                DB::table('kpr_screenings')->insert((array) $data);
            }
        }
    }
};
