<?php
$content = file_get_contents('resources/views/admin/milestone-templates/index.blade.php');

$searchSelectAll = "let visibleRows = Array.from(\$root.querySelectorAll('tr[data-milestone-id]')).filter(row => row.style.display !== 'none'); \n                                                                    selected = visibleRows.map(row => row.getAttribute('data-milestone-id'));";
$replaceSelectAll = "let visibleRows = Array.from(\$root.querySelectorAll('tr[data-milestone-id]')).filter(row => row.style.display !== 'none'); \n                                                                    selected = visibleRows.filter(row => { const cb = row.querySelector('input[type=\"checkbox\"]'); return cb && !cb.disabled; }).map(row => row.getAttribute('data-milestone-id'));";

$content = str_replace($searchSelectAll, $replaceSelectAll, $content);

$searchCheckbox = "<input type=\"checkbox\" :value=\"'{{ \$sm->id }}'\" x-model=\"selected\" class=\"rounded border-slate-300 text-brand-600 focus:ring-brand-500\">";
$replaceCheckbox = "<input type=\"checkbox\" :value=\"'{{ \$sm->id }}'\" x-model=\"selected\" class=\"rounded border-slate-300 text-brand-600 focus:ring-brand-500 disabled:opacity-30 disabled:cursor-not-allowed\" @if(in_array('Supervisor', \$template->required_approvers ?? []) && !\$sm->is_supervisor_approved) disabled title=\"Awaiting Supervisor Approval\" @endif>";

$content = str_replace($searchCheckbox, $replaceCheckbox, $content);
file_put_contents('resources/views/admin/milestone-templates/index.blade.php', $content);
