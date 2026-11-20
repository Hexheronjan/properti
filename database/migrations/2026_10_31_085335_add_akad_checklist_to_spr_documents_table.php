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
        Schema::table('spr_documents', function (Blueprint $table) {
            $table->json('akad_checklist')->nullable()->after('akad_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spr_documents', function (Blueprint $table) {
            $table->dropColumn('akad_checklist');
        });
    }
};
