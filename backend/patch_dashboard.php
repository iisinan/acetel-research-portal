<?php
$content = file_get_contents('app/Http/Controllers/DashboardController.php');

$old = <<<EOT
            ->whereHas('template', function(\$q) {
                \$q->where(function(\$sq) {
                    \$sq->whereJsonContains('required_approvers', 'Supervisor')
                       ->orWhereJsonContains('submission_approver_roles', 'Supervisor');
                });
            })
EOT;
$new = <<<EOT
            ->whereHas('template', function(\$q) {
                \$q->whereJsonContains('required_approvers', 'Supervisor');
            })
EOT;

$content = str_replace($old, $new, $content);
file_put_contents('app/Http/Controllers/DashboardController.php', $content);
