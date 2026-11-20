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
        // 1. Create developer_companies table
        Schema::create('developer_companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('pic_name');
            $table->text('pic_address')->nullable();
            $table->string('pic_phone');
            $table->string('entity_type')->default('badan_usaha'); // badan_usaha, perorangan
            $table->string('business_type')->nullable(); // pt, cv, dll
            $table->string('company_name');
            $table->string('nib')->nullable();
            $table->text('address');
            $table->string('phone');
            $table->string('email');
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
        });

        // 2. Add company_id and approval_status to users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('users', 'approval_status')) {
                $table->string('approval_status')->default('active')->after('role'); // active, pending, rejected
            }
        });

        // 3. Add company_id to housing_projects
        Schema::table('housing_projects', function (Blueprint $table) {
            if (!Schema::hasColumn('housing_projects', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            }
        });

        // 4. Add company_id to housing_units
        Schema::table('housing_units', function (Blueprint $table) {
            if (!Schema::hasColumn('housing_units', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housing_units', function (Blueprint $table) {
            if (Schema::hasColumn('housing_units', 'company_id')) {
                $table->dropColumn('company_id');
            }
        });

        Schema::table('housing_projects', function (Blueprint $table) {
            if (Schema::hasColumn('housing_projects', 'company_id')) {
                $table->dropColumn('company_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'company_id')) {
                $table->dropColumn('company_id');
            }
            if (Schema::hasColumn('users', 'approval_status')) {
                $table->dropColumn('approval_status');
            }
        });

        Schema::dropIfExists('developer_companies');
    }
};
