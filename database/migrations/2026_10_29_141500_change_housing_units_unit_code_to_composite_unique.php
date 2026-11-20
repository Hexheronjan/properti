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
        Schema::table('housing_units', function (Blueprint $table) {
            // Drop global unique constraint on unit_code
            $table->dropUnique('housing_units_unit_code_unique');

            // Add composite unique constraint per project
            $table->unique(['project_id', 'unit_code'], 'housing_units_project_unit_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housing_units', function (Blueprint $table) {
            $table->dropUnique('housing_units_project_unit_code_unique');
            $table->unique('unit_code', 'housing_units_unit_code_unique');
        });
    }
};
