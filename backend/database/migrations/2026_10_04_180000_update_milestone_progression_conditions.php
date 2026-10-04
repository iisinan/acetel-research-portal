<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\MilestoneTemplate;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Supervisors Assigned: requires tentative proposal submission
        MilestoneTemplate::where('slug', 'supervisors_assigned')->update([
            'requires_submission' => true,
            'submission_type' => ['file'],
            'description' => 'Student uploads tentative proposal. Program Coordinator assigns supervisors based on level (MSc: 2, PhD: 3).'
        ]);

        // 2. Proposal defence: allows file and PPT submission, allow defence date
        MilestoneTemplate::where('slug', 'proposal_defence')->update([
            'requires_submission' => true,
            'submission_type' => ['file', 'ppt'],
            'allow_defence_date' => true,
            'defence_type' => 'proposal',
        ]);

        // 3. Progress Report 1: allows file and PPT submission, allow defence date
        MilestoneTemplate::where('slug', 'progress_report_1')->update([
            'requires_submission' => true,
            'submission_type' => ['file', 'ppt'],
            'allow_defence_date' => true,
            'defence_type' => 'progress_report_1',
        ]);

        // 4. Progress Report 2: allows file and PPT submission, allow defence date
        MilestoneTemplate::where('slug', 'progress_report_2')->update([
            'requires_submission' => true,
            'submission_type' => ['file', 'ppt'],
            'allow_defence_date' => true,
            'defence_type' => 'progress_report_2',
        ]);
    }

    public function down(): void
    {
        MilestoneTemplate::where('slug', 'supervisors_assigned')->update([
            'requires_submission' => false,
            'submission_type' => null,
        ]);

        MilestoneTemplate::whereIn('slug', ['proposal_defence', 'progress_report_1', 'progress_report_2'])->update([
            'submission_type' => ['file'],
        ]);
    }
};
