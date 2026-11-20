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
        // 1. Tabel Rekening Bank Milik Developer / Perusahaan Pengembang
        Schema::create('developer_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('developer_company_id')->constrained('developer_companies')->onDelete('cascade');
            $table->string('bank_name'); // BCA, Mandiri, BRI, BNI, BSI, dll
            $table->string('account_number');
            $table->string('account_holder_name'); // a.n. PT / Pengembang
            $table->string('branch')->nullable();
            $table->string('qr_code_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Tambah kolom Bukti Transfer ke tabel utk_payments
        Schema::table('utk_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('utk_payments', 'developer_bank_account_id')) {
                $table->foreignId('developer_bank_account_id')->nullable()->after('payment_method')->constrained('developer_bank_accounts')->onDelete('set null');
            }
            if (!Schema::hasColumn('utk_payments', 'proof_of_transfer')) {
                $table->string('proof_of_transfer')->nullable()->after('status');
            }
            if (!Schema::hasColumn('utk_payments', 'sender_bank')) {
                $table->string('sender_bank')->nullable()->after('proof_of_transfer');
            }
            if (!Schema::hasColumn('utk_payments', 'sender_name')) {
                $table->string('sender_name')->nullable()->after('sender_bank');
            }
            if (!Schema::hasColumn('utk_payments', 'sender_account_number')) {
                $table->string('sender_account_number')->nullable()->after('sender_name');
            }
            if (!Schema::hasColumn('utk_payments', 'transfer_date')) {
                $table->date('transfer_date')->nullable()->after('sender_account_number');
            }
        });

        // 3. Tambah kolom Bukti Transfer ke tabel kpr_payments (cicilan termin / pelunasan)
        Schema::table('kpr_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('kpr_payments', 'developer_bank_account_id')) {
                $table->foreignId('developer_bank_account_id')->nullable()->after('payment_method')->constrained('developer_bank_accounts')->onDelete('set null');
            }
            if (!Schema::hasColumn('kpr_payments', 'proof_of_transfer')) {
                $table->string('proof_of_transfer')->nullable()->after('status');
            }
            if (!Schema::hasColumn('kpr_payments', 'sender_bank')) {
                $table->string('sender_bank')->nullable()->after('proof_of_transfer');
            }
            if (!Schema::hasColumn('kpr_payments', 'sender_name')) {
                $table->string('sender_name')->nullable()->after('sender_bank');
            }
            if (!Schema::hasColumn('kpr_payments', 'sender_account_number')) {
                $table->string('sender_account_number')->nullable()->after('sender_name');
            }
            if (!Schema::hasColumn('kpr_payments', 'transfer_date')) {
                $table->date('transfer_date')->nullable()->after('sender_account_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpr_payments', function (Blueprint $table) {
            $table->dropForeign(['developer_bank_account_id']);
            $table->dropColumn([
                'developer_bank_account_id',
                'proof_of_transfer',
                'sender_bank',
                'sender_name',
                'sender_account_number',
                'transfer_date',
            ]);
        });

        Schema::table('utk_payments', function (Blueprint $table) {
            $table->dropForeign(['developer_bank_account_id']);
            $table->dropColumn([
                'developer_bank_account_id',
                'proof_of_transfer',
                'sender_bank',
                'sender_name',
                'sender_account_number',
                'transfer_date',
            ]);
        });

        Schema::dropIfExists('developer_bank_accounts');
    }
};
