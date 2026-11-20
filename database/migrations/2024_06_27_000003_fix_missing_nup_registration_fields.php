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
            // Alamat Domisili (jika berbeda dengan KTP)
            if (!Schema::hasColumn('nup_registrations', 'domicile_address')) {
                $table->text('domicile_address')->nullable()->after('payment_gateway_response');
            }

            // Status Pernikahan
            if (!Schema::hasColumn('nup_registrations', 'marital_status')) {
                $table->enum('marital_status', ['menikah', 'belum_menikah', 'cerai_hidup', 'cerai_meninggal'])->nullable();
            }

            // Penghasilan/Bulan
            if (!Schema::hasColumn('nup_registrations', 'monthly_income')) {
                $table->decimal('monthly_income', 15, 2)->nullable();
            }

            // Angsuran/Bulan Aktif
            if (!Schema::hasColumn('nup_registrations', 'active_installment')) {
                $table->decimal('active_installment', 15, 2)->nullable();
            }

            // Rencana Perumahan
            if (!Schema::hasColumn('nup_registrations', 'housing_plan')) {
                $table->enum('housing_plan', ['sinar_mulia_residence', 'banjar_lagoon_village'])->nullable();
            }

            // Upload KTP
            if (!Schema::hasColumn('nup_registrations', 'ktp_file')) {
                $table->string('ktp_file')->nullable();
            }

            // Upload Kartu Keluarga
            if (!Schema::hasColumn('nup_registrations', 'kk_file')) {
                $table->string('kk_file')->nullable();
            }

            // Upload NPWP
            if (!Schema::hasColumn('nup_registrations', 'npwp_file')) {
                $table->string('npwp_file')->nullable();
            }

            // Upload KTP Pasangan
            if (!Schema::hasColumn('nup_registrations', 'spouse_ktp_file')) {
                $table->string('spouse_ktp_file')->nullable();
            }

            // Upload KK Pasangan
            if (!Schema::hasColumn('nup_registrations', 'spouse_kk_file')) {
                $table->string('spouse_kk_file')->nullable();
            }

            // Upload Surat Nikah
            if (!Schema::hasColumn('nup_registrations', 'marriage_certificate_file')) {
                $table->string('marriage_certificate_file')->nullable();
            }

            // Upload Surat Cerai/Kematian
            if (!Schema::hasColumn('nup_registrations', 'divorce_death_certificate_file')) {
                $table->string('divorce_death_certificate_file')->nullable();
            }

            // Pernyataan (agreement)
            if (!Schema::hasColumn('nup_registrations', 'agreement_accepted')) {
                $table->boolean('agreement_accepted')->default(false);
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
                'domicile_address',
                'marital_status',
                'monthly_income',
                'active_installment',
                'housing_plan',
                'ktp_file',
                'kk_file',
                'npwp_file',
                'spouse_ktp_file',
                'spouse_kk_file',
                'marriage_certificate_file',
                'divorce_death_certificate_file',
                'agreement_accepted',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('nup_registrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
