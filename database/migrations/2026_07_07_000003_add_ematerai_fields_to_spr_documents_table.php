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
            $table->string('ematerai_status')->default('pending')->after('issued_by');
            $table->string('ematerai_id')->nullable()->after('ematerai_status');
            $table->string('ematerai_url')->nullable()->after('ematerai_id');
            $table->string('ematerai_qr_code')->nullable()->after('ematerai_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spr_documents', function (Blueprint $table) {
            $table->dropColumn(['ematerai_status', 'ematerai_id', 'ematerai_url', 'ematerai_qr_code']);
        });
    }
};
