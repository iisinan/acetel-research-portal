<?php
$content = file_get_contents('app/Http/Controllers/MilestoneReviewController.php');

$search = <<<EOT
            if (\$roleFilled) {
                // Requirement: Post Submission Approval must be granted before Institutional Clearance.
                if (!\$this->workflowService->canApprove(\$milestone, Auth::user(), \$roleFilled)) {
                    return redirect()->route('dashboard')
                        ->with('error', 'Institutional Clearance cannot be granted until Post Submission Approval has been finalized.');
                }
EOT;

$replace = <<<EOT
            if (\$roleFilled) {
                // Requirement: Post Submission Approval must be granted before Institutional Clearance.
                \$error = \$this->workflowService->canApprove(\$milestone, Auth::user(), \$roleFilled);
                if (\$error) {
                    return redirect()->route('dashboard')
                        ->with('error', \$error);
                }
EOT;

$content = str_replace($search, $replace, $content);

// Also fix the optional message at the top
$search2 = <<<EOT
        if (!\$this->workflowService->canApprove(\$milestone, Auth::user())) {
            // Optional: flash message that previous approvals are missing
        }
EOT;

$replace2 = <<<EOT
        \$error = \$this->workflowService->canApprove(\$milestone, Auth::user());
        if (\$error) {
            // Optional: flash message that previous approvals are missing
        }
EOT;

$content = str_replace($search2, $replace2, $content);

file_put_contents('app/Http/Controllers/MilestoneReviewController.php', $content);
