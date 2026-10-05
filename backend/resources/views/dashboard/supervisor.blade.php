@extends('layouts.dashboard')

@section('header')
    Faculty Academic Hub
@endsection

@push('scripts')
<form id="global-jump-form" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="milestone_id" id="global-jump-target">
</form>

<script>
    async function jumpMilestoneGlobal(studentId, targetMilestoneId) {
        const ok = await window.confirmModal({
            title: 'Change Milestone',
            message: "Are you sure you want to change this student's milestone? This will reset their progress for future milestones.",
            type: 'warning',
            confirmText: 'Change Milestone'
        });
        if (!ok) return;
        const form = document.getElementById('global-jump-form');
        form.action = '{{ url("students") }}/' + studentId + '/set-milestone-global';
        document.getElementById('global-jump-target').value = targetMilestoneId;
        form.submit();
    }
</script>
@endpush

@section('content')
<div x-data="{
    activeTab: '{{ $defaultTab ?? 'overview' }}',
    supervisorExpanded: false,
    coordinatorExpanded: false,
    internalExpanded: false,
    externalExpanded: false,
    switchTab(tab) {
        this.activeTab = tab;
        localStorage.setItem('facultyDashboardTab', tab);
        let role = null;
        if (tab === 'supervision') role = 'Supervisor';
        else if (tab === 'coordination') role = 'Program Coordinator';
        else if (tab === 'internal_exam') role = 'Internal Examiner';
        else if (tab === 'external_exam') role = 'External Examiner';
        if (role) {
            window.dispatchEvent(new CustomEvent('role-changed', {detail: role}));
        }
    }
}" 
x-on:role-changed.window="
    if ($event.detail === 'Supervisor') activeTab = 'supervision';
    else if ($event.detail === 'Program Coordinator') activeTab = 'coordination';
    else if ($event.detail === 'Internal Examiner') activeTab = 'internal_exam';
    else if ($event.detail === 'External Examiner') activeTab = 'external_exam';
"
x-init="
    @if($facultyRoleCount > 1)
    const saved = localStorage.getItem('facultyDashboardTab');
    if (saved && ['overview', 'supervision', 'coordination', 'internal_exam', 'external_exam'].includes(saved)) {
        if (saved === 'supervision' && {{ $activeFacultyRoles['supervisor'] ? 'true' : 'false' }}) activeTab = saved;
        else if (saved === 'coordination' && {{ $activeFacultyRoles['coordinator'] ? 'true' : 'false' }}) activeTab = saved;
        else if (saved === 'internal_exam' && {{ $activeFacultyRoles['internal_examiner'] ? 'true' : 'false' }}) activeTab = saved;
        else if (saved === 'external_exam' && {{ $activeFacultyRoles['external_examiner'] ? 'true' : 'false' }}) activeTab = saved;
        else if (saved === 'overview') activeTab = saved;
    }
    @else
    activeTab = '{{ $defaultTab }}';
    @endif
