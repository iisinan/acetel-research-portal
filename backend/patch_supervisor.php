<?php
$content = file_get_contents('database/seeders/MilestoneTemplateSeeder.php');

$old = <<<EOT
            [
                'name' => 'Supervisors assigned',
                'slug' => 'supervisors_assigned',
                'order' => 2,
                'requires_submission' => false,
                'requires_approval' => true,
                'required_approvers' => ['Program Coordinator'],
EOT;

$new = <<<EOT
            [
                'name' => 'Supervisors assigned',
                'slug' => 'supervisors_assigned',
                'order' => 2,
                'requires_submission' => false,
                'requires_approval' => true,
                'required_approvers' => ['Program Coordinator'],
                'show_supervisor_assignment' => true,
EOT;

$content = str_replace($old, $new, $content);
file_put_contents('database/seeders/MilestoneTemplateSeeder.php', $content);
