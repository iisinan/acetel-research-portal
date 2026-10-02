<?php
$content = file_get_contents('app/Http/Controllers/DashboardController.php');

$old_query = "                ->whereNotNull('submitted_at')";
$new_query = "                ->where(function(\$q) {
                    \$q->whereNotNull('submitted_at')
                      ->orWhereHas('template', function(\$sq) {
                          \$sq->where('requires_submission', false);
                      });
                })";

$content = str_replace($old_query, $new_query, $content);
file_put_contents('app/Http/Controllers/DashboardController.php', $content);
