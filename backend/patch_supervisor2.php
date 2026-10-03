<?php
$content = file_get_contents('resources/views/dashboard/supervisor.blade.php');

// Remove the old buggy script
$content = preg_replace('/<form id="global-jump-form".*?<\/script>\n@endpush/s', '', $content);

// And replace the buggy dropdown
$search = <<<EOT
                                                 @foreach(\\App\\Models\\MilestoneTemplate::orderBy('order')->get() as \$t)
                                                     <button type="button" onclick="jumpMilestoneGlobal('{{ \$student->id }}', '{{ \$t->id }}')" class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-green-50 hover:text-green-600 rounded-lg transition-colors flex items-center justify-between group">
                                                         <span>{{ \$t->name }}</span>
                                                         @if(\$student->thesis && \$student->thesis->currentMilestone && \$t->id === \$student->thesis->currentMilestone->milestone_template_id)
                                                             <svg class="w-3 h-3 text-green-500 opacity-50 shrink-0 ml-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                         @endif
                                                     </button>
                                                 @endforeach
EOT;

$replace = <<<EOT
                                                 @foreach(\\App\\Models\\MilestoneTemplate::orderBy('order')->get() as \$t)
                                                     @php
                                                        \$sm = \$student->thesis ? \$student->thesis->milestones->where('milestone_template_id', \$t->id)->first() : null;
                                                     @endphp
                                                     @if(\$sm)
                                                     <button type="button" onclick="jumpMilestoneGlobal('{{ \$student->id }}', '{{ \$sm->id }}')" class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-green-50 hover:text-green-600 rounded-lg transition-colors flex items-center justify-between group">
                                                         <span>{{ \$t->name }}</span>
                                                         @if(\$student->thesis && \$student->thesis->currentMilestone && \$t->id === \$student->thesis->currentMilestone->milestone_template_id)
                                                             <svg class="w-3 h-3 text-green-500 opacity-50 shrink-0 ml-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                         @endif
                                                     </button>
                                                     @endif
                                                 @endforeach
EOT;

$content = str_replace($search, $replace, $content);

$bottomAddition = <<<EOT

<form id="global-jump-form" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="milestone_id" id="global-jump-target">
</form>

@push('scripts')
<script>
    function jumpMilestoneGlobal(studentId, targetMilestoneId) {
        if (!confirm('Are you sure you want to change this student\'s milestone? This will reset their progress for future milestones.')) return;
        const form = document.getElementById('global-jump-form');
        form.action = '{{ url("students") }}/' + studentId + '/set-milestone-global';
        document.getElementById('global-jump-target').value = targetMilestoneId;
        form.submit();
    }
</script>
@endpush
EOT;

$content = str_replace('@endsection', $bottomAddition . "\n@endsection", $content);

file_put_contents('resources/views/dashboard/supervisor.blade.php', $content);
