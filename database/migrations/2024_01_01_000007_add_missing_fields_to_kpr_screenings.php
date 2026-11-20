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
            // Update status enum to include flowchart statuses
            // Note: SQLite doesn't support enum changes directly, so we'll skip this for now
            // The enum values are handled in the application layer
            
            // Unit selection deadline (48 hours after approval)
            if (!Schema::hasColumn('kpr_screenings', 'unit_selection_deadline')) {
                $table->timestamp('unit_selection_deadline')->nullable()->after('notes');
            }
            
            // Participant decision fields
            if (!Schema::hasColumn('kpr_screenings', 'participant_decision')) {
                $table->enum('participant_decision', ['pending', 'select_unit', 'request_refund', 'switch_to_cash', 'switch_to_mitra_pintar'])->default('pending')->after('unit_selection_deadline');
            }
            if (!Schema::hasColumn('kpr_screenings', 'decision_made_at')) {
                $table->timestamp('decision_made_at')->nullable()->after('participant_decision');
            }
            if (!Schema::hasColumn('kpr_screenings', 'decision_notes')) {
                $table->text('decision_notes')->nullable()->after('decision_made_at');
            }
            
            // Notification tracking
            if (!Schema::hasColumn('kpr_screenings', 'notification_sent_at')) {
                $table->timestamp('notification_sent_at')->nullable()->after('decision_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpr_screenings', function (Blueprint $table) {
            // Revert status enum
            $table->enum('status', ['pending', 'submitted', 'processing', 'approved', 'rejected', 'requires_document'])->default('pending')->change();
            
            // Drop added columns
            $columnsToDrop = [
                'unit_selection_deadline',
                'participant_decision',
                'decision_made_at',
                'decision_notes',
                'notification_sent_at',
            ];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('kpr_screenings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
