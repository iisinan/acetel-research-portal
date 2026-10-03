<?php
$content = file_get_contents('resources/views/dashboard/supervisor.blade.php');

$search = <<<EOT
                                     <a href="{{ route('theses.show', \$student->thesis) }}" class="flex items-center justify-center px-6 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 text-xs font-black uppercase tracking-widest hover:bg-green-600 hover:border-green-600 hover:text-white transition-all shadow-sm">
                                         Audit Thesis
                                     </a>
EOT;

$replace = <<<EOT
                                     <div x-data="{ jumpMenuOpen: false }" class="relative inline-block text-left">
                                         <button @click.prevent.stop="jumpMenuOpen = !jumpMenuOpen" class="flex items-center justify-center p-3 bg-white border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 transition-all shadow-sm">
                                             <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                         </button>
                                         <div x-show="jumpMenuOpen" @click.outside="jumpMenuOpen = false" x-cloak class="absolute right-0 top-12 w-56 bg-white rounded-xl shadow-xl border border-slate-100 z-50 overflow-hidden text-left" style="display: none;">
                                             <div class="px-3 py-2 bg-slate-50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                                 Jump Milestone
                                             </div>
                                             <div class="max-h-48 overflow-y-auto p-1">
                                                 @foreach(\\App\\Models\\MilestoneTemplate::orderBy('order')->get() as \$t)
                                                     <button type="button" onclick="jumpMilestoneGlobal('{{ \$student->id }}', '{{ \$t->id }}')" class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-green-50 hover:text-green-600 rounded-lg transition-colors flex items-center justify-between group">
                                                         <span>{{ \$t->name }}</span>
                                                         @if(\$student->thesis && \$student->thesis->currentMilestone && \$t->id === \$student->thesis->currentMilestone->milestone_template_id)
                                                             <svg class="w-3 h-3 text-green-500 opacity-50 shrink-0 ml-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                         @endif
                                                     </button>
                                                 @endforeach
                                             </div>
                                         </div>
                                     </div>
                                     <a href="{{ route('theses.show', \$student->thesis) }}" class="flex items-center justify-center px-6 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 text-xs font-black uppercase tracking-widest hover:bg-green-600 hover:border-green-600 hover:text-white transition-all shadow-sm">
                                         Audit Thesis
                                     </a>
EOT;

$content = str_replace($search, $replace, $content);

// Add the global form and script at the bottom of the file
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

if (strpos($content, 'id="global-jump-form"') === false) {
    // Put before @endsection
    $content = str_replace('@endsection', $bottomAddition . "\n@endsection", $content);
}

file_put_contents('resources/views/dashboard/supervisor.blade.php', $content);