"
class="space-y-8 animate-in">

    {{-- Welcome Hero & Multi-Role Executive Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-white border border-green-100 shadow-sm p-8 lg:p-10">
        <div class="absolute top-0 right-0 w-80 h-80 bg-green-50 rounded-full -mr-24 -mt-24 opacity-60 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-56 h-56 bg-emerald-50 rounded-full -ml-20 -mb-20 opacity-40 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            {{-- Left: Greeting & Role Badges --}}
            <div class="flex-1 space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-50 rounded-full border border-green-200">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-600"></span>
                        </span>
                        <span class="text-xs font-semibold text-green-700 uppercase tracking-widest text-[10px]">Faculty Executive Core</span>
                    </div>

                    @if($facultyRoleCount > 1)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-900 text-white">
                            {{ $facultyRoleCount }} Concurrent Appointments
                        </span>
                    @endif
                </div>

                <div>
                    <h2 class="text-3xl lg:text-4xl font-black text-slate-800 tracking-tight leading-tight">
                        Welcome back, <span class="text-green-600">{{ auth()->user()->firstName() }}</span>
                    </h2>
                    <p class="mt-2 text-slate-500 font-medium leading-relaxed max-w-2xl text-sm">
                        Comprehensive academic governance portal. Seamlessly manage research candidates, orchestrate program coordination, and conduct thesis examinations across your active portfolios.
                    </p>
                </div>

                {{-- Academic Appointment Pills --}}
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    @if($activeFacultyRoles['supervisor'])
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-bold shadow-xs">
                            <span class="text-base">🎓</span>
                            <span>Research Supervisor</span>
                            <span class="px-1.5 py-0.5 bg-emerald-200/70 text-emerald-900 rounded-md text-[10px] font-black">
                                {{ $stats['assigned_students'] }}/{{ $supervisor->max_students ?? 10 }}
                            </span>
                        </div>
                    @endif

                    @if($activeFacultyRoles['coordinator'])
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-blue-50 border border-blue-200 rounded-xl text-blue-800 text-xs font-bold shadow-xs">
                            <span class="text-base">🏛️</span>
                            <span>Program Coordinator</span>
                            <span class="px-1.5 py-0.5 bg-blue-200/70 text-blue-900 rounded-md text-[10px] font-black">
                                {{ $coordinatedPrograms->count() }} {{ Str::plural('Program', $coordinatedPrograms->count()) }}
                            </span>
                        </div>
                    @endif

                    @if($activeFacultyRoles['internal_examiner'])
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-purple-50 border border-purple-200 rounded-xl text-purple-800 text-xs font-bold shadow-xs">
                            <span class="text-base">⚖️</span>
                            <span>Internal Examiner</span>
                            <span class="px-1.5 py-0.5 bg-purple-200/70 text-purple-900 rounded-md text-[10px] font-black">
                                {{ $stats['internal_theses'] }} {{ Str::plural('Dossier', $stats['internal_theses']) }}
                            </span>
                        </div>
                    @endif

                    @if($activeFacultyRoles['external_examiner'])
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs font-bold shadow-xs">
                            <span class="text-base">🌐</span>
                            <span>External Examiner</span>
                            <span class="px-1.5 py-0.5 bg-amber-200/70 text-amber-900 rounded-md text-[10px] font-black">
                                {{ $stats['external_theses'] }} {{ Str::plural('Dossier', $stats['external_theses']) }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Quick Communications Shortcuts --}}
                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="{{ route('inbox.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all duration-200 group">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Institutional Inbox
                        @if($stats['unread_inbox'] > 0)
                            <span class="bg-white text-green-700 text-[10px] font-black rounded-full px-1.5 py-0.5 leading-none">{{ $stats['unread_inbox'] }}</span>
                        @endif
                    </a>

                    @if($stats['unread_chat'] > 0)
                        <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                            Candidate Feedback
                            <span class="bg-white text-blue-700 text-[10px] font-black rounded-full px-1.5 py-0.5 leading-none animate-bounce">{{ $stats['unread_chat'] }}</span>
                        </div>
                    @endif

                    @if($stats['total_pending_actions'] > 0)
                        <div class="inline-flex items-center gap-1.5 px-3 py-2 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                            <span>{{ $stats['total_pending_actions'] }} Action {{ Str::plural('Item', $stats['total_pending_actions']) }} Awaiting Clearance</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right: Faculty Appointments Telemetry Cards --}}
            <div class="shrink-0 flex flex-wrap sm:flex-nowrap lg:flex-col gap-2.5 min-w-[210px]">
                @if($activeFacultyRoles['supervisor'])
                    <div class="flex-1 min-w-[130px] bg-green-50/80 border border-green-100 rounded-2xl p-4 text-center">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-[9px] font-black text-green-700 uppercase tracking-widest">Mentorship</span>
                            <span class="text-xs">🎓</span>
                        </div>
                        <p class="text-2xl font-black text-slate-800 tracking-tight leading-none">
                            {{ $stats['assigned_students'] }}<span class="text-xs text-slate-400 font-bold">/{{ $supervisor->max_students ?? 10 }}</span>
                        </p>
                        <div class="w-full mt-2.5 bg-green-200/60 rounded-full h-1.5 overflow-hidden">
                            @php $pLoad = min(100, round(($stats['assigned_students'] / max(1, ($supervisor->max_students ?? 10))) * 100)); @endphp
                            <div class="h-full bg-green-600 rounded-full transition-all duration-700" style="width: {{ $pLoad }}%"></div>
                        </div>
                    </div>
                @endif

                @if($activeFacultyRoles['coordinator'])
                    <div class="flex-1 min-w-[130px] bg-blue-50/80 border border-blue-100 rounded-2xl p-4 text-center">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-[9px] font-black text-blue-700 uppercase tracking-widest">Coordinated</span>
                            <span class="text-xs">🏛️</span>
                        </div>
                        <p class="text-2xl font-black text-slate-800 tracking-tight leading-none">
                            {{ $coordinatorStats['students'] }}
                        </p>
                        <p class="text-[9px] font-semibold text-slate-400 uppercase tracking-widest mt-1">{{ $coordinatedPrograms->count() }} {{ Str::plural('Program', $coordinatedPrograms->count()) }}</p>
                    </div>
                @endif

                @if($activeFacultyRoles['internal_examiner'])
                    <div class="flex-1 min-w-[130px] bg-purple-50/80 border border-purple-100 rounded-2xl p-4 text-center">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-[9px] font-black text-purple-700 uppercase tracking-widest">Internal Exam</span>
                            <span class="text-xs">⚖️</span>
                        </div>
                        <p class="text-2xl font-black text-slate-800 tracking-tight leading-none">
                            {{ $stats['internal_theses'] }}
                        </p>
                        <p class="text-[9px] font-semibold text-slate-400 uppercase tracking-widest mt-1">Dossiers</p>
                    </div>
                @endif

                @if($activeFacultyRoles['external_examiner'])
                    <div class="flex-1 min-w-[130px] bg-amber-50/80 border border-amber-100 rounded-2xl p-4 text-center">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-[9px] font-black text-amber-700 uppercase tracking-widest">External Exam</span>
                            <span class="text-xs">🌐</span>
                        </div>
                        <p class="text-2xl font-black text-slate-800 tracking-tight leading-none">
                            {{ $stats['external_theses'] }}
                        </p>
                        <p class="text-[9px] font-semibold text-slate-400 uppercase tracking-widest mt-1">Dossiers</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Role Perspective Navigation Tabs (Adaptive Hub) --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-2 shadow-xs">
        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
            {{-- Tab 1: Overview --}}
            <button type="button" @click="switchTab('overview')"
                    :class="activeTab === 'overview' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                <span>🌟 Overview</span>
                @if($stats['total_pending_actions'] > 0)
                    <span :class="activeTab === 'overview' ? 'bg-amber-400 text-slate-900' : 'bg-amber-100 text-amber-800'" class="px-1.5 py-0.5 rounded-full text-[9px] font-black">
                        {{ $stats['total_pending_actions'] }}
                    </span>
                @endif
            </button>

            {{-- Tab 2: Supervision --}}
            @if($activeFacultyRoles['supervisor'])
                <button type="button" @click="switchTab('supervision')"
                        :class="activeTab === 'supervision' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-emerald-700 hover:bg-emerald-50'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                    <span>🎓 Supervision</span>
                    <span :class="activeTab === 'supervision' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800'" class="px-1.5 py-0.5 rounded-full text-[9px] font-black">
                        {{ $stats['assigned_students'] }}
                    </span>
                    @if($pending_reviews->count() > 0)
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    @endif
                </button>
            @endif

            {{-- Tab 3: Program Coordination --}}
            @if($activeFacultyRoles['coordinator'])
                <button type="button" @click="switchTab('coordination')"
                        :class="activeTab === 'coordination' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-blue-700 hover:bg-blue-50'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                    <span>🏛️ Program Coordination</span>
                    <span :class="activeTab === 'coordination' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800'" class="px-1.5 py-0.5 rounded-full text-[9px] font-black">
                        {{ $coordinatorStats['students'] }}
                    </span>
                    @if($coordinatorPendingReviews->count() > 0)
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    @endif
                </button>
            @endif

            {{-- Tab 4: Internal Examination --}}
            @if($activeFacultyRoles['internal_examiner'])
                <button type="button" @click="switchTab('internal_exam')"
                        :class="activeTab === 'internal_exam' ? 'bg-purple-600 text-white shadow-sm' : 'text-slate-600 hover:text-purple-700 hover:bg-purple-50'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                    <span>⚖️ Internal Examination</span>
                    <span :class="activeTab === 'internal_exam' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-800'" class="px-1.5 py-0.5 rounded-full text-[9px] font-black">
                        {{ $stats['internal_theses'] }}
                    </span>
                    @if($internalPendingReviews->count() > 0 || $pending_evaluations->count() > 0)
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    @endif
                </button>
            @endif

            {{-- Tab 5: External Examination --}}
            @if($activeFacultyRoles['external_examiner'])
                <button type="button" @click="switchTab('external_exam')"
                        :class="activeTab === 'external_exam' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:text-amber-700 hover:bg-amber-50'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                    <span>🌐 External Examination</span>
                    <span :class="activeTab === 'external_exam' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800'" class="px-1.5 py-0.5 rounded-full text-[9px] font-black">
                        {{ $stats['external_theses'] }}
                    </span>
                    @if($externalPendingReviews->count() > 0)
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    @endif
                </button>
            @endif
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- TAB 1: UNIFIED OVERVIEW PANEL                                --}}
    {{-- ============================================================ --}}
    <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
        {{-- Unified Attention Required Alert (If Any Pending Reviews Exist Across Roles) --}}
        @if($stats['total_pending_actions'] > 0)
            <div class="relative rounded-3xl bg-amber-50 border border-amber-200 p-6 lg:p-8 shadow-sm overflow-hidden">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800 tracking-tight leading-none mb-1">Institutional Backlog Requires Immediate Review</h3>
                            <p class="text-xs font-semibold text-slate-600">You have {{ $stats['total_pending_actions'] }} pending clearance {{ Str::plural('item', $stats['total_pending_actions']) }} requiring approval across your active appointments.</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if($pending_reviews->count() > 0)
                            <button type="button" @click="switchTab('supervision')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
                                <span>Supervision ({{ $pending_reviews->count() }})</span>
                            </button>
                        @endif
                        @if($coordinatorPendingReviews->count() > 0)
                            <button type="button" @click="switchTab('coordination')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
                                <span>Coordination ({{ $coordinatorPendingReviews->count() }})</span>
                            </button>
                        @endif
                        @if($internalPendingReviews->count() > 0)
                            <button type="button" @click="switchTab('internal_exam')" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
                                <span>Internal Exam ({{ $internalPendingReviews->count() }})</span>
                            </button>
                        @endif
                        @if($externalPendingReviews->count() > 0)
                            <button type="button" @click="switchTab('external_exam')" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center gap-1.5">
                                <span>External Exam ({{ $externalPendingReviews->count() }})</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Metrics Matrix Tailored to Active Roles --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @if($activeFacultyRoles['supervisor'])
                <div @click="switchTab('supervision')" class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all cursor-pointer group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center font-bold">
                            🎓
                        </div>
                        <span class="text-[9px] font-black text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md uppercase">Supervision</span>
                    </div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Supervised Candidates</p>
                    <p class="text-3xl font-black text-slate-800 mt-1">{{ $stats['assigned_students'] }}</p>
                    <p class="text-[10px] font-semibold text-slate-400 mt-2">{{ $stats['pending_reviews'] }} pending clearance</p>
                </div>
            @endif

            @if($activeFacultyRoles['coordinator'])
                <div @click="switchTab('coordination')" class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md hover:border-blue-200 transition-all cursor-pointer group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center font-bold">
                            🏛️
                        </div>
                        <span class="text-[9px] font-black text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md uppercase">Program</span>
                    </div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cohort Candidates</p>
                    <p class="text-3xl font-black text-slate-800 mt-1">{{ $coordinatorStats['students'] }}</p>
                    <p class="text-[10px] font-semibold text-slate-400 mt-2">{{ $coordinatorStats['pending_reviews'] }} coordinator approvals</p>
                </div>
            @endif

            @if($activeFacultyRoles['internal_examiner'])
                <div @click="switchTab('internal_exam')" class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md hover:border-purple-200 transition-all cursor-pointer group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 border border-purple-100 flex items-center justify-center font-bold">
                            ⚖️
                        </div>
                        <span class="text-[9px] font-black text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md uppercase">Internal</span>
                    </div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Internal Theses</p>
                    <p class="text-3xl font-black text-slate-800 mt-1">{{ $stats['internal_theses'] }}</p>
                    <p class="text-[10px] font-semibold text-slate-400 mt-2">{{ $internalPendingReviews->count() }} dossiers pending</p>
                </div>
            @endif

            @if($activeFacultyRoles['external_examiner'])
                <div @click="switchTab('external_exam')" class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md hover:border-amber-200 transition-all cursor-pointer group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center font-bold">
                            🌐
                        </div>
                        <span class="text-[9px] font-black text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md uppercase">External</span>
                    </div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">External Theses</p>
                    <p class="text-3xl font-black text-slate-800 mt-1">{{ $stats['external_theses'] }}</p>
                    <p class="text-[10px] font-semibold text-slate-400 mt-2">{{ $externalPendingReviews->count() }} evaluations pending</p>
                </div>
            @endif

            {{-- Unread Direct Communication --}}
            <a href="{{ route('inbox.index') }}" class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 border border-green-100 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="text-[9px] font-black text-green-700 bg-green-50 px-2 py-0.5 rounded-md uppercase">Inbox</span>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Unread Messages</p>
                <p class="text-3xl font-black text-slate-800 mt-1">{{ $stats['unread_messages'] }}</p>
                <p class="text-[10px] font-semibold text-slate-400 mt-2">{{ $stats['unread_inbox'] }} email • {{ $stats['unread_chat'] }} chat</p>
            </a>
        </div>

        {{-- Upcoming Academic Defences Calendar (Overview Hub) --}}
        @if(isset($upcomingDefences) && $upcomingDefences->count() > 0)
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 lg:p-8 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-50 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-lg border border-purple-100">
                            📅
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-800 tracking-tight uppercase">UPCOMING ACADEMIC DEFENCES & SEMINARS</h3>
                            <p class="text-xs text-slate-400 font-medium">Scheduled candidate presentations, seminar defences, and viva examinations across your active portfolios.</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-purple-50 text-purple-700 text-xs font-bold rounded-xl border border-purple-100">
                        {{ $upcomingDefences->count() }} Scheduled
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($upcomingDefences as $ev)
                        @php
                            $isSeminar = ($ev->type === 'seminar');
                            $badgeColor = $isSeminar ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-purple-50 text-purple-700 border-purple-200';
                            $btnColor = $isSeminar ? 'bg-blue-600 hover:bg-blue-700' : 'bg-purple-600 hover:bg-purple-700';
                            $startTime = $ev->schedule_start ? \Carbon\Carbon::parse($ev->schedule_start) : null;
                            $isToday = $startTime && $startTime->isToday();
                        @endphp
                        <div class="p-5 rounded-2xl border {{ $isToday ? 'border-amber-300 bg-amber-50/20 shadow-xs' : 'border-slate-100 bg-slate-50/40 hover:bg-slate-50' }} space-y-3 transition-all group">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider border {{ $badgeColor }}">
                                    {{ $isSeminar ? 'Seminar Presentation' : 'Oral Viva Defence' }}
                                </span>
                                @if($startTime)
                                    <span class="text-[10px] font-bold {{ $isToday ? 'text-amber-700 font-black animate-pulse' : 'text-slate-400' }}">
                                        {{ $isToday ? 'TODAY' : $startTime->diffForHumans() }}
                                    </span>
                                @endif
                            </div>

                            <div>
                                <h4 class="text-sm font-black text-slate-800 line-clamp-1 group-hover:text-green-700 transition-colors uppercase">
                                    {{ $ev->thesis->student->user->name ?? 'Candidate' }}
                                </h4>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                                    {{ $ev->thesis->student->program->code ?? 'N/A' }} • {{ $ev->thesis->student->student_id_number ?? '' }}
                                </p>
                                @if($ev->thesis && $ev->thesis->title)
                                    <p class="text-xs text-slate-500 line-clamp-2 mt-1 italic">
                                        "{{ $ev->thesis->title }}"
                                    </p>
                                @endif
                            </div>

                            <div class="pt-2 border-t border-slate-200/50 flex flex-col gap-2">
                                <div class="flex items-center justify-between text-[11px] text-slate-500 font-medium">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $startTime ? $startTime->format('M d, Y • h:i A') : 'TBA' }}
                                    </span>
                                    @if($ev->location)
                                        <span class="truncate max-w-[120px] text-[10px] text-slate-400" title="{{ $ev->location }}">📍 {{ $ev->location }}</span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    @if($ev->isAuthorizedEvaluator(auth()->id()))
                                        <a href="{{ route('evaluations.create', ['defenceEvent' => $ev->id]) }}" class="flex-1 text-center py-2 {{ $btnColor }} text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                                            Grade Presentation
                                        </a>
                                    @elseif($ev->thesis)
                                        <a href="{{ route('theses.show', $ev->thesis) }}" class="flex-1 text-center py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                                            View Dossier
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Overview Grid: Portfolios & Institutional Assets --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Left: Role Portfolios Quick Jump Cards --}}
            <div class="lg:col-span-8 space-y-6">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8 space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-50 pb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-800 tracking-tight">ACTIVE ACADEMIC DOMAINS</h3>
                            <p class="text-xs text-slate-400 font-medium">Select an appointment domain to jump directly into its operational workspace.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($activeFacultyRoles['supervisor'])
                            <div @click="switchTab('supervision')" class="p-6 rounded-2xl border-2 border-emerald-100/70 hover:border-emerald-400 bg-emerald-50/20 hover:bg-emerald-50/40 transition-all cursor-pointer group">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-2xl">🎓</span>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded">Supervision</span>
                                </div>
                                <h4 class="text-base font-black text-slate-800 group-hover:text-emerald-700 transition-colors">Postgraduate Mentorship</h4>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Oversee {{ $stats['assigned_students'] }} active candidates, verify dissertation drafts, and record milestone jumps.</p>
                                <div class="mt-4 pt-3 border-t border-emerald-100/50 flex items-center justify-between text-xs font-bold text-emerald-700">
                                    <span>Open Supervision Hub</span>
                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </div>
                        @endif

                        @if($activeFacultyRoles['coordinator'])
                            <div @click="switchTab('coordination')" class="p-6 rounded-2xl border-2 border-blue-100/70 hover:border-blue-400 bg-blue-50/20 hover:bg-blue-50/40 transition-all cursor-pointer group">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-2xl">🏛️</span>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-blue-700 bg-blue-100/80 px-2 py-0.5 rounded">Coordination</span>
                                </div>
                                <h4 class="text-base font-black text-slate-800 group-hover:text-blue-700 transition-colors">Cohort Program Governance</h4>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Directs {{ $coordinatorStats['students'] }} candidates across {{ $coordinatedPrograms->count() }} program(s). Manage defence milestones and clearances.</p>
                                <div class="mt-4 pt-3 border-t border-blue-100/50 flex items-center justify-between text-xs font-bold text-blue-700">
                                    <span>Open Coordination Hub</span>
                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </div>
                        @endif

                        @if($activeFacultyRoles['internal_examiner'])
                            <div @click="switchTab('internal_exam')" class="p-6 rounded-2xl border-2 border-purple-100/70 hover:border-purple-400 bg-purple-50/20 hover:bg-purple-50/40 transition-all cursor-pointer group">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-2xl">⚖️</span>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-purple-700 bg-purple-100/80 px-2 py-0.5 rounded">Examination</span>
                                </div>
                                <h4 class="text-base font-black text-slate-800 group-hover:text-purple-700 transition-colors">Internal Thesis Appraisal</h4>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Examine {{ $stats['internal_theses'] }} assigned thesis manuscripts and certify oral viva defence performance.</p>
                                <div class="mt-4 pt-3 border-t border-purple-100/50 flex items-center justify-between text-xs font-bold text-purple-700">
                                    <span>Open Internal Dossiers</span>
                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </div>
                        @endif

                        @if($activeFacultyRoles['external_examiner'])
                            <div @click="switchTab('external_exam')" class="p-6 rounded-2xl border-2 border-amber-100/70 hover:border-amber-400 bg-amber-50/20 hover:bg-amber-50/40 transition-all cursor-pointer group">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-2xl">🌐</span>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 bg-amber-100/80 px-2 py-0.5 rounded">External</span>
                                </div>
                                <h4 class="text-base font-black text-slate-800 group-hover:text-amber-700 transition-colors">External Evaluation Panel</h4>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Independent external audit of {{ $stats['external_theses'] }} final doctoral dissertations and oral defence scoring.</p>
                                <div class="mt-4 pt-3 border-t border-amber-100/50 flex items-center justify-between text-xs font-bold text-amber-700">
                                    <span>Open External Dossiers</span>
                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Resources & System Information --}}
            <div class="lg:col-span-4 space-y-6">
                {{-- Institutional Resource Center --}}
                @if(isset($document_templates) && $document_templates->count() > 0)
                <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-50">
                        <h3 class="text-xs font-black text-slate-800 tracking-widest uppercase">Institutional Templates</h3>
                        <a href="{{ route('resources.index') }}" class="text-[10px] font-bold text-green-600 hover:text-green-700 uppercase">View All</a>
                    </div>
                    <div class="divide-y divide-slate-50 mt-2">
                        @foreach($document_templates->take(4) as $template)
                        <a href="{{ route('templates.download', $template) }}" class="flex items-center gap-3 py-3 hover:bg-green-50/50 px-2 rounded-xl transition-all group">
                            <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center shrink-0 group-hover:bg-green-600 group-hover:text-white transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-black text-slate-700 truncate leading-none uppercase">{{ $template->title }}</p>
                                <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase">v{{ $template->version }} • {{ strtoupper($template->type) }}</p>
                            </div>
                            <svg class="w-3 h-3 ml-auto text-slate-300 group-hover:text-green-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Protocol Actions --}}
                <div class="bg-slate-900 rounded-3xl p-6 text-white relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-green-500 rounded-full -mr-16 -mt-16 opacity-10 group-hover:scale-110 transition-transform duration-700"></div>
                    <h3 class="text-lg font-black tracking-tight leading-tight mb-2 uppercase">ACETEL <span class="text-green-400">RESEARCH PORTAL</span></h3>
                    <p class="text-xs text-slate-400 leading-relaxed mb-6 font-medium">Certified doctoral workflow and peer appraisal mechanisms conforming to university benchmarks.</p>
                    <div class="space-y-2">
                        <a href="{{ route('profile.edit') }}" class="w-full flex items-center justify-center py-3 bg-green-600 hover:bg-green-500 text-white rounded-xl text-[10px] font-black uppercase tracking-wider transition-all">
                            Academic Profile Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- TAB 2: RESEARCH SUPERVISION PANEL                            --}}
    {{-- ============================================================ --}}
    @if($activeFacultyRoles['supervisor'])
    <div x-show="activeTab === 'supervision'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
        {{-- Supervisory Cohort Intelligence Bar --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Metric 1: Average Velocity & Pipeline Stage --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg uppercase tracking-wider">Cohort Velocity</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">📈</div>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Average Progress</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-3xl font-black text-slate-800">{{ $supervisionAnalytics['average_progress'] }}%</span>
                    <span class="text-xs font-bold text-emerald-600">{{ $students->count() }} Candidates</span>
                </div>
                <div class="w-full mt-3 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-700" style="width: {{ $supervisionAnalytics['average_progress'] }}%"></div>
                </div>
                <p class="text-[10px] font-semibold text-slate-400 mt-2 truncate">
                    {{ $supervisionAnalytics['stage_proposal'] }} Proposal • {{ $supervisionAnalytics['stage_research'] }} Research • {{ $supervisionAnalytics['stage_defense'] }} Viva
                </p>
            </div>

            {{-- Metric 2: Supervision Allocation Gauge --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg uppercase tracking-wider">Mentorship Load</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">🎓</div>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Allocation Status</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-3xl font-black text-slate-800">{{ $stats['assigned_students'] }}</span>
                    <span class="text-xs font-bold text-slate-400">/ {{ $supervisor->max_students ?? 10 }} Max Cap</span>
                </div>
                @php
                    $loadPct = min(100, round(($stats['assigned_students'] / max(1, ($supervisor->max_students ?? 10))) * 100));
                    $slotsLeft = max(0, ($supervisor->max_students ?? 10) - $stats['assigned_students']);
                @endphp
                <div class="w-full mt-3 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="h-full {{ $loadPct >= 90 ? 'bg-rose-500' : ($loadPct >= 70 ? 'bg-amber-500' : 'bg-blue-600') }} rounded-full transition-all duration-700" style="width: {{ $loadPct }}%"></div>
                </div>
                <p class="text-[10px] font-semibold text-slate-400 mt-2">
                    {{ $slotsLeft > 0 ? "{$slotsLeft} supervision slot(s) open" : "At maximum allocation" }}
                </p>
            </div>

            {{-- Metric 3: Action & Attention Radar --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg uppercase tracking-wider">Attention Radar</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">⚠️</div>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Follow-up Items</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-3xl font-black text-slate-800">{{ $supervisionAnalytics['action_needed_count'] + $supervisionAnalytics['stalled_count'] }}</span>
                    <span class="text-xs font-bold text-amber-600">Pending Actions</span>
                </div>
                <div class="mt-3 flex items-center justify-between text-[10px] font-bold text-slate-500 pt-2 border-t border-slate-50">
                    <span class="text-amber-600">{{ $supervisionAnalytics['action_needed_count'] }} Awaiting Review</span>
                    <span class="text-rose-500">{{ $supervisionAnalytics['stalled_count'] }} Inactive (&gt;30d)</span>
                </div>
            </div>

            {{-- Metric 4: Seminar / Defence Evaluations --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg uppercase tracking-wider">Oral Viva & Seminars</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm">⚖️</div>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Scheduled Defences</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-3xl font-black text-slate-800">{{ $pending_seminars->count() + $pending_evaluations->count() }}</span>
                    <span class="text-xs font-bold text-purple-600">To Grade</span>
                </div>
                <div class="mt-3 flex items-center justify-between text-[10px] font-bold text-slate-500 pt-2 border-t border-slate-50">
                    <span>{{ $pending_seminars->count() }} Seminars</span>
                    <span>{{ $pending_evaluations->count() }} Viva Panels</span>
                </div>
            </div>
        </div>

        {{-- Attention Required Alert for Supervisor --}}
        @if($pending_reviews->count() > 0)
        <div class="relative rounded-3xl bg-amber-50 border border-amber-200 shadow-sm overflow-hidden" x-data="{ expanded: false }">
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6 p-6 lg:p-8 cursor-pointer" @click="expanded = !expanded">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <span class="relative flex h-3.5 w-3.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-200 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-white"></span>
                        </span>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800 tracking-tight leading-none mb-1">Supervisor Review Pending</h3>
                        <p class="text-xs font-semibold text-slate-600">{{ $pending_reviews->count() }} candidate {{ Str::plural('milestone', $pending_reviews->count()) }} require your mentorship review.</p>
                    </div>
                </div>
                
                <button type="button" @click.stop="expanded = !expanded" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-black uppercase tracking-wider text-[10px] transition-all flex items-center gap-2">
                    <span x-text="expanded ? 'Hide Candidates' : 'Review Candidates'"></span>
                    <svg class="w-4 h-4 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>

            <div x-show="expanded" x-collapse x-cloak class="px-6 pb-6 pt-0 border-t border-amber-100/60 mt-2">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                    @foreach($pending_reviews as $review)
                    <div class="bg-white border border-amber-200 rounded-2xl p-5 hover:border-amber-400 hover:shadow-md transition-all group">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-black text-sm shrink-0 border border-amber-100">
                                {{ substr($review->thesis?->student?->user?->name ?? '?', 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-sm font-black text-slate-800 truncate">{{ $review->thesis?->student?->user?->name ?? 'Candidate' }}</h4>
                                <span class="inline-block mt-1 px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200/50 rounded text-[9px] font-bold uppercase truncate max-w-full">
                                    {{ $review->template->name }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $review->submitted_at ? $review->submitted_at->diffForHumans() : 'Recently' }}</span>
                            <a href="{{ route('theses.show', ['thesis' => $review->thesis_project_id, 'expanded' => $review->id]) }}#milestone-{{$review->id}}" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-[9px] font-black uppercase tracking-wider transition-all">
                                Review Milestone
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Smart Managed Candidates Roster with Alpine Live Search & Filter --}}
        <div x-data="{
            searchQuery: '',
            statusFilter: 'all',
            matches(name, idNumber, program, title, hasAction, isStalled, order) {
                const q = this.searchQuery.toLowerCase().trim();
                const matchesQuery = !q || 
                    name.toLowerCase().includes(q) || 
                    idNumber.toLowerCase().includes(q) || 
                    program.toLowerCase().includes(q) || 
                    title.toLowerCase().includes(q);
                if (!matchesQuery) return false;

                if (this.statusFilter === 'action_needed') return hasAction;
                if (this.statusFilter === 'stalled') return isStalled;
                if (this.statusFilter === 'defense') return order >= 6;
                return true;
            }
        }" class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-6 bg-emerald-500 rounded-full"></div>
                        <div>
                            <h3 class="text-base font-black text-slate-800 tracking-tight uppercase">MANAGED CANDIDATES ROSTER</h3>
                            <p class="text-xs text-slate-400 font-medium">Postgraduate researchers currently allocated under your mentorship portfolio.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100">
                            {{ $students->count() }} Total Assigned
                        </span>
                    </div>
                </div>

                {{-- Smart Search Bar & Filter Chips --}}
                <div class="pt-2 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                    {{-- Search Input --}}
                    <div class="relative flex-1">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" x-model="searchQuery" placeholder="Filter by candidate name, student ID, program, or thesis title..." 
                               class="w-full pl-10 pr-9 py-2.5 bg-slate-50 border border-slate-200/80 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all placeholder:text-slate-400">
                        <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" style="display: none;">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Filter Chips --}}
                    <div class="flex flex-wrap items-center gap-1.5 shrink-0">
                        <button type="button" @click="statusFilter = 'all'" 
                                :class="statusFilter === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" 
                                class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all">
                            All ({{ $students->count() }})
                        </button>
                        <button type="button" @click="statusFilter = 'action_needed'" 
                                :class="statusFilter === 'action_needed' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-700 border border-amber-200/60 hover:bg-amber-100'" 
                                class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all">
                            ⚡ Review Pending ({{ $supervisionAnalytics['action_needed_count'] }})
                        </button>
                        <button type="button" @click="statusFilter = 'stalled'" 
                                :class="statusFilter === 'stalled' ? 'bg-rose-500 text-white shadow-xs' : 'bg-rose-50 text-rose-700 border border-rose-200/60 hover:bg-rose-100'" 
                                class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all">
                            ⚠️ Inactive (&gt;30d) ({{ $supervisionAnalytics['stalled_count'] }})
                        </button>
                        <button type="button" @click="statusFilter = 'defense'" 
                                :class="statusFilter === 'defense' ? 'bg-purple-600 text-white shadow-xs' : 'bg-purple-50 text-purple-700 border border-purple-200/60 hover:bg-purple-100'" 
                                class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all">
                            🎓 Viva Stage ({{ $supervisionAnalytics['stage_defense'] }})
                        </button>
                    </div>
                </div>
            </div>

            <div class="divide-y divide-slate-50">
                @forelse($students as $student)
                    @php
                        $safeName = addslashes($student?->user?->name ?? '');
                        $safeMatric = addslashes($student->student_id_number ?? '');
                        $safeProg = addslashes($student?->program?->code ?? '');
                        $safeTitle = addslashes($student->thesis_title ?? '');
                        $hasActionBool = $student->has_pending_review ? 'true' : 'false';
                        $isStalledBool = $student->is_stalled ? 'true' : 'false';
                        $orderNum = (int) ($student->current_milestone_order ?? 1);
                    @endphp
                    <div x-show="matches('{{ $safeName }}', '{{ $safeMatric }}', '{{ $safeProg }}', '{{ $safeTitle }}', {{ $hasActionBool }}, {{ $isStalledBool }}, {{ $orderNum }})" 
                         class="p-6 lg:p-8 hover:bg-slate-50/50 transition-colors group">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                            {{-- Candidate Information & Thesis Topic --}}
                            <div class="flex items-start gap-4 flex-1 min-w-0">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-700 font-black text-lg shadow-xs group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300 shrink-0">
                                    {{ substr($student?->user?->name ?? "Unknown", 0, 1) }}
                                </div>
                                <div class="min-w-0 flex-1 space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="text-base font-black text-slate-800 group-hover:text-emerald-600 transition-colors tracking-tight uppercase leading-none">
                                            {{ $student?->user?->name ?? "Unknown" }}
                                        </h4>
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[9px] font-black uppercase">
                                            {{ $student?->program?->code ?? 'N/A' }}
                                        </span>
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[9px] font-bold">
                                            {{ $student->level->name ?? '' }}
                                        </span>
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[9px] font-black uppercase">
                                            {{ $student->assignment_role ?? 'Supervisor' }}
                                        </span>

                                        {{-- Dynamic Health Pill --}}
                                        @if($student->has_pending_review)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-800 rounded text-[9px] font-black uppercase animate-pulse">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                                Review Pending
                                            </span>
                                        @elseif($student->is_stalled)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-100 text-rose-800 rounded text-[9px] font-black uppercase">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                                Inactive ({{ $student->days_inactive }}d)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded text-[9px] font-black uppercase">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                Active
                                            </span>
                                        @endif
                                    </div>

                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                                        ID: {{ $student->student_id_number }} • Current Stage: <span class="text-slate-700 font-black">{{ $student->current_milestone_name }}</span>
                                    </p>

                                    @if($student->thesis_title)
                                        <p class="text-xs text-slate-600 font-medium line-clamp-1 italic max-w-2xl">
                                            "{{ $student->thesis_title }}"
                                        </p>
                                    @endif

                                    {{-- Progress Bar & Last Activity Indicator --}}
                                    <div class="flex items-center gap-4 pt-1">
                                        <div class="w-36 h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ $student->overall_progress }}%"></div>
                                        </div>
                                        <span class="text-[10px] font-black text-emerald-600">{{ $student->overall_progress }}% Completed</span>
                                        @if($student->last_activity_at)
                                            <span class="text-[10px] text-slate-400 font-semibold">
                                                Last active {{ \Carbon\Carbon::parse($student->last_activity_at)->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Action Group --}}
                            <div class="flex items-center gap-2.5 shrink-0 self-end lg:self-center">
                                <a href="{{ route('inbox.compose', ['reply_to' => $student->user_id]) }}" class="p-3 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-xs" title="Send Direct Message">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                </a>

                                {{-- Jump Milestone Dropdown --}}
                                <div x-data="{ jumpMenuOpen: false }" class="relative inline-block text-left">
                                    <button @click.prevent.stop="jumpMenuOpen = !jumpMenuOpen" class="flex items-center justify-center p-3 bg-white border border-slate-200 rounded-xl text-slate-700 hover:bg-slate-50 transition-all shadow-xs" title="Jump Milestone">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                    </button>
                                    <div x-show="jumpMenuOpen" @click.outside="jumpMenuOpen = false" x-cloak class="absolute right-0 top-12 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden text-left" style="display: none;">
                                        <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                            Jump Milestone
                                        </div>
                                        <div class="max-h-56 overflow-y-auto p-1.5 custom-scrollbar">
                                            @foreach($milestone_templates as $t)
                                                @php
                                                    $sm = $student->thesis ? $student->thesis->milestones->where('milestone_template_id', $t->id)->first() : null;
                                                @endphp
                                                @if($sm)
                                                <button type="button" onclick="jumpMilestoneGlobal('{{ $student->id }}', '{{ $sm->id }}')" class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 rounded-xl transition-colors flex items-center justify-between">
                                                    <span class="truncate">{{ $t->name }}</span>
                                                    @if($student->thesis && $student->thesis->currentMilestone && $t->id === $student->thesis->currentMilestone->milestone_template_id)
                                                        <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0 ml-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                    @endif
                                                </button>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <a href="{{ route('theses.show', $student->thesis) }}" class="flex items-center justify-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-xs">
                                    Audit Thesis
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-16 text-center">
                        <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-slate-100 text-2xl">
                            🎓
                        </div>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-widest">No Managed Candidates Assigned</p>
                        <p class="text-xs text-slate-400 mt-1">Candidates assigned to your mentorship will appear here automatically.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pending Seminar Defences (If Any) --}}
        @if($pending_seminars->count() > 0)
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-50 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-6 bg-blue-500 rounded-full"></div>
                    <h3 class="text-base font-black text-slate-800 tracking-tight">PENDING DEFENCE EVALUATIONS</h3>
                </div>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-[10px] font-black rounded-lg uppercase">
                    {{ $pending_seminars->count() }} Pending Evaluation{{ $pending_seminars->count() === 1 ? '' : 's' }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($pending_seminars as $event)
                @php
                    $eventBadge = match($event->type) {
                        'proposal' => 'Proposal Defence',
                        'progress_report_1' => 'Progress Report 1',
                        'progress_report_2' => 'Progress Report 2',
                        'seminar' => 'Seminar Defence',
                        default => ucfirst(str_replace('_', ' ', $event->type)),
                    };
                @endphp
                <div class="p-5 bg-blue-50/40 border border-blue-100 rounded-2xl space-y-3">
                    <div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-blue-600 bg-blue-100 px-2 py-0.5 rounded">{{ $eventBadge }}</span>
                        <h4 class="text-sm font-black text-slate-800 mt-2">{{ $event->thesis->student->user->name }}</h4>
                        <p class="text-xs text-slate-500 truncate">{{ $event->thesis->title }}</p>
                    </div>
                    <a href="{{ route('evaluations.create', ['defenceEvent' => $event->id]) }}" class="block text-center py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                        Grade Defence
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- TAB 3: PROGRAM COORDINATION PANEL                            --}}
    {{-- ============================================================ --}}
    @if($activeFacultyRoles['coordinator'])
    <div x-show="activeTab === 'coordination'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
        {{-- Coordinated Programs Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Active Programs</p>
                <p class="text-3xl font-black text-slate-800 mt-1">{{ $coordinatedPrograms->count() }}</p>
                <div class="mt-2 flex flex-wrap gap-1">
                    @foreach($coordinatedPrograms as $cp)
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold">{{ $cp->code }}</span>
                    @endforeach
                </div>
            </div>

            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Enrolled Candidates</p>
                <p class="text-3xl font-black text-slate-800 mt-1">{{ $coordinatorStats['students'] }}</p>
                <p class="text-[10px] font-semibold text-slate-400 mt-2">{{ $coordinatorStats['theses'] }} active thesis projects</p>
            </div>

            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Supervisory Pool</p>
                <p class="text-3xl font-black text-slate-800 mt-1">{{ $coordinatorStats['supervisors'] }}</p>
                <p class="text-[10px] font-semibold text-slate-400 mt-2">Active faculty supervisors in program</p>
            </div>
        </div>

        {{-- Cohort Clearance Pipeline Metrics --}}
        <div class="bg-white rounded-3xl border border-slate-100 p-8 shadow-sm space-y-6">
            <div class="flex items-center justify-between border-b border-slate-50 pb-4">
                <div>
                    <h3 class="text-base font-black text-slate-800 tracking-tight">PROGRAM CLEARANCE BENCHMARKS</h3>
                    <p class="text-xs text-slate-400 font-medium">Clearance progression across coordinated postgraduate cohorts.</p>
                </div>
                <a href="{{ route('coordinator.milestones.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 uppercase">Manage Milestones →</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-slate-700 uppercase">M1 Proposal Clearance</span>
                        <span class="text-xs font-black text-blue-600">{{ $coordinatorClearanceMetrics['m1'] }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div class="bg-blue-600 h-full rounded-full transition-all duration-700" style="width: {{ $coordinatorClearanceMetrics['m1'] }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium">Candidate proposals approved</p>
                </div>

                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-slate-700 uppercase">M2 Supervisor Assigned</span>
                        <span class="text-xs font-black text-emerald-600">{{ $coordinatorClearanceMetrics['m2'] }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-600 h-full rounded-full transition-all duration-700" style="width: {{ $coordinatorClearanceMetrics['m2'] }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium">Formal supervision allocation</p>
                </div>

                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-slate-700 uppercase">M6 Final Viva Clearance</span>
                        <span class="text-xs font-black text-purple-600">{{ $coordinatorClearanceMetrics['m6'] }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div class="bg-purple-600 h-full rounded-full transition-all duration-700" style="width: {{ $coordinatorClearanceMetrics['m6'] }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium">Cleared for oral thesis defense</p>
                </div>
            </div>
        </div>

        {{-- Program Pending Reviews Alert (If Any) --}}
        @if($coordinatorPendingReviews->count() > 0)
        <div class="relative rounded-3xl bg-blue-50 border border-blue-200 p-6 lg:p-8 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold">
                        🏛️
                    </div>
                    <div>
                        <h4 class="text-base font-black text-slate-800">Coordinator Clearance Backlog</h4>
                        <p class="text-xs text-slate-500">{{ $coordinatorPendingReviews->count() }} milestones awaiting your program sign-off.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($coordinatorPendingReviews->take(6) as $cReview)
                <div class="bg-white border border-blue-100 rounded-2xl p-4 space-y-3">
                    <div>
                        <p class="text-xs font-black text-slate-800 truncate">{{ $cReview->thesis->student->user->name ?? 'Candidate' }}</p>
                        <p class="text-[10px] font-bold text-blue-600 uppercase truncate mt-0.5">{{ $cReview->template->name }}</p>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-slate-50">
                        <span class="text-[9px] font-bold text-slate-400 uppercase">{{ $cReview->submitted_at ? $cReview->submitted_at->diffForHumans() : 'Pending' }}</span>
                        <a href="{{ route('theses.show', ['thesis' => $cReview->thesis_project_id, 'expanded' => $cReview->id]) }}" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[9px] font-black uppercase">
                            Sign Off
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Coordinated Students Pipeline with Smart Search & Filter --}}
        <div x-data="{
            coordSearch: '',
            coordProgFilter: 'all',
            matchesCoord(name, idNumber, progCode) {
                const q = this.coordSearch.toLowerCase().trim();
                const matchesText = !q || 
                    name.toLowerCase().includes(q) || 
                    idNumber.toLowerCase().includes(q) || 
                    progCode.toLowerCase().includes(q);
                if (!matchesText) return false;
                if (this.coordProgFilter !== 'all' && progCode !== this.coordProgFilter) return false;
                return true;
            }
        }" class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-black text-slate-800 tracking-tight uppercase">PROGRAM CANDIDATE PIPELINE</h3>
                        <p class="text-xs text-slate-400 font-medium">Active postgraduate cohort candidates across your coordinated scopes.</p>
                    </div>
                    <a href="{{ route('coordinator.students.index') }}" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl text-xs font-bold transition-all shrink-0">
                        Full Registry →
                    </a>
                </div>

                {{-- Interactive Search & Program Filter --}}
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative flex-1 w-full">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" x-model="coordSearch" placeholder="Search candidate by name, matric number, or program..." 
                               class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all placeholder:text-slate-400">
                    </div>
                    <div class="shrink-0 w-full sm:w-auto">
                        <select x-model="coordProgFilter" class="w-full sm:w-auto px-3 py-2 bg-slate-50 border border-slate-200/80 rounded-xl text-xs font-bold text-slate-700 focus:bg-white">
                            <option value="all">All Coordinated Programs</option>
                            @foreach($coordinatedPrograms as $cp)
                                <option value="{{ $cp->code }}">{{ $cp->code }} - {{ $cp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                            <th class="px-8 py-4">Candidate</th>
                            <th class="px-6 py-4">Program & Level</th>
                            <th class="px-6 py-4">Supervisor Allocation</th>
                            <th class="px-6 py-4">Current Milestone</th>
                            <th class="px-8 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs font-medium">
                        @forelse($coordinatorStudents as $cs)
                        @php
                            $csName = addslashes($cs->user->name ?? '');
                            $csMatric = addslashes($cs->student_id_number ?? '');
                            $csProg = addslashes($cs->program->code ?? '');
                        @endphp
                        <tr x-show="matchesCoord('{{ $csName }}', '{{ $csMatric }}', '{{ $csProg }}')" class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-8 py-4">
                                <p class="font-black text-slate-800 uppercase">{{ $cs->user->name }}</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">{{ $cs->student_id_number }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold uppercase">{{ $cs->program->code ?? 'N/A' }}</span>
                                <span class="text-[10px] text-slate-400 ml-1">{{ $cs->level->name ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($cs->thesis && $cs->thesis->assignments->isNotEmpty())
                                    <span class="text-slate-700 font-bold">{{ $cs->thesis->assignments->first()->supervisor->user->name ?? 'Assigned' }}</span>
                                @else
                                    <span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded text-[9px] font-bold uppercase">Pending Assignment</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-slate-600 font-semibold">{{ $cs->thesis->currentMilestone->template->name ?? 'Initiation' }}</span>
                            </td>
                            <td class="px-8 py-4 text-right">
                                <a href="{{ route('coordinator.students.show', $cs) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-blue-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-wider transition-all">
                                    Manage
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 font-semibold text-xs">
                                No active students found in your coordinated programs.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- TAB 4: INTERNAL THESIS EXAMINATION PANEL                     --}}
    {{-- ============================================================ --}}
    @if($activeFacultyRoles['internal_examiner'])
    <div x-show="activeTab === 'internal_exam'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
        {{-- Internal Examination Intelligence Bar --}}
        @php
            $completedInternal = $internalTheses->where('status', 'completed')->count();
            $internalCompRate = $internalTheses->count() > 0 ? round(($completedInternal / $internalTheses->count()) * 100) : 0;
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Metric 1: Total Dossiers --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg uppercase tracking-wider">Internal Scope</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm">⚖️</div>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Assigned Theses</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-3xl font-black text-slate-800">{{ $internalTheses->count() }}</span>
                    <span class="text-xs font-bold text-purple-600">Manuscripts</span>
                </div>
                <p class="text-[10px] font-semibold text-slate-400 mt-2 truncate">Active postgraduate internal appraisal portfolio</p>
            </div>

            {{-- Metric 2: Clearance Backlog --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg uppercase tracking-wider">Clearance Backlog</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">⚡</div>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pending Approvals</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-3xl font-black text-slate-800">{{ $internalPendingReviews->count() }}</span>
                    <span class="text-xs font-bold text-amber-600">Awaiting Sign-off</span>
                </div>
                <p class="text-[10px] font-semibold text-slate-400 mt-2 truncate">
                    {{ $internalPendingReviews->count() > 0 ? 'Requires internal examiner action' : 'All milestones up to date' }}
                </p>
            </div>

            {{-- Metric 3: Oral Viva Defences --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg uppercase tracking-wider">Oral Viva Panels</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">🎓</div>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Defences to Grade</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-3xl font-black text-slate-800">{{ $pending_evaluations->count() }}</span>
                    <span class="text-xs font-bold text-blue-600">Scheduled Panels</span>
                </div>
                <p class="text-[10px] font-semibold text-slate-400 mt-2 truncate">Oral viva defense evaluation rubrics</p>
            </div>

            {{-- Metric 4: Finalized Theses --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg uppercase tracking-wider">Completion</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">✅</div>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Finalized Theses</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-3xl font-black text-slate-800">{{ $completedInternal }}</span>
                    <span class="text-xs font-bold text-emerald-600">({{ $internalCompRate }}%)</span>
                </div>
                <p class="text-[10px] font-semibold text-slate-400 mt-2 truncate">Examined & certified completion</p>
            </div>
        </div>

        {{-- Internal Examiner Attention Alert --}}
        @if($internalPendingReviews->count() > 0)
        <div class="relative rounded-3xl bg-purple-50 border border-purple-200 p-6 lg:p-8 shadow-sm">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center shrink-0 font-bold">
                    ⚖️
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-800 tracking-tight leading-none mb-1">Internal Clearance Required</h3>
                    <p class="text-xs font-semibold text-slate-600">{{ $internalPendingReviews->count() }} milestones awaiting internal examination verification.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($internalPendingReviews as $iReview)
                <div class="bg-white border border-purple-100 rounded-2xl p-5 space-y-3 hover:border-purple-300 transition-all">
                    <div>
                        <h4 class="text-sm font-black text-slate-800 truncate">{{ $iReview->thesis->student->user->name ?? 'Candidate' }}</h4>
                        <span class="inline-block mt-1 px-2 py-0.5 bg-purple-50 text-purple-700 rounded text-[9px] font-bold uppercase truncate max-w-full">
                            {{ $iReview->template->name }}
                        </span>
                    </div>
                    <div class="pt-3 border-t border-slate-50 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $iReview->submitted_at ? $iReview->submitted_at->diffForHumans() : 'Recently' }}</span>
                        <a href="{{ route('theses.show', ['thesis' => $iReview->thesis_project_id, 'expanded' => $iReview->id]) }}" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-[9px] font-black uppercase">
                            Assess Milestone
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Smart Internal Theses Roster with Alpine Search & Filter --}}
        <div x-data="{
            dossierSearch: '',
            statusFilter: 'all',
            matchesDossier(title, student, matric, prog, status) {
                const q = this.dossierSearch.toLowerCase().trim();
                const matchesText = !q || 
                    title.toLowerCase().includes(q) || 
                    student.toLowerCase().includes(q) || 
                    matric.toLowerCase().includes(q) || 
                    prog.toLowerCase().includes(q);
                if (!matchesText) return false;
                if (this.statusFilter !== 'all' && status.toLowerCase() !== this.statusFilter.toLowerCase()) return false;
                return true;
            }
        }" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 lg:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-50 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-6 bg-purple-600 rounded-full"></div>
                    <div>
                        <h3 class="text-base font-black text-slate-800 tracking-tight uppercase">ASSIGNED INTERNAL THESES</h3>
                        <p class="text-xs text-slate-400 font-medium">Postgraduate thesis manuscripts allocated to you for internal examination.</p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-purple-50 text-purple-700 rounded-xl text-xs font-bold border border-purple-100 shrink-0">
                    {{ $internalTheses->count() }} Total Dossiers
                </span>
            </div>

            {{-- Search & Filter Controls --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <div class="relative flex-1">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" x-model="dossierSearch" placeholder="Search by thesis title, candidate name, student ID, program..."
                           class="w-full pl-10 pr-9 py-2.5 bg-slate-50 border border-slate-200/80 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all placeholder:text-slate-400">
                    <button type="button" x-show="dossierSearch" @click="dossierSearch = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" style="display: none;">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 shrink-0">
                    <button type="button" @click="statusFilter = 'all'" 
                            :class="statusFilter === 'all' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all">
                        All ({{ $internalTheses->count() }})
                    </button>
                    <button type="button" @click="statusFilter = 'active'" 
                            :class="statusFilter === 'active' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-700 border border-blue-200/60 hover:bg-blue-100'"
                            class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all">
                        Active ({{ $internalTheses->where('status', 'active')->count() }})
                    </button>
                    <button type="button" @click="statusFilter = 'completed'" 
                            :class="statusFilter === 'completed' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 border border-emerald-200/60 hover:bg-emerald-100'"
                            class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all">
                        Completed ({{ $completedInternal }})
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($internalTheses as $iThesis)
                    @php
                        $safeTitle = addslashes($iThesis->title ?? '');
                        $safeCandidate = addslashes($iThesis->student->user->name ?? '');
                        $safeMatric = addslashes($iThesis->student->student_id_number ?? '');
                        $safeProg = addslashes($iThesis->student->program->code ?? '');
                        $safeStatus = addslashes($iThesis->status ?? 'active');
                        $progress = $iThesis->progress_percentage;
                        $hasPendingMilestone = $internalPendingReviews->where('thesis_project_id', $iThesis->id)->isNotEmpty();
                    @endphp
                    <div x-show="matchesDossier('{{ $safeTitle }}', '{{ $safeCandidate }}', '{{ $safeMatric }}', '{{ $safeProg }}', '{{ $safeStatus }}')"
                         class="p-6 rounded-2xl border border-slate-100 bg-slate-50/40 hover:bg-slate-50 transition-all space-y-4 group">
                        
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-black text-sm shrink-0 shadow-xs">
                                    {{ substr($iThesis->student->user->name ?? '?', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-black text-slate-800 uppercase truncate leading-none">
                                        {{ $iThesis->student->user->name ?? 'Candidate' }}
                                    </h4>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                                        {{ $iThesis->student->student_id_number }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                <span class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded text-[9px] font-black uppercase">
                                    {{ $iThesis->student->program->code ?? 'N/A' }}
                                </span>
                                @if($iThesis->student->level)
                                    <span class="px-2 py-0.5 bg-slate-200 text-slate-700 rounded text-[9px] font-bold uppercase">
                                        {{ $iThesis->student->level->name }}
                                    </span>
                                @endif
                                @if($hasPendingMilestone)
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 border border-amber-300 rounded text-[9px] font-black uppercase animate-pulse">
                                        ⚡ Action
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <h5 class="text-sm font-black text-slate-800 group-hover:text-purple-700 transition-colors line-clamp-2 leading-snug">
                                {{ $iThesis->title }}
                            </h5>
                            @if($iThesis->currentMilestone)
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Stage:</span>
                                    <span class="px-2 py-0.5 bg-purple-50 text-purple-700 rounded text-[10px] font-bold truncate">
                                        {{ $iThesis->currentMilestone->template->name }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Progress bar --}}
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[10px] font-bold">
                                <span class="text-slate-400 uppercase">Manuscript Progression</span>
                                <span class="text-purple-700 font-black">{{ $progress }}%</span>
                            </div>
                            <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-purple-600 h-full rounded-full transition-all duration-700" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-200/60 flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase {{ $iThesis->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                {{ ucfirst($iThesis->status) }}
                            </span>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('theses.show', $iThesis) }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-xs">
                                    Assess Dossier
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 py-12 text-center text-slate-400 text-xs font-semibold">
                        No internal examiner thesis dossiers assigned at this time.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pending Oral Viva Defence Evaluations --}}
        @if($pending_evaluations->count() > 0)
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-50 pb-4">
                <h3 class="text-base font-black text-slate-800 tracking-tight">ORAL DEFENCE EVALUATION SCORING</h3>
                <span class="px-2.5 py-1 bg-purple-50 text-purple-700 text-[10px] font-black rounded-lg uppercase">
                    {{ $pending_evaluations->count() }} To Grade
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($pending_evaluations as $event)
                <div class="p-5 bg-purple-50/40 border border-purple-100 rounded-2xl space-y-3">
                    <div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-purple-700 bg-purple-100 px-2 py-0.5 rounded">{{ $event->defence_type }} Defence</span>
                        <h4 class="text-sm font-black text-slate-800 mt-2">{{ $event->thesis->student->user->name }}</h4>
                        <p class="text-xs text-slate-500 truncate">{{ $event->thesis->title }}</p>
                    </div>
                    <a href="{{ route('evaluations.create', ['defenceEvent' => $event->id]) }}" class="block text-center py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                        Grade Oral Defence
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- TAB 5: EXTERNAL THESIS EXAMINATION PANEL                     --}}
    {{-- ============================================================ --}}
    @if($activeFacultyRoles['external_examiner'])
    <div x-show="activeTab === 'external_exam'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8">
        {{-- External Examination Intelligence Bar --}}
        @php
            $completedExternal = $externalTheses->where('status', 'completed')->count();
            $externalCompRate = $externalTheses->count() > 0 ? round(($completedExternal / $externalTheses->count()) * 100) : 0;
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Metric 1: Total External Theses --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg uppercase tracking-wider">External Audit</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">🌐</div>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Assigned Dissertations</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-3xl font-black text-slate-800">{{ $externalTheses->count() }}</span>
                    <span class="text-xs font-bold text-amber-600">Manuscripts</span>
                </div>
                <p class="text-[10px] font-semibold text-slate-400 mt-2 truncate">Independent external doctoral evaluation</p>
            </div>

            {{-- Metric 2: Pending External Sign-offs --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg uppercase tracking-wider">Review Backlog</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">⚡</div>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Awaiting Sign-off</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-3xl font-black text-slate-800">{{ $externalPendingReviews->count() }}</span>
                    <span class="text-xs font-bold text-amber-600">Pending Reviews</span>
                </div>
                <p class="text-[10px] font-semibold text-slate-400 mt-2 truncate">
                    {{ $externalPendingReviews->count() > 0 ? 'Requires external examiner sign-off' : 'All reviews certified' }}
                </p>
            </div>

            {{-- Metric 3: Oral Defence Panels --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg uppercase tracking-wider">Viva Panels</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm">⚖️</div>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Oral Viva Panels</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-3xl font-black text-slate-800">{{ $pending_evaluations->count() }}</span>
                    <span class="text-xs font-bold text-purple-600">To Grade</span>
                </div>
                <p class="text-[10px] font-semibold text-slate-400 mt-2 truncate">External defence evaluation scoring</p>
            </div>

            {{-- Metric 4: Completed Dissertations --}}
            <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg uppercase tracking-wider">Accredited</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">✅</div>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Completed Theses</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-3xl font-black text-slate-800">{{ $completedExternal }}</span>
                    <span class="text-xs font-bold text-emerald-600">({{ $externalCompRate }}%)</span>
                </div>
                <p class="text-[10px] font-semibold text-slate-400 mt-2 truncate">Certified external approval completed</p>
            </div>
        </div>

        {{-- External Examiner Attention Alert --}}
        @if($externalPendingReviews->count() > 0)
        <div class="relative rounded-3xl bg-amber-50 border border-amber-200 p-6 lg:p-8 shadow-sm">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 font-bold">
                    🌐
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-800 tracking-tight leading-none mb-1">External Clearance Required</h3>
                    <p class="text-xs font-semibold text-slate-600">{{ $externalPendingReviews->count() }} milestones awaiting external examiner clearance.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($externalPendingReviews as $eReview)
                <div class="bg-white border border-amber-100 rounded-2xl p-5 space-y-3 hover:border-amber-300 transition-all">
                    <div>
                        <h4 class="text-sm font-black text-slate-800 truncate">{{ $eReview->thesis->student->user->name ?? 'Candidate' }}</h4>
                        <span class="inline-block mt-1 px-2 py-0.5 bg-amber-50 text-amber-700 rounded text-[9px] font-bold uppercase truncate max-w-full">
                            {{ $eReview->template->name }}
                        </span>
                    </div>
                    <div class="pt-3 border-t border-slate-50 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $eReview->submitted_at ? $eReview->submitted_at->diffForHumans() : 'Recently' }}</span>
                        <a href="{{ route('theses.show', ['thesis' => $eReview->thesis_project_id, 'expanded' => $eReview->id]) }}" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-[9px] font-black uppercase">
                            Review Submission
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Smart External Theses Roster with Alpine Search & Filter --}}
        <div x-data="{
            externalSearch: '',
            statusFilter: 'all',
            matchesExternal(title, student, matric, prog, status) {
                const q = this.externalSearch.toLowerCase().trim();
                const matchesText = !q || 
                    title.toLowerCase().includes(q) || 
                    student.toLowerCase().includes(q) || 
                    matric.toLowerCase().includes(q) || 
                    prog.toLowerCase().includes(q);
                if (!matchesText) return false;
                if (this.statusFilter !== 'all' && status.toLowerCase() !== this.statusFilter.toLowerCase()) return false;
                return true;
            }
        }" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 lg:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-50 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-6 bg-amber-600 rounded-full"></div>
                    <div>
                        <h3 class="text-base font-black text-slate-800 tracking-tight uppercase">EXTERNAL DISSERTATION DOSSIERS</h3>
                        <p class="text-xs text-slate-400 font-medium">Independent doctoral manuscripts assigned for external accreditation.</p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-amber-50 text-amber-700 rounded-xl text-xs font-bold border border-amber-100 shrink-0">
                    {{ $externalTheses->count() }} External Theses
                </span>
            </div>

            {{-- Search & Filter Controls --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <div class="relative flex-1">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" x-model="externalSearch" placeholder="Search by thesis title, candidate name, student ID, program..."
                           class="w-full pl-10 pr-9 py-2.5 bg-slate-50 border border-slate-200/80 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all placeholder:text-slate-400">
                    <button type="button" x-show="externalSearch" @click="externalSearch = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" style="display: none;">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 shrink-0">
                    <button type="button" @click="statusFilter = 'all'" 
                            :class="statusFilter === 'all' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all">
                        All ({{ $externalTheses->count() }})
                    </button>
                    <button type="button" @click="statusFilter = 'active'" 
                            :class="statusFilter === 'active' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-700 border border-blue-200/60 hover:bg-blue-100'"
                            class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all">
                        Active ({{ $externalTheses->where('status', 'active')->count() }})
                    </button>
                    <button type="button" @click="statusFilter = 'completed'" 
                            :class="statusFilter === 'completed' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 border border-emerald-200/60 hover:bg-emerald-100'"
                            class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all">
                        Completed ({{ $completedExternal }})
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($externalTheses as $eThesis)
                    @php
                        $safeTitle = addslashes($eThesis->title ?? '');
                        $safeCandidate = addslashes($eThesis->student->user->name ?? '');
                        $safeMatric = addslashes($eThesis->student->student_id_number ?? '');
                        $safeProg = addslashes($eThesis->student->program->code ?? '');
                        $safeStatus = addslashes($eThesis->status ?? 'active');
                        $progress = $eThesis->progress_percentage;
                        $hasPendingMilestone = $externalPendingReviews->where('thesis_project_id', $eThesis->id)->isNotEmpty();
                    @endphp
                    <div x-show="matchesExternal('{{ $safeTitle }}', '{{ $safeCandidate }}', '{{ $safeMatric }}', '{{ $safeProg }}', '{{ $safeStatus }}')"
                         class="p-6 rounded-2xl border border-slate-100 bg-slate-50/40 hover:bg-slate-50 transition-all space-y-4 group">
                        
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-black text-sm shrink-0 shadow-xs">
                                    {{ substr($eThesis->student->user->name ?? '?', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-black text-slate-800 uppercase truncate leading-none">
                                        {{ $eThesis->student->user->name ?? 'Candidate' }}
                                    </h4>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                                        {{ $eThesis->student->student_id_number }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded text-[9px] font-black uppercase">
                                    {{ $eThesis->student->program->code ?? 'N/A' }}
                                </span>
                                @if($eThesis->student->level)
                                    <span class="px-2 py-0.5 bg-slate-200 text-slate-700 rounded text-[9px] font-bold uppercase">
                                        {{ $eThesis->student->level->name }}
                                    </span>
                                @endif
                                @if($hasPendingMilestone)
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 border border-amber-300 rounded text-[9px] font-black uppercase animate-pulse">
                                        ⚡ Action
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <h5 class="text-sm font-black text-slate-800 group-hover:text-amber-700 transition-colors line-clamp-2 leading-snug">
                                {{ $eThesis->title }}
                            </h5>
                            @if($eThesis->currentMilestone)
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Stage:</span>
                                    <span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded text-[10px] font-bold truncate">
                                        {{ $eThesis->currentMilestone->template->name }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Progress bar --}}
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[10px] font-bold">
                                <span class="text-slate-400 uppercase">Manuscript Progression</span>
                                <span class="text-amber-700 font-black">{{ $progress }}%</span>
                            </div>
                            <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-amber-600 h-full rounded-full transition-all duration-700" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-200/60 flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase {{ $eThesis->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                {{ ucfirst($eThesis->status) }}
                            </span>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('theses.show', $eThesis) }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-xs">
                                    Evaluate Thesis
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 py-12 text-center text-slate-400 text-xs font-semibold">
                        No external examiner thesis dossiers currently allocated.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pending Oral Viva Defence Evaluations --}}
        @if($pending_evaluations->count() > 0)
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-50 pb-4">
                <h3 class="text-base font-black text-slate-800 tracking-tight">ORAL DEFENCE EVALUATION SCORING</h3>
                <span class="px-2.5 py-1 bg-amber-50 text-amber-700 text-[10px] font-black rounded-lg uppercase">
                    {{ $pending_evaluations->count() }} To Grade
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($pending_evaluations as $event)
                <div class="p-5 bg-amber-50/40 border border-amber-100 rounded-2xl space-y-3">
                    <div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-amber-700 bg-amber-100 px-2 py-0.5 rounded">{{ $event->defence_type }} Defence</span>
                        <h4 class="text-sm font-black text-slate-800 mt-2">{{ $event->thesis->student->user->name }}</h4>
                        <p class="text-xs text-slate-500 truncate">{{ $event->thesis->title }}</p>
                    </div>
                    <a href="{{ route('evaluations.create', ['defenceEvent' => $event->id]) }}" class="block text-center py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs">
                        Grade Oral Defence
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
