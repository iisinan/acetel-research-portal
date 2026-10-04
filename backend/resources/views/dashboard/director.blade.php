@extends('layouts.dashboard')

@section('header')
    Director Command Centre
@endsection

@section('content')
<div class="space-y-10 pb-10">

    {{-- Executive Hero Header --}}
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 p-10 shadow-2xl shadow-slate-900/30">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-brand-500/10 rounded-full -translate-y-1/2 translate-x-1/3 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-indigo-500/10 rounded-full translate-y-1/2 -translate-x-1/4 blur-3xl"></div>

        <div class="relative z-10 flex flex-col lg:flex-row gap-10 items-start lg:items-center justify-between print:hidden">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="px-3 py-1.5 bg-white/5 border border-white/10 rounded-full flex items-center gap-2 backdrop-blur-sm">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-[10px] font-black uppercase tracking-widest text-white/60">Live Institutional Data</span>
                    </div>
                </div>
                <h1 class="text-4xl font-black text-white tracking-tight leading-tight">
                    Director<br><span class="text-brand-400">Command Centre</span>
                </h1>
                <p class="mt-3 text-slate-400 font-medium text-sm max-w-lg">
                    Real-time institutional intelligence. Monitor research progress, staff capacity, and academic pipeline at a glance.
                </p>
            </div>

            {{-- Global Strategic Filters --}}
            <form action="{{ route('dashboard') }}" method="GET" class="w-full lg:w-auto">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="col-span-2 lg:col-span-4 relative">
                        <input type="text" name="search_student" value="{{ request('search_student') }}"
                            placeholder="Search by name, email or matric no..."
                            class="w-full bg-white/5 border border-white/10 rounded-2xl text-sm font-medium text-white placeholder-slate-500 focus:ring-brand-500 focus:border-brand-400 pl-12 pr-4 py-3.5 transition-colors backdrop-blur-sm">
                        <svg class="w-4 h-4 text-slate-500 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <div class="relative">
                        <select name="program_id" class="w-full appearance-none bg-white/5 border border-white/10 rounded-xl text-xs font-bold text-white focus:ring-brand-500 focus:border-brand-400 px-3 py-3 transition-colors backdrop-blur-sm">
                            <option value="" class="bg-slate-800">All Programs</option>
                            @foreach($available_programs as $p)
                                <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }} class="bg-slate-800">{{ $p->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <select name="cohort_id" class="w-full appearance-none bg-white/5 border border-white/10 rounded-xl text-xs font-bold text-white focus:ring-brand-500 focus:border-brand-400 px-3 py-3 transition-colors backdrop-blur-sm">
                            <option value="" class="bg-slate-800">All Cohorts</option>
                            @foreach($available_cohorts as $c)
                                <option value="{{ $c->id }}" {{ request('cohort_id') == $c->id ? 'selected' : '' }} class="bg-slate-800">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <select name="level_id" class="w-full appearance-none bg-white/5 border border-white/10 rounded-xl text-xs font-bold text-white focus:ring-brand-500 focus:border-brand-400 px-3 py-3 transition-colors backdrop-blur-sm">
                            <option value="" class="bg-slate-800">All Levels</option>
                            @foreach(\App\Models\Level::all() as $l)
                                <option value="{{ $l->id }}" {{ request('level_id') == $l->id ? 'selected' : '' }} class="bg-slate-800">{{ $l->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 py-3 bg-brand-500 hover:bg-brand-600 text-white rounded-xl font-black text-xs tracking-widest uppercase transition-all shadow-lg shadow-brand-500/30">
                            Filter
                        </button>
                        <a href="{{ route('dashboard') }}" class="px-4 py-3 bg-white/5 hover:bg-white/10 text-slate-400 rounded-xl transition-all border border-white/10 flex items-center justify-center" title="Reset">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- KPI Metrics Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @php
            $kpis = [
                ['label' => 'Total Candidates', 'value' => $stats['total_students'], 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color' => 'brand', 'sub' => 'Registered', 'link' => route('coordinator.students.index')],
                ['label' => 'Active Proposals', 'value' => $stats['active_students'], 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'emerald', 'sub' => 'Mid-stage Theses', 'link' => route('admin.cohorts.index')],
                ['label' => 'Graduated', 'value' => $stats['graduated_students'], 'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z', 'color' => 'blue', 'sub' => 'Programs Completed', 'link' => null],
                ['label' => 'Unread Messages', 'value' => $stats['unread_messages'], 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color' => 'rose', 'sub' => 'Requires Attention', 'link' => route('inbox.index')],
                ['label' => 'Comm Health', 'value' => $comm_health['health_score'] . '%', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'color' => 'indigo', 'sub' => 'Response Rate', 'link' => null],
            ];
            $colorMap = [
                'brand'  => ['bg' => 'bg-brand-50',  'icon' => 'text-brand-600',  'text' => 'text-brand-700',  'ring' => 'ring-brand-100',  'glow' => 'shadow-brand-500/20'],
                'emerald'=> ['bg' => 'bg-emerald-50', 'icon' => 'text-emerald-600','text' => 'text-emerald-700','ring' => 'ring-emerald-100', 'glow' => 'shadow-emerald-500/20'],
                'blue'   => ['bg' => 'bg-blue-50',   'icon' => 'text-blue-600',   'text' => 'text-blue-700',   'ring' => 'ring-blue-100',   'glow' => 'shadow-blue-500/20'],
                'rose'   => ['bg' => 'bg-rose-50',   'icon' => 'text-rose-600',   'text' => 'text-rose-700',   'ring' => 'ring-rose-100',   'glow' => 'shadow-rose-500/20'],
                'indigo' => ['bg' => 'bg-indigo-50', 'icon' => 'text-indigo-600', 'text' => 'text-indigo-700', 'ring' => 'ring-indigo-100', 'glow' => 'shadow-indigo-500/20'],
            ];
        @endphp
        @foreach($kpis as $kpi)
            @php $c = $colorMap[$kpi['color']]; @endphp
            <div class="group">
                @if($kpi['link'])
                    <a href="{{ $kpi['link'] }}" class="block bg-white border border-slate-100 rounded-3xl p-6 shadow-sm hover:shadow-lg {{ $c['glow'] }} transition-all duration-300 hover:-translate-y-1">
                @else
                    <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm">
                @endif
                        <div class="flex items-start justify-between mb-6">
                            <div class="w-12 h-12 {{ $c['bg'] }} rounded-2xl flex items-center justify-center ring-4 {{ $c['ring'] }}">
                                <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $kpi['icon'] }}"/></svg>
                            </div>
                            @if($kpi['link'])
                                <svg class="w-4 h-4 text-slate-300 group-hover:{{ $c['icon'] }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            @endif
                        </div>
                        <p class="text-4xl font-black text-slate-900 tracking-tight">{{ $kpi['value'] }}</p>
                        <p class="text-[10px] font-black uppercase tracking-widest {{ $c['text'] }} mt-3">{{ $kpi['label'] }}</p>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $kpi['sub'] }}</p>
                @if($kpi['link'])
                    </a>
                @else
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Stalled Students / Grace Period Alert Banner --}}
    @if(isset($stalled_students) && $stalled_students->count() > 0)
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-600 to-amber-500 p-px shadow-2xl shadow-amber-600/30">
        <div class="bg-gradient-to-r from-amber-700 to-amber-600 rounded-3xl p-8 flex flex-col md:flex-row items-start md:items-center gap-8 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10" style="background-image: url(\"data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='white' fill-opacity='1' fill-rule='evenodd'%3E%3Ccircle cx='3' cy='3' r='1'/%3E%3C/g%3E%3C/svg%3E\");"></div>
            <div class="flex items-center gap-5 flex-1 relative z-10">
                <div class="w-16 h-14 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <span class="text-[9px] font-black uppercase tracking-widest text-amber-200">Grace Period Breach</span>
                    <h3 class="text-2xl font-black text-white mt-1 tracking-tight">{{ $stalled_students->count() }} Stalled Candidates</h3>
                    <p class="text-amber-100 text-sm font-medium mt-1">These students have had zero milestone activity for over 14 days.</p>
                </div>
            </div>
            
            <div class="relative z-10 group/stalled" x-data="{ open: false }">
                <button @click="open = !open" class="px-8 py-4 bg-white text-amber-700 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all flex items-center gap-3 shadow-lg">
                    Intervene Now
                    <svg class="w-3 h-3" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 top-full mt-3 w-96 bg-white rounded-3xl shadow-2xl border border-slate-100 z-50 overflow-hidden" style="display:none">
                    <div class="max-h-80 overflow-y-auto divide-y divide-slate-50">
                        @foreach($stalled_students as $stalled)
                        <div class="px-6 py-4 hover:bg-amber-50 transition-colors">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm font-black text-slate-800">{{ $stalled->thesis->student->user->name }}</p>
                                <span class="text-[9px] font-black text-amber-600 bg-amber-100 px-2 py-0.5 rounded">{{ $stalled->updated_at->diffInDays() }} days stuck</span>
                            </div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate">{{ $stalled->template->name }}</p>
                            <div class="flex items-center gap-2 mt-3">
                                <a href="{{ route('inbox.compose', ['user_id' => $stalled->thesis->student->user_id]) }}" class="text-[9px] font-black text-amber-600 hover:underline">Message Student</a>
                                <span class="text-slate-300">|</span>
                                <a href="{{ route('inbox.compose', ['user_id' => $stalled->thesis->assignments->first()->supervisor->user_id ?? '']) }}" class="text-[9px] font-black text-amber-600 hover:underline">Nudge Supervisor</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- Left: Core Intelligence (8-col) --}}
        <div class="lg:col-span-8 space-y-8">

            {{-- Institutional Performance Chart --}}
            <div class="bg-white border border-slate-100 rounded-3xl p-8 shadow-sm">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div class="w-1.5 h-8 bg-brand-500 rounded-full"></div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">Performance Comparison</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">By Academic Program</p>
                        </div>
                    </div>
                </div>
                <div class="relative min-h-[320px]">
                    <canvas id="programComparisonChart"></canvas>
                </div>
            </div>

            {{-- Defense Schedule --}}
            <div class="bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-sm">
                <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Upcoming Defense Schedule</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Institutional Assessment Nodes</p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-slate-50">
                    @forelse($upcoming_defences as $defence)
                        <div class="px-8 py-6 flex items-center gap-6 hover:bg-slate-50/50 transition-colors group">
                            <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-100 flex flex-col items-center justify-center shrink-0 group-hover:bg-blue-100 transition-colors">
                                <span class="text-xs font-black text-blue-700 uppercase tracking-tighter">{{ $defence->schedule_start->format('M') }}</span>
                                <span class="text-xl font-black text-blue-900 leading-none">{{ $defence->schedule_start->format('d') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="text-base font-black text-slate-900 truncate">{{ $defence->thesis->student->user->name }}</p>
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[8px] font-black uppercase tracking-widest border border-slate-200">{{ str_replace('_', ' ', $defence->type) }}</span>
                                </div>
                                <p class="text-xs font-bold text-brand-600 truncate italic">"{{ $defence->thesis->title }}"</p>
                                <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest flex items-center gap-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $defence->location ?? 'Venue TBA' }}
                                    <span class="text-slate-200">·</span>
                                    {{ $defence->thesis->student->program->code }} · {{ $defence->thesis->student->level->name ?? 'N/A' }}
                                </p>
                            </div>
                            <a href="{{ route('theses.show', $defence->thesis) }}" class="shrink-0 w-10 h-10 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-brand-600 hover:text-white hover:border-brand-600 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    @empty
                        <div class="py-20 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-400">No upcoming defences scheduled.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Staff Capacity Grid --}}
            <div class="bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-sm">
                <div class="px-8 py-6 border-b border-slate-50 flex items-center gap-4 bg-slate-50/30">
                    <div class="w-10 h-10 rounded-2xl bg-brand-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Supervisor Capacity</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Real-time Load Distribution</p>
                    </div>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-5">
                    @forelse($supervisor_workload as $sv)
                        @php
                            $svBg = $sv['status'] === 'overloaded' ? 'bg-rose-50 border-rose-200' : ($sv['status'] === 'near_capacity' ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-100');
                            $svBar = $sv['status'] === 'overloaded' ? 'bg-rose-500' : ($sv['status'] === 'near_capacity' ? 'bg-amber-500' : 'bg-brand-500');
                            $svBadge = $sv['status'] === 'overloaded' ? 'bg-rose-100 text-rose-700 border-rose-200' : ($sv['status'] === 'near_capacity' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200');
                        @endphp
                        <div class="p-6 rounded-2xl border {{ $svBg }} transition-all duration-300 hover:shadow-md group">
                            <div class="flex items-start justify-between mb-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center font-black text-slate-600 text-sm shadow-sm group-hover:bg-brand-50 group-hover:text-brand-700 transition-colors">
                                        {{ substr($sv['name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-slate-900 leading-tight">{{ $sv['name'] }}</h4>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $sv['program'] }}</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $svBadge }}">{{ str_replace('_', ' ', $sv['status']) }}</span>
                            </div>
                            <div class="flex items-center gap-6 mb-4">
                                <div class="text-center">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">MSc</p>
                                    <p class="text-2xl font-black text-slate-800">{{ $sv['msc_count'] }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">PhD</p>
                                    <p class="text-2xl font-black text-slate-800">{{ $sv['phd_count'] }}</p>
                                </div>
                                <div class="flex-1 text-right">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Utilization</p>
                                    <p class="text-2xl font-black text-brand-600">{{ $sv['load_percentage'] }}%</p>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <div class="w-full h-2 bg-white rounded-full overflow-hidden shadow-inner">
                                    <div class="{{ $svBar }} h-full rounded-full transition-all duration-1000" style="width: {{ $sv['load_percentage'] }}%"></div>
                                </div>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $sv['total'] }} of {{ $sv['max_load'] }} capacity used</p>
                            </div>
                        </div>
                    @empty
                        <p class="col-span-2 py-12 text-center text-slate-400 font-medium text-sm">No supervisor data captured.</p>
                    @endforelse
                </div>
            </div>

            {{-- Internal Examiner Capacity --}}
            <div class="bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-sm">
                <div class="px-8 py-6 border-b border-slate-50 flex items-center gap-4 bg-slate-50/30">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Internal Examiner Capacity</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Evaluation Load Surveillance</p>
                    </div>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-5">
                    @forelse($examiner_workload ?? [] as $ev)
                        @php
                            $evBg = $ev['status'] === 'overloaded' ? 'bg-rose-50 border-rose-200' : ($ev['status'] === 'near_capacity' ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-100');
                            $evBar = $ev['status'] === 'overloaded' ? 'bg-rose-500' : ($ev['status'] === 'near_capacity' ? 'bg-amber-500' : 'bg-indigo-500');
                            $evBadge = $ev['status'] === 'overloaded' ? 'bg-rose-100 text-rose-700 border-rose-200' : ($ev['status'] === 'near_capacity' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-emerald-100 text-emerald-700 border-emerald-200');
                        @endphp
                        <div class="p-6 rounded-2xl border {{ $evBg }} group hover:shadow-md transition-all duration-300">
                            <div class="flex items-start justify-between mb-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center font-black text-slate-600 text-sm shadow-sm group-hover:bg-indigo-50 group-hover:text-indigo-700 transition-colors">
                                        {{ substr($ev['name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black text-slate-900 leading-tight">{{ $ev['name'] }}</h4>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $ev['program'] }}</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $evBadge }}">{{ str_replace('_', ' ', $ev['status']) }}</span>
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Active Evaluations</p>
                                    <p class="text-2xl font-black text-slate-800">{{ $ev['total'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Utilization</p>
                                    <p class="text-2xl font-black text-indigo-600">{{ $ev['load_percentage'] }}%</p>
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <div class="w-full h-2 bg-white rounded-full overflow-hidden shadow-inner">
                                    <div class="{{ $evBar }} h-full rounded-full transition-all duration-1000" style="width: {{ $ev['load_percentage'] }}%"></div>
                                </div>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $ev['total'] }} of {{ $ev['max_load'] }} evaluations</p>
                            </div>
                        </div>
                    @empty
                        <p class="col-span-2 py-12 text-center text-slate-400 font-medium text-sm">No examiner data available.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right: Tactical Intelligence (4 col) --}}
        <div class="lg:col-span-4 space-y-8">

            {{-- Milestone Pipeline Chart --}}
            <div class="bg-white border border-slate-100 rounded-3xl p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-1.5 h-6 bg-brand-500 rounded-full"></div>
                    <h3 class="text-base font-black text-slate-900 tracking-tight uppercase">Milestone Pipeline</h3>
                </div>
                <div class="relative min-h-[280px]">
                    <canvas id="milestoneFunnelChart"></canvas>
                </div>
            </div>

            {{-- Delayed Milestones --}}
            <div class="bg-white border border-slate-100 rounded-3xl p-8 shadow-sm overflow-hidden relative">
                <div class="absolute top-0 right-0 w-32 h-32 bg-rose-50 rounded-full -translate-x-4 -translate-y-10 opacity-60"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-2 h-2 rounded-full bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.8)]"></div>
                        <h3 class="text-base font-black text-slate-900 tracking-tight uppercase">Delayed Milestones</h3>
                        @if(count($delayed_students) > 0)
                            <span class="ml-auto bg-rose-100 text-rose-700 text-[9px] font-black px-2.5 py-1 rounded-full border border-rose-200">{{ count($delayed_students) }}</span>
                        @endif
                    </div>
                    <div class="space-y-3">
                        @forelse($delayed_students as $milestone)
                            <div class="p-4 bg-rose-50 border border-rose-100 rounded-2xl hover:border-rose-300 transition-colors group">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <p class="text-sm font-black text-slate-900 group-hover:text-rose-700 transition-colors leading-tight">{{ $milestone->thesis->student->user->name }}</p>
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-700 border border-rose-200 rounded text-[9px] font-black shrink-0">{{ $milestone->due_date->diffInDays(now()) }}d Late</span>
                                </div>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3">{{ $milestone->template->name }}</p>
                                <a href="{{ route('inbox.index') }}" class="text-[9px] font-black text-brand-600 hover:text-brand-700 uppercase tracking-widest flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    Contact Student
                                </a>
                            </div>
                        @empty
                            <div class="py-10 text-center">
                                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <p class="text-xs font-bold text-emerald-600">All submissions on track!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Active Mentors Leaderboard --}}
            <div class="bg-white border border-slate-100 rounded-3xl p-8 shadow-sm">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-6 bg-emerald-500 rounded-full"></div>
                        <h3 class="text-base font-black text-slate-900 tracking-tight uppercase">Faculty Top Mentors</h3>
                    </div>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    </div>
                </div>
                <div class="space-y-6">
                    @forelse($faculty_leaderboard as $idx => $mentor)
                        <div class="flex items-center gap-4 relative">
                            <div class="w-8 h-8 rounded-full {{ $idx == 0 ? 'bg-amber-100 text-amber-700 ring-4 ring-amber-50' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center text-xs font-black shrink-0">
                                #{{ $idx + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-black text-slate-800 truncate leading-none">{{ $mentor['name'] }}</p>
                                <div class="flex items-center gap-3 mt-1.5">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-emerald-600 flex items-center gap-1">
                                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        {{ $mentor['graduated_count'] }} Completion
                                    </span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                                        {{ $mentor['response_time_display'] }} Response
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-slate-400 font-medium text-sm py-8">Rating data processing...</p>
                    @endforelse
                </div>
                <div class="mt-8 pt-6 border-t border-slate-50">
                    <a href="{{ route('reports.index') }}" class="block text-center text-[10px] font-black text-brand-600 uppercase tracking-widest hover:text-brand-700 transition-colors">View Faculty Full Rankings →</a>
                </div>
            </div>

            {{-- Cohort Progress --}}
            <div class="bg-white border border-slate-100 rounded-3xl p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-1.5 h-6 bg-indigo-500 rounded-full"></div>
                    <h3 class="text-base font-black text-slate-900 tracking-tight uppercase">Cohort Completion</h3>
                </div>
                <div class="space-y-5">
                    @forelse($cohort_monitoring as $cohort)
                        @php $rate = $cohort['completion_rate'] === 'N/A' ? 0 : (int) $cohort['completion_rate']; @endphp
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <p class="text-xs font-black text-slate-900">{{ $cohort['name'] }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $cohort['students_count'] }} Candidates</p>
                                </div>
                                <span class="text-sm font-black text-brand-600">{{ $cohort['completion_rate'] }}</span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-brand-500 rounded-full transition-all duration-1000" style="width: {{ $rate < 3 ? '3' : $rate }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-slate-400 font-medium text-sm py-8">No active cohorts to display.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Institutional Student Registry --}}
    <div class="bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-sm">
        <div class="px-8 py-7 border-b border-slate-50 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 flex items-center justify-center border border-brand-100 shrink-0">
                    <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Institutional Student Registry</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Granular Status Tracking & Capacity Monitor</p>
                </div>
            </div>
            <span class="px-4 py-2 bg-slate-50 text-slate-500 rounded-xl text-xs font-black border border-slate-100">{{ $students->total() }} Candidates Found</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-50 text-[9px] font-black text-slate-400 uppercase tracking-widest bg-slate-50/30">
                        <th class="px-8 py-5">Candidate</th>
                        <th class="px-6 py-5">Program</th>
                        <th class="px-6 py-5">Cohort</th>
                        <th class="px-6 py-5">Current Phase</th>
                        <th class="px-6 py-5">Progress</th>
                        <th class="px-8 py-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($students as $student)
                        <tr class="hover:bg-brand-50/20 transition-all duration-300 group">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-brand-100 to-brand-200 text-brand-800 flex items-center justify-center font-black text-base border border-brand-200 shadow-sm group-hover:scale-105 transition-transform">
                                        {{ substr($student?->user?->name ?? '?', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-800 group-hover:text-brand-700 transition-colors">{{ $student?->user?->name ?? 'N/A' }}</p>
                                        <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase tracking-wider">{{ $student->student_id_number }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[9px] font-black uppercase tracking-widest border border-slate-200">{{ $student?->program?->code ?? 'N/A' }}</span>
                                <p class="text-[9px] font-bold text-slate-400 mt-1.5 uppercase tracking-widest">{{ $student->level->name ?? '' }}</p>
                            </td>
                            <td class="px-6 py-5">
                                <p class="text-xs font-black text-slate-700">{{ $student->cohort->name ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $student->enrollment_status === 'active' ? 'bg-emerald-500' : 'bg-slate-300' }} shrink-0"></span>
                                    <span class="text-xs font-bold text-slate-600 truncate max-w-[160px]">
                                        {{ $student->thesis->currentMilestone->template->name ?? 'Not Started' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                @php $pct = $student->thesis->progress_percentage ?? 0; @endphp
                                <div class="w-28 space-y-1.5">
                                    <div class="flex justify-between text-[8px] font-black uppercase tracking-widest text-slate-400">
                                        <span>Progress</span>
                                        <span>{{ $pct }}%</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full {{ $pct >= 80 ? 'bg-emerald-500' : ($pct >= 40 ? 'bg-brand-500' : 'bg-slate-400') }} rounded-full transition-all duration-1000" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <a href="{{ route('admin.students.show', $student->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-brand-600 text-white rounded-xl text-[9px] font-black tracking-widest uppercase transition-all shadow-sm hover:shadow-lg hover:shadow-brand-600/20 active:scale-95">
                                    View Profile
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-24 text-center">
                                <div class="w-20 h-20 bg-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-6">
                                    <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </div>
                                <p class="text-sm font-black text-slate-400 uppercase tracking-widest">No candidates found matching your criteria</p>
                                <a href="{{ route('dashboard') }}" class="text-brand-600 text-[10px] font-black uppercase tracking-widest mt-4 inline-block hover:underline">Clear all filters →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="px-8 py-5 bg-slate-50/50 border-t border-slate-50">
                {{ $students->links() }}
            </div>
        @endif
        </div>

    {{-- Institutional Resource Center --}}
    @if(isset($document_templates) && $document_templates->count() > 0)
    <div class="bg-white border border-slate-100 rounded-3xl p-8 shadow-sm">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h3 class="text-xl font-black text-slate-900 tracking-tight">Institutional Resource Center</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Authorized Protocols & Guidelines</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($document_templates as $template)
            <a href="{{ route('templates.download', $template) }}" class="flex items-center gap-4 p-5 bg-slate-50 hover:bg-indigo-50 rounded-2xl border border-slate-100 hover:border-indigo-200 transition-all group">
                <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-black text-slate-800 truncate leading-none uppercase">{{ $template->title }}</p>
                    <p class="text-[9px] font-bold text-slate-400 mt-2 uppercase tracking-tighter italic">Version {{ $template->version }} • {{ strtoupper($template->type) }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.font.family = "'Figtree','Inter',sans-serif";
    Chart.defaults.font.weight = '700';
    Chart.defaults.color = '#94a3b8';

    // Program Comparison Chart
    const progCtx = document.getElementById('programComparisonChart').getContext('2d');
    new Chart(progCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($programs_performance, 'code')) !!},
            datasets: [
                {
                    label: 'Total Students',
                    data: {!! json_encode(array_column($programs_performance, 'total_students')) !!},
                    backgroundColor: '#16a34a',
                    borderRadius: 10,
                    barThickness: 32,
                },
                {
                    label: 'At Proposal Stage',
                    data: {!! json_encode(array_column($programs_performance, 'proposal_stage')) !!},
                    backgroundColor: '#e2e8f0',
                    borderRadius: 10,
                    barThickness: 32,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { padding: 24, boxWidth: 10, usePointStyle: true, pointStyle: 'circle', font: { size: 11, weight: '700' } }
                }
            },
            scales: {
                y: { grid: { color: '#f1f5f9' }, border: { display: false }, ticks: { font: { size: 11 } } },
                x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });

    // Milestone Funnel Chart
    const funnelCtx = document.getElementById('milestoneFunnelChart').getContext('2d');
    new Chart(funnelCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($milestone_pipeline, 'name')) !!},
            datasets: [{
                label: 'Students at Stage',
                data: {!! json_encode(array_column($milestone_pipeline, 'count')) !!},
                backgroundColor: 'rgba(22,163,74,0.15)',
                borderColor: '#16a34a',
                borderWidth: 2,
                borderRadius: 6,
                barThickness: 18,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { padding: 12, cornerRadius: 10, bodyFont: { weight: '700' } }
            },
            scales: {
                x: { display: false, min: 0 },
                y: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 10, weight: '700' } } }
            }
        }
    });
});
</script>
@endsection
