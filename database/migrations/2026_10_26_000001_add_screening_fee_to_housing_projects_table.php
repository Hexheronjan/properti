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
        Schema::table('housing_projects', function (Blueprint $table) {
            if (!Schema::hasColumn('housing_projects', 'screening_fee')) {
                $table->decimal('screening_fee', 15, 2)->nullable()->default(150000)->after('commitment_deadline_hours');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housing_projects', function (Blueprint $table) {
            if (Schema::hasColumn('housing_projects', 'screening_fee')) {
                $table->dropColumn('screening_fee');
            }
        });
    }
};
