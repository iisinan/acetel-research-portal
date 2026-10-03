<?php
$content = file_get_contents('app/Http/Controllers/Admin/StudentController.php');

$search = <<<EOT
                \$m->update([
                    'status' => 'not_started', // The WorkflowService or normal flow usually keeps it 'not_started' or 'in_progress', but 'in_progress' isn't fully used in this system consistently. Wait, we should just set it to not_started if it's a demotion, or keep it as is if it's already active.
                    // Actually, setting to 'not_started' lets the student start it fresh.
                    // Let's set it to 'not_started' and clear approvals if it was approved? No, let's just leave it 'not_started' so it's the active uncompleted one.
                ]);
                \$m->update(['status' => 'in_progress']); // Let's use in_progress for clarity? Wait, the system uses 'not_started' for the current one until they submit!
EOT;

$replace = <<<EOT
                \$m->update([
                    'status' => 'in_progress',
                    'due_date' => now()->addDays(30)
                ]);
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents('app/Http/Controllers/Admin/StudentController.php', $content);
