<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\MilestoneTemplate;

return new class extends Migration
{
    public function up(): void
    {
        MilestoneTemplate::where('slug', 'proposal_defence')->update([
            'allow_defence_date' => true,
            'defence_type' => 'proposal',
            'defence_date_role' => 'Program Coordinator',
            'required_approvers' => ['Supervisor'],
        ]);

        MilestoneTemplate::where('slug', 'progress_report_1')->update([
            'allow_defence_date' => true,
            'defence_type' => 'progress_report_1',
            'defence_date_role' => 'Program Coordinator',
            'required_approvers' => ['Supervisor'],
        ]);

        MilestoneTemplate::where('slug', 'progress_report_2')->update([
            'allow_defence_date' => true,
            'defence_type' => 'progress_report_2',
            'defence_date_role' => 'Program Coordinator',
            'required_approvers' => ['Supervisor'],
        ]);
    }

    public function down(): void
    {
        //
    }
};
