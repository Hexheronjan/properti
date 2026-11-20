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
            if (!Schema::hasColumn('housing_projects', 'nup_deadline_days')) {
                $table->integer('nup_deadline_days')->default(7)->after('commitment_deadline_hours');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housing_projects', function (Blueprint $table) {
            if (Schema::hasColumn('housing_projects', 'nup_deadline_days')) {
                $table->dropColumn('nup_deadline_days');
            }
        });
    }
};
