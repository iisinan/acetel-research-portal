<?php
$content = file_get_contents('app/Services/MilestoneWorkflowService.php');

$search = "        // 2. Submission approval locked
        if (\$template->submission_requires_approval && !\$milestone->is_submission_unlocked) {
            return \"Submission Gated: Post-submission authorization is required before clearance.\";
        }";

$replace = "        // 2. Submission approval locked
        if (\$template->submission_requires_approval && !\$milestone->is_submission_unlocked) {
            return \"Submission Gated: Post-submission authorization is required before clearance.\";
        }

        // Structural Requirements
        if (\$template->show_supervisor_assignment && \$milestone->thesis->assignments()->where('status', 'active')->count() === 0) {
            return \"Structural Block: Supervisors must be assigned before approval.\";
        }
        if (\$template->show_internal_examiner_assignment && empty(\$milestone->thesis->internal_examiner_profile_id)) {
            return \"Structural Block: Internal Examiner must be assigned before approval.\";
        }
        if (\$template->show_external_examiner_assignment && empty(\$milestone->thesis->external_examiner_profile_id)) {
            return \"Structural Block: External Examiner must be assigned before approval.\";
        }
        if (\$template->allow_defence_date && (empty(\$milestone->defence_date) || empty(\$milestone->date_approved_at))) {
            return \"Structural Block: Defence date must be scheduled and authorized before approval.\";
        }";

$content = str_replace($search, $replace, $content);
file_put_contents('app/Services/MilestoneWorkflowService.php', $content);
