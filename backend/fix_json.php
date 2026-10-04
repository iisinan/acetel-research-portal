<?php
$content = file_get_contents("app/Http/Controllers/DashboardController.php");

$pattern = "/->orWhereRaw\(\"NOT EXISTS \(\s*SELECT 1 FROM jsonb_each\(COALESCE\(approvals, '{}'::jsonb\)\)\s*WHERE value->>'user_id' = \?\s*\)\", \[\$user->id\]\);/s";

$replacement = "->orWhereJsonDoesntContain('approvals', ['user_id' => \$user->id]);";

$content = preg_replace($pattern, $replacement, $content);

file_put_contents("app/Http/Controllers/DashboardController.php", $content);

