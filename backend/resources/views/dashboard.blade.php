@extends('layouts.dashboard')

@section('header', 'Dashboard')

@section('content')
<div x-data="{ activeRole: localStorage.getItem('activeDashboardRole') || '{{ Auth::user()->getRoleNames()->first() ?? 'Student' }}' }" x-init="$watch('activeRole', val => { localStorage.setItem('activeDashboardRole', val); window.dispatchEvent(new CustomEvent('role-changed', {detail: val})); })" @@role-changed.window="activeRole = $event.detail" class="space-y-8">

    {{-- Welcome Banner --}}
    <div class="relative overflow-hidden rounded-2xl p-8" style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); box-shadow: 0 8px 32px rgba(22,163,74,0.25);">
        <!-- Decorative circles -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mt-20 -mr-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full -mb-16 -ml-16 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div>
                <p class="text-green-200 text-sm font-semibold mb-1 uppercase tracking-wider">Welcome back</p>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ Auth::user()->name }}</h1>
                <p class="text-green-100/80 text-sm mt-2">Your research dashboard is ready. Stay on track with your thesis journey.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <div class="px-4 py-2.5 bg-white/15 border border-white/25 rounded-xl flex items-center gap-2.5">
                    <div class="w-2.5 h-2.5 rounded-full bg-green-300 animate-pulse"></div>
                    <span class="text-sm font-bold text-white uppercase tracking-wider"><span x-text="activeRole"></span></span>
                </div>
            </div>
        </div>
    </div>

    @php
        $userRoles = Auth::user()->getRoleNames();
        $hasMultipleRoles = $userRoles->count() > 1;
    @endphp

    @if($hasMultipleRoles)
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach($userRoles as $role)
            <button @click="activeRole = '{{ $role }}'"
                    :class="activeRole === '{{ $role }}' ? 'bg-green-600 text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                    class="px-4 py-2 rounded-xl text-sm font-bold transition-all">
                {{ $role }} View
            </button>
        @endforeach
    </div>
    @endif

    {{-- Quick Links / Role-based actions --}}
    
    @hasrole('Student')
    <div x-show="activeRole === 'Student'" x-cloak style="display: none;" :style="activeRole === 'Student' ? 'display: grid;' : 'display: none;'" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <a href="{{ route('milestones.index') }}" class="group bg-white border border-slate-200 rounded-2xl p-6 hover:border-green-300 hover:shadow-md transition-all">
            <div class="w-11 h-11 bg-green-50 border border-green-100 rounded-xl flex items-center justify-center text-green-600 mb-4 group-hover:bg-green-100 group-hover:scale-110 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">My Milestones</h3>
            <p class="text-xs text-slate-500">Track your research progress</p>
        </a>
        <a href="{{ route('inbox.index') }}" class="group bg-white border border-slate-200 rounded-2xl p-6 hover:border-green-300 hover:shadow-md transition-all">
            <div class="w-11 h-11 bg-green-50 border border-green-100 rounded-xl flex items-center justify-center text-green-600 mb-4 group-hover:bg-green-100 group-hover:scale-110 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Inbox</h3>
            <p class="text-xs text-slate-500">My communications</p>
        </a>
    </div>
    @endhasrole

    @hasrole('Supervisor')
    <div x-show="activeRole === 'Supervisor'" x-cloak style="display: none;" :style="activeRole === 'Supervisor' ? 'display: grid;' : 'display: none;'" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <a href="{{ route('supervisor.students.index') }}" class="group bg-white border border-slate-200 rounded-2xl p-6 hover:border-green-300 hover:shadow-md transition-all">
            <div class="w-11 h-11 bg-green-50 border border-green-100 rounded-xl flex items-center justify-center text-green-600 mb-4 group-hover:bg-green-100 group-hover:scale-110 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">My Students</h3>
            <p class="text-xs text-slate-500">Track student progress</p>
        </a>
        <a href="{{ route('supervisor.seminars.index') }}" class="group bg-white border border-slate-200 rounded-2xl p-6 hover:border-green-300 hover:shadow-md transition-all">
            <div class="w-11 h-11 bg-green-50 border border-green-100 rounded-xl flex items-center justify-center text-green-600 mb-4 group-hover:bg-green-100 group-hover:scale-110 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Seminar Exams</h3>
            <p class="text-xs text-slate-500">Evaluate assigned seminars</p>
        </a>
        <a href="{{ route('inbox.index') }}" class="group bg-white border border-slate-200 rounded-2xl p-6 hover:border-green-300 hover:shadow-md transition-all">
            <div class="w-11 h-11 bg-green-50 border border-green-100 rounded-xl flex items-center justify-center text-green-600 mb-4 group-hover:bg-green-100 group-hover:scale-110 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Inbox</h3>
            <p class="text-xs text-slate-500">Student communications</p>
        </a>
    </div>
    @endhasrole

    @hasrole('Program Coordinator')
    <div x-show="activeRole === 'Program Coordinator'" x-cloak style="display: none;" :style="activeRole === 'Program Coordinator' ? 'display: grid;' : 'display: none;'" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <a href="{{ route('coordinator.students.index') }}" class="group bg-white border border-slate-200 rounded-2xl p-6 hover:border-green-300 hover:shadow-md transition-all">
            <div class="w-11 h-11 bg-green-50 border border-green-100 rounded-xl flex items-center justify-center text-green-600 mb-4 group-hover:bg-green-100 group-hover:scale-110 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Students</h3>
            <p class="text-xs text-slate-500">Manage programme students</p>
        </a>
        <a href="{{ route('coordinator.supervisors.index') }}" class="group bg-white border border-slate-200 rounded-2xl p-6 hover:border-green-300 hover:shadow-md transition-all">
            <div class="w-11 h-11 bg-green-50 border border-green-100 rounded-xl flex items-center justify-center text-green-600 mb-4 group-hover:bg-green-100 group-hover:scale-110 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Supervisors</h3>
            <p class="text-xs text-slate-500">Assign and manage supervisors</p>
        </a>
        <a href="{{ route('reports.index') }}" class="group bg-white border border-slate-200 rounded-2xl p-6 hover:border-green-300 hover:shadow-md transition-all">
            <div class="w-11 h-11 bg-green-50 border border-green-100 rounded-xl flex items-center justify-center text-green-600 mb-4 group-hover:bg-green-100 group-hover:scale-110 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Reports</h3>
            <p class="text-xs text-slate-500">View programme analytics</p>
        </a>
    </div>
    @endhasrole

    @hasrole('Internal Examiner|External Examiner')
    <div x-show="['Internal Examiner', 'External Examiner'].includes(activeRole)" x-cloak style="display: none;" :style="['Internal Examiner', 'External Examiner'].includes(activeRole) ? 'display: grid;' : 'display: none;'" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <a href="{{ route('examiner.theses.index') }}" class="group bg-white border border-slate-200 rounded-2xl p-6 hover:border-green-300 hover:shadow-md transition-all">
            <div class="w-11 h-11 bg-green-50 border border-green-100 rounded-xl flex items-center justify-center text-green-600 mb-4 group-hover:bg-green-100 group-hover:scale-110 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Assigned Theses</h3>
            <p class="text-xs text-slate-500">Review thesis submissions</p>
        </a>
        <a href="{{ route('inbox.index') }}" class="group bg-white border border-slate-200 rounded-2xl p-6 hover:border-green-300 hover:shadow-md transition-all">
            <div class="w-11 h-11 bg-green-50 border border-green-100 rounded-xl flex items-center justify-center text-green-600 mb-4 group-hover:bg-green-100 group-hover:scale-110 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Inbox</h3>
            <p class="text-xs text-slate-500">Communications</p>
        </a>
    </div>
    @endhasrole

    @hasrole('Admin')
    <div x-show="activeRole === 'Admin'" x-cloak style="display: none;" :style="activeRole === 'Admin' ? 'display: grid;' : 'display: none;'" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <a href="{{ route('admin.users.index') }}" class="group bg-white border border-slate-200 rounded-2xl p-6 hover:border-green-300 hover:shadow-md transition-all">
            <div class="w-11 h-11 bg-green-50 border border-green-100 rounded-xl flex items-center justify-center text-green-600 mb-4 group-hover:bg-green-100 group-hover:scale-110 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Manage Users</h3>
            <p class="text-xs text-slate-500">Add, edit, and manage accounts</p>
        </a>
        <a href="{{ route('admin.cohorts.index') }}" class="group bg-white border border-slate-200 rounded-2xl p-6 hover:border-green-300 hover:shadow-md transition-all">
            <div class="w-11 h-11 bg-green-50 border border-green-100 rounded-xl flex items-center justify-center text-green-600 mb-4 group-hover:bg-green-100 group-hover:scale-110 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Cohorts</h3>
            <p class="text-xs text-slate-500">Manage academic cohorts</p>
        </a>
        <a href="{{ route('admin.audit-logs.index') }}" class="group bg-white border border-slate-200 rounded-2xl p-6 hover:border-green-300 hover:shadow-md transition-all">
            <div class="w-11 h-11 bg-green-50 border border-green-100 rounded-xl flex items-center justify-center text-green-600 mb-4 group-hover:bg-green-100 group-hover:scale-110 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <h3 class="font-bold text-slate-800 mb-1">Audit Logs</h3>
            <p class="text-xs text-slate-500">System activity and events</p>
        </a>
    </div>
    @endhasrole

    {{-- Info Card --}}
    <div class="bg-white border border-green-100 rounded-2xl p-8 shadow-sm">
        <div class="flex items-start gap-5">
            <div class="w-14 h-14 bg-green-50 border border-green-200 rounded-2xl flex items-center justify-center text-green-600 shrink-0">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="font-black text-slate-800 text-lg mb-2">Getting Started</h3>
                <p class="text-slate-500 text-sm leading-relaxed max-w-2xl">
                    Welcome to the ACETEL Thesis Monitoring System. Use the navigation on the left to access your personalized features. 
                    @role('Student') Track your milestones, communicate with your supervisor, and stay on top of your research journey. @endrole
                    @role('Supervisor') Review your students' progress, provide feedback, and track milestone submissions. @endrole
                    @role('Program Coordinator') Oversee your programme's cohorts, manage supervisors, and generate reports. @endrole
                    @role('Admin') Manage users, cohorts, announcements, and system settings. @endrole
                </p>
                <div class="flex items-center gap-3 mt-4">
                    <a href="{{ route('inbox.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-bold rounded-xl hover:bg-green-700 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Open Inbox
                    </a>
                    <a href="{{ route('repository.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 text-green-700 text-sm font-bold rounded-xl border border-green-200 hover:bg-green-100 transition-colors">
                        Browse Repository →
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
