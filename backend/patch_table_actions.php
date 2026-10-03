<?php
$content = file_get_contents('resources/views/admin/milestone-templates/index.blade.php');

// Add the Actions header
$search1 = <<<EOT
                                                        @if(\$template->slug === 'seminar_as_a_course')
                                                        <th class="px-4 py-3 font-semibold text-slate-700">PPT</th>
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Score</th>
                                                        @endif
                                                    </tr>
EOT;

$replace1 = <<<EOT
                                                        @if(\$template->slug === 'seminar_as_a_course')
                                                        <th class="px-4 py-3 font-semibold text-slate-700">PPT</th>
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Score</th>
                                                        @endif
                                                        <th class="px-4 py-3 w-16 text-right font-semibold text-slate-700"></th>
                                                    </tr>
EOT;

$content = str_replace($search1, $replace1, $content);

// Add the Actions cell
$search2 = <<<EOT
                                                            @if(\$template->slug === 'seminar_as_a_course')
                                                            <td class="px-4 py-3 text-xs">
EOT;

$replace2 = <<<EOT
                                                            @if(\$template->slug === 'seminar_as_a_course')
                                                            <td class="px-4 py-3 text-xs">
EOT;

$search3 = <<<EOT
                                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold">N/A</span>
                                                                @endif
                                                            </td>
                                                            @endif
                                                        </tr>
EOT;

$replace3 = <<<EOT
                                                                <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold">N/A</span>
                                                                @endif
                                                            </td>
                                                            @endif
                                                            <td class="px-4 py-3 text-right">
                                                                <div x-data="{ menuOpen: false }" class="relative inline-block text-left">
                                                                    <button type="button" @click.prevent.stop="menuOpen = !menuOpen" class="p-1.5 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors focus:outline-none">
                                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                                                    </button>
                                                                    <div x-show="menuOpen" @click.outside="menuOpen = false" x-cloak class="absolute right-8 top-0 w-48 bg-white rounded-xl shadow-xl border border-slate-100 z-[60] overflow-hidden text-left" style="display: none;">
                                                                        <div class="px-3 py-2 bg-slate-50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                                                            Jump to Milestone
                                                                        </div>
                                                                        <div class="max-h-48 overflow-y-auto p-1">
                                                                            @foreach(\$templates as \$t)
                                                                                <button type="button" onclick="jumpMilestone('{{ \$sm->thesis->student->id }}', '{{ \$t->slug }}')" class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-brand-50 hover:text-brand-600 rounded-lg transition-colors flex items-center justify-between group">
                                                                                    <span>{{ \$t->name }}</span>
                                                                                    @if(\$t->id === \$template->id)
                                                                                        <svg class="w-3 h-3 text-brand-500 opacity-50 shrink-0 ml-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                                                    @endif
                                                                                </button>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
EOT;

$content = str_replace($search3, $replace3, $content);

// Add the global form and script at the bottom of the file
$bottomAddition = <<<EOT

<form id="global-jump-form" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="target_milestone_slug" id="global-jump-target">
</form>

@push('scripts')
<script>
    function jumpMilestone(studentId, targetSlug) {
        if (!confirm('Are you sure you want to change this student\'s milestone? This will reset their progress for future milestones.')) return;
        const form = document.getElementById('global-jump-form');
        form.action = '{{ url("admin/students") }}/' + studentId + '/set-milestone';
        document.getElementById('global-jump-target').value = targetSlug;
        form.submit();
    }
</script>
@endpush
EOT;

if (strpos($content, 'id="global-jump-form"') === false) {
    $content .= $bottomAddition;
}

file_put_contents('resources/views/admin/milestone-templates/index.blade.php', $content);
