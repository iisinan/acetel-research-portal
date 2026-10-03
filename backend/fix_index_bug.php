<?php
$content = file_get_contents('resources/views/admin/milestone-templates/index.blade.php');

$search = <<<EOT
                                                                            @foreach(\$templates as \$t)
                                                                                <button type="button" onclick="jumpMilestone('{{ \$sm->thesis->student->id }}', '{{ \$t->slug }}')" class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-brand-50 hover:text-brand-600 rounded-lg transition-colors flex items-center justify-between group">
                                                                                    <span>{{ \$t->name }}</span>
                                                                                    @if(\$t->id === \$template->id)
                                                                                        <svg class="w-3 h-3 text-brand-500 opacity-50 shrink-0 ml-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                                                    @endif
                                                                                </button>
                                                                            @endforeach
EOT;

$replace = <<<EOT
                                                                            @foreach(\$templates as \$t)
                                                                                @php
                                                                                    \$targetSm = \$sm->thesis->milestones->where('milestone_template_id', \$t->id)->first();
                                                                                @endphp
                                                                                @if(\$targetSm)
                                                                                <button type="button" onclick="jumpMilestone('{{ \$sm->thesis->student->id }}', '{{ \$targetSm->id }}')" class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-brand-50 hover:text-brand-600 rounded-lg transition-colors flex items-center justify-between group">
                                                                                    <span>{{ \$t->name }}</span>
                                                                                    @if(\$t->id === \$template->id)
                                                                                        <svg class="w-3 h-3 text-brand-500 opacity-50 shrink-0 ml-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                                                    @endif
                                                                                </button>
                                                                                @endif
                                                                            @endforeach
EOT;

$content = str_replace($search, $replace, $content);

// Also change target_milestone_slug to milestone_id in the form
$content = str_replace('name="target_milestone_slug"', 'name="milestone_id"', $content);

file_put_contents('resources/views/admin/milestone-templates/index.blade.php', $content);
