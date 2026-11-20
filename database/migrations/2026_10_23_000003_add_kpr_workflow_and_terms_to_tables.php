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
        // 1. Tambah field alur KPR, SP3K & Jadwal Akad di spr_documents
        Schema::table('spr_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('spr_documents', 'argo_deadline')) {
                $table->timestamp('argo_deadline')->nullable()->after('status');
            }
            if (!Schema::hasColumn('spr_documents', 'bank_submission_status')) {
                $table->string('bank_submission_status')->default('pending')->after('argo_deadline'); // pending, submitted_to_bank, sp3k_approved, sp3k_rejected
            }
            if (!Schema::hasColumn('spr_documents', 'sp3k_number')) {
                $table->string('sp3k_number')->nullable()->after('bank_submission_status');
            }
            if (!Schema::hasColumn('spr_documents', 'sp3k_file')) {
                $table->string('sp3k_file')->nullable()->after('sp3k_number');
            }
            if (!Schema::hasColumn('spr_documents', 'sp3k_approved_amount')) {
                $table->decimal('sp3k_approved_amount', 15, 2)->nullable()->after('sp3k_file');
            }
            if (!Schema::hasColumn('spr_documents', 'sp3k_notes')) {
                $table->text('sp3k_notes')->nullable()->after('sp3k_approved_amount');
            }
            // Jadwal Akad Kredit
            if (!Schema::hasColumn('spr_documents', 'akad_date')) {
                $table->dateTime('akad_date')->nullable()->after('sp3k_notes');
            }
            if (!Schema::hasColumn('spr_documents', 'akad_location')) {
                $table->string('akad_location')->nullable()->after('akad_date');
            }
            if (!Schema::hasColumn('spr_documents', 'akad_notary')) {
                $table->string('akad_notary')->nullable()->after('akad_location');
            }
            if (!Schema::hasColumn('spr_documents', 'akad_notes')) {
                $table->text('akad_notes')->nullable()->after('akad_notary');
            }
            if (!Schema::hasColumn('spr_documents', 'is_akad_completed')) {
                $table->boolean('is_akad_completed')->default(false)->after('akad_notes');
            }
            if (!Schema::hasColumn('spr_documents', 'bast_date')) {
                $table->dateTime('bast_date')->nullable()->after('is_akad_completed');
            }
            if (!Schema::hasColumn('spr_documents', 'bast_file')) {
                $table->string('bast_file')->nullable()->after('bast_date');
            }
        });

        // 2. Tambah aturan Tunai & In-House di housing_projects
        Schema::table('housing_projects', function (Blueprint $table) {
            if (!Schema::hasColumn('housing_projects', 'cash_due_days')) {
                $table->integer('cash_due_days')->default(30)->after('supported_payment_schemes');
            }
            if (!Schema::hasColumn('housing_projects', 'cash_penalty_percent')) {
                $table->decimal('cash_penalty_percent', 5, 2)->default(1.00)->after('cash_due_days');
            }
            if (!Schema::hasColumn('housing_projects', 'inhouse_max_tenor_months')) {
                $table->integer('inhouse_max_tenor_months')->default(24)->after('cash_penalty_percent');
            }
            if (!Schema::hasColumn('housing_projects', 'inhouse_interest_rate')) {
                $table->decimal('inhouse_interest_rate', 5, 2)->default(0.00)->after('inhouse_max_tenor_months');
            }
            if (!Schema::hasColumn('housing_projects', 'inhouse_due_day')) {
                $table->integer('inhouse_due_day')->default(10)->after('inhouse_interest_rate');
            }
            if (!Schema::hasColumn('housing_projects', 'inhouse_penalty_percent')) {
                $table->decimal('inhouse_penalty_percent', 5, 2)->default(1.00)->after('inhouse_due_day');
            }
        });

        // 3. Tambah field Pefindo di kpr_screenings
        Schema::table('kpr_screenings', function (Blueprint $table) {
            if (!Schema::hasColumn('kpr_screenings', 'pefindo_score')) {
                $table->string('pefindo_score')->nullable()->after('status');
            }
            if (!Schema::hasColumn('kpr_screenings', 'pefindo_grade')) {
                $table->string('pefindo_grade')->nullable()->after('pefindo_score'); // 1 - Sangat Baik, 2 - Baik, 3 - Cukup, dll
            }
            if (!Schema::hasColumn('kpr_screenings', 'pefindo_notes')) {
                $table->text('pefindo_notes')->nullable()->after('pefindo_grade');
            }
            if (!Schema::hasColumn('kpr_screenings', 'pefindo_file')) {
                $table->string('pefindo_file')->nullable()->after('pefindo_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpr_screenings', function (Blueprint $table) {
            $cols = ['pefindo_score', 'pefindo_grade', 'pefindo_notes', 'pefindo_file'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('kpr_screenings', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('housing_projects', function (Blueprint $table) {
            $cols = [
                'cash_due_days',
                'cash_penalty_percent',
                'inhouse_max_tenor_months',
                'inhouse_interest_rate',
                'inhouse_due_day',
                'inhouse_penalty_percent',
            ];
            foreach ($cols as $c) {
                if (Schema::hasColumn('housing_projects', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('spr_documents', function (Blueprint $table) {
            $cols = [
                'argo_deadline',
                'bank_submission_status',
                'sp3k_number',
                'sp3k_file',
                'sp3k_approved_amount',
                'sp3k_notes',
                'akad_date',
                'akad_location',
                'akad_notary',
                'akad_notes',
                'is_akad_completed',
                'bast_date',
                'bast_file',
            ];
            foreach ($cols as $c) {
                if (Schema::hasColumn('spr_documents', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
