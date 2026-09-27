import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/supervisor/seminars/index.blade.php'

new_content = """@extends('layouts.dashboard')

@section('content')
<div class="space-y-10 pb-10">
    <!-- Sophisticated Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <div class="p-1.5 rounded-lg bg-acetel-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Evaluation Panel</span>
            </div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Seminar Examinations</h1>
            <p class="mt-2 text-sm font-medium text-slate-500">View your assigned seminar examinations, review student presentations, and award scores.</p>
        </div>
    </div>

    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden">
        <div class="overflow-x-auto">
            @if($events->isEmpty())
                <div class="text-center py-20 bg-slate-50/50">
                    <div class="max-w-xs mx-auto">
                        <div class="w-16 h-16 bg-white rounded-3xl border border-slate-100 flex items-center justify-center mx-auto mb-6 shadow-sm">
                            <svg class="w-8 h-8 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">No Examinations Scheduled</h4>
                        <p class="text-xs text-slate-500 mt-2 font-medium">You currently have no students assigned for seminar evaluation.</p>
                    </div>
                </div>
            @else
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Date Scheduled</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Student</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Presentation</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Marks / Score</th>
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 border-t border-slate-50">
                        @foreach($events as $event)
                            @php
                                $milestone = $event->thesis->milestones->first();
                                $evaluation = $event->evaluations->first();
                            @endphp
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="px-8 py-7">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-500 shadow-sm">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-slate-900 leading-tight">{{ $event->schedule_start->format('M d, Y') }}</p>
                                            <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">{{ $event->schedule_start->format('H:i') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-7">
                                    <div class="max-w-xs">
                                        <p class="text-base font-black text-slate-900 leading-tight">{{ $event->thesis->student->user->name }}</p>
                                        <p class="text-[10px] font-medium text-slate-500 mt-1 line-clamp-1">{{ $event->thesis->student->matric_number }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-7">
                                    @if($milestone && $milestone->submissions->where('type', 'ppt')->count() > 0)
                                        <div class="flex flex-col gap-2">
                                            @foreach($milestone->submissions->where('type', 'ppt') as $submission)
                                                <a href="{{ Storage::url($submission->file_url) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all w-max shadow-sm">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                    View PPT
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-500 border border-slate-200">
                                            Not uploaded
                                        </span>
                                    @endif
                                </td>
                                <form action="{{ route('supervisor.seminars.score', $event->id) }}" method="POST">
                                    @csrf
                                    <td class="px-6 py-7">
                                        <div class="flex flex-col gap-3 max-w-xs">
                                            <div class="flex items-center gap-2">
                                                <input type="number" name="score" value="{{ $evaluation ? $evaluation->score['total'] ?? '' : '' }}" min="0" max="100" required class="w-20 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 text-center" placeholder="0">
                                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">/ 100</span>
                                            </div>
                                            <input type="text" name="comments" value="{{ $evaluation ? $evaluation->comments : '' }}" placeholder="Remarks (optional)" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                        </div>
                                    </td>
                                    <td class="px-8 py-7 text-right align-middle">
                                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 {{ $evaluation ? 'bg-slate-900 hover:bg-slate-800' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                            {{ $evaluation ? 'Update Score' : 'Save Marks' }}
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
"""

with open(path, 'w') as f:
    f.write(new_content)
