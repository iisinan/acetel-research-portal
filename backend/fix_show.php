<?php
$content = file_get_contents("resources/views/presentations/show.blade.php");

// Change column header
$content = str_replace("<th class=\"px-4 py-3.5\">Supervisors</th>", "<th class=\"px-4 py-3.5\">Panel / Supervisors</th>", $content);

// Change how supervisors/examiners are displayed
$old_php = "\$supervisors = \$sm->thesis?->assignments?->map(fn(\$a) => \$a->supervisor?->user?->name)->filter()->implode(', ');";
$new_php = "\$tableEventType = \$template->defence_type ?? 'seminar';
                                    \$tableDefEvent = \$sm->thesis?->defenceEvents?->where('type', \$tableEventType)->first();
                                    \$examiners = \$tableDefEvent ? \$tableDefEvent->panelMembers->map(fn(\$pm) => \$pm->user?->name)->filter()->implode(', ') : '';
                                    \$supervisors = \$sm->thesis?->assignments?->map(fn(\$a) => \$a->supervisor?->user?->name)->filter()->implode(', ');
                                    \$displayPanel = \$examiners ?: \$supervisors;";

$content = str_replace($old_php, $new_php, $content);

// Make sure $tableDefEvent etc is not redefined later in the block
$old_table_php = "\$tableEventType = \$template->defence_type ?? 'seminar';
                                            \$tableDefEvent = \$sm->thesis?->defenceEvents?->where('type', \$tableEventType)->first();
                                            \$tableCanEval = \$tableDefEvent && \$tableDefEvent->isAuthorizedEvaluator(auth()->id());
                                            \$tableEval = \$tableCanEval ? \$tableDefEvent->evaluations->firstWhere('evaluator_id', auth()->id()) : null;";
$new_table_php = "\$tableCanEval = \$tableDefEvent && \$tableDefEvent->isAuthorizedEvaluator(auth()->id());
                                            \$tableEval = \$tableCanEval ? \$tableDefEvent->evaluations->firstWhere('evaluator_id', auth()->id()) : null;";
                                            
$content = str_replace($old_table_php, $new_table_php, $content);

// Update table cell
$content = str_replace("{{ \$supervisors ?: 'Unassigned' }}", "{{ \$displayPanel ?: 'Unassigned' }}", $content);

file_put_contents("resources/views/presentations/show.blade.php", $content);

