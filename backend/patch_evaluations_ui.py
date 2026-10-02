import re

with open('resources/views/milestones/show.blade.php', 'r') as f:
    content = f.read()

evaluations_html = """
            <!-- Examiner Evaluations -->
            @php
                $evaluations = collect();
                if ($milestone->template->allow_defence_date && $milestone->defence_date) {
                    $event = $milestone->thesis->defenceEvents->where('type', $milestone->template->defence_type ?? 'seminar')->where('scheduled_at', $milestone->defence_date)->first();
                    if ($event) {
                        $evaluations = $event->evaluations()->with('evaluator')->get();
                    }
                }
            @endphp
            @if($evaluations->isNotEmpty())
            <div class="mt-8">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-4">Official Evaluations</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($evaluations as $eval)
                    <div class="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between shadow-sm">
                        <div>
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Examiner</p>
                                    <p class="text-sm font-bold text-slate-800">{{ $eval->evaluator->name }}</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $eval->recommendation === 'pass' ? 'bg-emerald-100 text-emerald-700' : ($eval->recommendation === 'fail' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ str_replace('_', ' ', $eval->recommendation) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <div class="bg-slate-50 p-2 rounded-lg"><p class="text-[9px] font-bold text-slate-500 uppercase">Originality</p><p class="text-xs font-black">{{ $eval->score['originality'] ?? '-' }}/10</p></div>
                                <div class="bg-slate-50 p-2 rounded-lg"><p class="text-[9px] font-bold text-slate-500 uppercase">Methodology</p><p class="text-xs font-black">{{ $eval->score['methodology'] ?? '-' }}/10</p></div>
                                <div class="bg-slate-50 p-2 rounded-lg"><p class="text-[9px] font-bold text-slate-500 uppercase">Presentation</p><p class="text-xs font-black">{{ $eval->score['presentation'] ?? '-' }}/10</p></div>
                                <div class="bg-slate-50 p-2 rounded-lg"><p class="text-[9px] font-bold text-slate-500 uppercase">Q&A</p><p class="text-xs font-black">{{ $eval->score['qa'] ?? '-' }}/10</p></div>
                            </div>
                            @if($eval->comments)
                            <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-100 mb-3 italic">"{{ $eval->comments }}"</div>
                            @endif
                        </div>
                        <a href="{{ route('evaluations.show', $eval) }}" class="inline-flex items-center justify-center w-full py-2 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-800 transition-colors">View Full Report</a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
"""

if '<!-- Examiner Evaluations -->' not in content:
    content = content.replace('<!-- Submissions List -->', evaluations_html + '\n            <!-- Submissions List -->')
    with open('resources/views/milestones/show.blade.php', 'w') as f:
        f.write(content)
    print("Added evaluations section.")
else:
    print("Already added.")
