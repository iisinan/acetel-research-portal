@extends('layouts.dashboard')

@section('header')
    My Dashboard
@endsection

@section('content')
<div class="space-y-8">

    {{-- Welcome Hero --}}
    <div class="relative overflow-hidden rounded-3xl bg-white border border-green-100 shadow-sm p-8 lg:p-10">
        <div class="absolute top-0 right-0 w-72 h-72 bg-green-50 rounded-full -mr-20 -mt-20 opacity-60"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-green-50 rounded-full -ml-16 -mb-16 opacity-40"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center gap-8">
            {{-- Left: Greeting & CTA --}}
            <div class="flex-1 space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-50 rounded-full border border-green-200">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-600"></span>
                    </span>
                    <span class="text-xs font-semibold text-green-700 uppercase tracking-widest">Research Active</span>
                </div>

                <div>
                    <h2 class="text-3xl lg:text-4xl font-black text-slate-800 tracking-tight leading-tight">
                        Welcome back, <span class="text-green-600">{{ auth()->user()->firstName() }}</span>
                    </h2>
                    <p class="mt-2 text-slate-500 font-medium leading-relaxed max-w-xl">
                        Track your thesis milestones, stay in sync with your supervisor, and monitor your research progress all in one place.
                    </p>
                    
                    @if(isset($student))
                    <div class="flex flex-wrap items-center gap-3 mt-5 mb-2">
                        <div class="px-3 py-2 bg-white border border-green-100 rounded-xl shadow-sm flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-0.5">Matric No.</p>
                                <p class="text-xs font-bold text-slate-800 leading-none">{{ $student->student_id_number ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="px-3 py-2 bg-white border border-green-100 rounded-xl shadow-sm flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477-4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-0.5">Program / Course</p>
                                <p class="text-xs font-bold text-slate-800 leading-none">{{ $student?->program?->name ?? 'Not Assigned' }}</p>
                            </div>
                        </div>
                        <div class="px-3 py-2 bg-white border border-green-100 rounded-xl shadow-sm flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-0.5">Cohort Enrollment</p>
                                <p class="text-xs font-bold text-slate-800 leading-none">{{ $student->cohort->name ?? 'Not Assigned' }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3 pt-1">

                    <a href="{{ route('inbox.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-green-50 text-slate-700 text-sm font-bold rounded-xl border border-slate-200 transition-all duration-200 relative group">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        Institutional Inbox
                        @if($stats['unread_inbox'] > 0)
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-black rounded-full w-5 h-5 flex items-center justify-center border-2 border-white shadow-sm">{{ $stats['unread_inbox'] }}</span>
                        @endif
                    </a>

                    @if($stats['unread_chat'] > 0 && $active_thesis)
                        <a href="{{ route('theses.show', $active_thesis) }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm transition-all duration-200 group relative">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                            Research Feedback
                            <span class="absolute -top-2 -right-2 bg-blue-400 text-white text-[10px] font-black rounded-full w-5 h-5 flex items-center justify-center border-2 border-white shadow-sm animate-bounce">{{ $stats['unread_chat'] }}</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Right: Progress Ring --}}
            <div class="shrink-0 flex flex-col items-center justify-center bg-green-50 border border-green-100 rounded-2xl p-8 min-w-[160px] text-center">
                <p class="text-[10px] font-black text-green-600 uppercase tracking-widest mb-2">Overall Progress</p>
                <p class="text-5xl font-black text-slate-800 tracking-tighter leading-none">
                    {{ $stats['overall_progress'] }}<span class="text-xl text-green-600">%</span>
                </p>
                <div class="w-full mt-4 bg-green-100 rounded-full h-2 overflow-hidden">
                    <div class="h-full bg-green-500 rounded-full transition-all duration-700" style="width: {{ $stats['overall_progress'] }}%"></div>
                </div>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest mt-2">Milestones Approved</p>
            </div>
        </div>
    </div>

    {{-- Scheduled Presentation Banner --}}
    @php
        $todayStr = now()->toDateString();
        $studentSchedMilestone = $milestones->first(function($m) use ($todayStr) {
            return !empty($m->defence_date) && \Carbon\Carbon::parse($m->defence_date)->format('Y-m-d') >= $todayStr && $m->status !== 'approved';
        });
    @endphp

    @if($studentSchedMilestone)
        @php
            $isSchedToday = (\Carbon\Carbon::parse($studentSchedMilestone->defence_date)->format('Y-m-d') === $todayStr);
        @endphp
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-900 via-slate-900 to-emerald-950 text-white p-6 sm:p-8 shadow-xl border border-emerald-500/30">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full {{ $isSchedToday ? 'bg-emerald-500 text-slate-950 font-black' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }} text-[10px] uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full {{ $isSchedToday ? 'bg-slate-950 animate-ping' : 'bg-emerald-400 animate-pulse' }}"></span>
                        <span>{{ $isSchedToday ? "Today's Live Presentation" : 'Scheduled Presentation' }}</span>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                        {{ $studentSchedMilestone->template->presentation_title }}
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-300 font-medium">
                        Scheduled Date: <strong class="text-white">{{ \Carbon\Carbon::parse($studentSchedMilestone->defence_date)->format('l, F j, Y') }}</strong>
                        @if($studentSchedMilestone->defence_time)
                            at <strong class="text-emerald-400">{{ \Carbon\Carbon::parse($studentSchedMilestone->defence_time)->format('g:i A') }}</strong>
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @if($studentSchedMilestone->meeting_link)
                        <a href="{{ $studentSchedMilestone->meeting_link }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-5 py-3 bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-emerald-500/20 transition active:scale-95">
                            <svg class="w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span>Join Zoom Session</span>
                        </a>
                    @endif
                    <a href="{{ route('presentations.show', $studentSchedMilestone->milestone_template_id) }}"
                       class="inline-flex items-center gap-2 px-5 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold uppercase tracking-wider border border-white/20 transition active:scale-95">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>View Presentation Schedule</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $sCards = [
                ['label' => 'Total Milestones', 'value' => $stats['total_milestones'], 'color' => 'green', 'path' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ['label' => 'Approved', 'value' => $stats['completed_milestones'], 'color' => 'emerald', 'path' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'Pending Review', 'value' => $stats['pending_milestones'], 'color' => 'amber', 'path' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'Inbox Messages', 'value' => $stats['unread_inbox'], 'color' => 'blue', 'path' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ];
            $bgMap = ['green' => 'bg-green-50 border-green-100 text-green-600', 'emerald' => 'bg-emerald-50 border-emerald-100 text-emerald-600', 'amber' => 'bg-amber-50 border-amber-100 text-amber-600', 'blue' => 'bg-blue-50 border-blue-100 text-blue-600'];
        @endphp
        @foreach($sCards as $sc)
        <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl border flex items-center justify-center shrink-0 {{ $bgMap[$sc['color']] ?? 'bg-green-50 border-green-100 text-green-600' }}">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $sc['path'] }}"/></svg>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide leading-none">{{ $sc['label'] }}</p>
            </div>
            <p class="text-3xl font-black text-slate-800 tracking-tight">{{ $sc['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Milestone Timeline (left 2/3) --}}
        <div class="lg:col-span-2 bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-6 bg-green-500 rounded-full"></div>
                    <h3 class="text-base font-black text-slate-800 tracking-tight">Milestone Progress</h3>
                </div>

            </div>

            <div class="p-6">
                @if($active_thesis && $milestones->count() > 0)
                    <div class="space-y-3">
                        @foreach($milestones as $milestone)
                        @php
                            $isApproved = in_array($milestone->status, ['approved', 'completed']);
                            $isPending  = in_array($milestone->status, ['submitted', 'pending', 'partially_approved']);
                            $isRevision = $milestone->status === 'revision_required';
                        @endphp
                        <div class="flex items-start gap-4 group">
                            {{-- Status indicator --}}
                            <div class="shrink-0 flex flex-col items-center mt-0.5">
                                @if($isApproved)
                                    <div class="w-8 h-8 rounded-full bg-green-100 border-2 border-green-400 flex items-center justify-center shadow-sm">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                @elseif($isPending)
                                    <div class="w-8 h-8 rounded-full bg-amber-100 border-2 border-amber-400 flex items-center justify-center shadow-sm">
                                        <div class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></div>
                                    </div>
                                @elseif($isRevision)
                                    <div class="w-8 h-8 rounded-full bg-red-100 border-2 border-red-300 flex items-center justify-center shadow-sm">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-100 border-2 border-slate-200 flex items-center justify-center">
                                        <div class="w-2 h-2 rounded-full bg-slate-300"></div>
                                    </div>
                                @endif
                                @if(!$loop->last)
                                    <div class="w-0.5 h-6 bg-slate-100 mt-1"></div>
                                @endif
                            </div>

                            {{-- Milestone card --}}
                            <div class="flex-1 pb-3">
                                <div class="flex items-center justify-between gap-4 p-4 rounded-xl border transition-all duration-200
                                    {{ $isApproved ? 'bg-green-50 border-green-100' : ($isPending ? 'bg-amber-50 border-amber-100' : ($isRevision ? 'bg-red-50 border-red-100' : 'bg-slate-50 border-slate-100')) }}
                                    group-hover:shadow-sm">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-800 truncate">{{ $milestone->template->name }}</p>
                                        @if($milestone->defence_date && !$isApproved)
                                            <p class="text-xs text-emerald-700 font-bold mt-0.5 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span>Presentation: {{ \Carbon\Carbon::parse($milestone->defence_date)->format('M d, Y') }} @if($milestone->defence_time) at {{ \Carbon\Carbon::parse($milestone->defence_time)->format('g:i A') }} @endif</span>
                                            </p>
                                        @elseif($milestone->submitted_at)
                                            <p class="text-xs text-slate-400 mt-0.5">Submitted {{ $milestone->submitted_at->diffForHumans() }}</p>
                                        @else
                                            <p class="text-xs text-slate-400 mt-0.5">Not yet submitted</p>
                                        @endif
                                    </div>
                                    @if($isApproved)
                                        <span class="shrink-0 px-2.5 py-1 bg-green-100 text-green-700 text-[10px] font-black uppercase tracking-wider rounded-full border border-green-200">Approved</span>
                                    @elseif($milestone->defence_date && !$isApproved)
                                        <a href="{{ route('presentations.show', $milestone->milestone_template_id) }}" class="shrink-0 px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-black uppercase tracking-wider rounded-full shadow-sm transition">Scheduled</a>
                                    @elseif($isPending)
                                        <span class="shrink-0 px-2.5 py-1 bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-wider rounded-full border border-amber-200">Under Review</span>
                                    @elseif($isRevision)
                                        <span class="shrink-0 px-2.5 py-1 bg-red-100 text-red-700 text-[10px] font-black uppercase tracking-wider rounded-full border border-red-200">Revision Needed</span>
                                    @else
                                        <span class="shrink-0 px-2.5 py-1 bg-slate-100 text-slate-500 text-[10px] font-black uppercase tracking-wider rounded-full border border-slate-200">Pending</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-16 text-center">
                        <div class="w-14 h-14 bg-green-50 border border-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
                        </div>
                        <p class="text-sm font-bold text-slate-600">No thesis assigned yet</p>
                        <p class="text-xs text-slate-400 mt-1">Contact your coordinator to get started.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar panels (right 1/3) --}}
        <div class="space-y-5">

            {{-- Supervisor Info --}}
            <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
                    <div class="w-1 h-5 bg-green-500 rounded-full"></div>
                    <h3 class="text-sm font-black text-slate-800 tracking-tight">My Supervisor(s)</h3>
                </div>
                <div class="p-5">
                    @if($active_thesis && $supervisors->count() > 0)
                        <div class="space-y-3">
                            @foreach($supervisors as $assignment)
                            <div class="flex items-center justify-between p-3 bg-green-50 border border-green-100 rounded-xl">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-green-200 flex items-center justify-center font-black text-sm text-green-800 shrink-0">
                                        {{ substr($assignment->supervisor->user->name ?? '?', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-800 truncate">{{ $assignment->supervisor->user->name ?? 'Not assigned' }}</p>
                                        <p class="text-[10px] font-semibold text-green-600 uppercase tracking-wide">{{ ucfirst($assignment->role ?? 'Supervisor') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 shrink-0 ml-2">
                                    @if(isset($assignment->supervisor->user->email))
                                    <a href="mailto:{{ $assignment->supervisor->user->email }}" title="Send Email" class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-slate-400 hover:text-green-600 hover:bg-green-100 transition-colors border border-slate-100 shadow-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    </a>
                                    @endif
                                    @if(isset($assignment->supervisor->user->id))
                                    <a href="{{ route('inbox.compose', ['reply_to' => $assignment->supervisor->user->id]) }}" title="In-App Message" class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-slate-400 hover:text-green-600 hover:bg-green-100 transition-colors border border-slate-100 shadow-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400 text-center py-4">No supervisor assigned yet.</p>
                    @endif
                </div>
            </div>

            {{-- Action Items --}}
            @if($action_items->count() > 0)
            <div class="bg-white border border-amber-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-amber-100 flex items-center gap-2">
                    <div class="w-1 h-5 bg-amber-500 rounded-full"></div>
                    <h3 class="text-sm font-black text-slate-800 tracking-tight">Action Required</h3>
                    <span class="ml-auto bg-amber-100 text-amber-700 text-[10px] font-black px-2 py-0.5 rounded-full border border-amber-200">{{ $action_items->count() }}</span>
                </div>
                <div class="p-5 space-y-3">
                    @foreach($action_items->take(4) as $item)
                    <div class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-100 rounded-xl">
                        <div class="w-5 h-5 rounded-full bg-amber-200 flex items-center justify-center shrink-0 mt-0.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-600"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-700 leading-snug">{{ $item->description ?? $item->title ?? 'Action item' }}</p>
                            @if($item->due_date)
                                <p class="text-[10px] text-amber-600 font-semibold mt-0.5">Due: {{ \Carbon\Carbon::parse($item->due_date)->format('M d, Y') }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Institutional Resource Center --}}
            @if(isset($document_templates) && $document_templates->count() > 0)
            <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-1 h-5 bg-blue-500 rounded-full"></div>
                        <h3 class="text-sm font-black text-slate-800 tracking-tight uppercase">Resource Center</h3>
                    </div>
                </div>
                <div class="p-2 space-y-1">
                    @foreach($document_templates as $template)
                    <a href="{{ route('templates.download', $template) }}" class="flex items-center gap-3 p-3 hover:bg-blue-50 rounded-xl transition-all group">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-black text-slate-700 truncate leading-none uppercase">{{ $template->title }}</p>
                            <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase tracking-tighter italic">Version {{ $template->version }} • {{ strtoupper($template->type) }}</p>
                        </div>
                        <svg class="w-3 h-3 ml-auto text-slate-300 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif



        </div>
    </div>

</div>
@endsection
