<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. student_milestones performance indexes
        if (Schema::hasTable('student_milestones')) {
            Schema::table('student_milestones', function (Blueprint $table) {
                try {
                    $table->index(['thesis_project_id', 'status'], 'idx_sm_thesis_status');
                } catch (\Throwable $e) {}
                try {
                    $table->index(['status', 'submitted_at'], 'idx_sm_status_submitted');
                } catch (\Throwable $e) {}
            });
        }

        // 2. supervision_assignments performance index
        if (Schema::hasTable('supervision_assignments')) {
            Schema::table('supervision_assignments', function (Blueprint $table) {
                try {
                    $table->index(['supervisor_profile_id', 'status'], 'idx_sa_sup_status');
                } catch (\Throwable $e) {}
            });
        }

        // 3. message_read_states performance index
        if (Schema::hasTable('message_read_states')) {
            Schema::table('message_read_states', function (Blueprint $table) {
                try {
                    $table->index(['user_id', 'read_at'], 'idx_mrs_user_read');
                } catch (\Throwable $e) {}
            });
        }

        // 4. inbox_message_recipients performance index
        if (Schema::hasTable('inbox_message_recipients')) {
            Schema::table('inbox_message_recipients', function (Blueprint $table) {
                try {
                    $table->index(['user_id', 'is_archived', 'read_at'], 'idx_imr_user_archived_read');
                } catch (\Throwable $e) {}
            });
        }

        // 5. defence_events performance index
        if (Schema::hasTable('defence_events')) {
            Schema::table('defence_events', function (Blueprint $table) {
                try {
                    $table->index(['schedule_start', 'type'], 'idx_de_sched_type');
                } catch (\Throwable $e) {}
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('student_milestones')) {
            Schema::table('student_milestones', function (Blueprint $table) {
                try { $table->dropIndex('idx_sm_thesis_status'); } catch (\Throwable $e) {}
                try { $table->dropIndex('idx_sm_status_submitted'); } catch (\Throwable $e) {}
            });
        }

        if (Schema::hasTable('supervision_assignments')) {
            Schema::table('supervision_assignments', function (Blueprint $table) {
                try { $table->dropIndex('idx_sa_sup_status'); } catch (\Throwable $e) {}
            });
        }

        if (Schema::hasTable('message_read_states')) {
            Schema::table('message_read_states', function (Blueprint $table) {
                try { $table->dropIndex('idx_mrs_user_read'); } catch (\Throwable $e) {}
            });
        }

        if (Schema::hasTable('inbox_message_recipients')) {
            Schema::table('inbox_message_recipients', function (Blueprint $table) {
                try { $table->dropIndex('idx_imr_user_archived_read'); } catch (\Throwable $e) {}
            });
        }

        if (Schema::hasTable('defence_events')) {
            Schema::table('defence_events', function (Blueprint $table) {
                try { $table->dropIndex('idx_de_sched_type'); } catch (\Throwable $e) {}
            });
        }
    }
};
