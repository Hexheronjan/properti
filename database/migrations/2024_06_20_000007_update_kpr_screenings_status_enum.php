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
        Schema::table('kpr_screenings', function (Blueprint $table) {
            $table->enum('status', ['pending', 'submitted', 'processing', 'approved', 'layak_bersyarat', 'rejected', 'requires_document'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpr_screenings', function (Blueprint $table) {
            $table->enum('status', ['pending', 'submitted', 'processing', 'approved', 'rejected', 'requires_document'])->default('pending')->change();
        });
    }
};
