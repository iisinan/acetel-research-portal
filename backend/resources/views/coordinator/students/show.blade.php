@extends('layouts.coordinator')

@section('header', 'Student Details')

@section('content')
<div x-data="{ 
    changeModalOpen: false, 
    changeAssignmentId: '', 
    changeSupervisorId: '' 
}" class="space-y-10 pb-10">
    <!-- Sophisticated Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div class="flex items-center gap-6">
            <a href="{{ route('coordinator.students.index') }}" class="w-12 h-12 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-400 hover:border-acetel-500 hover:text-acetel-500 transition-all hover:-translate-x-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
            </a>
            <div>
                <div class="flex items-center gap-3 mb-2 text-acetel-600">
                    <div class="p-1.5 rounded-lg bg-acetel-50">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-[0.3em]">Student Profile</span>
                </div>
                <h1 class="text-2xl md:text-4xl font-black text-slate-900 tracking-tight">{{ $student->user->name }}</h1>
                <p class="mt-2 text-sm font-medium text-slate-500">Official student record and progress tracking.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl @if($student->enrollment_status === 'active') bg-emerald-50 text-emerald-600 @else bg-slate-100 text-slate-500 @endif text-xs font-black uppercase tracking-widest border border-current/10 shadow-sm">
                @if($student->enrollment_status === 'active')
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                @endif
                {{ $student->enrollment_status }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <!-- Main Intel Matrix (Spans 8) -->
        <div class="lg:col-span-8 space-y-10">
            <!-- Identity & Academic Vector -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 p-5 md:p-10 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-64 h-64 bg-acetel-50 rounded-full blur-[80px] -mr-32 -mt-32 opacity-50 group-hover:opacity-100 transition-opacity duration-1000"></div>
                
                <h3 class="text-xl font-black text-slate-900 mb-8 tracking-tight relative z-10 flex items-center gap-3">
                    Academic Profile
                    <span class="text-[10px] font-bold text-acetel-500 bg-acetel-50 px-2 py-0.5 rounded-full uppercase tracking-tighter shadow-sm border border-acetel-100/50">Verified</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 relative z-10">
                    <div class="space-y-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email Address</p>
                                <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $student->user->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h2a2 2 0 012 2v1m-4 0h4" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Matric Number</p>
                                <p class="text-sm font-bold text-slate-900 mt-0.5 text-acetel-600">{{ $student->student_id_number }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl @if(str_contains(strtolower($student->level->name ?? ''), 'phd')) bg-acetel-50 text-acetel-500 @else bg-acetel-50 text-acetel-500 @endif flex items-center justify-center">
                                @if(str_contains(strtolower($student->level->name ?? ''), 'phd'))
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" /><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm0 0V6" /></svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Academic Level</p>
                                <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $student->level->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Program</p>
                                <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $student->program->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-slate-50 flex flex-wrap gap-4 relative z-10">
                    @if($student->cohort)
                        <div class="px-6 py-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center gap-4">
                            <div class="w-2 h-2 rounded-full bg-purple-500 shadow-[0_0_10px_rgba(168,85,247,0.5)]"></div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none">Cohort</p>
                                <p class="text-xs font-black text-slate-900 mt-1.5">{{ $student->cohort->name }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="px-6 py-4 rounded-2xl bg-emerald-50/50 border border-emerald-100 flex items-center gap-4">
                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                        <div>
                            <p class="text-[9px] font-black text-emerald-600 uppercase tracking-[0.2em] leading-none">Admission Date</p>
                            <p class="text-xs font-black text-emerald-700 mt-1.5">{{ $student->user->created_at?->format('F Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Research Telemetry / Thesis Card -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden relative group">
                <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Thesis Details</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Current Status</p>
                    </div>
                    @if($student->thesis)
                        <span class="px-4 py-2 rounded-xl @if($student->thesis->status === 'active') bg-emerald-50 text-emerald-600 @else bg-slate-100 text-slate-500 @endif text-[10px] font-black uppercase tracking-widest shadow-sm ring-1 ring-current/10">
                            {{ $student->thesis->status }}
                        </span>
                    @endif
                </div>
                
                <div class="p-10">
                    @if($student->thesis)
                        <div class="space-y-10">
                            <div>
                                <h4 class="text-[10px] font-black text-acetel-500 uppercase tracking-[0.2em] mb-4">Thesis Title</h4>
                                <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100/50 relative overflow-hidden">
                                    <div class="absolute top-0 left-0 w-1.5 h-full bg-acetel-500"></div>
                                    <p class="text-xl font-black text-slate-900 leading-tight tracking-tight">{{ $student->thesis->title }}</p>
                                </div>
                            </div>

                            @if($student->thesis->abstract)
                                <div>
                                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Abstract</h4>
                                    <p class="text-sm text-slate-600 leading-relaxed font-medium bg-white p-2 rounded-xl italic">
                                        "{{ $student->thesis->abstract }}"
                                    </p>
                                </div>
                            @endif

                            <div class="pt-8 border-t border-slate-50">
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Milestone Tracker</h4>
                                @php
                                    $allMilestones = $student->thesis->milestones()->with('template')->get()->sortBy('template.order');
                                @endphp
                                <div class="space-y-3 mb-8 max-h-[300px] overflow-y-auto pr-2">
                                    @foreach($allMilestones as $m)
                                        <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100/50">
                                            <div class="flex items-center gap-4">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs {{ $m->status === 'approved' ? 'bg-emerald-100 text-emerald-600' : ($m->status === 'submitted' ? 'bg-amber-100 text-amber-600' : 'bg-slate-200 text-slate-500') }}">
                                                    {{ $m->template->order }}
                                                </div>
                                                <div>
                                                    <span class="text-xs font-bold text-slate-900">{{ $m->template->name }}</span>
                                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Updated: {{ $m->updated_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                            <div>
                                                @if($m->status === 'approved')
                                                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-widest border border-emerald-100">Cleared</span>
                                                @elseif($m->status === 'submitted' || $m->status === 'partially_approved')
                                                    <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-600 text-[9px] font-black uppercase tracking-widest border border-amber-100">Review</span>
                                                @else
                                                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-[9px] font-black uppercase tracking-widest border border-slate-200">Pending</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <a href="{{ route('milestones.index', ['thesis_id' => $student->thesis->id]) }}" class="group w-full flex items-center justify-between px-8 py-5 bg-slate-900 text-white rounded-[2rem] font-black uppercase tracking-[0.1em] text-xs hover:bg-acetel-600 transition-all shadow-xl shadow-slate-900/10 hover:shadow-acetel-500/20">
                                    <span>Manage Milestones Detailed View</span>
                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7-7 7M3 12h18" /></svg>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="py-20 text-center">
                            <div class="w-24 h-24 rounded-[2.5rem] bg-slate-50 border border-slate-100 flex items-center justify-center mb-8 mx-auto shadow-inner">
                                <svg class="w-10 h-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            </div>
                            <h4 class="text-lg font-black text-slate-900 tracking-tight">No Thesis Assigned</h4>
                            <p class="text-sm text-slate-500 mt-2 max-w-xs mx-auto">No active thesis has been assigned to this student yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Components (Spans 4) -->
        <div class="lg:col-span-4 space-y-10">
            <!-- Mentor Allocation Hub -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden group">
                <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/20 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Supervision</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Supervision Status</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-acetel-50 text-acetel-500 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" /></svg>
                    </div>
                </div>
                
                <div class="p-10 space-y-6">
                    @php $assignments = $student->thesis?->assignments->where('status', 'active') ?? collect(); @endphp
                    @forelse($assignments as $assignment)
                        <div class="flex items-center gap-4 p-5 bg-slate-50/50 rounded-2xl border border-slate-100 hover:border-acetel-200 transition-colors group/item">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 font-black text-sm group-hover/item:text-acetel-500 group-hover/item:border-acetel-200 transition-all">
                                {{ substr($assignment->supervisor?->user?->name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-black text-slate-900 truncate tracking-tight">{{ $assignment->supervisor?->user?->name }}</h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[8px] font-black text-acetel-600 uppercase tracking-widest italic">{{ $assignment->role }}</span>
                                    <span class="w-0.5 h-0.5 rounded-full bg-slate-300"></span>
                                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter">Main Supervisor</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="changeAssignmentId = '{{ $assignment->id }}'; changeSupervisorId = '{{ $assignment->supervisor_profile_id }}'; changeModalOpen = true" class="p-2 text-slate-300 hover:text-acetel-600 transition-colors" title="Change Supervisor">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <form action="{{ route('coordinator.supervisors.unassign-student', [$assignment->supervisor, $student->thesis]) }}" method="POST"
                                    data-confirm="Are you sure you want to remove this supervisor from {{ $student->user->name }}'s committee?"
                                    data-confirm-title="Remove Supervisor"
                                    data-confirm-type="danger"
                                    data-confirm-btn="Remove Supervisor">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-300 hover:text-rose-500 transition-colors" title="Remove Supervisor">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                                <a href="{{ route('coordinator.supervisors.show', $assignment->supervisor) }}" class="p-2 text-slate-300 hover:text-acetel-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center bg-slate-50/50 rounded-3xl border-2 border-dashed border-slate-100">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-loose">No Supervisors Assigned</p>
                        </div>
                    @endforelse

                    @if($student->isSeminarCourseLevel())
                        <div class="pt-6 border-t border-slate-50">
                            <div class="p-6 bg-amber-50 rounded-2xl border border-amber-100 text-center">
                                <p class="text-xs font-bold text-amber-800">Students at the seminar course level do not require a supervisor.</p>
                            </div>
                        </div>
                    @elseif($student->thesis)
                        <div class="pt-6 border-t border-slate-50">
                            <button class="w-full px-6 py-5 bg-slate-900 text-white rounded-3xl text-[10px] font-black uppercase tracking-widest hover:bg-acetel-600 transition-all shadow-xl shadow-slate-900/10 flex items-center justify-center gap-3 group" onclick="document.getElementById('assign-form-container').classList.toggle('hidden')">
                                <svg class="w-4 h-4 group-hover:rotate-90 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                Assign Supervisor
                            </button>
                            
                            <div id="assign-form-container" class="hidden mt-8 animate-in-up">
                                <div x-data="{ selectedRank: '' }">
                                <form action="{{ route('coordinator.students.assign-supervisor', $student) }}" method="POST" class="space-y-6 bg-slate-50 p-6 rounded-3xl border border-slate-100">
                                    @csrf
                                    <div>
                                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-3 pl-1">Select Supervisor</label>
                                        <div class="relative">
                                            <select name="supervisor_id" 
                                                    x-on:change="selectedRank = $event.target.options[$event.target.selectedIndex].dataset.rank"
                                                    class="w-full bg-white border-slate-200 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-acetel-500 py-3.5 pl-4 pr-10 appearance-none shadow-sm">
                                                <option value="" data-rank="">Choose Supervisor...</option>
                                                @foreach($availableSupervisors as $sup)
                                                    <option value="{{ $sup->id }}" data-rank="{{ $sup->rank ?? '' }}">
                                                        {{ $sup->user->name }} [{{ $sup->rank ?? 'Lecturer' }}] ({{ $sup->assignments->count() }}/{{ $sup->max_students }} Load)
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-3 pl-1">Role</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <label class="relative block cursor-pointer group/radio">
                                                <input type="radio" name="type" value="main" class="peer sr-only" checked :disabled="selectedRank && selectedRank !== 'Professor'">
                                                <div class="p-4 bg-white border border-slate-200 rounded-2xl text-center group-hover/radio:border-acetel-500 peer-checked:bg-acetel-500 peer-checked:border-acetel-500 transition-all shadow-sm peer-disabled:opacity-40 peer-disabled:grayscale peer-disabled:cursor-not-allowed">
                                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-900 peer-checked:text-white">Main</p>
                                                    <p class="text-[8px] font-bold text-slate-400 mt-1 peer-checked:text-acetel-100 uppercase tracking-tighter">Professor Only</p>
                                                </div>
                                            </label>
                                            <label class="relative block cursor-pointer group/radio">
                                                <input type="radio" name="type" value="co-supervisor" class="peer sr-only">
                                                <div class="p-4 bg-white border border-slate-200 rounded-2xl text-center group-hover/radio:border-acetel-500 peer-checked:bg-acetel-500 peer-checked:border-acetel-500 transition-all shadow-sm">
                                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 peer-checked:text-white/70">Co-Supervisor</p>
                                                    <p class="text-[10px] font-black text-slate-900 mt-1 peer-checked:text-white">Assistant</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full py-4 bg-acetel-500 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl shadow-lg shadow-acetel-500/20 hover:scale-105 active:scale-95 transition-all">
                                        Assign Supervisor
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Audit Board Card -->
            <div class="bg-slate-900 rounded-[2.5rem] border border-slate-800 shadow-2xl relative overflow-visible group" x-data="{ openAssign: null }">
                <div class="p-10 relative overflow-hidden rounded-t-[2.5rem]">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-acetel-500/10 rounded-full blur-[40px] -mr-16 -mt-16 group-hover:bg-acetel-500/20 transition-all duration-1000"></div>
                
                    <h3 class="text-lg font-black text-white mb-8 tracking-tight relative z-10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            Examination Board
                            <span class="text-[9px] font-bold text-acetel-400 border border-acetel-500/30 px-2 py-0.5 rounded-full uppercase tracking-tighter">Status</span>
                        </div>
                    </h3>

                    @php
                        $internalAssign = $student->thesis ? $student->thesis->examinerAssignments()->where('type', 'internal_examiner')->where('active', true)->first() : null;
                        $externalAssign = $student->thesis ? $student->thesis->examinerAssignments()->where('type', 'program_examiner')->where('active', true)->first() : null;
                    @endphp

                    <div class="space-y-4 relative z-10">
                        {{-- Internal Examiner Slot --}}
                        @if($student->thesis && $internalAssign)
                            <div class="p-5 bg-white/5 rounded-2xl border border-white/5 flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-acetel-500/20 text-acetel-400 flex items-center justify-center font-black">
                                        {{ substr($internalAssign->examiner->name ?? 'I', 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-white truncate">{{ $internalAssign->examiner->name ?? 'Unknown' }}</h4>
                                        <p class="text-[9px] font-black text-acetel-400 uppercase tracking-widest mt-0.5">Internal Examiner</p>
                                    </div>
                                </div>
                                <button type="button" @click="openAssign = openAssign === 'internal' ? null : 'internal'" class="text-xs font-black uppercase text-slate-400 hover:text-white transition-colors" title="Change">
                                    Change
                                </button>
                            </div>
                        @else
                            <button @click="openAssign = openAssign === 'internal' ? null : 'internal'" class="w-full p-5 text-left bg-white/5 hover:bg-white/10 rounded-2xl border border-white/5 border-dashed hover:border-acetel-500/50 transition-all flex items-center justify-between group/btn">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-500 group-hover/btn:text-acetel-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-white">Add Internal Examiner</h4>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Select from faculty</p>
                                    </div>
                                </div>
                            </button>
                        @endif

                        {{-- Forms for Internal Examiner --}}
                        <div x-show="openAssign === 'internal'" x-collapse x-cloak class="mt-4 pb-4">
                            <form action="{{ route('coordinator.students.assign-internal', $student->thesis->id ?? 0) }}" method="POST" class="bg-slate-800 p-5 rounded-2xl space-y-4 border border-slate-700">
                                @csrf
                                <div>
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2">Select Internal Examiner</label>
                                    <select name="examiner_id" required class="w-full bg-slate-900 border-slate-700 text-white rounded-xl text-xs py-3 focus:ring-acetel-500/30 focus:border-acetel-500">
                                        <option value="">Choose...</option>
                                        @foreach($availableInternalExaminers as $iex)
                                            <option value="{{ $iex->user->id }}">{{ $iex->user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="w-full py-3 bg-acetel-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-acetel-700">Confirm Assignment</button>
                            </form>
                        </div>

                        {{-- External Examiner Slot --}}
                        @if($student->thesis && $externalAssign)
                            <div class="p-5 bg-white/5 rounded-2xl border border-white/5 flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center font-black">
                                        {{ substr($externalAssign->examiner->name ?? 'E', 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-white truncate">{{ $externalAssign->examiner->name ?? 'Unknown' }}</h4>
                                        <p class="text-[9px] font-black text-purple-400 uppercase tracking-widest mt-0.5">External Examiner</p>
                                    </div>
                                </div>
                                <button type="button" @click="openAssign = openAssign === 'external' ? null : 'external'" class="text-xs font-black uppercase text-slate-400 hover:text-white transition-colors" title="Change">
                                    Change
                                </button>
                            </div>
                        @else
                            <button @click="openAssign = openAssign === 'external' ? null : 'external'" class="w-full p-5 text-left bg-white/5 hover:bg-white/10 rounded-2xl border border-white/5 border-dashed hover:border-purple-500/50 transition-all flex items-center justify-between group/btn">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-500 group-hover/btn:text-purple-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-white">Add External/Program Examiner</h4>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Select from panel</p>
                                    </div>
                                </div>
                            </button>
                        @endif

                        {{-- Forms for External Examiner --}}
                        <div x-show="openAssign === 'external'" x-collapse x-cloak class="mt-4 pb-4">
                            <form action="{{ route('coordinator.students.assign-external', $student->thesis->id ?? 0) }}" method="POST" class="bg-slate-800 p-5 rounded-2xl space-y-4 border border-slate-700">
                                @csrf
                                <div>
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-2">Select External Examiner</label>
                                    <select name="examiner_id" required class="w-full bg-slate-900 border-slate-700 text-white rounded-xl text-xs py-3 focus:ring-purple-500/30 focus:border-purple-500">
                                        <option value="">Choose...</option>
                                        @foreach($availableExternalExaminers as $eex)
                                            <option value="{{ $eex->user->id }}">{{ $eex->user->name }} ({{ $eex->institution }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="w-full py-3 bg-purple-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-purple-700">Confirm Assignment</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reassignment Modal -->
    <template x-teleport="body">
        <div x-show="changeModalOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm">
            <div @click.away="changeModalOpen = false" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl w-full max-w-lg overflow-hidden animate-in-up">
                <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Reassign Supervisor</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Change assigned supervisor</p>
                    </div>
                    <button @click="changeModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l18 18" /></svg>
                    </button>
                </div>
                
                <form action="{{ route('coordinator.students.assign-supervisor', $student) }}" method="POST" class="p-10 space-y-8">
                    @csrf
                    <!-- Hidden field to indicate this is a swap -->
                    <input type="hidden" name="replace_assignment_id" x-model="changeAssignmentId">
                    
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-4 pl-1">New Supervisor</label>
                        <div class="relative">
                            <select name="supervisor_id" class="w-full bg-slate-50 border-slate-100 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-500 py-4 pl-5 pr-12 appearance-none shadow-sm" x-model="changeSupervisorId">
                                @foreach($availableSupervisors as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->user->name }} ({{ $sup->assignments->where('status', 'active')->count() }}/{{ $sup->max_students }} Load)</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-50 flex gap-4">
                        <button type="button" @click="changeModalOpen = false" class="flex-1 py-4 bg-slate-50 text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all">Cancel</button>
                        <button type="submit" class="flex-[2] py-4 bg-acetel-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-acetel-500/20 hover:bg-acetel-700 active:scale-95 transition-all">Confirm Change</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endsection
