<?php
$content = file_get_contents('resources/views/admin/milestone-templates/index.blade.php');

$search = "                                                            if (\$sm->status === 'not_started') {
                                                                \$detailedStatus = 'Awaiting Submission';
                                                                \$statusColor = 'bg-slate-100 text-slate-600';
                                                            } elseif (\$sm->status === 'submitted') {";

$replace = "                                                            if (\$sm->status === 'not_started' || \$sm->status === 'in_progress') {
                                                                \$detailedStatus = 'Awaiting Submission';
                                                                \$statusColor = 'bg-slate-100 text-slate-600';
                                                            } elseif (\$sm->status === 'submitted') {";

$content = str_replace($search, $replace, $content);
file_put_contents('resources/views/admin/milestone-templates/index.blade.php', $content);
