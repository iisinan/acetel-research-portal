<?php
$content = file_get_contents('resources/views/admin/milestone-templates/index.blade.php');

$search = <<<EOT
                                                        @php
                                                            \$event = current(\$sm->thesis->defenceEvents->where('type', \$template->defence_type ?? 'seminar')->all());
EOT;

$replace = <<<EOT
                                                        @php
                                                            \$detailedStatus = ucfirst(str_replace('_', ' ', \$sm->status));
                                                            \$statusColor = 'bg-slate-100 text-slate-700';
                                                            
                                                            if (\$sm->status === 'not_started') {
                                                                \$detailedStatus = 'Awaiting Submission';
                                                                \$statusColor = 'bg-slate-100 text-slate-600';
                                                            } elseif (\$sm->status === 'submitted') {
                                                                \$detailedStatus = 'Doc Uploaded';
                                                                \$statusColor = 'bg-blue-100 text-blue-700';
                                                            } elseif (\$sm->status === 'revision_required') {
                                                                \$detailedStatus = 'Rejected (Revision)';
                                                                \$statusColor = 'bg-red-100 text-red-700';
                                                            } elseif (\$sm->status === 'partially_approved') {
                                                                if (in_array('Supervisor', \$template->required_approvers ?? [])) {
                                                                    if (\$sm->is_supervisor_approved) {
                                                                        \$detailedStatus = 'Supervisor Accepted';
                                                                        \$statusColor = 'bg-indigo-100 text-indigo-700';
                                                                    } else {
                                                                        \$detailedStatus = 'Pending Supervisor';
                                                                        \$statusColor = 'bg-amber-100 text-amber-700';
                                                                    }
                                                                } else {
                                                                    \$detailedStatus = 'Partially Cleared';
                                                                    \$statusColor = 'bg-indigo-100 text-indigo-700';
                                                                }
                                                            } elseif (\$sm->status === 'approved') {
                                                                \$detailedStatus = 'Fully Accepted';
                                                                \$statusColor = 'bg-emerald-100 text-emerald-700';
                                                            }
                                                            
                                                            \$event = current(\$sm->thesis->defenceEvents->where('type', \$template->defence_type ?? 'seminar')->all());
EOT;

$content = str_replace($search, $replace, $content);

$searchStatusCell = <<<EOT
                                                            <td class="px-4 py-3">
                                                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-medium">{{ ucfirst(str_replace('_', ' ', \$sm->status)) }}</span>
                                                            </td>
EOT;

$replaceStatusCell = <<<EOT
                                                            <td class="px-4 py-3">
                                                                <span class="px-2 py-1 rounded-md text-xs font-bold {{ \$statusColor }}">{{ \$detailedStatus }}</span>
                                                            </td>
EOT;

$content = str_replace($searchStatusCell, $replaceStatusCell, $content);
file_put_contents('resources/views/admin/milestone-templates/index.blade.php', $content);
