<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\MilestoneTemplate;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        MilestoneTemplate::where('slug', 'internal_defence')->orWhere('order', 6)->update([
            'requires_submission' => true,
            'submission_type' => ['file', 'publications'],
            'show_internal_examiner_assignment' => true,
            'allow_defence_date' => true,
            'defence_type' => 'internal',
            'required_approvers' => ['Supervisor', 'Internal Examiner'],
            'description' => 'Student uploads thesis manuscript and publications. Admin or Program Coordinator assigns Internal Examiner and sets defence date. Post-defence, student uploads corrected thesis, which must be approved by the Internal Examiner to advance.',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        MilestoneTemplate::where('slug', 'internal_defence')->orWhere('order', 6)->update([
            'submission_type' => ['file'],
            'required_approvers' => ['Supervisor', 'Internal Examiner', 'Program Coordinator'],
        ]);
    }
};
