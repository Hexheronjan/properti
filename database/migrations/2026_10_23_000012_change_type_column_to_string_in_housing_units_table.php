<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE housing_units MODIFY COLUMN type VARCHAR(100) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE housing_units MODIFY COLUMN type ENUM('type_36', 'type_45', 'type_54', 'type_70', 'type_120') NOT NULL");
    }
};
