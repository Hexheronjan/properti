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

            // Agent/Marketing
            if (!Schema::hasColumn('nup_registrations', 'agent_name')) {
                $table->string('agent_name')->nullable()->after('domicile_address');
            }

            // Contact Agent/Marketing
            if (!Schema::hasColumn('nup_registrations', 'agent_contact')) {
                $table->string('agent_contact')->nullable()->after('agent_name');
            }

            // Status Pernikahan
            if (!Schema::hasColumn('nup_registrations', 'marital_status')) {
                $table->enum('marital_status', ['menikah', 'belum_menikah', 'cerai_hidup', 'cerai_meninggal'])->nullable()->after('agent_contact');
            }

            // Penghasilan/Bulan
            if (!Schema::hasColumn('nup_registrations', 'monthly_income')) {
                $table->decimal('monthly_income', 15, 2)->nullable()->after('marital_status');
            }

            // Angsuran/Bulan Aktif
            if (!Schema::hasColumn('nup_registrations', 'active_installment')) {
                $table->decimal('active_installment', 15, 2)->nullable()->after('monthly_income');
            }

            // Rencana Perumahan
            if (!Schema::hasColumn('nup_registrations', 'housing_plan')) {
                $table->enum('housing_plan', ['sinar_mulia_residence', 'banjar_lagoon_village'])->nullable()->after('active_installment');
            }

            // Upload KTP
            if (!Schema::hasColumn('nup_registrations', 'ktp_file')) {
                $table->string('ktp_file')->nullable()->after('housing_plan');
            }

            // Upload Kartu Keluarga
            if (!Schema::hasColumn('nup_registrations', 'kk_file')) {
                $table->string('kk_file')->nullable()->after('ktp_file');
            }

            // Pernyataan (agreement)
            if (!Schema::hasColumn('nup_registrations', 'agreement_accepted')) {
                $table->boolean('agreement_accepted')->default(false)->after('kk_file');
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
                'agent_name',
                'agent_contact',
                'marital_status',
                'monthly_income',
                'active_installment',
                'housing_plan',
                'ktp_file',
                'kk_file',
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
