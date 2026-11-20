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
        Schema::table('kpr_screenings', function (Blueprint $table) {
            // Time limit for unit selection after screening approval
            $table->timestamp('unit_selection_deadline')->nullable()->after('approval_date');
            
            // Participant decision for layak bersyarat
            $table->enum('participant_decision', ['pending', 'continue', 'refund'])->nullable()->after('unit_selection_deadline');
            $table->timestamp('decision_made_at')->nullable()->after('participant_decision');
            $table->text('decision_notes')->nullable()->after('decision_made_at');
            
            // Notification sent timestamp
            $table->timestamp('notification_sent_at')->nullable()->after('decision_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpr_screenings', function (Blueprint $table) {
            $table->dropColumn([
                'unit_selection_deadline',
                'participant_decision',
                'decision_made_at',
                'decision_notes',
                'notification_sent_at',
            ]);
        });
    }
};
