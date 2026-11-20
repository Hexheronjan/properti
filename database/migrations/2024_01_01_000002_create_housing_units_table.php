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
        Schema::create('housing_units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_code')->unique();
            $table->string('block');
            $table->string('number');
            $table->enum('type', ['type_36', 'type_45', 'type_54', 'type_70', 'type_120']);
            $table->decimal('price', 15, 2);
            $table->integer('building_area');
            $table->integer('land_area');
            $table->integer('bedrooms')->default(2);
            $table->integer('bathrooms')->default(1);
            $table->text('description')->nullable();
            $table->enum('status', ['available', 'booked', 'sold', 'unavailable'])->default('available');
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housing_units');
    }
};