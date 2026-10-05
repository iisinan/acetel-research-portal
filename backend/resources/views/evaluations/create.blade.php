@extends('layouts.dashboard')

@section('header')
    Defence Evaluation
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-in-up">
    <div class="flex items-center gap-4">
        <a href="{{ route('dashboard') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-primary-600 hover:border-primary-200 transition-all shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h2 class="text-2xl font-black text-slate-900">Structured Evaluation Rubric</h2>
            <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest mt-1">Official Defense Assessment</p>
        </div>
    </div>

    <!-- Candidate Info -->
    <x-card title="Candidate Protocol">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-2">
            <div>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Candidate Name</p>
                <p class="text-lg font-black text-slate-900">{{ $defenceEvent->thesis->student->user->name }}</p>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Program</p>
                <p class="text-lg font-black text-slate-900">{{ $defenceEvent->thesis->student->program->name }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Thesis Title</p>
                <p class="text-base font-bold text-primary-600 ita">{{ $defenceEvent->thesis->title }}</p>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Event Type</p>
                <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ $defenceEvent->type }}</span>
            </div>

            @php
                // Attempt to find the terminal milestone (M9) for plagiarism data
                $milestone = $defenceEvent->thesis->milestones->where('status', '!=', 'not_started')->sortByDesc('template.order')->first();
                $plagData = null;
                if ($milestone) {
                    $plagData = $milestone->submissions->where('type', 'plagiarism_report')->sortByDesc('created_at')->first() ?? 
                               $milestone->submissions->where('type', 'manuscript')->whereNotNull('plagiarism_data')->sortByDesc('created_at')->first();
                }
            @endphp

            @if($plagData)
            <div class="md:col-span-2 p-4 bg-amber-50 rounded-2xl border border-amber-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-amber-600 shadow-sm border border-amber-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-amber-900 uppercase tracking-widest">Similarity Certification</p>
                        <p class="text-sm font-black text-amber-600 tracking-tight">
                            {{ ($plagData->type === 'plagiarism_report' ? ($plagData->file_meta['similarity_score'] ?? 'N/A') : ($plagData->plagiarism_data['similarity_score'] ?? 'N/A')) }}% Indexed
                        </p>
                    </div>
                </div>
                <a href="{{ Storage::url($plagData->type === 'plagiarism_report' ? $plagData->file_url : ($plagData->plagiarism_data['report_path'] ?? $plagData->file_url)) }}" 
                   target="_blank"
                   class="px-4 py-2 bg-white border border-amber-200 rounded-xl text-[10px] font-black text-amber-700 uppercase tracking-widest hover:bg-amber-100 transition-all shadow-sm">
                    View Cert
                </a>
            </div>
            @endif
        </div>
    </x-card>

    @php
        $isPassFail = in_array($defenceEvent->type, ['proposal', 'progress_report_1', 'progress_report_2']);
        $eventTypeLabel = match($defenceEvent->type) {
            'proposal' => 'Proposal Defence',
            'progress_report_1' => 'Progress Report 1 Defence',
            'progress_report_2' => 'Progress Report 2 Defence',
            'seminar' => 'Seminar as a Course',
            default => ucfirst(str_replace('_', ' ', $defenceEvent->type)),
        };
    @endphp

    <form action="{{ route('evaluations.store', $defenceEvent) }}" method="POST" class="space-y-8">
        @csrf

        @if($isPassFail)
            {{-- Pass / Fail Grading Card for Proposal & Progress Reports --}}
            <x-card title="Official Grading & Verdict: {{ $eventTypeLabel }}">
                <div class="space-y-6 p-2">
                    <div class="p-4 bg-indigo-50/70 border border-indigo-100 rounded-2xl flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="text-xs text-indigo-950">
                            <p class="font-bold">Grading Protocol:</p>
                            <p class="mt-0.5 text-indigo-700">Please assign either <strong>PASS</strong> or <strong>FAIL</strong> for this defence session. An average grade of Pass across examiners is required for the candidate to advance to the next milestone. If the average is Fail, the candidate must repeat this milestone.</p>
                            <p class="mt-1 text-[11px] font-semibold text-emerald-700">✔ Submitting this evaluation will officially record your attendance as Present.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-3">Select Verdict <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- PASS Option --}}
                            <label class="cursor-pointer group relative flex flex-col items-center justify-center p-6 border-2 border-slate-200 rounded-3xl hover:border-emerald-500 transition-all bg-white has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/70 has-[:checked]:ring-4 has-[:checked]:ring-emerald-500/20 shadow-sm">
                                <input type="radio" name="verdict" value="pass" class="peer sr-only" required>
                                <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <span class="text-lg font-black text-slate-800 uppercase tracking-wider peer-checked:text-emerald-700">PASS</span>
                                <span class="text-xs font-semibold text-slate-500 mt-1 text-center">Candidate meets requirements and is recommended to advance</span>
                            </label>

                            {{-- FAIL Option --}}
                            <label class="cursor-pointer group relative flex flex-col items-center justify-center p-6 border-2 border-slate-200 rounded-3xl hover:border-rose-500 transition-all bg-white has-[:checked]:border-rose-600 has-[:checked]:bg-rose-50/70 has-[:checked]:ring-4 has-[:checked]:ring-rose-500/20 shadow-sm">
                                <input type="radio" name="verdict" value="fail" class="peer sr-only" required>
                                <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <span class="text-lg font-black text-slate-800 uppercase tracking-wider peer-checked:text-rose-700">FAIL</span>
                                <span class="text-xs font-semibold text-slate-500 mt-1 text-center">Candidate requires major revisions and must repeat this milestone</span>
                            </label>
                        </div>
                    </div>

                    {{-- Examiner Comments --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Examiner Comments & Feedback</label>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Sent to Student Inbox
                            </span>
                        </div>
                        <textarea name="comments" rows="6" 
                            class="w-full bg-slate-50 border border-slate-200 rounded-2xl text-sm p-4 focus:ring-2 focus:ring-primary-500 focus:bg-white transition-all placeholder:text-slate-400 font-medium" 
                            placeholder="Provide your constructive feedback, corrections, and observations. These comments will appear directly in the student's personal message inbox..."></textarea>
                    </div>

                    <div class="mt-8 flex justify-end gap-4 border-t border-slate-100 pt-6">
                        <a href="{{ route('dashboard') }}" class="px-6 py-3 text-xs font-black text-slate-600 uppercase tracking-widest hover:bg-slate-100 rounded-xl transition-colors">Cancel</a>
                        <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-primary-500/20 transition-all active:scale-95 cursor-pointer">
                            Submit Grade & Mark Attendance
                        </button>
                    </div>
                </div>
            </x-card>
        @else
            {{-- Standard Rubric Scoring for Other Defence Types --}}
            <x-card title="Scoring Matrix">
                <div class="space-y-8 p-2">
                    
                    <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="space-y-1">
                            <label class="block text-sm font-black text-slate-900">Originality & Contribution</label>
                            <p class="text-[10px] font-medium text-slate-500 max-w-sm">Does the thesis provide a novel contribution to the field of study?</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" name="score[originality]" min="0" max="25" required class="w-20 text-center text-lg font-black bg-white border-slate-200 rounded-xl focus:ring-primary-500" placeholder="0-25">
                            <span class="text-xs font-black text-slate-400">/ 25</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="space-y-1">
                            <label class="block text-sm font-black text-slate-900">Methodology & Rigor</label>
                            <p class="text-[10px] font-medium text-slate-500 max-w-sm">Are the research methods appropriate, correctly applied, and rigorous?</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" name="score[methodology]" min="0" max="25" required class="w-20 text-center text-lg font-black bg-white border-slate-200 rounded-xl focus:ring-primary-500" placeholder="0-25">
                            <span class="text-xs font-black text-slate-400">/ 25</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="space-y-1">
                            <label class="block text-sm font-black text-slate-900">Presentation & Document Quality</label>
                            <p class="text-[10px] font-medium text-slate-500 max-w-sm">Is the thesis well-written, structured logically, and correctly formatted?</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" name="score[presentation]" min="0" max="25" required class="w-20 text-center text-lg font-black bg-white border-slate-200 rounded-xl focus:ring-primary-500" placeholder="0-25">
                            <span class="text-xs font-black text-slate-400">/ 25</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="space-y-1">
                            <label class="block text-sm font-black text-slate-900">Q&A Defense Capability</label>
                            <p class="text-[10px] font-medium text-slate-500 max-w-sm">How well did the candidate defend their work against panel questions?</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" name="score[qa]" min="0" max="25" required class="w-20 text-center text-lg font-black bg-white border-slate-200 rounded-xl focus:ring-primary-500" placeholder="0-25">
                            <span class="text-xs font-black text-slate-400">/ 25</span>
                        </div>
                    </div>

                </div>
            </x-card>

            <x-card title="Final Verdict">
                <div class="space-y-6">
                    <!-- Recommendation -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Institutional Recommendation</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <label class="cursor-pointer group relative flex flex-col items-center justify-center p-4 border border-slate-200 rounded-2xl hover:border-emerald-500 transition-all bg-white has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-500">
                                <input type="radio" name="recommendation" value="pass" class="peer sr-only" required>
                                <svg class="w-6 h-6 text-emerald-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="text-xs font-black text-slate-700 uppercase tracking-widest peer-checked:text-emerald-700">Clear Pass</span>
                            </label>

                            <label class="cursor-pointer group relative flex flex-col items-center justify-center p-4 border border-slate-200 rounded-2xl hover:border-acetel-500 transition-all bg-white has-[:checked]:border-acetel-500 has-[:checked]:bg-acetel-50 has-[:checked]:ring-2 has-[:checked]:ring-acetel-500">
                                <input type="radio" name="recommendation" value="minor_revisions" class="peer sr-only" required>
                                <svg class="w-6 h-6 text-acetel-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                <span class="text-xs font-black text-slate-700 uppercase tracking-widest peer-checked:text-acetel-700">Minor Fixes</span>
                            </label>

                            <label class="cursor-pointer group relative flex flex-col items-center justify-center p-4 border border-slate-200 rounded-2xl hover:border-amber-500 transition-all bg-white has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:ring-2 has-[:checked]:ring-amber-500">
                                <input type="radio" name="recommendation" value="major_revisions" class="peer sr-only" required>
                                <svg class="w-6 h-6 text-amber-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <span class="text-xs font-black text-slate-700 uppercase tracking-widest peer-checked:text-amber-700">Major Revis.</span>
                            </label>

                            <label class="cursor-pointer group relative flex flex-col items-center justify-center p-4 border border-slate-200 rounded-2xl hover:border-rose-500 transition-all bg-white has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50 has-[:checked]:ring-2 has-[:checked]:ring-rose-500">
                                <input type="radio" name="recommendation" value="fail" class="peer sr-only" required>
                                <svg class="w-6 h-6 text-rose-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="text-xs font-black text-slate-700 uppercase tracking-widest peer-checked:text-rose-700">Fail/Retake</span>
                            </label>
                        </div>
                    </div>

                    <!-- Comprehensive Comments -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Formal Review Comments</label>
                        <textarea name="comments" rows="5" class="w-full bg-slate-50 border-slate-200 rounded-xl text-sm p-4 focus:ring-primary-500 focus:bg-white transition-colors placeholder:text-slate-400" placeholder="Provide detailed written feedback for the candidate's revisions or general assessment..."></textarea>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-end gap-4 border-t border-slate-100 pt-6">
                    <a href="{{ route('dashboard') }}" class="px-6 py-3 text-xs font-black text-slate-600 uppercase tracking-widest hover:bg-slate-100 rounded-xl transition-colors">Cancel</a>
                    <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-primary-700 shadow-lg shadow-primary-500/20 transition-all active:scale-95">
                        Submit Evaluation Protocol
                    </button>
                </div>
            </x-card>
        @endif
    </form>
</div>
@endsection
