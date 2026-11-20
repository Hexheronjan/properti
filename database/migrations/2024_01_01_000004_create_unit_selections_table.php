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
        Schema::create('unit_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nup_registration_id')->constrained()->onDelete('cascade');
            $table->foreignId('housing_unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('selection_order');
            $table->timestamp('selection_date');
            $table->enum('status', ['selected', 'cancelled', 'confirmed'])->default('selected');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_selections');
    }
};