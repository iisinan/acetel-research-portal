@extends('layouts.dashboard')

@section('header')
    Evaluation Protocol Summary
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-in-up">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-primary-600 hover:border-primary-200 transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-black text-slate-900">Evaluation Record</h2>
                <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest mt-1">Ref: EV-{{ substr($evaluation->id, 0, 8) }}</p>
            </div>
        </div>
        <a href="{{ route('evaluations.pdf', $evaluation) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-acetel-50 text-acetel-700 hover:bg-acetel-600 hover:text-white border border-acetel-100 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Export PDF
        </a>
    </div>

    <!-- Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
            <x-card title="Candidate Identity">
                <div class="grid grid-cols-2 gap-6 p-2">
                    <div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Scholar</p>
                        <p class="text-lg font-black text-slate-900">{{ $evaluation->defenceEvent->thesis->student->user->name ?? 'Unknown' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Program Details</p>
                        <p class="text-xs font-bold text-slate-900">{{ $evaluation->defenceEvent->thesis->student->program->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </x-card>

            <x-card title="Scoring Breakdown">
                <div class="space-y-4 p-2">
                    @php
                        $scores = $evaluation->score;
                        $total = $scores['total'] ?? (array_sum($scores) > 0 ? array_sum($scores) / 2 : 0);
                    @endphp
                    
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-sm font-black text-slate-700">Originality & Contribution</span>
                        <span class="text-lg font-black {{ $scores['originality'] >= 18 ? 'text-emerald-600' : ($scores['originality'] >= 12 ? 'text-amber-600' : 'text-rose-600') }}">{{ $scores['originality'] }} / 25</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-sm font-black text-slate-700">Methodology & Rigor</span>
                        <span class="text-lg font-black {{ $scores['methodology'] >= 18 ? 'text-emerald-600' : ($scores['methodology'] >= 12 ? 'text-amber-600' : 'text-rose-600') }}">{{ $scores['methodology'] }} / 25</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-sm font-black text-slate-700">Presentation Quality</span>
                        <span class="text-lg font-black {{ $scores['presentation'] >= 18 ? 'text-emerald-600' : ($scores['presentation'] >= 12 ? 'text-amber-600' : 'text-rose-600') }}">{{ $scores['presentation'] }} / 25</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-sm font-black text-slate-700">Q&A Defense</span>
                        <span class="text-lg font-black {{ $scores['qa'] >= 18 ? 'text-emerald-600' : ($scores['qa'] >= 12 ? 'text-amber-600' : 'text-rose-600') }}">{{ $scores['qa'] }} / 25</span>
                    </div>
                </div>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Final Verdict" color="indigo">
                <div class="flex flex-col items-center justify-center p-6 bg-slate-50 rounded-2xl mb-4">
                    <p class="text-[10px] uppercase font-black tracking-widest text-slate-500 mb-2">Total Score</p>
                    <p class="text-5xl font-black text-slate-900 tracking-tighter">{{ $total }}<span class="text-2xl text-slate-400">/100</span></p>
                </div>

                <div class="space-y-4">
                    <p class="text-[10px] uppercase font-black tracking-widest text-slate-500">Recommendation</p>
                    @if($evaluation->recommendation === 'pass')
                        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl">
                            <span class="text-sm font-black uppercase tracking-widest">Clear Pass</span>
                        </div>
                    @elseif($evaluation->recommendation === 'minor_revisions')
                        <div class="flex items-center gap-3 p-4 bg-acetel-50 border border-acetel-200 text-acetel-700 rounded-xl">
                            <span class="text-sm font-black uppercase tracking-widest">Minor Revisions</span>
                        </div>
                    @elseif($evaluation->recommendation === 'major_revisions')
                        <div class="flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl">
                            <span class="text-sm font-black uppercase tracking-widest">Major Revisions</span>
                        </div>
                    @else
                        <div class="flex items-center gap-3 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl">
                            <span class="text-sm font-black uppercase tracking-widest">Fail / Retake</span>
                        </div>
                    @endif
                </div>
                
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <p class="text-[10px] uppercase font-black tracking-widest text-slate-500 mb-1">Evaluator Node</p>
                    <p class="text-xs font-bold text-slate-900">{{ $evaluation->evaluator->name ?? 'Unknown' }}</p>
                    <p class="text-[9px] text-slate-400 font-medium mt-1">Submitted: {{ $evaluation->submitted_at?->format('M d, Y H:i') }}</p>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Comments -->
    @if($evaluation->comments)
        <x-card title="Examiner's Formal Comments">
            <div class="p-6 bg-slate-50 border border-slate-100 rounded-2xl">
                <p class="text-sm text-slate-700 leading-relaxed font-medium whitespace-pre-wrap">{{ $evaluation->comments }}</p>
            </div>
        </x-card>
    @endif
</div>
@endsection
