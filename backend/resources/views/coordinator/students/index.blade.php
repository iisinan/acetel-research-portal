@extends('layouts.coordinator')

@section('header', 'Student List')

@section('content')
<div class="space-y-10 pb-10">
    <!-- Clean Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 mb-2 text-acetel-600">
                <div class="p-1.5 rounded-xl bg-acetel-50 border border-acetel-100 shadow-xs">
                    <svg class="w-4 h-4 text-acetel-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Student Directory</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">Student List</h1>
            <p class="mt-1.5 text-sm font-medium text-slate-500">List of all enrolled students and their research supervision status.</p>
        </div>

        <!-- Metric Counter Pill -->
        <div class="flex items-center gap-3 shrink-0">
            <div class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-semibold text-slate-500">Total Enrolled:</span>
                <span class="text-sm font-black text-slate-900">{{ number_format($students->total()) }}</span>
                <span class="text-[11px] font-medium text-slate-400">Students</span>
            </div>
        </div>
    </div>

    <!-- Polished Search & Filter Control Panel -->
    @php
        $hasActiveFilters = request('search') || request('program_id') || request('level_id') || request('cohort_id') || request('batch') || request('milestone_id');
        $activeFilterCount = collect(['search', 'program_id', 'level_id', 'cohort_id', 'batch', 'milestone_id'])->filter(fn($key) => request()->filled($key))->count();
    @endphp

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 md:p-6 transition-all">
        <form method="GET" action="{{ route('coordinator.students.index') }}" id="studentFilterForm" class="space-y-4">
            <!-- Top Row: Search Input + Action Buttons -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search by student name, matric number, or thesis title..." 
                           class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 focus:border-acetel-500 rounded-2xl pl-11 pr-10 py-3 text-sm text-slate-800 font-medium placeholder-slate-400 focus:ring-4 focus:ring-acetel-500/10 transition-all outline-none">
                    
                    @if(request('search'))
                        <button type="button" 
                                onclick="document.querySelector('input[name=search]').value=''; document.getElementById('studentFilterForm').submit();" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                                title="Clear search text">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-2.5 shrink-0">
                    <button type="submit" 
                            class="px-5 py-3 rounded-2xl bg-slate-900 hover:bg-acetel-600 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-sm hover:shadow-acetel-500/20 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <span>Filter</span>
                    </button>

                    @if($hasActiveFilters)
                        <a href="{{ route('coordinator.students.index') }}" 
                           class="inline-flex items-center gap-1.5 px-4 py-3 rounded-2xl bg-rose-50 hover:bg-rose-100/80 text-rose-600 font-bold text-xs uppercase tracking-wider transition-all border border-rose-200/60 shadow-xs" 
                           title="Clear all active filters">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            <span>Reset ({{ $activeFilterCount }})</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Filter Controls Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 pt-3 border-t border-slate-100">
                @if(isset($programs) && count($programs) > 1)
                    <div class="relative">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            </svg>
                            Program
                        </label>
                        <div class="relative">
                            <select name="program_id" 
                                    onchange="this.form.submit()" 
                                    class="w-full appearance-none bg-slate-50 hover:bg-slate-100/70 {{ request('program_id') ? 'border-acetel-500 bg-acetel-50/30 text-acetel-900 font-bold' : 'border-slate-200 text-slate-700 font-medium' }} border rounded-xl py-2.5 pl-3.5 pr-8 text-xs focus:ring-2 focus:ring-acetel-500/20 focus:border-acetel-500 transition-all cursor-pointer truncate">
                                <option value="">All Programs</option>
                                @foreach($programs as $prog)
                                    <option value="{{ $prog->id }}" {{ request('program_id') == $prog->id ? 'selected' : '' }}>{{ $prog->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                @endif

                @if(isset($levels) && count($levels) > 1)
                    <div class="relative">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            Degree Level
                        </label>
                        <div class="relative">
                            <select name="level_id" 
                                    onchange="this.form.submit()" 
                                    class="w-full appearance-none bg-slate-50 hover:bg-slate-100/70 {{ request('level_id') ? 'border-acetel-500 bg-acetel-50/30 text-acetel-900 font-bold' : 'border-slate-200 text-slate-700 font-medium' }} border rounded-xl py-2.5 pl-3.5 pr-8 text-xs focus:ring-2 focus:ring-acetel-500/20 focus:border-acetel-500 transition-all cursor-pointer truncate">
                                <option value="">All Levels</option>
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id }}" {{ request('level_id') == $lvl->id ? 'selected' : '' }}>{{ $lvl->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                @endif

                @if(isset($cohorts) && count($cohorts) > 0)
                    <div class="relative">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Cohort
                        </label>
                        <div class="relative">
                            <select name="cohort_id" 
                                    onchange="this.form.submit()" 
                                    class="w-full appearance-none bg-slate-50 hover:bg-slate-100/70 {{ request('cohort_id') ? 'border-acetel-500 bg-acetel-50/30 text-acetel-900 font-bold' : 'border-slate-200 text-slate-700 font-medium' }} border rounded-xl py-2.5 pl-3.5 pr-8 text-xs focus:ring-2 focus:ring-acetel-500/20 focus:border-acetel-500 transition-all cursor-pointer truncate">
                                <option value="">All Cohorts</option>
                                @foreach($cohorts as $coh)
                                    <option value="{{ $coh->id }}" {{ request('cohort_id') == $coh->id ? 'selected' : '' }}>{{ $coh->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="relative">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                        Batch
                    </label>
                    <div class="relative">
                        <select name="batch" 
                                onchange="this.form.submit()" 
                                class="w-full appearance-none bg-slate-50 hover:bg-slate-100/70 {{ request('batch') ? 'border-acetel-500 bg-acetel-50/30 text-acetel-900 font-bold' : 'border-slate-200 text-slate-700 font-medium' }} border rounded-xl py-2.5 pl-3.5 pr-8 text-xs focus:ring-2 focus:ring-acetel-500/20 focus:border-acetel-500 transition-all cursor-pointer truncate">
                            <option value="">All Batches</option>
                            <option value="1" {{ request('batch') == '1' ? 'selected' : '' }}>Batch 1</option>
                            <option value="2" {{ request('batch') == '2' ? 'selected' : '' }}>Batch 2</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                @if(isset($milestoneTemplates) && count($milestoneTemplates) > 0)
                    <div class="relative">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Milestone Status
                        </label>
                        <div class="relative">
                            <select name="milestone_id" 
                                    onchange="this.form.submit()" 
                                    class="w-full appearance-none bg-slate-50 hover:bg-slate-100/70 {{ request('milestone_id') ? 'border-acetel-500 bg-acetel-50/30 text-acetel-900 font-bold' : 'border-slate-200 text-slate-700 font-medium' }} border rounded-xl py-2.5 pl-3.5 pr-8 text-xs focus:ring-2 focus:ring-acetel-500/20 focus:border-acetel-500 transition-all cursor-pointer truncate">
                                <option value="">All Milestones</option>
                                @foreach($milestoneTemplates as $tmpl)
                                    <option value="{{ $tmpl->id }}" {{ request('milestone_id') == $tmpl->id ? 'selected' : '' }}>
                                        M{{ $tmpl->order }} - {{ $tmpl->name }}
                                    </option>
                                @endforeach
                                <option value="completed" {{ request('milestone_id') == 'completed' ? 'selected' : '' }}>Completed All</option>
                                <option value="no_thesis" {{ request('milestone_id') == 'no_thesis' ? 'selected' : '' }}>Pending Init</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Active Filters Quick Dismiss Chips -->
            @if($hasActiveFilters)
                <div class="flex items-center gap-2 pt-3 border-t border-slate-100 flex-wrap">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mr-1">Active Filters:</span>
                    
                    @if(request('search'))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold">
                            <span>Keyword: "{{ request('search') }}"</span>
                            <button type="button" onclick="document.querySelector('input[name=search]').value=''; document.getElementById('studentFilterForm').submit();" class="text-slate-400 hover:text-slate-700">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </span>
                    @endif

                    @if(request('program_id') && isset($programs))
                        @php $activeProg = $programs->firstWhere('id', request('program_id')); @endphp
                        @if($activeProg)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-acetel-50 text-acetel-700 border border-acetel-200/60 text-xs font-semibold">
                                <span>Program: {{ $activeProg->code ?? \Illuminate\Support\Str::limit($activeProg->name, 22) }}</span>
                                <button type="button" onclick="document.querySelector('select[name=program_id]').value=''; document.getElementById('studentFilterForm').submit();" class="text-acetel-500 hover:text-acetel-800">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </span>
                        @endif
                    @endif

                    @if(request('level_id') && isset($levels))
                        @php $activeLvl = $levels->firstWhere('id', request('level_id')); @endphp
                        @if($activeLvl)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-blue-50 text-blue-700 border border-blue-200/60 text-xs font-semibold">
                                <span>Level: {{ $activeLvl->name }}</span>
                                <button type="button" onclick="document.querySelector('select[name=level_id]').value=''; document.getElementById('studentFilterForm').submit();" class="text-blue-500 hover:text-blue-800">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </span>
                        @endif
                    @endif

                    @if(request('cohort_id') && isset($cohorts))
                        @php $activeCoh = $cohorts->firstWhere('id', request('cohort_id')); @endphp
                        @if($activeCoh)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-purple-50 text-purple-700 border border-purple-200/60 text-xs font-semibold">
                                <span>Cohort: {{ $activeCoh->name }}</span>
                                <button type="button" onclick="document.querySelector('select[name=cohort_id]').value=''; document.getElementById('studentFilterForm').submit();" class="text-purple-500 hover:text-purple-800">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </span>
                        @endif
                    @endif

                    @if(request('batch'))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-50 text-amber-700 border border-amber-200/60 text-xs font-semibold">
                            <span>Batch: {{ request('batch') }}</span>
                            <button type="button" onclick="document.querySelector('select[name=batch]').value=''; document.getElementById('studentFilterForm').submit();" class="text-amber-500 hover:text-amber-800">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </span>
                    @endif

                    @if(request('milestone_id'))
                        @php
                            $mLabel = request('milestone_id') === 'completed' 
                                ? 'Completed' 
                                : (request('milestone_id') === 'no_thesis' 
                                    ? 'Pending Init' 
                                    : 'Milestone M' . (optional($milestoneTemplates->firstWhere('id', request('milestone_id')))->order ?? ''));
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-teal-50 text-teal-700 border border-teal-200/60 text-xs font-semibold">
                            <span>Status: {{ $mLabel }}</span>
                            <button type="button" onclick="document.querySelector('select[name=milestone_id]').value=''; document.getElementById('studentFilterForm').submit();" class="text-teal-500 hover:text-teal-800">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </span>
                    @endif

                    <a href="{{ route('coordinator.students.index') }}" class="text-xs font-bold text-rose-600 hover:text-rose-700 hover:underline ml-auto">
                        Clear all filters
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Premium Table Container -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden min-h-[500px] flex flex-col">
        <div class="px-8 sm:px-10 py-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/40">
            <div>
                <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">Enrolled Students</h3>
                <p class="text-xs font-semibold text-slate-400 mt-0.5">
                    @if($students->total() > 0)
                        Showing <span class="font-bold text-slate-700">{{ $students->firstItem() }}</span> to <span class="font-bold text-slate-700">{{ $students->lastItem() }}</span> of <span class="font-bold text-slate-700">{{ number_format($students->total()) }}</span> students
                    @else
                        No students matching current criteria
                    @endif
                </p>
            </div>
            @if($hasActiveFilters)
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200/60 text-emerald-700 text-xs font-bold self-start sm:self-auto">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Filtered View ({{ $activeFilterCount }} active)</span>
                </div>
            @endif
        </div>

        <div class="flex-1">
            @if(isset($students) && $students->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Student</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Program</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Thesis</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Progress</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Alert Status</th>
                                <th class="px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($students as $student)
                                <tr class="hover:bg-slate-50/30 transition-colors group">
                                    <td class="px-10 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-acetel-50 group-hover:text-acetel-500 transition-all font-black text-sm shadow-sm ring-1 ring-slate-100 group-hover:ring-acetel-100">
                                                {{ substr($student->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-900 leading-none group-hover:text-acetel-600 transition-colors">{{ $student->user->name }}</p>
                                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5">{{ $student->student_id_number }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 font-medium">
                                        <div class="space-y-1.5">
                                            <p class="text-xs font-bold text-slate-700 leading-none">{{ $student->program->name ?? '--' }}</p>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md @if(str_contains(strtolower($student->level->name ?? ''), 'phd')) bg-acetel-50 text-acetel-600 @else bg-amber-50 text-amber-600 @endif text-[8px] font-black uppercase tracking-tighter shadow-sm border border-current/10">
                                                    {{ $student->level->name ?? '--' }}
                                                </span>
                                                @if($student->cohort)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[8px] font-bold uppercase tracking-tighter shadow-sm border border-slate-200" title="Cohort">
                                                        {{ $student->cohort->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        @if($student->thesis)
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold text-slate-900 truncate max-w-[200px]" title="{{ $student->thesis->title }}">{{ $student->thesis->title }}</span>
                                                @php
                                                    $current = $student->thesis->currentMilestone;
                                                @endphp
                                                @if($current)
                                                    <span class="text-[8px] font-black text-primary-600 uppercase tracking-tighter mt-1 italic">
                                                        Current: M{{ $current->template->order ?? '?' }} - {{ $current->template->name ?? 'Unknown' }}
                                                    </span>
                                                @else
                                                    <span class="text-[8px] font-black text-emerald-600 uppercase tracking-tighter mt-1 italic">
                                                        COMPLETED
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest italic">No Thesis Init</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-6">
                                        @if($student->thesis)
                                            <div class="w-full bg-slate-100 rounded-full h-1.5 mb-1 overlow-hidden">
                                                <div class="bg-acetel-500 h-1.5 rounded-full" style="width: {{ $student->thesis->progress_percentage }}%"></div>
                                            </div>
                                            <span class="text-[8px] font-black text-slate-500 uppercase">{{ $student->thesis->progress_percentage }}% Cleared</span>
                                        @else
                                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest italic">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-6">
                                        @if($student->thesis)
                                            @php
                                                $daysSinceLastUpdate = $student->thesis->updated_at->diffInDays(now());
                                                $statusClass = 'bg-emerald-50 text-emerald-600';
                                                $statusText = 'On Track';
                                                $dotClass = 'bg-emerald-500';
                                                
                                                if ($daysSinceLastUpdate > 30) {
                                                    $statusClass = 'bg-rose-50 text-rose-600';
                                                    $statusText = 'Stalled (' . $daysSinceLastUpdate . 'd)';
                                                    $dotClass = 'bg-rose-500 animate-pulse';
                                                } elseif ($daysSinceLastUpdate > 14) {
                                                    $statusClass = 'bg-amber-50 text-amber-600';
                                                    $statusText = 'Delayed';
                                                    $dotClass = 'bg-amber-500';
                                                }

                                                if($student->thesis->status === 'completed') {
                                                    $statusClass = 'bg-blue-50 text-blue-600';
                                                    $statusText = 'Archived';
                                                    $dotClass = 'bg-blue-500';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full {{ $statusClass }} text-[8px] font-black uppercase tracking-widest shadow-sm ring-1 ring-current/10">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                                {{ $statusText }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-[8px] font-black uppercase tracking-widest shadow-sm ring-1 ring-current/10">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Pending Init
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-10 py-6 text-right">
                                        <div class="flex justify-end items-center gap-2">
                                            @if($student->thesis)
                                                <a href="{{ route('milestones.index', ['thesis_id' => $student->thesis->id]) }}" class="inline-flex items-center justify-center p-2 rounded-xl bg-slate-50 border border-slate-100 text-slate-400 hover:border-acetel-500 hover:text-acetel-500 translate-y-0 hover:-translate-y-1 transition-all" title="Milestones">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                </a>
                                            @endif
                                            <a href="{{ route('coordinator.students.show', $student) }}" class="inline-flex items-center justify-center p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:border-acetel-500 hover:text-acetel-500 hover:shadow-lg hover:shadow-acetel-500/10 transition-all translate-y-0 hover:-translate-y-1" title="View Details">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-[400px] text-center px-10">
                    <div class="w-20 h-20 rounded-[2rem] bg-slate-50 border border-slate-100 flex items-center justify-center mb-6 shadow-inner">
                        <svg class="w-10 h-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <h4 class="text-lg font-black text-slate-900 tracking-tight">No Students Found</h4>
                    <p class="text-sm text-slate-500 mt-2 max-w-xs mx-auto">No students found in the assigned programs.</p>
                </div>
            @endif
        </div>

        @if($students->hasPages())
            <div class="px-10 py-6 bg-slate-50/50 border-t border-slate-50">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
