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
            $table->string('occupation_other', 150)->nullable()->after('occupation');
            $table->string('sk_karyawan_file')->nullable()->after('npwp_file');
            $table->string('salary_slip_file')->nullable()->after('sk_karyawan_file');
            $table->string('bank_statement_file')->nullable()->after('salary_slip_file');
            $table->string('sku_file')->nullable()->after('bank_statement_file');
            $table->string('financial_statement_file')->nullable()->after('sku_file');
            $table->string('additional_income_file')->nullable()->after('financial_statement_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nup_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'occupation_other',
                'sk_karyawan_file',
                'salary_slip_file',
                'bank_statement_file',
                'sku_file',
                'financial_statement_file',
                'additional_income_file',
            ]);
        });
    }
};
