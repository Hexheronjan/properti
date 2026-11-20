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
        Schema::table('nup_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('nup_registrations', 'full_name')) {
                $table->string('full_name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('nup_registrations', 'nik')) {
                $table->string('nik', 30)->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('nup_registrations', 'phone')) {
                $table->string('phone', 30)->nullable()->after('nik');
            }
            if (!Schema::hasColumn('nup_registrations', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('nup_registrations', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('address');
            }
            if (!Schema::hasColumn('nup_registrations', 'occupation')) {
                $table->string('occupation', 100)->nullable()->after('birth_date');
            }
        });

        Schema::table('spr_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('spr_documents', 'buyer_name')) {
                $table->string('buyer_name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('spr_documents', 'buyer_nik')) {
                $table->string('buyer_nik', 30)->nullable()->after('buyer_name');
            }
            if (!Schema::hasColumn('spr_documents', 'buyer_phone')) {
                $table->string('buyer_phone', 30)->nullable()->after('buyer_nik');
            }
            if (!Schema::hasColumn('spr_documents', 'buyer_address')) {
                $table->text('buyer_address')->nullable()->after('buyer_phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nup_registrations', function (Blueprint $table) {
            $cols = ['occupation', 'birth_date', 'address', 'phone', 'nik', 'full_name'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('nup_registrations', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('spr_documents', function (Blueprint $table) {
            $cols = ['buyer_address', 'buyer_phone', 'buyer_nik', 'buyer_name'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('spr_documents', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
