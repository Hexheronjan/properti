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
            if (!Schema::hasColumn('housing_projects', 'commitment_fee')) {
                $table->decimal('commitment_fee', 15, 2)->nullable()->default(5000000)->after('is_active');
            }
            if (!Schema::hasColumn('housing_projects', 'commitment_deadline_hours')) {
                $table->integer('commitment_deadline_hours')->default(48)->after('commitment_fee');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housing_projects', function (Blueprint $table) {
            if (Schema::hasColumn('housing_projects', 'commitment_deadline_hours')) {
                $table->dropColumn('commitment_deadline_hours');
            }
            if (Schema::hasColumn('housing_projects', 'commitment_fee')) {
                $table->dropColumn('commitment_fee');
            }
        });
    }
};
