<?php
$content = file_get_contents("app/Http/Controllers/DashboardController.php");

$target = "->orWhereRaw(\"NOT EXISTS (
                                SELECT 1 FROM jsonb_each(COALESCE(approvals, '{}'::jsonb)) 
                                WHERE value->>'user_id' = ?
                            )\", [\$user->id])";
                            
$target1 = "->orWhereRaw(\"NOT EXISTS (
                          SELECT 1 FROM jsonb_each(COALESCE(approvals, '{}'::jsonb)) 
                          WHERE value->>'user_id' = ?
                      )\", [\$user->id])";

$target2 = "->orWhereRaw(\"NOT EXISTS (
                            SELECT 1 FROM jsonb_each(COALESCE(approvals, '{}'::jsonb)) 
                            WHERE value->>'user_id' = ?
                        )\", [\$user->id])";

$target3 = "->orWhereRaw(\"NOT EXISTS (
                       SELECT 1 FROM jsonb_each(COALESCE(approvals, '{}'::jsonb)) 
                       WHERE value->>'user_id' = ?
                   )\", [\$user->id])";

$replacement = "->orWhereJsonDoesntContain('approvals', ['user_id' => \$user->id])";

// Simple preg_replace with generic whitespace
$pattern = '/->orWhereRaw\(\"NOT EXISTS \(\s*SELECT 1 FROM jsonb_each\(COALESCE\(approvals, .{}.::jsonb\)\)\s*WHERE value->>.user_id. = \?\s*\)\", \[\$user->id\]\)/s';
$content = preg_replace($pattern, $replacement, $content);

file_put_contents("app/Http/Controllers/DashboardController.php", $content);

