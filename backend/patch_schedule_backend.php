<?php
$content = file_get_contents('app/Http/Controllers/Admin/MilestoneTemplateController.php');

$search = <<<EOT
            foreach (\$ids as \$id) {
                \$milestone = \App\Models\StudentMilestone::findOrFail(\$id);
                \$thesis = \$milestone->thesis;
                \$template = \$milestone->template;
EOT;

$replace = <<<EOT
            foreach (\$ids as \$id) {
                \$milestone = \App\Models\StudentMilestone::findOrFail(\$id);
                \$thesis = \$milestone->thesis;
                \$template = \$milestone->template;
                
                if (in_array('Supervisor', \$template->required_approvers ?? []) && !\$milestone->is_supervisor_approved) {
                    continue; // Skip if supervisor hasn't approved
                }
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents('app/Http/Controllers/Admin/MilestoneTemplateController.php', $content);
