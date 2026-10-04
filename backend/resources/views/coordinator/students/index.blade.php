@extends('layouts.coordinator')

@section('header', 'Student List')

@section('content')
<div class="space-y-10 pb-10">
    <!-- Sophisticated Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <div class="p-1.5 rounded-lg bg-acetel-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Students</span>
            </div>
            <h1 class="text-2xl md:text-4xl font-black text-slate-900 tracking-tight">Student List</h1>
            <p class="mt-2 text-sm font-medium text-slate-500">List of all students and their supervision status.</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <form method="GET" action="{{ route('coordinator.students.index') }}" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <div class="relative w-full sm:w-80">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, matric, thesis..." class="w-full bg-white border border-slate-200 rounded-2xl pl-11 pr-4 py-2.5 placeholder-slate-400 text-sm focus:ring-2 focus:ring-acetel-500/20 focus:border-acetel-500 text-slate-900 font-medium shadow-sm transition-all outline-none">
                </div>

                @if(isset($programs) && count($programs) > 1)
                    <div class="px-5 py-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-2 hover:border-acetel-300 transition-colors">
                        <select name="program_id" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold uppercase tracking-widest text-slate-500 focus:ring-0 cursor-pointer">
                            <option value="">All Programs</option>
                            @foreach($programs as $prog)
                                <option value="{{ $prog->id }}" {{ request('program_id') == $prog->id ? 'selected' : '' }}>{{ $prog->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if(isset($levels) && count($levels) > 1)
                    <div class="px-5 py-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-2 hover:border-acetel-300 transition-colors">
                        <select name="level_id" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold uppercase tracking-widest text-slate-500 focus:ring-0 cursor-pointer">
                            <option value="">All Levels</option>
                            @foreach($levels as $lvl)
                                <option value="{{ $lvl->id }}" {{ request('level_id') == $lvl->id ? 'selected' : '' }}>{{ $lvl->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if(isset($cohorts) && count($cohorts) > 0)
                    <div class="px-5 py-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-2 hover:border-acetel-300 transition-colors">
                        <select name="cohort_id" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold uppercase tracking-widest text-slate-500 focus:ring-0 cursor-pointer">
                            <option value="">All Cohorts</option>
                            @foreach($cohorts as $coh)
                                <option value="{{ $coh->id }}" {{ request('cohort_id') == $coh->id ? 'selected' : '' }}>{{ $coh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="px-5 py-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-2 hover:border-acetel-300 transition-colors">
                    <select name="batch" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold uppercase tracking-widest text-slate-500 focus:ring-0 cursor-pointer">
                        <option value="">All Batches</option>
                        <option value="1" {{ request('batch') == '1' ? 'selected' : '' }}>Batch 1</option>
                        <option value="2" {{ request('batch') == '2' ? 'selected' : '' }}>Batch 2</option>
                    </select>
                </div>

                @if(request('search') || request('program_id') || request('level_id') || request('cohort_id') || request('batch'))
                    <a href="{{ route('coordinator.students.index') }}" class="p-2.5 text-slate-400 hover:text-rose-500 bg-white border border-slate-200 rounded-2xl transition-all shadow-sm" title="Reset Filters">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </a>
                @endif
                <button type="submit" class="hidden"></button>
            </form>
            <div class="px-5 py-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-3">
                <span class="text-xs font-bold text-slate-600">Total: {{ number_format($students->total()) }}</span>
            </div>
        </div>
    </div>

    <!-- Premium Table Container -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden min-h-[500px] flex flex-col">
        <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
            <div>
                <h3 class="text-xl font-black text-slate-900 tracking-tight">Student List</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Coordinator View</p>
            </div>
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
