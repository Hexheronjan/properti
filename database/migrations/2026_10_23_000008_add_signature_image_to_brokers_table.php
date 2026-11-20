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
        Schema::table('brokers', function (Blueprint $table) {
            $table->string('signature_image')->nullable()->after('address');
        });

        Schema::table('nup_registrations', function (Blueprint $table) {
            $table->foreignId('broker_id')->nullable()->after('agent_contact')->constrained('brokers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nup_registrations', function (Blueprint $table) {
            $table->dropForeign(['broker_id']);
            $table->dropColumn('broker_id');
        });

        Schema::table('brokers', function (Blueprint $table) {
            $table->dropColumn('signature_image');
        });
    }
};
