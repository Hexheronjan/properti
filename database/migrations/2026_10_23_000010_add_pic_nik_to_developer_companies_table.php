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
        Schema::table('developer_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('developer_companies', 'pic_nik')) {
                $table->string('pic_nik', 20)->nullable()->after('pic_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('developer_companies', function (Blueprint $table) {
            if (Schema::hasColumn('developer_companies', 'pic_nik')) {
                $table->dropColumn('pic_nik');
            }
        });
    }
};
