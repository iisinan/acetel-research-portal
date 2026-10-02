import re

with open('backend/resources/views/examiner/theses/show.blade.php', 'r') as f:
    content = f.read()

placeholder = """            <!-- Placeholder for Evaluations -->
            <div class="bg-white border border-amber-200 rounded-2xl shadow-sm overflow-hidden relative">
                <div class="absolute top-0 right-0 p-3 opacity-10 pointer-events-none">
                    <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L1 21h22L12 2zm0 3.83l8.27 14.17H3.73L12 5.83zM11 10h2v5h-2v-5zm0 6h2v2h-2v-2z"/></svg>
                </div>
                <div class="p-6 relative z-10">
                    <h3 class="font-bold text-amber-800 text-lg mb-2">Evaluation Form</h3>
                    <p class="text-sm text-amber-700 mb-4 leading-relaxed">You will be able to submit your official evaluation report here once the final defence event is scheduled by the coordinator.</p>
                    <button disabled class="w-full py-2.5 bg-amber-100/50 text-amber-700 font-bold rounded-xl cursor-not-allowed opacity-70 border border-amber-200">
                        Pending Scheduling
                    </button>
                </div>
            </div>"""

new_evaluation_block = """            <!-- Evaluations -->
            @php
                $defenceEvents = $thesis->defenceEvents()->orderBy('schedule_start', 'desc')->get();
            @endphp
            
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-lg">Evaluation</h3>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($defenceEvents as $event)
                        @php
                            $existingEvaluation = \App\Models\Evaluation::where('defence_event_id', $event->id)
                                ->where('evaluator_id', auth()->id())
                                ->first();
                        @endphp
                        <div class="p-4 rounded-xl border {{ $existingEvaluation ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50' }}">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="font-bold text-slate-800 capitalize">{{ str_replace('_', ' ', $event->type) }}</h4>
                                    <p class="text-xs text-slate-500">{{ $event->schedule_start->format('M d, Y H:i') }}</p>
                                </div>
                                @if($existingEvaluation)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Submitted</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">Pending Action</span>
                                @endif
                            </div>
                            
                            @if($existingEvaluation)
                                <a href="{{ route('evaluations.show', $existingEvaluation->id) }}" class="block w-full text-center py-2 bg-white border border-green-200 text-green-700 font-bold rounded-lg hover:bg-green-100 transition-colors">
                                    View My Evaluation
                                </a>
                            @else
                                <a href="{{ route('evaluations.create', $event->id) }}" class="block w-full text-center py-2 bg-amber-500 text-white font-bold rounded-lg hover:bg-amber-600 transition-colors shadow-sm">
                                    Submit Evaluation
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="text-center p-4">
                            <div class="w-12 h-12 mx-auto bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700 mb-1">No Events Scheduled</p>
                            <p class="text-xs text-slate-500">You will be able to submit your evaluation once the coordinator schedules the defence.</p>
                        </div>
                    @endforelse
                </div>
            </div>"""

content = content.replace(placeholder, new_evaluation_block)

with open('backend/resources/views/examiner/theses/show.blade.php', 'w') as f:
    f.write(content)

