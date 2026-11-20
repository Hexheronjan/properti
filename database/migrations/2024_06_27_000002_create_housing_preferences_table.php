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
        Schema::create('housing_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nup_registration_id')->constrained()->onDelete('cascade');
            $table->string('housing_name')->nullable()->comment('Nama perumahan yang diminati');
            $table->string('broker_name')->nullable()->comment('Nama broker');
            $table->string('marketing_name')->nullable()->comment('Nama marketing');
            $table->string('marketing_contact')->nullable()->comment('Contact marketing');
            $table->enum('purchase_plan', ['tunai', 'kredit_mitra_pintar', 'kpr_bank_flpp'])->nullable()->comment('Rencana pembelian');
            $table->integer('priority_order')->default(1)->comment('Urutan prioritas pilihan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housing_preferences');
    }
};
