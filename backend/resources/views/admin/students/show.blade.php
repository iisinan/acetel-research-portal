@extends('layouts.admin')

@section('content')
<div class="space-y-8 animate-in-up" x-data="studentProfile()">
    {{-- Strategic Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="flex items-center gap-6">
            <div class="relative">
                <div class="w-20 h-20 rounded-3xl bg-slate-900 flex items-center justify-center text-3xl font-black text-white shadow-2xl shadow-slate-200">
                    {{ substr($student->user->name ?? 'S', 0, 1) }}
                </div>
                <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-xl bg-emerald-500 border-4 border-white flex items-center justify-center shadow-lg">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                        <li><a href="{{ route('coordinator.students.index') }}" class="hover:text-brand-600 transition-colors">Students</a></li>
                        <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg></li>
                        <li class="text-slate-600">Institutional Profile</li>
                    </ol>
                </nav>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ $student->user->name }}</h1>
                <div class="flex items-center gap-3 mt-2">
                    <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-slate-200">{{ $student->student_id_number }}</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                    <span class="text-sm font-bold text-slate-500">{{ $student->user->email }}</span>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
             <a href="{{ route('inbox.index', ['user_id' => $student->user_id]) }}" class="px-6 py-3 bg-white hover:bg-slate-50 text-slate-700 rounded-xl font-black text-[10px] tracking-widest uppercase transition-all border border-slate-200 shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                Communicate
            </a>
            <a href="{{ route('admin.users.edit', $student->user_id) }}" class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-black text-[10px] tracking-widest uppercase transition-all shadow-lg shadow-brand-600/20 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Account
            </a>
        </div>
    </div>

    {{-- Research Progress Tracker (Visual Step) --}}
    @if($student->thesis)
    <div class="bg-white border border-slate-100 rounded-3xl p-8 shadow-sm">
        <div class="flex items-center gap-4 mb-10">
            <div class="w-1.5 h-8 bg-brand-500 rounded-full"></div>
            <h3 class="text-xl font-bold text-gray-900 tracking-tight">Research Roadmap</h3>
        </div>

        <div class="relative px-4">
            <!-- Timeline Track -->
            <div class="absolute top-[26px] left-0 right-0 h-1.5 bg-slate-100 rounded-full hidden md:block"></div>
            
            <div class="relative flex justify-between items-start gap-4 overflow-x-auto pb-4 custom-scrollbar">
                @php 
                    $milestones = $student->thesis->milestones->sortBy('template.order');
                    $ongoingMilestoneId = $student->thesis->currentMilestone->id ?? null;
                @endphp
                @foreach($milestones as $m)
                    <div class="flex flex-col items-center min-w-[140px] group">
                        <!-- Connector Node -->
                        <div class="relative z-10 w-14 h-14 rounded-2xl flex items-center justify-center border-4 border-white shadow-lg transition-all duration-500 group-hover:scale-110 {{ $m->status === 'approved' ? 'bg-emerald-500 text-white' : ($m->id == $ongoingMilestoneId ? 'bg-brand-500 text-white ring-8 ring-brand-50/50' : 'bg-white text-slate-400 border-slate-100') }}">
                            @if($m->status === 'approved')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                            @elseif($m->id == $ongoingMilestoneId)
                                <div class="w-3 h-3 bg-white rounded-full animate-pulse"></div>
                            @else
                                <span class="text-sm font-black">{{ $m->template->order }}</span>
                            @endif
                        </div>

                        <!-- Info Area -->
                        <div class="mt-6 text-center max-w-[120px]">
                            <p class="text-[10px] font-bold uppercase tracking-widest leading-none mb-1.5 {{ $m->status === 'approved' ? 'text-emerald-600' : ($m->id == $ongoingMilestoneId ? 'text-brand-600' : 'text-slate-400') }}">
                                Milestone {{ $m->template->order }}
                            </p>
                            <h4 class="text-xs font-bold text-slate-900 leading-tight group-hover:text-brand-600 transition-colors line-clamp-2">
                                {{ $m->template->name }}
                            </h4>
                            
                            @if($m->status === 'approved' && $m->approved_at)
                                <p class="text-[9px] font-medium text-slate-400 mt-2 uppercase tracking-tighter">
                                    Validated {{ $m->approved_at->format('M Y') }}
                                </p>
                            @elseif($m->id == $ongoingMilestoneId)
                                <div class="mt-2 inline-flex items-center gap-1.5 px-2 py-0.5 bg-brand-50 text-brand-600 rounded-full border border-brand-100 animate-pulse">
                                    <span class="w-1 h-1 bg-brand-600 rounded-full"></span>
                                    <span class="text-[9px] font-bold uppercase tracking-widest">Active</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Profile Column --}}
        <div class="space-y-8">
            {{-- Institutional Profile --}}
            <div class="bg-white border border-slate-100 rounded-[2rem] p-8 shadow-sm">
                <h4 class="text-lg font-black text-slate-900 tracking-tight mb-6">Institutional Metadata</h4>
                <div class="space-y-6">
                    <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Enrollment Program</p>
                            <p class="text-sm font-black text-slate-900">{{ $student->program?->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Academic Cohort</p>
                            <p class="text-sm font-black text-slate-900">{{ $student->cohort->name ?? 'N/A' }} ({{ $student->cohort->intake_year ?? '' }})</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Degree Level</p>
                            <p class="text-sm font-black text-slate-900">{{ $student->level->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Supervision Cluster --}}
            <div class="bg-white border border-slate-100 rounded-[2rem] p-8 shadow-sm">
                <h4 class="text-lg font-black text-slate-900 tracking-tight mb-6">Supervision Cluster</h4>
                <div class="space-y-4">
                    @forelse($student->thesis->supervisors ?? [] as $assoc)
                        <div class="group p-4 bg-white border border-slate-100 rounded-2xl hover:border-brand-200 transition-all duration-300 shadow-sm flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-slate-50 flex items-center justify-center text-lg font-black text-slate-400 group-hover:bg-brand-50 group-hover:text-brand-600 transition-colors">
                                    {{ substr($assoc->supervisor->user->name ?? '?', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-900">{{ $assoc->supervisor->user->name ?? 'Unknown' }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $assoc->role }} Supervisor</p>
                                </div>
                            </div>
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        </div>
                    @empty
                        <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                             <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No Supervisors Assigned</p>
                        </div>
                    @endforelse

                    @if($student->thesis->internalExaminer)
                        <div class="mt-6 pt-6 border-t border-slate-50">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Institutional Internal Examiner</p>
                            <div class="p-4 bg-slate-900 rounded-2xl flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-xs font-black text-white">
                                        {{ substr($student->thesis->internalExaminer->user->name ?? '?', 0, 1) }}
                                    </div>
                                    <p class="text-xs font-black text-white">{{ $student->thesis->internalExaminer->user->name ?? 'N/A' }}</p>
                                </div>
                                <div class="px-2 py-1 bg-white/10 rounded text-[8px] font-black text-white uppercase tracking-widest">Active</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Scheduling Nodes --}}
            <div class="bg-white border border-slate-100 rounded-[2rem] p-8 shadow-sm">
                <h4 class="text-lg font-black text-slate-900 tracking-tight mb-6">Scheduling Nodes</h4>
                <div class="space-y-4">
                    @forelse($student->thesis->defenceEvents ?? [] as $defence)
                        <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 flex items-start gap-4">
                             <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-brand-600 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                             </div>
                             <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $defence->type }} Event</p>
                                <p class="text-sm font-black text-slate-900 mt-1">{{ $defence->schedule_start->format('l, M d, Y') }}</p>
                                <p class="text-[10px] font-bold text-slate-500 mt-1 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 12l4.243-4.243a2 2 0 112.828 2.828L15.657 12l4.828 4.828a2 2 0 11-2.828 2.828zM12 18a6 6 0 100-12 6 6 0 000 12z"/></svg>
                                    {{ $defence->location ?? 'Venue TBA' }}
                                </p>
                             </div>
                        </div>
                    @empty
                        <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                             <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No defense events scheduled</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- System Audit Trail --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col mt-8">
                <div class="px-6 py-5 border-b border-slate-50 flex items-center gap-3 bg-slate-50/30">
                    <div class="w-1 h-5 bg-slate-400 rounded-full"></div>
                    <h3 class="text-sm font-black text-slate-900 tracking-tight uppercase">Recent Activity Log</h3>
                </div>

                <div class="divide-y divide-slate-50">
                    @forelse($recent_logs as $log)
                        <div class="px-6 py-4 hover:bg-slate-50/30 transition-colors flex items-center gap-4">
                            <div class="w-8 h-8 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 transition-all font-black shadow-sm shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-800 leading-tight mb-0.5">{{ collect(explode('\\', $log->model_type))->last() }} {{ $log->action }}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $log->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">No recent activity.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Milestone Execution Log Column --}}
        <div class="lg:col-span-2 space-y-8">
            {{-- Thesis Context --}}
            <div class="bg-slate-900 rounded-[2rem] p-10 text-white shadow-2xl shadow-slate-200 overflow-hidden relative">
                <div class="absolute top-0 right-0 w-64 h-64 bg-brand-600/10 rounded-full -translate-x-1/2 -translate-y-1/2 blur-3xl"></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-brand-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                        Approved Project Context
                    </p>
                    <h2 class="text-3xl font-black leading-tight tracking-tight mb-6">
                        {{ $student->thesis->title ?? 'Untitled Thesis Project' }}
                    </h2>
                    <p class="text-slate-400 text-sm leading-relaxed max-w-3xl line-clamp-4">
                        {{ $student->thesis->abstract ?? 'Institutional description of research scope and objectives pending submission.' }}
                    </p>
                    <div class="flex items-center gap-6 mt-10 pt-10 border-t border-white/5">
                        <div>
                            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Submission Phase</p>
                            <p class="text-sm font-black text-white mt-1">{{ str_replace('_', ' ', $student->thesis->status) }}</p>
                        </div>
                        <div class="w-px h-8 bg-white/5"></div>
                        <div>
                            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Internal Clearance</p>
                            @if($student->thesis->cleared_for_internal_at)
                                <p class="text-sm font-black text-emerald-400 mt-1 flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Cleared on {{ $student->thesis->cleared_for_internal_at->format('M d, Y') }}
                                </p>
                            @else
                                <p class="text-sm font-black text-slate-500 mt-1">Pending Approval</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sequential Execution Roadmap --}}
            <div class="space-y-6">
                <div class="flex flex-col sm:flex-row items-center justify-between px-4 gap-4">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Sequential Execution Roadmap</h3>
                    
                    <div class="flex items-center gap-4">
                        <form action="{{ route('admin.students.set-milestone', $student) }}" method="POST" class="flex items-center gap-2 mr-2">
                            @csrf
                            <select name="milestone_id" class="text-[10px] font-black uppercase tracking-widest text-slate-600 bg-slate-100 border-none rounded-lg focus:ring-brand-500 cursor-pointer" onchange="const sel = this; window.confirmModal({ title: 'Change Milestone', message: 'Are you sure you want to set the student to this milestone?', type: 'warning', confirmText: 'Set Milestone' }).then(ok => { if(ok) sel.form.submit(); else sel.selectedIndex = 0; });">
                                <option value="" disabled selected>Jump to Milestone</option>
                                @foreach($student->thesis->milestones->sortBy('template.order') as $m)
                                    <option value="{{ $m->id }}">{{ $m->template->order }}. {{ $m->template->name }}</option>
                                @endforeach
                            </select>
                        </form>

                        <div class="w-px h-4 bg-slate-200"></div>

                        <form action="{{ route('admin.students.demote-milestone', $student) }}" method="POST" data-confirm="Are you sure you want to demote this student to the previous milestone?" data-confirm-title="Demote Student" data-confirm-type="danger" data-confirm-btn="Demote">
                            @csrf
                            <button type="submit" class="text-[10px] font-black uppercase tracking-widest text-rose-500 hover:text-rose-700 transition-colors flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                Demote
                            </button>
                        </form>

                        <form action="{{ route('admin.students.promote-milestone', $student) }}" method="POST" data-confirm="Are you sure you want to promote this student to the next milestone?" data-confirm-title="Promote Student" data-confirm-type="success" data-confirm-btn="Promote">
                            @csrf
                            <button type="submit" class="text-[10px] font-black uppercase tracking-widest text-emerald-500 hover:text-emerald-700 transition-colors flex items-center gap-1">
                                Promote
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </form>

                        <div class="w-px h-4 bg-slate-200"></div>

                        <form action="{{ route('admin.students.sync-milestones', $student) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-brand-600 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Re-sync
                            </button>
                        </form>
                    </div>
                </div>

                <div class="space-y-6 relative">
                    {{-- Vertical Timeline Line --}}
                    <div class="absolute top-0 bottom-0 left-12 w-1 bg-slate-50 -translate-x-1/2 z-0"></div>

                    @foreach($milestones as $milestone)
                        @php $isCompleted = $milestone->status === 'approved'; @endphp
                        <div class="relative z-10">
                            <div class="flex items-start gap-8 group">
                                <div class="w-24 flex flex-col items-center pt-6">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-300 {{ $isCompleted ? 'bg-emerald-500 text-white shadow-xl shadow-emerald-200' : 'bg-white border-4 border-slate-50 text-slate-300' }}">
                                        @if($isCompleted)
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <span class="text-sm font-black">{{ $milestone->template->order }}</span>
                                        @endif
                                    </div>
                                    @if($milestone->status === 'submitted')
                                        <div class="mt-2 px-2 py-0.5 bg-amber-100 text-amber-600 text-[8px] font-black rounded uppercase">Review</div>
                                    @endif
                                </div>

                                <div class="flex-1 bg-white border border-slate-100 rounded-[2rem] p-8 shadow-sm group-hover:shadow-xl group-hover:shadow-slate-200 transition-all duration-500">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="text-base font-black text-slate-900">{{ $milestone->template->name }}</h4>
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-2">
                                                @if($milestone->status === 'submitted' || $milestone->status === 'partially_approved')
                                                <button 
                                                    @click="confirmQuickApprove($event, '{{ $milestone->id }}')" 
                                                    class="p-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-xl transition-all shadow-sm group/approve"
                                                    title="Quick Approve">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                                @endif
                                                <a href="{{ route('milestones.review', $milestone) }}" class="p-2 bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-slate-900 rounded-xl transition-all" title="Review Submission">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <p class="text-xs text-slate-500 font-medium leading-relaxed mb-6">{{ $milestone->template->description }}</p>

                                    {{-- Submission Repository --}}
                                    @if($milestone->submissions->count() > 0)
                                        <div class="space-y-3">
                                            <div class="flex items-center gap-2 mb-3">
                                                <div class="w-1 h-3 bg-brand-600 rounded-full"></div>
                                                <p class="text-[9px] font-black text-slate-900 uppercase tracking-widest">Submission Repository</p>
                                            </div>
                                            @foreach($milestone->submissions as $sub)
                                                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100 hover:bg-white hover:border-brand-200 transition-all duration-300 group/sub">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-rose-500 group-hover/sub:bg-rose-50 transition-colors">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                        </div>
                                                        <div>
                                                            <div class="flex items-center gap-2">
                                                                <p class="text-xs font-black text-slate-900">v{{ $sub->version }} Manuscript</p>
                                                                @if($sub->plagiarism_data)
                                                                    <span class="px-1.5 py-0.5 bg-rose-100 text-rose-700 rounded text-[8px] font-black">
                                                                        Similarity: {{ $sub->plagiarism_data['similarity_index'] ?? ($sub->plagiarism_data['similarity_score'] ?? 'N/A') }}%
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest flex items-center gap-1.5">
                                                                <svg class="w-3.5 h-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                Uploaded {{ $sub->created_at->diffForHumans() }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div x-data="{ open: false }" class="flex items-center gap-2">
                                                        <button @click="open = true" class="px-4 py-2 bg-white hover:bg-slate-900 hover:text-white text-slate-700 rounded-lg text-[9px] font-black uppercase tracking-widest border border-slate-200 transition-all shadow-sm">
                                                            Review File
                                                        </button>

                                                        {{-- Document Review Modal --}}
                                                        <div x-show="open" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                                                <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="open = false"></div>
                                                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                                                
                                                                <div x-show="open" x-transition.scale.origin.bottom class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl w-full p-6">
                                                                    <div class="flex items-center justify-between mb-5">
                                                                        <h3 class="text-lg font-black text-slate-900" id="modal-title">
                                                                            Review v{{ $sub->version }} Manuscript
                                                                        </h3>
                                                                        <button @click="open = false" class="p-2 bg-slate-50 text-slate-400 hover:text-rose-500 rounded-xl transition-all">
                                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                        </button>
                                                                    </div>
                                                                    
                                                                    <div class="bg-slate-100 rounded-xl overflow-hidden shadow-inner border border-slate-200" style="height: 70vh;">
                                                                        <iframe src="{{ $sub->file_url }}" class="w-full h-full border-0"></iframe>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($milestone->due_date)
                                      <div class="p-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200 flex flex-col items-center justify-center">
                                          <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Expecting Submission by</p>
                                          <p class="text-sm font-black text-slate-900 mt-2">{{ $milestone->due_date->format('M d, Y') }}</p>
                                      </div>
                                    @endif
                                    
                                    @if($isCompleted)
                                         <div class="mt-6 pt-6 border-t border-slate-50 flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <div class="flex -space-x-2">
                                                    @foreach($milestone->approvals ?? [] as $role => $approval)
                                                        <div class="w-7 h-7 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center text-[8px] font-black text-white" title="{{ $role }} Approved">
                                                            {{ substr($role, 0, 1) }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">Unanimously Approved</p>
                                            </div>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Cycle Time: {{ $milestone->created_at->diffInDays($milestone->approved_at) }} Days</p>
                                         </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
function studentProfile() {
    return {
        async confirmQuickApprove(event, milestoneId) {
            const ok = await window.confirmModal({
                title: 'Institutional Clearance',
                message: 'Are you sure you want to grant immediate institutional clearance for this milestone? This action will be logged.',
                type: 'success',
                confirmText: 'Grant Clearance'
            });
            if (!ok) return;
            
            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

            fetch(`/milestones/${milestoneId}/quick-approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    type: 'clear_role',
                    role: '{{ Auth::user()->getRoleNames()->first() }}'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.toast.success(data.message || 'Milestone approved successfully.');
                    // Refresh only the milestone part or reload if threshold met
                    if (data.is_fully_complete) {
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
                        btn.classList.add('bg-emerald-500', 'text-white');
                    }
                } else {
                    window.toast.error(data.message || 'Approval failed.');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.toast.error('An institutional error occurred.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
        }
    }
}
</script>
@endpush
@endsection
