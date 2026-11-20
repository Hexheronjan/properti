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
            // Alamat Terstruktur
            if (!Schema::hasColumn('housing_projects', 'province')) {
                $table->string('province')->nullable()->after('location');
            }
            if (!Schema::hasColumn('housing_projects', 'city')) {
                $table->string('city')->nullable()->after('province');
            }
            if (!Schema::hasColumn('housing_projects', 'district')) {
                $table->string('district')->nullable()->after('city');
            }
            if (!Schema::hasColumn('housing_projects', 'village')) {
                $table->string('village')->nullable()->after('district');
            }
            if (!Schema::hasColumn('housing_projects', 'postal_code')) {
                $table->string('postal_code', 10)->nullable()->after('village');
            }
            if (!Schema::hasColumn('housing_projects', 'full_address')) {
                $table->text('full_address')->nullable()->after('postal_code');
            }

            // Site Plan & Masterplan
            if (!Schema::hasColumn('housing_projects', 'site_plan')) {
                $table->string('site_plan')->nullable()->after('image');
            }

            // Perizinan: KKPR / PKKPR & Dokumen Lain
            if (!Schema::hasColumn('housing_projects', 'kkpr_number')) {
                $table->string('kkpr_number')->nullable()->after('site_plan');
            }
            if (!Schema::hasColumn('housing_projects', 'doc_kkpr')) {
                $table->string('doc_kkpr')->nullable()->after('kkpr_number');
            }
            if (!Schema::hasColumn('housing_projects', 'doc_permit_other')) {
                $table->string('doc_permit_other')->nullable()->after('doc_kkpr');
            }

            // Skema Pembayaran yang Didukung (Checklist)
            if (!Schema::hasColumn('housing_projects', 'supported_payment_schemes')) {
                $table->json('supported_payment_schemes')->nullable()->after('doc_permit_other');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housing_projects', function (Blueprint $table) {
            $columns = [
                'province',
                'city',
                'district',
                'village',
                'postal_code',
                'full_address',
                'site_plan',
                'kkpr_number',
                'doc_kkpr',
                'doc_permit_other',
                'supported_payment_schemes',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('housing_projects', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
