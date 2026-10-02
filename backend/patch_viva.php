<?php
$content = file_get_contents('database/seeders/MilestoneTemplateSeeder.php');

$old = <<<EOT
            [
                'name' => 'Viva',
                'slug' => 'viva',
                'order' => 7,
                'requires_submission' => true,
                'submission_type' => ['file', 'publications'],
                'requires_approval' => true,
                'required_approvers' => ['Program Coordinator', 'External Examiner', 'Director'],
EOT;
$new = <<<EOT
            [
                'name' => 'Viva',
                'slug' => 'viva',
                'order' => 7,
                'requires_submission' => true,
                'submission_type' => ['file', 'publications'],
                'submission_requires_approval' => true,
                'submission_approver_roles' => ['Program Coordinator'],
                'requires_approval' => true,
                'required_approvers' => ['Program Coordinator', 'External Examiner', 'Director'],
EOT;

$content = str_replace($old, $new, $content);
file_put_contents('database/seeders/MilestoneTemplateSeeder.php', $content);
