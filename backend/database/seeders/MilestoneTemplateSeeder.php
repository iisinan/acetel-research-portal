<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MilestoneTemplate;
use Illuminate\Support\Facades\DB;

class MilestoneTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $milestones = [
            [
                'name' => 'Seminar course',
                'slug' => 'seminar_as_a_course',
                'order' => 1,
                'requires_submission' => true,
                'requires_approval' => true,
                'required_approvers' => ['Admin'],
                'allow_defence_date' => true,
                'defence_type' => 'seminar',
                'defence_date_role' => 'Admin',
                'has_chat' => true,
                'submission_type' => ['ppt'],
                'description' => 'Student uploads PPT for presentation. Admin records result and approves milestone.'
            ],
            [
                'name' => 'Supervisors assigned',
                'slug' => 'supervisors_assigned',
                'order' => 2,
                'requires_submission' => false,
                'requires_approval' => true,
                'required_approvers' => ['Program Coordinator'],
                'description' => 'Program Coordinator assigns supervisors based on level (MSc: 2, PhD: 3).'
            ],
            [
                'name' => 'Proposal defence',
                'slug' => 'proposal_defence',
                'order' => 3,
                'requires_submission' => true,
                'requires_approval' => true,
                'required_approvers' => ['Supervisor', 'Program Coordinator'],
                'allow_defence_date' => true,
                'defence_type' => 'proposal',
                'defence_date_role' => 'Program Coordinator',
                'description' => 'Supervisor approval required, followed by Program Coordinator scheduling proposal defence.'
            ],
            [
                'name' => 'Progress Report 1',
                'slug' => 'progress_report_1',
                'order' => 4,
                'requires_submission' => true,
                'submission_type' => ['file'],
                'requires_approval' => true,
                'required_approvers' => ['Supervisor'],
                'description' => 'First progress report document upload and supervisor validation.'
            ],
            [
                'name' => 'Progress Report 2',
                'slug' => 'progress_report_2',
                'order' => 5,
                'requires_submission' => true,
                'submission_type' => ['file'],
                'requires_approval' => true,
                'required_approvers' => ['Supervisor'],
                'description' => 'Second progress report document upload and supervisor validation.'
            ],
            [
                'name' => 'Internal defence',
                'slug' => 'internal_defence',
                'order' => 6,
                'requires_submission' => true,
                'requires_approval' => true,
                'submission_requires_approval' => true,
                'submission_approver_roles' => ['Supervisor'],
                'required_approvers' => ['Internal Examiner', 'Program Coordinator'],
                'allow_defence_date' => true,
                'defence_type' => 'internal',
                'defence_date_role' => 'Program Coordinator',
                'show_internal_examiner_assignment' => true,
                'submission_type' => ['file'],
                'description' => 'Student submits thesis. Supervisor authorizes. Internal defence scheduling and outcome recording.'
            ],
            [
                'name' => 'Viva',
                'slug' => 'viva',
                'order' => 7,
                'requires_submission' => true,
                'requires_approval' => true,
                'submission_requires_approval' => true,
                'submission_approver_roles' => ['Program Coordinator'],
                'required_approvers' => ['External Examiner', 'Program Coordinator', 'Director'],
                'allow_defence_date' => true,
                'defence_type' => 'external',
                'defence_date_role' => 'Director',
                'show_external_examiner_assignment' => true,
                'is_final_archival' => true,
                'submission_type' => ['file', 'publications'],
                'description' => 'Student uploads thesis and publications. External examiner assignment and Final Viva.'
            ],
        ];

        // Delete templates that are not in the new list to ensure ONLY these exist
        $slugsToKeep = array_column($milestones, 'slug');
        MilestoneTemplate::whereNotIn('slug', $slugsToKeep)->delete();

        foreach ($milestones as $milestone) {
            MilestoneTemplate::updateOrCreate(
                ['slug' => $milestone['slug'], 'program_id' => null],
                $milestone
            );
        }
    }
}
