<?php
$content = file_get_contents('resources/views/admin/milestone-templates/index.blade.php');

$search = <<<EOT
                                {{-- Summary Cards --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
EOT;

$replace = <<<EOT
                                {{-- Summary Cards --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-5 gap-4 mb-6">
EOT;

$content = str_replace($search, $replace, $content);

$search2 = <<<EOT
                                    <div class="bg-amber-50 rounded-xl p-4 border border-amber-100">
                                        <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-1">Not Scheduled</p>
                                        <p class="text-2xl font-black text-amber-700">{{ \$template->studentMilestones->filter(fn(\$m) => empty(\$m->defence_date))->count() }}</p>
                                    </div>
EOT;

$replace2 = <<<EOT
                                    <div class="bg-amber-50 rounded-xl p-4 border border-amber-100">
                                        <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-1">Not Scheduled</p>
                                        <p class="text-2xl font-black text-amber-700">{{ \$template->studentMilestones->filter(fn(\$m) => empty(\$m->defence_date))->count() }}</p>
                                    </div>
                                    @if(in_array('Supervisor', \$template->required_approvers ?? []))
                                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                                        <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-1">Approved by Supervisor</p>
                                        <p class="text-2xl font-black text-blue-700">{{ \$template->studentMilestones->filter(fn(\$m) => \$m->is_supervisor_approved)->count() }}</p>
                                    </div>
                                    <div class="bg-rose-50 rounded-xl p-4 border border-rose-100">
                                        <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest mb-1">Pending Supervisor</p>
                                        <p class="text-2xl font-black text-rose-700">{{ \$template->studentMilestones->filter(fn(\$m) => !\$m->is_supervisor_approved)->count() }}</p>
                                    </div>
                                    @endif
EOT;

$content = str_replace($search2, $replace2, $content);
file_put_contents('resources/views/admin/milestone-templates/index.blade.php', $content);
