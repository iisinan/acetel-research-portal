@extends('layouts.dashboard')

@section('header')
    Candidate Milestones
@endsection

@section('content')
<div x-data="directorMilestoneHub(@js($studentDossiers))" class="space-y-8 pb-12">

    {{-- Executive Header Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 p-6 sm:p-8 shadow-xl shadow-slate-900/20 text-white">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950"></div>
        <div class="absolute top-0 right-0 w-80 h-80 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/3 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-teal-500/10 rounded-full translate-y-1/2 -translate-x-1/4 blur-3xl"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-3 py-1 bg-white/10 rounded-full text-[10px] font-black uppercase tracking-widest text-emerald-400 border border-white/10">
                        Milestone Progression Surveillance
                    </span>
                    <span class="text-xs text-slate-400">• {{ $totalActiveStudents }} Active Candidates In-Flight</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    Candidate <span class="text-emerald-400">Milestone Radar</span>
                </h1>
                <p class="mt-2 text-slate-300 text-xs sm:text-sm max-w-2xl font-medium">
                    Monitor student cohort distribution across approved academic milestones. Select any milestone below to inspect only candidates currently situated at that phase along with their supervisory committee and examination board.
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('dashboard', ['role' => 'Director']) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/15 text-white rounded-xl text-xs font-bold border border-white/15 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Command Centre
                </a>
                <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-black tracking-wider uppercase transition-all shadow-lg shadow-emerald-900/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Analytics
                </a>
            </div>
        </div>
    </div>

    {{-- Horizontal Milestone Template Stepper / Tabs --}}
    <div class="bg-white border border-slate-100 rounded-3xl p-4 sm:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest">Academic Pipeline Stages</h3>
            </div>
            <p class="text-[11px] font-bold text-slate-400">Click a milestone tab to view its active candidates</p>
        </div>

        <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-thin">
            @foreach($templates as $template)
                @php
                    $count = $milestoneCounts[$template->id] ?? 0;
                    $isSelected = $selectedMilestone && $selectedMilestone->id === $template->id;
                @endphp
                <a href="{{ route('director.milestones.index', array_merge(request()->query(), ['milestone_id' => $template->id])) }}"
                    class="group shrink-0 flex items-center gap-3 px-4 py-3 rounded-2xl border text-xs font-bold transition-all {{ $isSelected ? 'bg-emerald-600 text-white border-emerald-600 shadow-md shadow-emerald-600/30 ring-2 ring-emerald-500/20' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200/80 hover:border-slate-300' }}">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center font-black text-[11px] {{ $isSelected ? 'bg-white/20 text-white' : 'bg-white text-slate-700 border border-slate-200' }}">
                        {{ $template->order }}
                    </div>
                    <div class="text-left">
                        <p class="font-black truncate max-w-[170px] leading-tight {{ $isSelected ? 'text-white' : 'text-slate-800' }}">{{ $template->name }}</p>
                        <p class="text-[10px] mt-0.5 {{ $isSelected ? 'text-emerald-100 font-bold' : 'text-slate-400 font-medium' }}">
                            {{ $count }} {{ Str::plural('candidate', $count) }}
                        </p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $isSelected ? 'bg-white text-emerald-800' : ($count > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600') }}">
                        {{ $count }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="bg-white border border-slate-100 rounded-3xl p-5 shadow-sm">
        <form action="{{ route('director.milestones.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="hidden" name="milestone_id" value="{{ $selectedMilestone?->id }}">

            {{-- Search --}}
            <div class="sm:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search candidate name, matric, or thesis title..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-emerald-500 focus:border-emerald-500 pl-10 pr-4 py-3 transition-colors">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            {{-- Program Filter --}}
            <div>
                <select name="program_id" class="w-full bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:ring-emerald-500 focus:border-emerald-500 px-3 py-3">
                    <option value="">All Academic Programs</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->code }} - {{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Cohort Filter --}}
            <div>
                <select name="cohort_id" class="w-full bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:ring-emerald-500 focus:border-emerald-500 px-3 py-3">
                    <option value="">All Cohorts</option>
                    @foreach($cohorts as $c)
                        <option value="{{ $c->id }}" {{ request('cohort_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Actions --}}
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-emerald-600/20">
                    Apply Filter
                </button>
                <a href="{{ route('director.milestones.index', ['milestone_id' => $selectedMilestone?->id]) }}" class="px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-2xl text-xs font-bold transition-all flex items-center justify-center" title="Reset Filters">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </a>
            </div>
        </form>
    </div>

    {{-- Candidates Currently Active Under This Milestone --}}
    <div class="bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-sm">
        <div class="px-6 sm:px-8 py-5 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-slate-50/50">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">
                        Stage: {{ $selectedMilestone?->name ?? 'All' }}
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase tracking-wider">
                        Milestone {{ $selectedMilestone?->order ?? '1' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    Showing candidates who have completed all preceding milestones and are currently active here.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-700 shadow-sm">
                    {{ $students->total() }} {{ Str::plural('Candidate', $students->total()) }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="px-6 sm:px-8 py-4">Candidate</th>
                        <th class="px-4 py-4">Program & Level</th>
                        <th class="px-4 py-4">Thesis Project</th>
                        <th class="px-4 py-4">Supervisory Team</th>
                        <th class="px-4 py-4">Examination Board</th>
                        <th class="px-4 py-4">Milestone Status</th>
                        <th class="px-6 sm:px-8 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                        @php
                            $dossier = $studentDossiers[$student->id] ?? null;
                            $thesis = $student->thesis;
                            $supervisors = $dossier['supervisors'] ?? [];
                            $internalExam = $dossier['internal_examiner'] ?? null;
                            $externalExam = $dossier['external_examiner'] ?? null;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            {{-- Candidate Name (Clickable) --}}
                            <td class="px-6 sm:px-8 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-100 to-teal-100 border border-emerald-200 text-emerald-800 flex items-center justify-center font-black text-sm shrink-0 shadow-sm">
                                        {{ substr($student->user?->name ?? '?', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <button type="button" 
                                            @click="openDossier('{{ $student->id }}')" 
                                            class="text-left font-black text-slate-900 group-hover:text-emerald-700 transition-colors text-sm hover:underline block truncate max-w-[190px]">
                                            {{ $student->user?->name ?? 'N/A' }}
                                        </button>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">
                                            {{ $student->student_id_number }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Program & Level --}}
                            <td class="px-4 py-4">
                                <div class="space-y-1">
                                    <span class="inline-flex px-2 py-0.5 bg-slate-100 text-slate-700 rounded-lg text-[10px] font-black uppercase tracking-wider border border-slate-200">
                                        {{ $student->program?->code ?? 'N/A' }}
                                    </span>
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                        {{ $student->level?->name ?? ($student->isPhdAI() ? 'PhD' : 'MSc') }} • {{ $student->cohort?->name ?? 'N/A' }}
                                    </p>
                                </div>
                            </td>

                            {{-- Thesis Project --}}
                            <td class="px-4 py-4">
                                <div class="max-w-[220px]">
                                    <p class="text-xs font-bold text-slate-800 truncate" title="{{ $thesis?->title }}">
                                        {{ $thesis?->title ?? 'No Proposal Registered' }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $thesis?->progress_percentage ?? 0 }}%"></div>
                                        </div>
                                        <span class="text-[9px] font-black text-slate-400">{{ $thesis?->progress_percentage ?? 0 }}%</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Supervisory Team --}}
                            <td class="px-4 py-4">
                                @if(count($supervisors) > 0)
                                    <div class="space-y-1">
                                        <p class="text-xs font-bold text-slate-800 truncate max-w-[180px]">
                                            {{ $supervisors[0]['name'] }}
                                        </p>
                                        <p class="text-[9px] font-medium text-slate-400">
                                            {{ $supervisors[0]['role'] }}
                                            @if(count($supervisors) > 1)
                                                <span class="font-black text-emerald-600">+{{ count($supervisors) - 1 }} co-supervisor</span>
                                            @endif
                                        </p>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-amber-50 text-amber-700 rounded-lg text-[9px] font-black uppercase tracking-wider border border-amber-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Unassigned
                                    </span>
                                @endif
                            </td>

                            {{-- Examination Board (Internal & External) --}}
                            <td class="px-4 py-4">
                                <div class="space-y-1.5">
                                    {{-- Internal Examiner --}}
                                    <div class="flex items-center gap-1.5">
                                        @if($internalExam)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-md text-[9px] font-bold border border-emerald-200" title="{{ $internalExam['name'] }}">
                                                <svg class="w-2.5 h-2.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                Int: {{ Str::limit($internalExam['name'], 14) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-slate-100 text-slate-400 rounded-md text-[9px] font-bold border border-slate-200">
                                                Int: None
                                            </span>
                                        @endif
                                    </div>

                                    {{-- External Examiner --}}
                                    <div class="flex items-center gap-1.5">
                                        @if($externalExam)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded-md text-[9px] font-bold border border-blue-200" title="{{ $externalExam['name'] }} ({{ $externalExam['institution'] }})">
                                                <svg class="w-2.5 h-2.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                Ext: {{ Str::limit($externalExam['name'], 14) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-slate-100 text-slate-400 rounded-md text-[9px] font-bold border border-slate-200">
                                                Ext: None
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Milestone Status --}}
                            <td class="px-4 py-4">
                                @php
                                    $status = $dossier['milestone_status'] ?? 'not_started';
                                    $statusMap = [
                                        'approved' => ['bg' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'label' => 'Approved'],
                                        'completed' => ['bg' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'label' => 'Completed'],
                                        'submitted' => ['bg' => 'bg-blue-100 text-blue-800 border-blue-200', 'label' => 'Submitted'],
                                        'in_review' => ['bg' => 'bg-indigo-100 text-indigo-800 border-indigo-200', 'label' => 'In Review'],
                                        'revision_required' => ['bg' => 'bg-amber-100 text-amber-800 border-amber-200', 'label' => 'Revision Req.'],
                                        'rejected' => ['bg' => 'bg-rose-100 text-rose-800 border-rose-200', 'label' => 'Rejected'],
                                        'in_progress' => ['bg' => 'bg-cyan-100 text-cyan-800 border-cyan-200', 'label' => 'In Progress'],
                                        'not_started' => ['bg' => 'bg-slate-100 text-slate-600 border-slate-200', 'label' => 'Active Phase'],
                                    ];
                                    $badge = $statusMap[$status] ?? ['bg' => 'bg-slate-100 text-slate-600 border-slate-200', 'label' => ucwords(str_replace('_', ' ', $status))];
                                @endphp
                                <div class="space-y-1">
                                    <span class="inline-flex px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider border {{ $badge['bg'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                    @if(!empty($dossier['milestone_defence_date']))
                                        <p class="text-[9px] font-bold text-slate-500 flex items-center gap-1">
                                            <svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            {{ $dossier['milestone_defence_date'] }}
                                        </p>
                                    @endif
                                </div>
                            </td>

                            {{-- Action --}}
                            <td class="px-6 sm:px-8 py-4 text-right">
                                <button type="button" 
                                    @click="openDossier('{{ $student->id }}')" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-wider transition-all shadow-sm active:scale-95">
                                    <span>Inspect</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-20 text-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">No Candidates Currently Active at this Milestone</h3>
                                <p class="text-xs text-slate-400 font-medium mt-1 max-w-sm mx-auto">
                                    There are currently no active students situated at "{{ $selectedMilestone?->name }}". Switch to another milestone tab above to inspect candidates.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="px-6 sm:px-8 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $students->links() }}
            </div>
        @endif
    </div>

    {{-- Interactive Alpine.js Modal: Student Thesis & Examination Board Dossier --}}
    <div x-show="showModal" 
         x-cloak 
         @keydown.escape.window="closeDossier()" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        
        {{-- Backdrop --}}
        <div x-show="showModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             @click="closeDossier()" 
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="showModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-slate-100">
                
                <template x-if="activeCandidate">
                    <div>
                        {{-- Modal Header --}}
                        <div class="relative bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 p-6 sm:p-8 text-white">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-xl font-black text-white shrink-0 shadow-inner">
                                        <span x-text="activeCandidate.name.charAt(0)"></span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2.5 py-0.5 bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 rounded-full text-[10px] font-black uppercase tracking-wider" x-text="activeCandidate.level"></span>
                                            <span class="px-2.5 py-0.5 bg-white/10 text-slate-300 rounded-full text-[10px] font-bold" x-text="activeCandidate.cohort"></span>
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                                                  :class="activeCandidate.enrollment_status === 'active' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-500/20 text-slate-300'"
                                                  x-text="activeCandidate.enrollment_status"></span>
                                        </div>
                                        <h3 class="text-xl sm:text-2xl font-black text-white leading-tight" x-text="activeCandidate.name"></h3>
                                        <p class="text-xs text-slate-300 font-medium mt-1">
                                            <span class="font-bold text-white" x-text="activeCandidate.matric"></span> • <span x-text="activeCandidate.program_name"></span>
                                        </p>
                                    </div>
                                </div>
                                <button type="button" @click="closeDossier()" class="p-2 text-slate-400 hover:text-white rounded-xl bg-white/5 hover:bg-white/10 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            {{-- Active Milestone Strip inside Header --}}
                            <div class="mt-6 pt-5 border-t border-white/10 flex flex-wrap items-center justify-between gap-4 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Active Milestone:</span>
                                    <span class="px-3 py-1 bg-white/10 rounded-xl font-black text-emerald-300 border border-white/10" x-text="activeCandidate.milestone_name"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Phase Status:</span>
                                    <span class="px-3 py-1 rounded-xl font-black uppercase text-[10px] tracking-wider bg-white/20 text-white" x-text="activeCandidate.milestone_status.replace('_', ' ')"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Body --}}
                        <div class="p-6 sm:p-8 space-y-8 max-h-[calc(85vh-200px)] overflow-y-auto">

                            {{-- 1. Thesis Details --}}
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-2 h-6 bg-emerald-600 rounded-full"></div>
                                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-wider">Thesis Project Dossier</h4>
                                    </div>
                                    <span class="text-xs font-black text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                                        Overall Progress: <span x-text="activeCandidate.thesis_progress + '%'"></span>
                                    </span>
                                </div>

                                <div class="p-5 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-3">
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Thesis Title</p>
                                        <h5 class="text-base font-black text-slate-900 mt-1 leading-snug" x-text="activeCandidate.thesis_title"></h5>
                                    </div>

                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Abstract / Research Scope</p>
                                        <p class="text-xs text-slate-600 mt-1 font-medium leading-relaxed" x-text="activeCandidate.thesis_description"></p>
                                    </div>

                                    {{-- Degree Progress Bar --}}
                                    <div class="pt-2">
                                        <div class="w-full h-2.5 bg-slate-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-emerald-600 rounded-full transition-all duration-700" :style="'width: ' + activeCandidate.thesis_progress + '%'"></div>
                                        </div>
                                    </div>

                                    {{-- Defence Session Info (If Scheduled) --}}
                                    <template x-if="activeCandidate.milestone_defence_date">
                                        <div class="mt-4 pt-4 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                            <div>
                                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Defense Date & Time</p>
                                                <p class="text-xs font-black text-slate-800 mt-0.5">
                                                    <span x-text="activeCandidate.milestone_defence_date"></span>
                                                    <span x-show="activeCandidate.milestone_defence_time" x-text="' at ' + activeCandidate.milestone_defence_time"></span>
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Location / Venue</p>
                                                <p class="text-xs font-bold text-slate-700 mt-0.5" x-text="activeCandidate.milestone_defence_location || 'Not Specified'"></p>
                                            </div>
                                            <div>
                                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Meeting Link</p>
                                                <template x-if="activeCandidate.meeting_link">
                                                    <a :href="activeCandidate.meeting_link" target="_blank" class="text-xs font-black text-emerald-600 hover:underline mt-0.5 inline-flex items-center gap-1">
                                                        <span>Join Defence Session</span>
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                    </a>
                                                </template>
                                                <template x-if="!activeCandidate.meeting_link">
                                                    <p class="text-xs font-medium text-slate-400 mt-0.5">No video link</p>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- 2. Supervisory Team --}}
                            <div class="space-y-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-2 h-6 bg-teal-600 rounded-full"></div>
                                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-wider">Supervisory Team</h4>
                                </div>

                                <template x-if="activeCandidate.supervisors && activeCandidate.supervisors.length > 0">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <template x-for="supervisor in activeCandidate.supervisors" :key="supervisor.email + supervisor.role">
                                            <div class="p-5 bg-white border border-slate-200 rounded-2xl shadow-sm hover:border-teal-300 transition-colors">
                                                <div class="flex items-start justify-between gap-2 mb-2">
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                                                          :class="supervisor.role === 'Primary Supervisor' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200'"
                                                          x-text="supervisor.role"></span>
                                                    <span class="text-[9px] font-bold text-slate-400" x-text="'Assigned ' + supervisor.assigned_at"></span>
                                                </div>
                                                <h5 class="text-sm font-black text-slate-900" x-text="supervisor.name"></h5>
                                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mt-0.5" x-text="supervisor.rank"></p>
                                                
                                                <div class="mt-3 pt-3 border-t border-slate-100 text-xs space-y-1 text-slate-600">
                                                    <p class="flex items-center gap-2">
                                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                        <span class="truncate" x-text="supervisor.email"></span>
                                                    </p>
                                                    <p class="flex items-center gap-2" x-show="supervisor.staff_id !== 'N/A'">
                                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                                        <span class="text-[11px] font-bold text-slate-500 uppercase" x-text="'Staff ID: ' + supervisor.staff_id"></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="!activeCandidate.supervisors || activeCandidate.supervisors.length === 0">
                                    <div class="p-6 bg-amber-50/70 border border-amber-200 rounded-2xl flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        </div>
                                        <div>
                                            <h5 class="text-xs font-black text-amber-900 uppercase tracking-wider">No Supervisors Assigned</h5>
                                            <p class="text-xs text-amber-700 mt-0.5">This candidate does not currently have any registered supervisory assignments.</p>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- 3. Examination Board: Internal & External Examiners --}}
                            <div class="space-y-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-2 h-6 bg-indigo-600 rounded-full"></div>
                                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-wider">Examination Board Evaluation Nodes</h4>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {{-- Internal Examiner Card --}}
                                    <div class="p-5 rounded-2xl border transition-colors"
                                         :class="activeCandidate.internal_examiner ? 'bg-white border-slate-200 hover:border-indigo-300' : 'bg-slate-50 border-dashed border-slate-200'">
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                                                  :class="activeCandidate.internal_examiner ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-slate-200 text-slate-600'">
                                                Internal Examiner
                                            </span>
                                            <span class="text-[9px] font-black uppercase tracking-wider"
                                                  :class="activeCandidate.internal_examiner ? 'text-emerald-600' : 'text-slate-400'"
                                                  x-text="activeCandidate.internal_examiner ? 'Assigned' : 'Not Assigned'"></span>
                                        </div>

                                        <template x-if="activeCandidate.internal_examiner">
                                            <div class="space-y-2">
                                                <h5 class="text-sm font-black text-slate-900" x-text="activeCandidate.internal_examiner.name"></h5>
                                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="activeCandidate.internal_examiner.program"></p>
                                                <div class="pt-2 text-xs text-slate-600 flex items-center gap-2">
                                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                    <span class="truncate" x-text="activeCandidate.internal_examiner.email"></span>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="!activeCandidate.internal_examiner">
                                            <div class="py-4 text-center">
                                                <p class="text-xs font-bold text-slate-400">No Internal Examiner assigned to this thesis yet.</p>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- External Examiner Card --}}
                                    <div class="p-5 rounded-2xl border transition-colors"
                                         :class="activeCandidate.external_examiner ? 'bg-white border-slate-200 hover:border-blue-300' : 'bg-slate-50 border-dashed border-slate-200'">
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"
                                                  :class="activeCandidate.external_examiner ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-slate-200 text-slate-600'">
                                                External Examiner
                                            </span>
                                            <span class="text-[9px] font-black uppercase tracking-wider"
                                                  :class="activeCandidate.external_examiner ? 'text-emerald-600' : 'text-slate-400'"
                                                  x-text="activeCandidate.external_examiner ? 'Assigned' : 'Not Assigned'"></span>
                                        </div>

                                        <template x-if="activeCandidate.external_examiner">
                                            <div class="space-y-2">
                                                <h5 class="text-sm font-black text-slate-900" x-text="activeCandidate.external_examiner.name"></h5>
                                                <p class="text-[11px] font-bold text-blue-700 uppercase tracking-wider" x-text="activeCandidate.external_examiner.institution"></p>
                                                <p class="text-[10px] font-medium text-slate-500 italic" x-show="activeCandidate.external_examiner.expertise" x-text="'Specialization: ' + activeCandidate.external_examiner.expertise"></p>
                                                
                                                <div class="pt-2 text-xs text-slate-600 space-y-1">
                                                    <p class="flex items-center gap-2">
                                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                        <span class="truncate" x-text="activeCandidate.external_examiner.email"></span>
                                                    </p>
                                                    <p class="flex items-center gap-2" x-show="activeCandidate.external_examiner.phone && activeCandidate.external_examiner.phone !== 'N/A'">
                                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                        <span x-text="activeCandidate.external_examiner.phone"></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="!activeCandidate.external_examiner">
                                            <div class="py-4 text-center">
                                                <p class="text-xs font-bold text-slate-400">No External Examiner assigned to this thesis yet.</p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- Modal Footer --}}
                        <div class="px-6 sm:px-8 py-5 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <template x-if="activeCandidate.thesis_id">
                                    <a :href="'/theses/' + activeCandidate.thesis_id" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-sm">
                                        <span>Open Full Thesis Record</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </template>
                                <a :href="'/admin/students/' + activeCandidate.id" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white hover:bg-slate-100 text-slate-800 border border-slate-200 rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-sm">
                                    <span>Student Profile</span>
                                </a>
                            </div>

                            <button type="button" @click="closeDossier()" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                                Close Dossier
                            </button>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </div>

</div>

<script>
function directorMilestoneHub(dossiers) {
    return {
        dossiers: dossiers,
        showModal: false,
        activeCandidate: null,

        openDossier(studentId) {
            if (this.dossiers && this.dossiers[studentId]) {
                this.activeCandidate = this.dossiers[studentId];
                this.showModal = true;
            }
        },

        closeDossier() {
            this.showModal = false;
            this.activeCandidate = null;
        }
    }
}
</script>
@endsection
