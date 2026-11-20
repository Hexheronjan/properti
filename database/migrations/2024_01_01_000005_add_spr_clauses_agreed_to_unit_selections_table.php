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
        Schema::table('unit_selections', function (Blueprint $table) {
            $table->boolean('spr_clauses_agreed')->default(false)->after('notes');
            $table->timestamp('spr_clauses_agreed_at')->nullable()->after('spr_clauses_agreed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_selections', function (Blueprint $table) {
            $table->dropColumn(['spr_clauses_agreed', 'spr_clauses_agreed_at']);
        });
    }
};
