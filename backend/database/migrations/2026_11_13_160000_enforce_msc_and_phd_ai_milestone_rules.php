<?php

use App\Models\ThesisProject;
use App\Services\MilestoneWorkflowService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Enforce PhD AI (no seminar_as_a_course) and MSc (no progress_report_2)
     * milestone rules in production while migrating active MSc progress_report_2
     * work (submissions, feedback, supervisor approvals, messages, defence events)
     * cleanly back into progress_report_1.
     */
    public function up(): void
    {
        $workflow = app(MilestoneWorkflowService::class);

        ThesisProject::with(['student.program', 'student.level'])
            ->get()
            ->each(function (ThesisProject $thesis) use ($workflow) {
                if ($thesis->isPhdAI()) {
                    $workflow->purgePhdAiSeminarCourse($thesis);
                }

                if ($thesis->isMsc()) {
                    $workflow->migrateMscProgressReport2($thesis);
                }
            });
    }

    public function down(): void
    {
        // Consolidation and exemption cleanup cannot be automatically reversed without historical snapshots.
    }
};
