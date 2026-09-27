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
                    <tbody x-data="{ expanded: false, showStudents: false, selectAll: false, selected: [], search: '' }" class="divide-y divide-slate-50 border-t border-slate-50">
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
                                        <button @click="showDatePicker = !showDatePicker" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            {{ $template->global_defence_date->format('M d, Y') }}
                                        </button>
                                    @else
                                        <button @click="showDatePicker = !showDatePicker" class="inline-flex items-center gap-2 px-4 py-2 {{ ($template->global_defence_date && \Carbon\Carbon::parse($template->global_defence_date)->isPast()) ? 'bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 shadow-sm shadow-red-100' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-sm' }} rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            {{ ($template->global_defence_date && \Carbon\Carbon::parse($template->global_defence_date)->isPast()) ? 'Expired / Set New Date' : 'Set Date' }}
                                        </button>
                                    @endif

                                    <!-- Dropdown Date Picker -->
                                    <div x-show="showDatePicker" @click.away="showDatePicker = false" class="absolute right-0 mt-2 p-4 bg-white border border-slate-100 rounded-2xl shadow-xl shadow-slate-200/50 z-50 w-64" style="display: none;">
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
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
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
                                </div>

                                {{-- Actions Row --}}
                                <div class="flex flex-wrap items-center gap-3 mb-6">
                                    <a href="{{ isset($isCoordinator) && $isCoordinator ? route('coordinator.milestone-templates.export-students', $template->id) : route('admin.milestone-templates.export-students', $template->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Export CSV
                                    </a>

                                    @if($template->studentMilestones->count() > 0)
                                        <button @click="showStudents = !showStudents; if(showStudents) $nextTick(() => $refs.searchInput?.focus())" 
                                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                                            :class="showStudents ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            <span x-text="showStudents ? 'Hide Students' : 'View Student Details'"></span>
                                        </button>
                                    @endif
                                </div>

                                {{-- Global Examiner Assignment (Seminar only, Admin only) --}}
                                @if($template->slug === 'seminar_as_a_course' && (!isset($isCoordinator) || !$isCoordinator))
                                    @php
                                        $firstEvent = $template->studentMilestones->first() 
                                            ? current($template->studentMilestones->first()->thesis->defenceEvents->where('type', $template->defence_type ?? 'seminar')->all()) 
                                            : null;
                                        $currentExaminer = $firstEvent ? current($firstEvent->panelMembers->where('role', 'Examiner')->all()) : null;
                                    @endphp
                                    <div class="mb-6 bg-indigo-50/50 border border-indigo-100 rounded-xl p-4">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-black text-indigo-900 uppercase tracking-wider">Seminar Examiner</p>
                                                    <p class="text-[10px] text-indigo-600 mt-0.5">
                                                        @if($currentExaminer)
                                                            Currently: <strong>{{ $currentExaminer->user->name }}</strong>
                                                        @else
                                                            No examiner assigned yet.
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <form action="{{ route('admin.milestone-templates.assign-examiner-global', $template->id) }}" method="POST" class="flex items-center gap-2 sm:ml-auto">
                                                @csrf
                                                <select name="supervisor_profile_id" required class="block py-1.5 pl-3 pr-8 text-xs border-indigo-200 bg-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-lg">
                                                    <option value="">Select Examiner</option>
                                                    @foreach($supervisors as $sup)
                                                        <option value="{{ $sup->id }}" {{ $currentExaminer && $currentExaminer->user_id == $sup->user_id ? 'selected' : '' }}>
                                                            {{ $sup->user->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                                    Assign to All
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif

                                {{-- Schedule Form (Admin only) --}}
                                @if($template->studentMilestones->count() > 0 && (!isset($isCoordinator) || !$isCoordinator))
                                <div x-show="showStudents" x-cloak class="mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
                                    <form action="{{ route('admin.milestone-templates.schedule') }}" method="POST" class="flex flex-wrap gap-4 items-end">
                                        @csrf
                                        <template x-for="id in selected">
                                            <input type="hidden" name="milestone_ids[]" :value="id">
                                        </template>
                                        
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1">Start Date</label>
                                            <input type="date" name="start_date" required class="px-3 py-1.5 rounded-lg border border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1">Students / Day</label>
                                            <input type="number" name="students_per_day" value="5" min="1" required class="w-24 px-3 py-1.5 rounded-lg border border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                        </div>
                                        <div>
                                            <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-xs font-bold transition-colors" :disabled="selected.length === 0" :class="{'opacity-50 cursor-not-allowed': selected.length === 0}">
                                                Generate Schedule
                                            </button>
                                        </div>
                                        <div class="ml-auto text-xs text-slate-500 self-center">
                                            <span x-text="selected.length"></span> student(s) selected.
                                        </div>
                                    </form>
                                </div>
                                @endif

                                {{-- Student List (hidden until "View Student Details" is clicked) --}}
                                <div x-show="showStudents" x-cloak x-transition>
                                    @if($template->studentMilestones->count() > 0)
                                    {{-- Search --}}
                                    <div class="mb-4">
                                        <div class="relative">
                                            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                            <input x-ref="searchInput" x-model="search" type="text" placeholder="Search by name or matric number..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                        </div>
                                    </div>

                                    <div class="overflow-hidden border border-slate-100 rounded-xl">
                                        <div class="max-h-[400px] overflow-y-auto">
                                            <table class="w-full text-left text-sm">
                                                <thead class="bg-slate-50 border-b border-slate-100 sticky top-0 z-10">
                                                    <tr>
                                                        @if(!isset($isCoordinator) || !$isCoordinator)
                                                        <th class="px-4 py-3 w-10">
                                                            <input type="checkbox" x-model="selectAll" @change="selected = selectAll ? [{{ $template->studentMilestones->map(fn($m) => "'" . $m->id . "'")->implode(',') }}] : []" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                                        </th>
                                                        @endif
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Student</th>
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Status</th>
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Date</th>
                                                        @if($template->slug === 'seminar_as_a_course')
                                                        <th class="px-4 py-3 font-semibold text-slate-700">PPT</th>
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Score</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100">
                                                    @foreach($template->studentMilestones as $sm)
                                                        @php
                                                            $event = current($sm->thesis->defenceEvents->where('type', $template->defence_type ?? 'seminar')->all());
                                                            $avgScore = null;
                                                            if ($event && $event->evaluations->count() > 0) {
                                                                $total = 0;
                                                                $count = 0;
                                                                foreach($event->evaluations as $eval) {
                                                                    if (isset($eval->score['total'])) {
                                                                        $total += $eval->score['total'];
                                                                        $count++;
                                                                    }
                                                                }
                                                                if ($count > 0) {
                                                                    $avgScore = round($total / $count, 1);
                                                                }
                                                            }
                                                            $studentName = $sm->thesis->student->user->name ?? 'N/A';
                                                            $matricNo = $sm->thesis->student->matric_number ?? 'N/A';
                                                        @endphp
                                                        <tr class="hover:bg-slate-50/50 transition-colors" 
                                                            x-show="!search || '{{ strtolower($studentName) }}'.includes(search.toLowerCase()) || '{{ strtolower($matricNo) }}'.includes(search.toLowerCase())">
                                                            @if(!isset($isCoordinator) || !$isCoordinator)
                                                            <td class="px-4 py-3">
                                                                <input type="checkbox" :value="'{{ $sm->id }}'" x-model="selected" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                                            </td>
                                                            @endif
                                                            <td class="px-4 py-3">
                                                                <div class="font-medium text-slate-900">{{ $studentName }}</div>
                                                                <div class="text-xs text-slate-500">{{ $matricNo }}</div>
                                                            </td>
                                                            <td class="px-4 py-3">
                                                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-medium">{{ ucfirst(str_replace('_', ' ', $sm->status)) }}</span>
                                                            </td>
                                                            <td class="px-4 py-3 text-slate-600 text-xs">
                                                                {{ $sm->defence_date ? \Carbon\Carbon::parse($sm->defence_date)->format('M d, Y') : 'Not scheduled' }}
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
                                                            @endif
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
@endsection