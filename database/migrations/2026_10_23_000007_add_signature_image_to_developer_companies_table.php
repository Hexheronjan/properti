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
            $table->string('signature_image')->nullable()->after('logo');
            $table->string('company_initials', 20)->nullable()->after('company_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('developer_companies', function (Blueprint $table) {
            $table->dropColumn(['signature_image', 'company_initials']);
        });
    }
};
