@extends('layouts.dashboard')

@section('header')
    Director Command Centre
@endsection

@section('content')
<div class="space-y-8 pb-12">

    {{-- Executive Command Hero Header --}}
    <div class="relative overflow-hidden rounded-3xl bg-slate-950 p-6 sm:p-10 shadow-2xl shadow-slate-950/30 text-white">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950/80"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/3 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-indigo-500/10 rounded-full translate-y-1/2 -translate-x-1/4 blur-3xl"></div>

        <div class="relative z-10 flex flex-col lg:flex-row gap-8 items-start lg:items-center justify-between">
            <div class="space-y-3 max-w-2xl">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="px-3 py-1 bg-white/10 border border-white/15 rounded-full flex items-center gap-2 backdrop-blur-md">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-300">Live Institutional Telemetry</span>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">ACETEL • National Open University of Nigeria</span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight leading-tight">
                    Director <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300">Command Centre</span>
                </h1>
                
                <p class="text-slate-300 text-xs sm:text-sm font-medium leading-relaxed">
                    Real-time academic oversight, faculty supervision workload radar, and postgraduate candidate research progression surveillance.
                </p>

                <div class="pt-2 flex flex-wrap items-center gap-3">
                    <a href="{{ route('director.milestones.index') }}" class="inline-flex items-center gap-2.5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-lg shadow-emerald-950/40 hover:-translate-y-0.5 active:translate-y-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span>Open Candidate Milestones Radar</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/15 text-white rounded-2xl text-xs font-bold border border-white/15 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Institutional Reports</span>
                    </a>
                </div>
            </div>

            {{-- Global Strategic Filter Box --}}
            <div class="w-full lg:w-auto bg-white/5 border border-white/10 rounded-3xl p-5 backdrop-blur-md shrink-0 sm:min-w-[340px]">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-white/10">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Institutional Filter Matrix</span>
                    @if(request()->hasAny(['search_student', 'program_id', 'cohort_id', 'level_id']))
                        <a href="{{ route('dashboard', ['role' => 'Director']) }}" class="text-[10px] font-bold text-emerald-400 hover:underline">Clear Filters</a>
                    @endif
                </div>

                <form action="{{ route('dashboard') }}" method="GET" class="space-y-2.5">
                    <input type="hidden" name="role" value="Director">
                    
                    <div class="relative">
                        <input type="text" name="search_student" value="{{ request('search_student') }}"
                            placeholder="Search name, matric or email..."
                            class="w-full bg-slate-900/80 border border-white/15 rounded-xl text-xs font-medium text-white placeholder-slate-400 focus:ring-emerald-500 focus:border-emerald-500 pl-9 pr-3 py-2.5">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <select name="program_id" class="w-full bg-slate-900/80 border border-white/15 rounded-xl text-xs font-bold text-white focus:ring-emerald-500 focus:border-emerald-500 px-2.5 py-2">
                            <option value="" class="bg-slate-900">All Programs</option>
                            @foreach($available_programs as $p)
                                <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }} class="bg-slate-900">{{ $p->code }}</option>
                            @endforeach
                        </select>

                        <select name="cohort_id" class="w-full bg-slate-900/80 border border-white/15 rounded-xl text-xs font-bold text-white focus:ring-emerald-500 focus:border-emerald-500 px-2.5 py-2">
                            <option value="" class="bg-slate-900">All Cohorts</option>
                            @foreach($available_cohorts as $c)
                                <option value="{{ $c->id }}" {{ request('cohort_id') == $c->id ? 'selected' : '' }} class="bg-slate-900">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <select name="level_id" class="w-full bg-slate-900/80 border border-white/15 rounded-xl text-xs font-bold text-white focus:ring-emerald-500 focus:border-emerald-500 px-2.5 py-2">
                                <option value="" class="bg-slate-900">All Degree Levels</option>
                                @foreach(\App\Models\Level::all() as $l)
                                    <option value="{{ $l->id }}" {{ request('level_id') == $l->id ? 'selected' : '' }} class="bg-slate-900">{{ $l->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-black text-xs uppercase tracking-wider transition-all shadow-md">
                            Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- KPI Metrics Row (6 Command Cards) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        @php
            $kpis = [
                [
                    'label' => 'Total Candidates',
                    'value' => $stats['total_students'],
                    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'color' => 'emerald',
                    'sub' => 'Registered Cohorts',
                    'link' => route('director.milestones.index'),
                ],
                [
                    'label' => 'Active Theses',
                    'value' => $stats['active_students'],
                    'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    'color' => 'teal',
                    'sub' => 'Mid-stage Research',
                    'link' => route('director.milestones.index'),
                ],
                [
                    'label' => 'Graduated',
                    'value' => $stats['graduated_students'],
                    'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
                    'color' => 'blue',
                    'sub' => 'Completed Programs',
                    'link' => null,
                ],
                [
                    'label' => 'Defences Scheduled',
                    'value' => $upcoming_defences->count(),
                    'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                    'color' => 'indigo',
                    'sub' => 'Viva & Proposals',
                    'link' => null,
                ],
                [
                    'label' => 'Unread Messages',
                    'value' => $stats['unread_messages'],
                    'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                    'color' => 'rose',
                    'sub' => 'Attention Required',
                    'link' => route('inbox.index'),
                ],
                [
                    'label' => 'Comm Health',
                    'value' => $comm_health['health_score'] . '%',
                    'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    'color' => 'amber',
                    'sub' => 'Faculty Response Index',
                    'link' => null,
                ],
            ];

            $colorPalette = [
                'emerald' => ['bg' => 'bg-emerald-50', 'icon' => 'text-emerald-600', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-100', 'shadow' => 'shadow-emerald-600/10'],
                'teal'    => ['bg' => 'bg-teal-50',    'icon' => 'text-teal-600',    'text' => 'text-teal-700',    'ring' => 'ring-teal-100',    'shadow' => 'shadow-teal-600/10'],
                'blue'    => ['bg' => 'bg-blue-50',    'icon' => 'text-blue-600',    'text' => 'text-blue-700',    'ring' => 'ring-blue-100',    'shadow' => 'shadow-blue-600/10'],
                'indigo'  => ['bg' => 'bg-indigo-50',  'icon' => 'text-indigo-600',  'text' => 'text-indigo-700',  'ring' => 'ring-indigo-100',  'shadow' => 'shadow-indigo-600/10'],
                'rose'    => ['bg' => 'bg-rose-50',    'icon' => 'text-rose-600',    'text' => 'text-rose-700',    'ring' => 'ring-rose-100',    'shadow' => 'shadow-rose-600/10'],
                'amber'   => ['bg' => 'bg-amber-50',   'icon' => 'text-amber-600',   'text' => 'text-amber-700',   'ring' => 'ring-amber-100',   'shadow' => 'shadow-amber-600/10'],
            ];
        @endphp

        @foreach($kpis as $kpi)
            @php $p = $colorPalette[$kpi['color']]; @endphp
            <div class="group">
                @if($kpi['link'])
                    <a href="{{ $kpi['link'] }}" class="block bg-white border border-slate-100 rounded-3xl p-4 sm:p-5 shadow-sm hover:shadow-lg {{ $p['shadow'] }} transition-all duration-300 hover:-translate-y-1">
                @else
                    <div class="bg-white border border-slate-100 rounded-3xl p-4 sm:p-5 shadow-sm">
                @endif
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 {{ $p['bg'] }} rounded-2xl flex items-center justify-center ring-4 {{ $p['ring'] }} shrink-0">
                                <svg class="w-5 h-5 {{ $p['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $kpi['icon'] }}"/></svg>
                            </div>
                            @if($kpi['link'])
                                <svg class="w-3.5 h-3.5 text-slate-300 group-hover:{{ $p['icon'] }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            @endif
                        </div>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-none">{{ $kpi['value'] }}</p>
                        <p class="text-[10px] font-black uppercase tracking-wider {{ $p['text'] }} mt-2 truncate">{{ $kpi['label'] }}</p>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5 truncate">{{ $kpi['sub'] }}</p>
                @if($kpi['link'])
                    </a>
                @else
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Highlight Banner: Dedicated Milestone Radar Hub --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-900 via-teal-900 to-slate-900 p-6 sm:p-7 shadow-xl shadow-emerald-950/20 text-white">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5 relative z-10">
            <div class="flex items-start sm:items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 bg-emerald-400/20 text-emerald-300 border border-emerald-400/30 rounded-md text-[9px] font-black uppercase tracking-widest">New Milestone Hub</span>
                        <span class="text-xs font-bold text-slate-300">Phase-by-Phase Oversight</span>
                    </div>
                    <h3 class="text-base sm:text-lg font-black text-white mt-1">Explore Candidates Categorized by Active Research Milestone</h3>
                    <p class="text-xs text-slate-300 mt-0.5 max-w-2xl">
                        Click on any milestone to view only candidates at that stage, and click any candidate's name to view their full thesis abstract, supervisory team, internal examiner, and external examiner.
                    </p>
                </div>
            </div>

            <a href="{{ route('director.milestones.index') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-white text-slate-900 hover:bg-emerald-50 rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-lg shrink-0">
                <span>View Milestone Hub</span>
                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>

    {{-- Stalled Students / Grace Period Alert Banner --}}
    @if(isset($stalled_students) && $stalled_students->count() > 0)
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-600 to-amber-500 p-px shadow-xl shadow-amber-600/20">
        <div class="bg-gradient-to-r from-amber-700 to-amber-600 rounded-3xl p-5 sm:p-7 flex flex-col md:flex-row items-start md:items-center gap-5 justify-between relative overflow-hidden">
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-[9px] font-black uppercase tracking-widest text-amber-200">Grace Period Alert</span>
                    <h3 class="text-lg sm:text-xl font-black text-white mt-0.5 tracking-tight">{{ $stalled_students->count() }} Stalled Candidates</h3>
                    <p class="text-amber-100 text-xs font-medium">These candidates have had zero milestone progression for over 14 consecutive days.</p>
                </div>
            </div>
            
            <div class="relative z-10 w-full md:w-auto" x-data="{ open: false }">
                <button @click="open = !open" class="w-full md:w-auto justify-center px-5 py-3 bg-white text-amber-800 hover:bg-amber-50 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all flex items-center gap-2 shadow-lg">
                    <span>Inspect Stalled Candidates</span>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 top-full mt-3 w-full sm:w-96 max-w-sm bg-white rounded-3xl shadow-2xl border border-slate-100 z-50 overflow-hidden" style="display:none">
                    <div class="p-4 bg-amber-50 border-b border-amber-100">
                        <p class="text-xs font-black text-amber-900 uppercase tracking-wider">Candidate Interventions</p>
                    </div>
                    <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                        @foreach($stalled_students as $stalled)
                        <div class="p-4 hover:bg-amber-50/50 transition-colors">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-xs font-black text-slate-800">{{ $stalled->thesis->student->user->name }}</p>
                                <span class="text-[9px] font-black text-amber-700 bg-amber-100 px-2 py-0.5 rounded">{{ $stalled->updated_at->diffInDays() }}d stalled</span>
                            </div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider truncate">{{ $stalled->template->name }}</p>
                            <div class="flex items-center gap-3 mt-2.5">
                                <a href="{{ route('inbox.compose', ['user_id' => $stalled->thesis->student->user_id]) }}" class="text-[10px] font-black text-amber-700 hover:underline">Message Student</a>
                                <span class="text-slate-300">•</span>
                                <a href="{{ route('inbox.compose', ['user_id' => $stalled->thesis->assignments->first()->supervisor->user_id ?? '']) }}" class="text-[10px] font-black text-emerald-700 hover:underline">Nudge Supervisor</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Main Executive Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- Left Column: Core Institutional Telemetry (8-col) --}}
        <div class="lg:col-span-8 space-y-8">

            {{-- Program Performance Comparison Chart --}}
            <div class="bg-white border border-slate-100 rounded-3xl p-6 sm:p-8 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-7 bg-emerald-600 rounded-full"></div>
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">Program Performance Comparison</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Total Enrollment vs Proposals In-Flight</p>
                        </div>
                    </div>
                </div>
                <div class="relative min-h-[300px]">
                    <canvas id="programComparisonChart"></canvas>
                </div>
            </div>

            {{-- Upcoming Academic Defences --}}
            <div class="bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-sm">
                <div class="px-6 sm:px-8 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 tracking-tight">Scheduled Academic Defences</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Upcoming Viva, Proposal & Seminar Nodes</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-xl text-[10px] font-black uppercase tracking-wider">
                        {{ $upcoming_defences->count() }} Scheduled
                    </span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($upcoming_defences as $defence)
                        <div class="p-6 flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6 hover:bg-slate-50/60 transition-colors group">
                            <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-100 flex flex-col items-center justify-center shrink-0 group-hover:bg-blue-100 transition-colors">
                                <span class="text-[10px] font-black text-blue-700 uppercase tracking-tighter">{{ $defence->schedule_start->format('M') }}</span>
                                <span class="text-xl font-black text-blue-900 leading-none">{{ $defence->schedule_start->format('d') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <p class="text-sm font-black text-slate-900 truncate">{{ $defence->thesis->student->user->name }}</p>
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[9px] font-black uppercase tracking-wider border border-slate-200">
                                        {{ str_replace('_', ' ', $defence->type) }}
                                    </span>
                                </div>
                                <p class="text-xs font-bold text-emerald-700 truncate italic">"{{ $defence->thesis->title }}"</p>
                                <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest flex items-center gap-1.5">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $defence->location ?? 'Venue TBA' }}
                                    <span class="text-slate-300">•</span>
                                    {{ $defence->thesis->student->program->code }} • {{ $defence->thesis->student->level->name ?? 'N/A' }}
                                </p>
                            </div>
                            <a href="{{ route('theses.show', $defence->thesis) }}" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-slate-900 hover:text-white text-slate-700 border border-slate-200 rounded-xl text-xs font-bold transition-all shadow-sm">
                                <span>Inspect</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    @empty
                        <div class="py-16 text-center">
                            <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-xs font-bold text-slate-400">No upcoming academic defences currently scheduled.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Faculty Supervision Capacity Radar --}}
            <div class="bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-sm">
                <div class="px-6 sm:px-8 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 tracking-tight">Supervisor Workload Capacity</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Faculty Candidate Distribution & Allocation</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($supervisor_workload as $sv)
                        @php
                            $svBg = $sv['status'] === 'overloaded' ? 'bg-rose-50/60 border-rose-200' : ($sv['status'] === 'near_capacity' ? 'bg-amber-50/60 border-amber-200' : 'bg-slate-50 border-slate-200/80');
                            $svBar = $sv['status'] === 'overloaded' ? 'bg-rose-500' : ($sv['status'] === 'near_capacity' ? 'bg-amber-500' : 'bg-emerald-600');
                            $svBadge = $sv['status'] === 'overloaded' ? 'bg-rose-100 text-rose-800 border-rose-200' : ($sv['status'] === 'near_capacity' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200');
                        @endphp
                        <div class="p-5 rounded-2xl border {{ $svBg }} transition-all hover:shadow-sm">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center font-black text-slate-700 text-xs shadow-sm">
                                        {{ substr($sv['name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-slate-900 leading-tight">{{ $sv['name'] }}</h4>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">{{ $sv['program'] }}</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider border {{ $svBadge }}">
                                    {{ str_replace('_', ' ', $sv['status']) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between mb-3 text-xs">
                                <div class="flex items-center gap-4">
                                    <div>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase">MSc:</span>
                                        <span class="font-black text-slate-800 ml-1">{{ $sv['msc_count'] }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase">PhD:</span>
                                        <span class="font-black text-slate-800 ml-1">{{ $sv['phd_count'] }}</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-[10px] font-black text-emerald-700">{{ $sv['load_percentage'] }}% Used</span>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <div class="w-full h-2 bg-white rounded-full overflow-hidden shadow-inner">
                                    <div class="{{ $svBar }} h-full rounded-full transition-all duration-700" style="width: {{ $sv['load_percentage'] }}%"></div>
                                </div>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest text-right">{{ $sv['total'] }} of {{ $sv['max_load'] }} max candidates</p>
                            </div>
                        </div>
                    @empty
                        <p class="col-span-2 py-8 text-center text-slate-400 font-medium text-xs">No active supervisor telemetry captured.</p>
                    @endforelse
                </div>
            </div>

            {{-- Internal Examiner Evaluation Capacity --}}
            <div class="bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-sm">
                <div class="px-6 sm:px-8 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 tracking-tight">Internal Examiner Capacity</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Examination Board Load Surveillance</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($examiner_workload ?? [] as $ev)
                        @php
                            $evBg = $ev['status'] === 'overloaded' ? 'bg-rose-50/60 border-rose-200' : ($ev['status'] === 'near_capacity' ? 'bg-amber-50/60 border-amber-200' : 'bg-slate-50 border-slate-200/80');
                            $evBar = $ev['status'] === 'overloaded' ? 'bg-rose-500' : ($ev['status'] === 'near_capacity' ? 'bg-amber-500' : 'bg-indigo-600');
                            $evBadge = $ev['status'] === 'overloaded' ? 'bg-rose-100 text-rose-800 border-rose-200' : ($ev['status'] === 'near_capacity' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200');
                        @endphp
                        <div class="p-5 rounded-2xl border {{ $evBg }} transition-all hover:shadow-sm">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center font-black text-slate-700 text-xs shadow-sm">
                                        {{ substr($ev['name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-slate-900 leading-tight">{{ $ev['name'] }}</h4>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">{{ $ev['program'] }}</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider border {{ $evBadge }}">
                                    {{ str_replace('_', ' ', $ev['status']) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between mb-3 text-xs">
                                <div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase">Evaluations:</span>
                                    <span class="font-black text-slate-800 ml-1">{{ $ev['total'] }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-black text-indigo-700">{{ $ev['load_percentage'] }}% Used</span>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <div class="w-full h-2 bg-white rounded-full overflow-hidden shadow-inner">
                                    <div class="{{ $evBar }} h-full rounded-full transition-all duration-700" style="width: {{ $ev['load_percentage'] }}%"></div>
                                </div>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest text-right">{{ $ev['total'] }} of {{ $ev['max_load'] }} max evaluations</p>
                            </div>
                        </div>
                    @empty
                        <p class="col-span-2 py-8 text-center text-slate-400 font-medium text-xs">No internal examiner workloads recorded.</p>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Right Column: Tactical Intelligence & Leaderboards (4-col) --}}
        <div class="lg:col-span-4 space-y-8">

            {{-- Milestone Progression Pipeline Chart --}}
            <div class="bg-white border border-slate-100 rounded-3xl p-6 sm:p-8 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2.5">
                        <div class="w-2 h-6 bg-emerald-600 rounded-full"></div>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Milestone Pipeline</h3>
                    </div>
                    <a href="{{ route('director.milestones.index') }}" class="text-[10px] font-black text-emerald-700 uppercase tracking-wider hover:underline">Inspect →</a>
                </div>
                <div class="relative min-h-[260px]">
                    <canvas id="milestoneFunnelChart"></canvas>
                </div>
            </div>

            {{-- Delayed Milestones Alert Stack --}}
            <div class="bg-white border border-slate-100 rounded-3xl p-6 sm:p-8 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Delayed Milestones</h3>
                    </div>
                    @if(count($delayed_students) > 0)
                        <span class="bg-rose-100 text-rose-800 text-[10px] font-black px-2 py-0.5 rounded-full border border-rose-200">{{ count($delayed_students) }}</span>
                    @endif
                </div>

                <div class="space-y-3">
                    @forelse($delayed_students as $milestone)
                        <div class="p-3.5 bg-rose-50/70 border border-rose-100 rounded-2xl hover:border-rose-300 transition-colors">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <p class="text-xs font-black text-slate-900 leading-tight">{{ $milestone->thesis->student->user->name }}</p>
                                <span class="px-2 py-0.5 bg-rose-100 text-rose-800 border border-rose-200 rounded text-[9px] font-black shrink-0">
                                    {{ $milestone->due_date->diffInDays(now()) }}d Late
                                </span>
                            </div>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ $milestone->template->name }}</p>
                            <div class="mt-2 pt-2 border-t border-rose-100 flex items-center justify-between">
                                <a href="{{ route('inbox.compose', ['user_id' => $milestone->thesis->student->user_id]) }}" class="text-[9px] font-black text-rose-700 hover:underline uppercase tracking-wider">
                                    Message Student →
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center">
                            <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center mx-auto mb-2 text-emerald-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-xs font-bold text-emerald-700">All submissions on schedule!</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Faculty Top Mentors --}}
            <div class="bg-white border border-slate-100 rounded-3xl p-6 sm:p-8 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2.5">
                        <div class="w-2 h-6 bg-teal-600 rounded-full"></div>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Top Faculty Mentors</h3>
                    </div>
                    <div class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse($faculty_leaderboard as $idx => $mentor)
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-lg {{ $idx == 0 ? 'bg-amber-100 text-amber-800 ring-2 ring-amber-200' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center text-xs font-black shrink-0">
                                #{{ $idx + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-black text-slate-800 truncate leading-none">{{ $mentor['name'] }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[9px] font-black uppercase text-teal-700">
                                        {{ $mentor['graduated_count'] }} Graduated
                                    </span>
                                    <span class="text-slate-300">•</span>
                                    <span class="text-[9px] font-bold text-slate-400">
                                        {{ $mentor['response_time_display'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-slate-400 font-medium text-xs py-6">Faculty ratings computing...</p>
                    @endforelse
                </div>
            </div>

            {{-- Cohort Completion Monitoring --}}
            <div class="bg-white border border-slate-100 rounded-3xl p-6 sm:p-8 shadow-sm">
                <div class="flex items-center gap-2.5 mb-6">
                    <div class="w-2 h-6 bg-indigo-600 rounded-full"></div>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Cohort Progression</h3>
                </div>

                <div class="space-y-4">
                    @forelse($cohort_monitoring as $cohort)
                        @php $rate = $cohort['completion_rate'] === 'N/A' ? 0 : (int) $cohort['completion_rate']; @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1.5 text-xs">
                                <div>
                                    <span class="font-black text-slate-900">{{ $cohort['name'] }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold ml-1">({{ $cohort['students_count'] }} candidates)</span>
                                </div>
                                <span class="font-black text-emerald-700">{{ $cohort['completion_rate'] }}</span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-600 rounded-full transition-all duration-700" style="width: {{ $rate < 3 ? '3' : $rate }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-slate-400 font-medium text-xs py-6">No active cohorts recorded.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    {{-- Institutional Candidate Registry Table --}}
    <div class="bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-sm">
        <div class="px-6 sm:px-8 py-5 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">Institutional Candidate Registry</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Global Status Tracking & Progress Audit</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-700 shadow-sm">
                    {{ $students->total() }} Candidates
                </span>
                <a href="{{ route('director.milestones.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                    <span>View by Milestone</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="px-6 sm:px-8 py-4">Candidate</th>
                        <th class="px-4 py-4">Program & Level</th>
                        <th class="px-4 py-4">Cohort</th>
                        <th class="px-4 py-4">Active Phase</th>
                        <th class="px-4 py-4">Degree Progress</th>
                        <th class="px-6 sm:px-8 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                        <tr class="hover:bg-slate-50/70 transition-colors group">
                            <td class="px-6 sm:px-8 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-100 to-teal-100 border border-emerald-200 text-emerald-800 flex items-center justify-center font-black text-sm shrink-0 shadow-sm">
                                        {{ substr($student?->user?->name ?? '?', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-900 group-hover:text-emerald-700 transition-colors leading-tight">
                                            {{ $student?->user?->name ?? 'N/A' }}
                                        </p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">
                                            {{ $student->student_id_number }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <span class="inline-flex px-2 py-0.5 bg-slate-100 text-slate-700 rounded-lg text-[10px] font-black uppercase tracking-wider border border-slate-200">
                                    {{ $student?->program?->code ?? 'N/A' }}
                                </span>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">
                                    {{ $student->level->name ?? ($student->isPhd() ? 'PhD' : ($student->isMsc() ? 'MSc' : 'Research')) }}
                                </p>
                            </td>

                            <td class="px-4 py-4">
                                <p class="text-xs font-bold text-slate-700">{{ $student->cohort->name ?? 'N/A' }}</p>
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $student->enrollment_status === 'active' ? 'bg-emerald-500' : 'bg-slate-300' }} shrink-0"></span>
                                    <span class="text-xs font-bold text-slate-700 truncate max-w-[180px]">
                                        {{ $student->thesis->currentMilestone->template->name ?? 'Research Topic Pending' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                @php $pct = $student->thesis->progress_percentage ?? 0; @endphp
                                <div class="w-28 space-y-1">
                                    <div class="flex justify-between text-[9px] font-black uppercase tracking-wider text-slate-400">
                                        <span>Progress</span>
                                        <span class="text-slate-700">{{ $pct }}%</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full {{ $pct >= 80 ? 'bg-emerald-600' : ($pct >= 40 ? 'bg-teal-600' : 'bg-slate-400') }} rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 sm:px-8 py-4 text-right">
                                <a href="{{ route('admin.students.show', $student->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-wider transition-all shadow-sm active:scale-95">
                                    <span>Profile</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">No candidates match your current search/filter criteria</p>
                                <a href="{{ route('dashboard', ['role' => 'Director']) }}" class="text-emerald-700 text-xs font-black uppercase tracking-wider mt-2 inline-block hover:underline">Clear all filters →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="px-6 sm:px-8 py-4 bg-slate-50/50 border-t border-slate-100">
                {{ $students->links() }}
            </div>
        @endif
    </div>

    {{-- Institutional Resource Center --}}
    @if(isset($document_templates) && $document_templates->count() > 0)
    <div class="bg-white border border-slate-100 rounded-3xl p-6 sm:p-8 shadow-sm">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">Institutional Resource Center</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Authorized Protocols & Guidelines</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($document_templates as $template)
            <a href="{{ route('templates.download', $template) }}" class="flex items-center gap-4 p-4 bg-slate-50 hover:bg-emerald-50/60 rounded-2xl border border-slate-100 hover:border-emerald-200 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-400 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-black text-slate-800 truncate leading-none uppercase">{{ $template->title }}</p>
                    <p class="text-[9px] font-bold text-slate-400 mt-1.5 uppercase tracking-wider">v{{ $template->version }} • {{ strtoupper($template->type) }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.font.family = "'Figtree','Inter',sans-serif";
    Chart.defaults.font.weight = '700';
    Chart.defaults.color = '#94a3b8';

    // Program Comparison Chart
    const progEl = document.getElementById('programComparisonChart');
    if (progEl) {
        const progCtx = progEl.getContext('2d');
        new Chart(progCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($programs_performance, 'code')) !!},
                datasets: [
                    {
                        label: 'Total Candidates',
                        data: {!! json_encode(array_column($programs_performance, 'total_students')) !!},
                        backgroundColor: '#059669',
                        borderRadius: 8,
                        barThickness: 28,
                    },
                    {
                        label: 'Proposals / Research Active',
                        data: {!! json_encode(array_column($programs_performance, 'proposal_stage')) !!},
                        backgroundColor: '#cbd5e1',
                        borderRadius: 8,
                        barThickness: 28,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { padding: 16, boxWidth: 10, usePointStyle: true, pointStyle: 'circle', font: { size: 11, weight: '700' } }
                    }
                },
                scales: {
                    y: { grid: { color: '#f1f5f9' }, border: { display: false }, ticks: { font: { size: 11 } } },
                    x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    }

    // Milestone Pipeline Funnel Chart
    const funnelEl = document.getElementById('milestoneFunnelChart');
    if (funnelEl) {
        const funnelCtx = funnelEl.getContext('2d');
        new Chart(funnelCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($milestone_pipeline, 'name')) !!},
                datasets: [{
                    label: 'Candidates at Stage',
                    data: {!! json_encode(array_column($milestone_pipeline, 'count')) !!},
                    backgroundColor: 'rgba(5, 150, 105, 0.15)',
                    borderColor: '#059669',
                    borderWidth: 2,
                    borderRadius: 6,
                    barThickness: 16,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { padding: 10, cornerRadius: 8, bodyFont: { weight: '700' } }
                },
                scales: {
                    x: { display: false, min: 0 },
                    y: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 10, weight: '700' } } }
                }
            }
        });
    }
});
</script>
@endsection
