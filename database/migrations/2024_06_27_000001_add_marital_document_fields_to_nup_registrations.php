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
            // NPWP (untuk semua status pernikahan)
            if (!Schema::hasColumn('nup_registrations', 'npwp_file')) {
                $table->string('npwp_file')->nullable()->after('kk_file');
            }

            // KTP Pasangan (untuk status menikah)
            if (!Schema::hasColumn('nup_registrations', 'spouse_ktp_file')) {
                $table->string('spouse_ktp_file')->nullable()->after('npwp_file');
            }

            // KK Pasangan (opsional untuk status menikah jika KK berbeda)
            if (!Schema::hasColumn('nup_registrations', 'spouse_kk_file')) {
                $table->string('spouse_kk_file')->nullable()->after('spouse_ktp_file');
            }

            // Surat Nikah (untuk status menikah)
            if (!Schema::hasColumn('nup_registrations', 'marriage_certificate_file')) {
                $table->string('marriage_certificate_file')->nullable()->after('spouse_kk_file');
            }

            // Surat Cerai/Kematian Pasangan (untuk status janda/duda)
            if (!Schema::hasColumn('nup_registrations', 'divorce_death_certificate_file')) {
                $table->string('divorce_death_certificate_file')->nullable()->after('marriage_certificate_file');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nup_registrations', function (Blueprint $table) {
            $columnsToDrop = [
                'npwp_file',
                'spouse_ktp_file',
                'spouse_kk_file',
                'marriage_certificate_file',
                'divorce_death_certificate_file',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('nup_registrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
