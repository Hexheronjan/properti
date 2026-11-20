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
        Schema::create('spr_clauses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('housing_project_id')->nullable();
            $table->string('clause_number');
            $table->string('clause_title');
            $table->text('clause_content');
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['housing_project_id', 'is_active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spr_clauses');
    }
};
