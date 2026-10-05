@extends('layouts.admin')

@section('content')
<div class="space-y-10 pb-10">
    <!-- Sophisticated Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <div class="p-1.5 rounded-lg bg-acetel-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Milestone Templates</span>
            </div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Milestones</h1>
            <p class="mt-2 text-sm font-medium text-slate-500">View the static milestones and approval requirements for student progress.</p>
        </div>
    </div>


    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="w-20 px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 text-center">Order</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Milestone Name</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Program</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Requirements</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 text-right">Schedule</th>
                    </tr>
                </thead>
                
                    @forelse($templates as $template)
                    <tbody x-data="{ expanded: false, showStudents: false, selectAll: false, selected: [], search: '', cohortFilter: '' }" class="divide-y divide-slate-50 border-t border-slate-50">
                    <tr class="hover:bg-slate-50/30 transition-colors group cursor-pointer" data-id="{{ $template->id }}" @click="if(!$event.target.closest('button') && !$event.target.closest('a') && !$event.target.closest('input') && !$event.target.closest('form')) expanded = !expanded">
                        <td class="px-10 py-7 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <span class="text-[10px] font-black text-slate-400 tabular-nums uppercase">{{ $template->order }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            <div class="max-w-xs">
                                <p class="text-base font-black text-slate-900 leading-tight group-hover:text-acetel-600 transition-colors">{{ $template->name }}</p>
                                <p class="text-[10px] font-medium text-slate-400 mt-1 line-clamp-1">{{ $template->description }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            @if($template->program_id)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest bg-acetel-50 text-acetel-600 border border-acetel-100">
                                    {{ $template->program->code }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest bg-slate-900 text-white border border-transparent">
                                    All Programs
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-7">
                            <div class="flex flex-wrap gap-2">
                                @if($template->requires_submission)
                                    <span class="px-2 py-1 rounded-lg bg-indigo-50 text-[9px] font-black text-indigo-600 uppercase tracking-widest border border-indigo-100">Submission</span>
                                @endif
                                @if($template->requires_approval)
                                    <span class="px-2 py-1 rounded-lg bg-emerald-50 text-[9px] font-black text-emerald-600 uppercase tracking-widest border border-emerald-100">Approval</span>
                                @endif
                                @if($template->allow_defence_date)
                                     <span class="px-2 py-1 rounded-lg bg-rose-50 text-[9px] font-black text-rose-600 uppercase tracking-widest border border-rose-100">Defence</span>
                                @endif
                                @if($template->is_final_archival)
                                     <span class="px-2 py-1 rounded-lg bg-amber-50 text-[9px] font-black text-amber-600 uppercase tracking-widest border border-amber-100">Final Archival</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-7 text-right">
                            @if($template->allow_defence_date)
                                <div x-data="{ 
                                    showDatePicker: false, 
                                    localDate: '{{ $template->global_defence_date ? $template->global_defence_date->format('Y-m-d') : '' }}',
                                    isDateExpired: {{ ($template->global_defence_date && \Carbon\Carbon::parse($template->global_defence_date)->isPast()) ? 'true' : 'false' }}
                                }" class="relative inline-block text-left">
                                    
                                    @if($template->global_defence_date && !\Carbon\Carbon::parse($template->global_defence_date)->isPast())
                                        <button @click.stop="showDatePicker = !showDatePicker" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            {{ $template->global_defence_date->format('M d, Y') }}
                                        </button>
                                    @else
                                        <button @click.stop="showDatePicker = !showDatePicker" class="inline-flex items-center gap-2 px-4 py-2 {{ ($template->global_defence_date && \Carbon\Carbon::parse($template->global_defence_date)->isPast()) ? 'bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 shadow-sm shadow-red-100' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-sm' }} rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            {{ ($template->global_defence_date && \Carbon\Carbon::parse($template->global_defence_date)->isPast()) ? 'Expired / Set New Date' : 'Set Date' }}
                                        </button>
                                    @endif

                                    <!-- Dropdown Date Picker -->
                                    <div x-show="showDatePicker" @click.outside="showDatePicker = false" @click.stop class="absolute right-0 mt-2 p-4 bg-white border border-slate-100 rounded-2xl shadow-xl shadow-slate-200/50 z-50 w-64" style="display: none;">
                                        <form action="{{ route('admin.milestone-templates.set-date', $template->id) }}" method="POST" class="flex flex-col gap-3">
                                            @csrf
                                            <input type="date" name="global_defence_date" x-model="localDate" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500" required>
                                            <button type="submit" class="w-full px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Save Schedule</button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr x-cloak x-show="expanded" x-transition class="bg-slate-50/30">
                        <td colspan="5" class="p-6">
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 cursor-default" @click.stop>
                                
                                {{-- Summary Cards --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-5 gap-4 mb-6">
                                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Students</p>
                                        <p class="text-2xl font-black text-slate-900">{{ $template->studentMilestones->count() }}</p>
                                    </div>
                                    <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100">
                                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">Scheduled</p>
                                        <p class="text-2xl font-black text-emerald-700">{{ $template->studentMilestones->filter(fn($m) => !empty($m->defence_date))->count() }}</p>
                                    </div>
                                    <div class="bg-amber-50 rounded-xl p-4 border border-amber-100">
                                        <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-1">Not Scheduled</p>
                                        <p class="text-2xl font-black text-amber-700">{{ $template->studentMilestones->filter(fn($m) => empty($m->defence_date))->count() }}</p>
                                    </div>
                                    @if(in_array('Supervisor', $template->required_approvers ?? []))
                                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                                        <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-1">Approved by Supervisor</p>
                                        <p class="text-2xl font-black text-blue-700">{{ $template->studentMilestones->filter(fn($m) => $m->is_supervisor_approved || ($m->submissions->last() && $m->submissions->last()->feedback && $m->submissions->last()->feedback->decision === 'approved'))->count() }}</p>
                                    </div>
                                    <div class="bg-rose-50 rounded-xl p-4 border border-rose-100">
                                        <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest mb-1">Pending Supervisor</p>
                                        <p class="text-2xl font-black text-rose-700">{{ $template->studentMilestones->filter(fn($m) => !$m->is_supervisor_approved && !($m->submissions->last() && $m->submissions->last()->feedback && $m->submissions->last()->feedback->decision === 'approved'))->count() }}</p>
                                    </div>
                                    @endif
                                </div>

                                {{-- Actions Row --}}
                                <div class="flex flex-wrap items-center gap-3 mb-6">
                                    <a href="{{ isset($isCoordinator) && $isCoordinator ? route('coordinator.milestone-templates.export-students', $template->id) : route('admin.milestone-templates.export-students', $template->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Export CSV
                                    </a>
                                    @if($template->slug !== 'supervisors_assigned')
                                    <a href="{{ isset($isCoordinator) && $isCoordinator ? route('coordinator.milestone-templates.export-scheduled-scores', $template->id) : route('admin.milestone-templates.export-scheduled-scores', $template->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                        SCHEDULED SCORES
                                    </a>
                                    @endif
                                    @if($template->slug === 'seminar_as_a_course' && (!isset($isCoordinator) || !$isCoordinator))
                                    <a href="{{ route('admin.milestone-templates.export-examiner-attendance', $template->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Examiner Attendance
                                    </a>
                                    @endif

                                    @if($template->studentMilestones->count() > 0)
                                        <button @click="showStudents = !showStudents; if(showStudents) $nextTick(() => $refs.searchInput?.focus())" 
                                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                                            :class="showStudents ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            <span x-text="showStudents ? 'Hide Students' : 'View Student Details'"></span>
                                        </button>
                                    @endif

                                    @php
                                        $scheduledMilestones = $template->studentMilestones
                                            ->filter(fn($m) => !empty($m->defence_date) && $m->status !== 'approved')
                                            ->sortBy(fn($m) => \Carbon\Carbon::parse($m->defence_date)->format('Y-m-d') . ' ' . ($m->defence_time ?? ''))
                                            ->values();
                                        $scheduledCount = $scheduledMilestones->count();
                                        $existingLink = $scheduledMilestones->first(fn($m) => !empty($m->meeting_link))?->meeting_link;
                                        $existingTimeRaw = $scheduledMilestones->first(fn($m) => !empty($m->defence_time))?->defence_time;
                                        $existingTime = $existingTimeRaw ? \Carbon\Carbon::parse($existingTimeRaw)->format('H:i') : null;
                                        $lastScheduledDate = $scheduledCount > 0 ? \Carbon\Carbon::parse($scheduledMilestones->last()->defence_date)->format('Y-m-d') : null;
                                    @endphp

                                    @if($scheduledCount > 0)
                                        @if(!isset($isCoordinator) || !$isCoordinator)
                                            <button type="button" @click="showStudents = true; $nextTick(() => $refs.scheduleForm?.scrollIntoView({ behavior: 'smooth', block: 'center' }))"
                                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                                <span>Add Students</span>
                                            </button>

                                            <div x-data="{ editOpen: false }" class="inline">
                                                <button type="button" @click="editOpen = true"
                                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    <span>Edit Schedule</span>
                                                </button>

                                                <template x-teleport="body">
                                                    <div x-show="editOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @keydown.escape.window="editOpen = false">
                                                        <div @click.outside="editOpen = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden">
                                                            <form action="{{ route('admin.milestone-templates.update-schedule', $template->id) }}" method="POST" class="flex flex-col min-h-0">
                                                                @csrf
                                                                {{-- Header --}}
                                                                <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between gap-4">
                                                                    <div>
                                                                        <h3 class="text-lg font-black text-slate-900">Edit {{ $template->presentation_title }} Schedule</h3>
                                                                        <p class="text-xs text-slate-500 mt-0.5">Change dates, times or meeting links, or remove students. Only students whose slot changes will be notified.</p>
                                                                    </div>
                                                                    <button type="button" @click="editOpen = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                    </button>
                                                                </div>

                                                                {{-- Apply to all --}}
                                                                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                                    <div>
                                                                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Apply time to all (optional)</label>
                                                                        <input type="time" name="apply_time_all" class="w-full px-3 py-2 bg-white rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Apply meeting link to all (optional)</label>
                                                                        <input type="url" name="apply_link_all" placeholder="https://zoom.us/j/..." class="w-full px-3 py-2 bg-white rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                                    </div>
                                                                </div>

                                                                {{-- Per-student rows --}}
                                                                <div class="overflow-y-auto flex-1 min-h-0">
                                                                    <table class="w-full text-left text-xs">
                                                                        <thead class="bg-white sticky top-0 z-10 border-b border-slate-100 text-[10px] font-black uppercase tracking-wider text-slate-400">
                                                                            <tr>
                                                                                <th class="px-6 py-3">Student</th>
                                                                                <th class="px-3 py-3">Date</th>
                                                                                <th class="px-3 py-3">Time</th>
                                                                                <th class="px-3 py-3">Meeting Link</th>
                                                                                <th class="px-6 py-3 text-center">Remove</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody class="divide-y divide-slate-100">
                                                                            @foreach($scheduledMilestones as $esm)
                                                                                <tr x-data="{ rm: false }" :class="rm ? 'bg-rose-50/60 opacity-60' : ''">
                                                                                    <td class="px-6 py-3">
                                                                                        <div class="font-bold text-slate-900">{{ $esm->thesis?->student?->user?->name ?? 'Candidate' }}</div>
                                                                                        <div class="text-[11px] text-slate-500">{{ $esm->thesis?->student?->student_id_number ?? 'N/A' }}</div>
                                                                                    </td>
                                                                                    <td class="px-3 py-3">
                                                                                        <input type="date" name="entries[{{ $esm->id }}][defence_date]" value="{{ \Carbon\Carbon::parse($esm->defence_date)->format('Y-m-d') }}" :disabled="rm" required
                                                                                            class="w-36 px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                                                    </td>
                                                                                    <td class="px-3 py-3">
                                                                                        <input type="time" name="entries[{{ $esm->id }}][defence_time]" value="{{ $esm->defence_time ? \Carbon\Carbon::parse($esm->defence_time)->format('H:i') : '' }}" :disabled="rm"
                                                                                            class="w-28 px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                                                    </td>
                                                                                    <td class="px-3 py-3">
                                                                                        <input type="url" name="entries[{{ $esm->id }}][meeting_link]" value="{{ $esm->meeting_link }}" placeholder="https://..." :disabled="rm"
                                                                                            class="w-full min-w-[200px] px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                                                    </td>
                                                                                    <td class="px-6 py-3 text-center">
                                                                                        <input type="hidden" name="entries[{{ $esm->id }}][remove]" :value="rm ? 1 : 0">
                                                                                        <button type="button" @click="rm = !rm"
                                                                                            :class="rm ? 'bg-rose-600 text-white border-rose-600' : 'bg-white text-rose-600 border-rose-200 hover:bg-rose-50'"
                                                                                            class="px-2.5 py-1 rounded-lg border text-[10px] font-black uppercase tracking-wider transition"
                                                                                            x-text="rm ? 'Undo' : 'Remove'"></button>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                {{-- Footer --}}
                                                                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between gap-3 bg-white">
                                                                    <p class="text-[11px] text-slate-400">{{ $scheduledCount }} scheduled student(s). To add more, close this and use <strong>Add Students</strong>.</p>
                                                                    <div class="flex gap-2">
                                                                        <button type="button" @click="editOpen = false" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200">Close</button>
                                                                        <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-md shadow-amber-600/20">Save Changes</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>

                                            <form action="{{ route('admin.milestone-templates.end-schedule', $template->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" data-confirm="Are you sure you want to end the presentation session for {{ addslashes($template->name) }}? Eligible scheduled students who have completed all requirements (presentation, PPT upload, and supervisor approval) will be approved and advanced to the next milestone."
                                                    data-confirm-title="End Presentation Session"
                                                    data-confirm-type="success"
                                                    data-confirm-btn="End Session"
                                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span>End Presentation Session</span>
                                                </button>
                                            </form>
<form action="{{ route('admin.milestone-templates.cancel-schedule', $template->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                    data-confirm="Are you sure you want to cancel the presentation schedule for {{ addslashes($template->name) }}? This will clear all presentation dates, times, and Zoom links for {{ $scheduledCount }} scheduled student(s), and remove the live presentation tab."
                                                    data-confirm-title="Cancel Presentation Schedule"
                                                    data-confirm-type="danger"
                                                    data-confirm-btn="Cancel Schedule"
                                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    <span>Cancel Presentation Schedule</span>
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('presentations.show', $template->id) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            <span>View Presentation Page</span>
                                        </a>
                                    @elseif($template->allow_defence_date || in_array($template->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2']))
                                        @if(!isset($isCoordinator) || !$isCoordinator)
                                            <button type="button" disabled title="No presentations currently scheduled for this milestone"
                                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-50 text-slate-400 border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest cursor-not-allowed opacity-60">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>End Presentation Session</span>
                                            </button>
                                        @endif
                                    @endif
                                </div>

                                {{-- Global Examiner Assignment (Admin & Coordinator) --}}
                                @if(in_array($template->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2']))
                                    @php
                                        $globalEventType = $template->defence_type ?? match($template->slug) {
                                            'seminar_as_a_course' => 'seminar',
                                            'proposal_defence' => 'proposal',
                                            'progress_report_1' => 'progress_report_1',
                                            'progress_report_2' => 'progress_report_2',
                                            default => 'first_seminar',
                                        };

                                        // Resolve current examiners: metadata first, then search events
                                        $metaUserIds = $template->metadata['examiner_user_ids'] ?? [];
                                        if (!empty($metaUserIds)) {
                                            $currentExaminers = \App\Models\User::whereIn('id', $metaUserIds)->get();
                                        } else {
                                            $firstEventWithEx = null;
                                            foreach ($template->studentMilestones as $mItem) {
                                                $ev = $mItem->thesis?->defenceEvents?->firstWhere('type', $globalEventType);
                                                if ($ev && $ev->panelMembers->where('role', 'examiner')->count() > 0) {
                                                    $firstEventWithEx = $ev;
                                                    break;
                                                }
                                            }
                                            $currentExaminers = $firstEventWithEx 
                                                ? $firstEventWithEx->panelMembers->where('role', 'examiner')->map(fn($pm) => $pm->user)->filter() 
                                                : collect();
                                        }

                                        $examinerBoxTitle = match($template->slug) {
                                            'proposal_defence' => 'Proposal Defence Examiner(s)',
                                            'progress_report_1' => 'Progress Report 1 Examiner(s)',
                                            'progress_report_2' => 'Progress Report 2 Examiner(s)',
                                            default => 'Seminar Examiner(s)',
                                        };

                                        $selectedProfileIds = $template->metadata['examiner_profile_ids'] ?? [];
                                        if (empty($selectedProfileIds) && $currentExaminers->count() > 0) {
                                            $currentExaminerUserIds = $currentExaminers->pluck('id')->all();
                                            $selectedProfileIds = $supervisors->whereIn('user_id', $currentExaminerUserIds)->pluck('id')->values()->all();
                                        }
                                        $selectedJsArray = collect($selectedProfileIds)->map(fn($id) => "'{$id}'")->implode(', ');
                                    @endphp
                                    <div class="mb-6 bg-indigo-50/50 border border-indigo-100 rounded-xl p-4">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-black text-indigo-900 uppercase tracking-wider">{{ $examinerBoxTitle }}</p>
                                                    <p class="text-[10px] text-indigo-600 mt-0.5">
                                                        @if($currentExaminers->count() > 0)
                                                            Currently: <strong>{{ $currentExaminers->map(fn($e) => $e->name)->implode(', ') }}</strong>
                                                        @else
                                                            No examiner assigned yet.
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <form action="{{ (auth()->user()->hasRole('Program Coordinator') && !auth()->user()->hasRole('Admin')) ? route('coordinator.milestone-templates.assign-examiner-global', $template->id) : route('admin.milestone-templates.assign-examiner-global', $template->id) }}" method="POST" class="flex items-center gap-2 sm:ml-auto overflow-visible relative" 
                                                x-data="{
                                                    search: '',
                                                    options: [
                                                        @foreach($supervisors as $sup)
                                                            { id: '{{ $sup->id }}', name: '{{ addslashes($sup->user->name) }}', program: '{{ addslashes($sup->programs->first()->code ?? 'N/A') }}' }{{ !$loop->last ? ',' : '' }}
                                                        @endforeach
                                                    ],
                                                    selected: [{{ $selectedJsArray }}],
                                                    open: false,
                                                    get filteredOptions() {
                                                        if (this.search === '') return this.options;
                                                        let s = this.search.toLowerCase();
                                                        return this.options.filter(o => o.name.toLowerCase().includes(s) || o.program.toLowerCase().includes(s));
                                                    },
                                                    get selectedNames() {
                                                        if (this.selected.length === 0) return 'Select Examiner(s)';
                                                        if (this.selected.length === 1) return this.options.find(o => o.id == this.selected[0])?.name || '';
                                                        return this.selected.length + ' selected';
                                                    }
                                                }"
                                                @submit="if(selected.length === 0) { (window.toast ? window.toast.warning('Please select at least one examiner.') : alert('Please select at least one examiner.')); $event.preventDefault(); }">
                                                @csrf
                                                <template x-for="id in selected" :key="id">
                                                    <input type="hidden" name="supervisor_profile_ids[]" :value="id">
                                                </template>
                                                
                                                <!-- Alpine component for multiselect -->
                                                <div class="relative w-48 z-50">
                                                    <div @click="open = !open" class="block w-full py-1.5 pl-3 pr-8 text-xs border border-indigo-200 bg-white rounded-lg cursor-pointer flex items-center justify-between">
                                                        <span class="truncate text-slate-700" x-text="selectedNames"></span>
                                                        <svg class="w-3 h-3 text-slate-400 absolute right-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                    </div>

                                                    <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 w-64 mt-1 bg-white border border-indigo-100 rounded-lg shadow-xl overflow-y-auto z-50" style="display: none;">
                                                        <div class="p-2 sticky top-0 bg-slate-50 border-b border-slate-100 z-10 flex flex-col gap-2">
                                                            <input type="text" x-model="search" placeholder="Search name or programme..." class="w-full text-xs p-1.5 border border-slate-200 rounded-md focus:ring-indigo-500 focus:border-indigo-500" @click.stop>
                                                            <div class="flex justify-between gap-2">
                                                                <button type="button" @click.stop="selected = [...new Set([...selected, ...filteredOptions.map(o => o.id)])]" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-wider px-2 py-1 bg-indigo-50 hover:bg-indigo-100 rounded flex-1">Select All</button>
                                                                <button type="button" @click.stop="selected = []" class="text-[10px] font-bold text-slate-500 hover:text-slate-700 uppercase tracking-wider px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded flex-1">Clear</button>
                                                            </div>
                                                        </div>
                                                        <div class="py-1 max-h-40 overflow-y-auto">
                                                            <template x-for="option in filteredOptions" :key="option.id">
                                                                <label class="flex items-center px-3 py-2 hover:bg-indigo-50 cursor-pointer">
                                                                    <input type="checkbox" :value="option.id" x-model="selected" class="rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 mr-2 w-3.5 h-3.5">
                                                                    <span class="text-xs text-slate-700 flex flex-col"><span x-text="option.name"></span><span class="text-[9px] text-slate-400 font-bold tracking-wider" x-text="option.program"></span></span>
                                                                </label>
                                                            </template>
                                                        </div>
                                                        <div class="p-2 border-t border-slate-100 bg-slate-50">
                                                            <textarea name="custom_message" placeholder="Custom Message (Optional)..." rows="2" class="w-full text-xs p-1 mb-2 border border-slate-200 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                                            <button type="button" @click.stop="open = false" class="w-full py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold uppercase tracking-wider rounded">Done</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <button type="submit" class="text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-1.5 rounded-lg text-xs font-bold transition-colors shadow-sm shrink-0">
                                                    Assign to All
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif

                                {{-- Schedule Form (Admin only) --}}
                                @if($template->studentMilestones->count() > 0 && (!isset($isCoordinator) || !$isCoordinator))
                                <div x-ref="scheduleForm" x-show="showStudents" x-cloak class="mb-6 bg-gradient-to-br from-slate-50 via-emerald-50/20 to-white p-5 rounded-2xl border border-emerald-100 shadow-sm">
                                    <form action="{{ route('admin.milestone-templates.schedule') }}" method="POST">
                                        @csrf
                                        <template x-for="id in selected">
                                            <input type="hidden" name="milestone_ids[]" :value="id">
                                        </template>

                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-emerald-600/10 text-emerald-700 flex items-center justify-center font-bold">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
                                                        <span>{{ $scheduledCount > 0 ? 'Add Students to Schedule' : 'Generate Presentation Schedule' }}</span>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">Automated</span>
                                                    </h4>
                                                    <p class="text-xs text-slate-500 font-medium">{{ $scheduledCount > 0 ? 'Select unscheduled students below and add them to the existing schedule. Fields are pre-filled from the current schedule.' : 'Batch assign defence dates, presentation time, and Zoom meeting link to selected students.' }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-colors"
                                                    :class="selected.length > 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200'">
                                                    <span class="w-2 h-2 rounded-full" :class="selected.length > 0 ? 'bg-emerald-600 animate-pulse' : 'bg-slate-400'"></span>
                                                    <span x-text="selected.length + ' student' + (selected.length === 1 ? '' : 's') + ' selected'"></span>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    <span>Start Date</span>
                                                    <span class="text-red-500">*</span>
                                                </label>
                                                <input type="date" name="start_date" required value="{{ $lastScheduledDate ?? '' }}"
                                                    class="w-full px-3 py-2 bg-white rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm transition">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span>Start Time</span>
                                                </label>
                                                <input type="time" name="start_time" value="{{ $existingTime ?? '09:00' }}" 
                                                    class="w-full px-3 py-2 bg-white rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm transition">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                    <span>Students / Day</span>
                                                    <span class="text-red-500">*</span>
                                                </label>
                                                <input type="number" name="students_per_day" value="5" min="1" required 
                                                    class="w-full px-3 py-2 bg-white rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm transition">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                    <span>Zoom / Meeting Link</span>
                                                </label>
                                                <input type="url" name="meeting_link" value="{{ $existingLink ?? '' }}" placeholder="https://zoom.us/j/..." 
                                                    class="w-full px-3 py-2 bg-white rounded-xl border border-slate-200 text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm transition">
                                            </div>
                                        </div>

                                        <div class="mt-4 pt-3.5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                                            <p class="text-xs text-slate-400 font-medium flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-emerald-600/70 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Weekends are automatically skipped. Only supervisor-approved candidates will be scheduled.</span>
                                            </p>
                                            <button type="submit" 
                                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-emerald-600/20 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none" 
                                                :disabled="selected.length === 0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span>Generate Schedule</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                @endif

                                {{-- Student List (hidden until "View Student Details" is clicked) --}}
                                <div x-show="showStudents" x-cloak x-transition>
                                    @if($template->studentMilestones->count() > 0)
                                    {{-- Search and Filter --}}
                                    <div class="mb-4 flex flex-col md:flex-row gap-4">
                                        <div class="relative flex-1">
                                            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                            <input x-ref="searchInput" x-model="search" type="text" placeholder="Search by name or matric number..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                        </div>
                                        <div class="w-full md:w-64">
                                            <select x-model="cohortFilter" class="w-full py-2.5 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                                <option value="">All Cohorts (Sets/Batches)</option>
                                                @foreach($cohorts as $cohort)
                                                    <option value="{{ $cohort->id }}">{{ $cohort->name }} ({{ $cohort->intake_year }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="overflow-hidden border border-slate-100 rounded-xl">
                                        <div class="max-h-[400px] overflow-y-auto">
                                            <table class="w-full text-left text-sm">
                                                <thead class="bg-slate-50 border-b border-slate-100 sticky top-0 z-10">
                                                    <tr>
                                                        @if(!isset($isCoordinator) || !$isCoordinator)
                                                        <th class="px-4 py-3 w-10">
                                                            <input type="checkbox" x-model="selectAll" 
                                                                @change="if(selectAll) { 
                                                                    let visibleRows = Array.from($root.querySelectorAll('tr[data-milestone-id]')).filter(row => row.style.display !== 'none'); 
                                                                    selected = visibleRows.filter(row => { const cb = row.querySelector('input[type=checkbox]'); return cb && !cb.disabled; }).map(row => row.getAttribute('data-milestone-id')); 
                                                                } else { selected = []; }" 
                                                                class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                                        </th>
                                                        @endif
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Student</th>
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Status</th>
                                                        <th class="px-4 py-3 font-semibold text-slate-700">{{ $template->allow_defence_date ? 'Schedule & Meeting' : ($template->slug === 'supervisors_assigned' ? 'Assigned Supervisors' : 'Details') }}</th>
                                                        @if(in_array($template->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2']))
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Examiner(s)</th>
                                                        @if($template->slug === 'seminar_as_a_course')
                                                        <th class="px-4 py-3 font-semibold text-slate-700">PPT</th>
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Score</th>
                                                        @else
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Grade / Result</th>
                                                        @endif
                                                        @endif
                                                        <th class="px-4 py-3 w-16 text-right font-semibold text-slate-700"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100">
                                                    @foreach($template->studentMilestones as $sm)
                                                        @php
                                                            $latestSub = $sm->submissions->sortByDesc('created_at')->first();
                                                            $hasAcceptedUpload = $latestSub && $latestSub->feedback && $latestSub->feedback->decision === 'approved';
                                                            $hasRejectedUpload = $latestSub && $latestSub->feedback && $latestSub->feedback->decision === 'revision_required';
                                                            $hasUpload = $latestSub !== null;

                                                            if ($sm->status === 'approved') {
                                                                $detailedStatus = 'Approved';
                                                                $statusColor = 'bg-emerald-100 text-emerald-800 border border-emerald-200';
                                                            } elseif ($sm->status === 'revision_required' || $hasRejectedUpload) {
                                                                $detailedStatus = 'Revision Required';
                                                                $statusColor = 'bg-rose-100 text-rose-800 border border-rose-200';
                                                            } elseif ($sm->is_supervisor_approved) {
                                                                $detailedStatus = 'Approved by Supervisor';
                                                                $statusColor = 'bg-emerald-100 text-emerald-800 border border-emerald-200';
                                                            } elseif ($sm->status === 'submitted' || $hasUpload) {
                                                                $detailedStatus = 'Doc Uploaded (Pending Review)';
                                                                $statusColor = 'bg-blue-100 text-blue-700 border border-blue-200';
                                                            } elseif ($sm->status === 'partially_approved') {
                                                                $detailedStatus = 'Partially Cleared';
                                                                $statusColor = 'bg-indigo-100 text-indigo-700';
                                                            } else {
                                                                $detailedStatus = 'Awaiting Submission';
                                                                $statusColor = 'bg-slate-100 text-slate-600';
                                                            }
                                                            
                                                            $rowEventType = $template->defence_type ?? match($template->slug) {
                                                                'seminar_as_a_course' => 'seminar',
                                                                'proposal_defence' => 'proposal',
                                                                'progress_report_1' => 'progress_report_1',
                                                                'progress_report_2' => 'progress_report_2',
                                                                default => 'first_seminar',
                                                            };
                                                            $event = $sm->thesis ? current($sm->thesis->defenceEvents->where('type', $rowEventType)->all()) : null;
                                                            $rowExaminers = $event ? $event->panelMembers->where('role', 'examiner') : collect();
                                                            if ($rowExaminers->isEmpty() && isset($currentExaminers) && $currentExaminers->isNotEmpty()) {
                                                                $rowExaminers = $currentExaminers->map(fn($u) => (object)[
                                                                    'user' => $u,
                                                                    'user_id' => $u->id,
                                                                    'is_present' => false,
                                                                ]);
                                                            }
                                                            $avgScore = null;
                                                            $passCount = 0;
                                                            $failCount = 0;
                                                            $gradingOutcome = null;

                                                            if ($event && $event->evaluations->count() > 0) {
                                                                $total = 0;
                                                                $count = 0;
                                                                foreach($event->evaluations as $eval) {
                                                                    $verdict = strtolower($eval->verdict ?? $eval->recommendation ?? '');
                                                                    if ($verdict === 'pass') {
                                                                        $passCount++;
                                                                    } elseif ($verdict === 'fail') {
                                                                        $failCount++;
                                                                    }

                                                                    if (isset($eval->score['total'])) {
                                                                        $total += $eval->score['total'];
                                                                        $count++;
                                                                    }
                                                                }
                                                                if ($count > 0) {
                                                                    $avgScore = round($total / $count, 1);
                                                                }
                                                                if (($passCount + $failCount) > 0) {
                                                                    $gradingOutcome = ($passCount >= $failCount) ? 'pass' : 'fail';
                                                                }
                                                            }
                                                            $studentName = $sm->thesis->student->user->name ?? 'N/A';
                                                            $matricNo = $sm->thesis->student->student_id_number ?? 'N/A';
                                                            $cohortId = $sm->thesis->student->cohort_id ?? '';
                                                        @endphp
                                                        <tr data-milestone-id="{{ $sm->id }}" class="hover:bg-slate-50/50 transition-colors" 
                                                            x-show="(cohortFilter === '' || cohortFilter == '{{ $cohortId }}') && (!search || '{{ strtolower($studentName) }}'.includes(search.toLowerCase()) || '{{ strtolower($matricNo) }}'.includes(search.toLowerCase()))">
                                                            @if(!isset($isCoordinator) || !$isCoordinator)
                                                            <td class="px-4 py-3">
                                                                <input type="checkbox" :value="'{{ $sm->id }}'" x-model="selected" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 disabled:opacity-30 disabled:cursor-not-allowed" @if(in_array('Supervisor', $template->required_approvers ?? []) && !$sm->is_supervisor_approved) disabled title="Awaiting Supervisor Approval" @endif>
                                                            </td>
                                                            @endif
                                                            <td class="px-4 py-3">
                                                                <a href="{{ route('admin.students.show', $sm->thesis->student->id) }}" class="font-medium text-brand-600 hover:text-brand-700 hover:underline block">
                                                                    {{ $studentName }}
                                                                </a>
                                                                <div class="text-xs text-slate-500">{{ $matricNo }}</div>
                                                            </td>
                                                            <td class="px-4 py-3">
                                                                @if($latestSub && $latestSub->file_url)
                                                                    <button type="button" 
                                                                        @click.prevent="$dispatch('open-document-preview', { 
                                                                            url: '{{ Storage::url($latestSub->file_url) }}', 
                                                                            title: '{{ addslashes($studentName) }} - {{ addslashes($template->name) }} (v.0{{ $latestSub->version }})',
                                                                            type: '{{ str_ends_with(strtolower($latestSub->file_url), '.pdf') ? 'pdf' : (in_array(pathinfo($latestSub->file_url, PATHINFO_EXTENSION), ['jpg','jpeg','png','webp']) ? 'image' : 'other') }}'
                                                                        })"
                                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold {{ $statusColor }} cursor-pointer hover:opacity-90 hover:shadow-sm active:scale-95 transition-all text-left group"
                                                                        title="Click to preview uploaded document">
                                                                        <span>{{ $detailedStatus }}</span>
                                                                        <svg class="w-3.5 h-3.5 opacity-70 group-hover:opacity-100 transition-opacity shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                        </svg>
                                                                    </button>
                                                                @else
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold {{ $statusColor }}">
                                                                        {{ $detailedStatus }}
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-3 text-slate-600 text-xs">
                                                                @if($template->slug === 'supervisors_assigned')
                                                                    @php
                                                                        $assignedSups = $sm->thesis->assignments->where('status', 'active');
                                                                    @endphp
                                                                    @if($assignedSups->count() > 0)
                                                                        <div class="text-xs">
                                                                            <span class="font-bold text-slate-800">{{ $assignedSups->count() }} Allocated</span>
                                                                            <div class="text-[11px] text-slate-500 truncate max-w-[200px]" title="{{ $assignedSups->map(fn($a) => ($a->role === 'primary' ? '★ ' : '') . ($a->supervisor?->user?->name ?? 'Supervisor'))->implode(', ') }}">
                                                                                {{ $assignedSups->map(fn($a) => ($a->role === 'primary' ? '★ ' : '') . ($a->supervisor?->user?->name ?? 'Supervisor'))->implode(', ') }}
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                                                            Awaiting Allocation
                                                                        </span>
                                                                    @endif
                                                                @elseif($sm->defence_date)
                                                                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                                                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                                        </svg>
                                                                        <span>{{ \Carbon\Carbon::parse($sm->defence_date)->format('M d, Y') }}</span>
                                                                    </div>
                                                                    @if($sm->defence_time)
                                                                        <div class="text-[11px] text-emerald-700 font-semibold mt-0.5 flex items-center gap-1 pl-5">
                                                                            <svg class="w-3 h-3 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                            </svg>
                                                                            <span>{{ \Carbon\Carbon::parse($sm->defence_time)->format('g:i A') }}</span>
                                                                        </div>
                                                                    @endif
                                                                    @if($sm->meeting_link)
                                                                        <div class="mt-1 pl-5">
                                                                            <a href="{{ $sm->meeting_link }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded text-[10px] font-bold transition-colors">
                                                                                <svg class="w-3 h-3 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                                                </svg>
                                                                                <span>Zoom Link</span>
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                @else
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-400">
                                                                        Not scheduled
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            @if(in_array($template->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2']))
                                                            <td class="px-4 py-3 text-xs">
                                                                @if($rowExaminers->count() > 0)
                                                                    <div class="flex flex-col gap-1">
                                                                        @foreach($rowExaminers as $ex)
                                                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-purple-50 text-purple-800 text-[11px] font-bold border border-purple-100 max-w-[170px]" title="{{ $ex->is_present ? 'Present (Graded candidate)' : 'Assigned (Pending grading)' }}">
                                                                                <span class="w-1.5 h-1.5 rounded-full {{ $ex->is_present ? 'bg-emerald-500' : 'bg-purple-300' }}"></span>
                                                                                <span class="truncate">{{ $ex->user?->name ?? 'Examiner' }}</span>
                                                                                @if($ex->is_present)
                                                                                    <span class="text-[9px] text-emerald-600 font-extrabold uppercase shrink-0">Present</span>
                                                                                @endif
                                                                            </span>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                                                        Unassigned
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            @if($template->slug === 'seminar_as_a_course')
                                                            <td class="px-4 py-3 text-xs">
                                                                @if($sm->submissions->count() > 0)
                                                                    <a href="{{ Storage::url($sm->submissions->first()->file_url) }}" target="_blank" class="text-indigo-600 hover:underline">Download</a>
                                                                @else
                                                                    <span class="text-slate-400">Not uploaded</span>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-3 text-xs font-bold">
                                                                @if($avgScore !== null)
                                                                    <span class="text-emerald-600">{{ $avgScore }}</span>
                                                                @else
                                                                    <span class="text-slate-400">-</span>
                                                                @endif
                                                            </td>
                                                            @else
                                                            <td class="px-4 py-3 text-xs">
                                                                @if($gradingOutcome === 'pass')
                                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                                        <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                        <span>PASS</span>
                                                                        <span class="text-[10px] text-emerald-700 font-bold ml-1">({{ $passCount }}/{{ $passCount + $failCount }})</span>
                                                                    </span>
                                                                @elseif($gradingOutcome === 'fail')
                                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-black uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">
                                                                        <svg class="w-3 h-3 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                        <span>FAIL</span>
                                                                        <span class="text-[10px] text-rose-700 font-bold ml-1">({{ $failCount }}/{{ $passCount + $failCount }})</span>
                                                                    </span>
                                                                @else
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-500">
                                                                        Awaiting Grade
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            @endif
                                                            @endif
                                                            <td class="px-4 py-3 text-right">
                                                                <div x-data="{ menuOpen: false, showAssignModal: false }" class="relative inline-block text-left">
                                                                    <button type="button" @click.prevent.stop="menuOpen = !menuOpen" class="p-1.5 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors focus:outline-none">
                                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                                                    </button>
                                                                    <div x-show="menuOpen" @click.outside="menuOpen = false" x-cloak class="absolute right-8 top-0 w-56 bg-white rounded-xl shadow-xl border border-slate-100 z-[60] overflow-hidden text-left" style="display: none;">
                                                                        @if((!isset($isCoordinator) || !$isCoordinator) && $sm->status !== 'approved')
                                                                            <div class="p-1.5 border-b border-slate-100 space-y-1">
                                                                                @if(in_array($template->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2']))
                                                                                <button type="button" @click="showAssignModal = true; menuOpen = false" class="w-full text-left px-3 py-2 text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors flex items-center gap-2">
                                                                                    <svg class="w-3.5 h-3.5 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                                                                    </svg>
                                                                                    <span>Assign Examiner(s)</span>
                                                                                </button>
                                                                                @endif
                                                                                @if(!empty($sm->defence_date) || in_array($template->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2']))
                                                                                <form action="{{ route('milestones.end_presentation', $sm) }}" method="POST">
                                                                                    @csrf
                                                                                    <button type="submit" 
                                                                                        data-confirm="Are you sure you want to end the presentation for {{ addslashes($studentName) }}? If all requirements are met, the milestone will be approved and the student advanced."
                                                                                        data-confirm-title="End Presentation Session"
                                                                                        data-confirm-type="success"
                                                                                        data-confirm-btn="End Presentation"
                                                                                        class="w-full text-left px-3 py-2 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors flex items-center gap-2">
                                                                                        <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                                        </svg>
                                                                                        <span>End Presentation</span>
                                                                                    </button>
                                                                                </form>
                                                                                @endif
                                                                                <form action="{{ route('milestones.review.update', $sm) }}" method="POST">
                                                                                    @csrf
                                                                                    @method('PATCH')
                                                                                    <input type="hidden" name="decision" value="approved">
                                                                                    <input type="hidden" name="remarks" value="Approved by Administrator from Milestone Template Management.">
                                                                                    <button type="submit" 
                                                                                        data-confirm="Are you sure you want to officially approve this milestone for {{ addslashes($studentName) }} and advance them to the next stage?"
                                                                                        data-confirm-title="Approve Milestone & Advance"
                                                                                        data-confirm-type="success"
                                                                                        data-confirm-btn="Approve & Advance"
                                                                                        class="w-full text-left px-3 py-2 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors flex items-center gap-2">
                                                                                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                                                        </svg>
                                                                                        <span>Approve & Advance</span>
                                                                                    </button>
                                                                                </form>
                                                                            </div>
                                                                        @endif
                                                                        <div class="px-3 py-2 bg-slate-50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                                                            Jump to Milestone
                                                                        </div>
                                                                        <div class="max-h-48 overflow-y-auto p-1">
                                                                            @foreach($templates as $t)
                                                                                @php
                                                                                    $targetSm = $sm->thesis->milestones->where('milestone_template_id', $t->id)->first();
                                                                                @endphp
                                                                                @if($targetSm)
                                                                                <button type="button" onclick="jumpMilestone('{{ $sm->thesis->student->id }}', '{{ $targetSm->id }}')" class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-brand-50 hover:text-brand-600 rounded-lg transition-colors flex items-center justify-between group">
                                                                                    <span>{{ $t->name }}</span>
                                                                                    @if($t->id === $template->id)
                                                                                        <svg class="w-3 h-3 text-brand-500 opacity-50 shrink-0 ml-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                                                    @endif
                                                                                </button>
                                                                                @endif
                                                                            @endforeach
                                                                        </div>
                                                                    </div>

                                                                    {{-- Individual Student Examiner Assignment Modal --}}
                                                                    @if(in_array($template->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2']))
                                                                    <div x-show="showAssignModal" @click.outside="showAssignModal = false" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" style="display: none;">
                                                                        <div @click.stop class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4 text-left">
                                                                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                                                                <div>
                                                                                    <h4 class="text-sm font-black text-slate-900">Assign Examiner(s)</h4>
                                                                                    <p class="text-xs text-slate-500 mt-0.5">{{ $studentName }} — {{ $template->name }}</p>
                                                                                </div>
                                                                                <button type="button" @click="showAssignModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
                                                                            </div>
                                                                            <form action="{{ (auth()->user()->hasRole('Program Coordinator') && !auth()->user()->hasRole('Admin')) ? route('coordinator.milestone-templates.assign-examiner', $sm->id) : route('admin.milestone-templates.assign-examiner', $sm->id) }}" method="POST" class="space-y-4">
                                                                                @csrf
                                                                                <div>
                                                                                    <label class="block text-xs font-bold text-slate-700 mb-2">Select Examiner(s)</label>
                                                                                    <div class="max-h-56 overflow-y-auto border border-slate-200 rounded-xl divide-y divide-slate-100 p-1">
                                                                                        @foreach($supervisors as $sup)
                                                                                            <label class="flex items-center gap-2.5 p-2 hover:bg-indigo-50/50 rounded-lg cursor-pointer">
                                                                                                <input type="checkbox" name="supervisor_profile_ids[]" value="{{ $sup->id }}" {{ $rowExaminers->pluck('user_id')->contains($sup->user_id) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                                                                <div class="text-xs">
                                                                                                    <span class="font-bold text-slate-800">{{ $sup->user->name }}</span>
                                                                                                    <span class="text-[10px] text-slate-400 block font-semibold">{{ $sup->programs->first()->code ?? 'Supervisor' }}</span>
                                                                                                </div>
                                                                                            </label>
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                                                                                    <button type="button" @click="showAssignModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancel</button>
                                                                                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">Save Examiner(s)</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @else
                                    <div class="text-center py-8 text-slate-500 text-sm">
                                        No active students currently at this milestone.
                                    </div>
                                    @endif
                                </div>

                                {{-- Empty state when no students and panel is expanded --}}
                                @if($template->studentMilestones->count() === 0)
                                <div class="text-center py-8 text-slate-500 text-sm">
                                    No active students currently at this milestone.
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    </tbody>

                    <tbody>@empty
                    <tr>
                        <td colspan="5" class="px-10 py-20 text-center bg-slate-50/50">
                            <div class="max-w-xs mx-auto">
                                <div class="w-16 h-16 bg-white rounded-3xl border border-slate-100 flex items-center justify-center mx-auto mb-6 shadow-sm">
                                    <svg class="w-8 h-8 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                                </div>
                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">No Milestones Found</h4>
                                <p class="text-xs text-slate-500 mt-2 font-medium">No milestone templates have been created yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </table>
        </div>
    </div>
</div>

<form id="global-jump-form" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="milestone_id" id="global-jump-target">
</form>

@push('scripts')
<script>
    async function jumpMilestone(studentId, targetSlug) {
        const ok = await window.confirmModal({
            title: 'Change Milestone Protocol',
            message: 'Are you sure you want to change this student\'s milestone? This will reset their progress for future milestones.',
            type: 'warning',
            confirmText: 'Change Milestone',
            cancelText: 'Cancel'
        });
        if (!ok) return;
        const form = document.getElementById('global-jump-form');
        form.action = '{{ url("admin/students") }}/' + studentId + '/set-milestone';
        document.getElementById('global-jump-target').value = targetSlug;
        form.submit();
    }
</script>
@endpush

@endsection

