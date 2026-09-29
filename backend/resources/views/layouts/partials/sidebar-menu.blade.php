<div x-data="{ activeRole: localStorage.getItem('activeDashboardRole') || '{{ Auth::user()->getRoleNames()->first() ?? 'Student' }}' }" @@role-changed.window="activeRole = $event.detail" x-init="$watch('activeRole', val => { localStorage.setItem('activeDashboardRole', val); window.dispatchEvent(new CustomEvent('role-changed', {detail: val})); })">

@php
    $userRoles = Auth::user()->getRoleNames();
    $hasMultipleRoles = $userRoles->count() > 1;
@endphp

@if($hasMultipleRoles)
    <div class="px-4 mb-6">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 block">My Roles</label>
        <div class="flex flex-col gap-1.5">
            @foreach($userRoles as $role)
                <button @click="activeRole = '{{ $role }}'"
                        :class="activeRole === '{{ $role }}' ? 'bg-green-600 text-white shadow-md' : 'bg-slate-50 text-slate-600 hover:bg-green-50 hover:text-green-700'"
                        class="w-full text-left px-3 py-2.5 rounded-xl text-xs font-bold transition-all border border-transparent flex items-center justify-between"
                        :class="activeRole === '{{ $role }}' ? 'border-green-700' : 'border-slate-200'">
                    <span>{{ $role }}</span>
                    <svg x-show="activeRole === '{{ $role }}'" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4"/></svg>
                </button>
            @endforeach
        </div>
    </div>
@endif

@php
    $navClass = 'flex items-center px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 group ' . 
                'hover:bg-green-50 hover:text-green-700 border border-transparent ';
    $activeClass = 'bg-green-100 text-green-700 border-green-200 shadow-sm';
    $inactiveClass = 'text-slate-600 hover:text-green-700';
@endphp

<!-- Dashboard -->
<div class="mb-6">
    <a href="{{ route('dashboard') }}" 
       class="{{ $navClass }} {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">
        <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('dashboard') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
        Dashboard
    </a>
    @unlessrole('Admin')
    <a href="{{ route('resources.index') }}" 
       class="{{ $navClass }} {{ request()->routeIs('resources.index') ? $activeClass : $inactiveClass }}">
        <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('resources.index') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
        Resource Center
    </a>
    @endunlessrole
    <a href="{{ route('inbox.index') }}" 
       class="{{ $navClass }} {{ request()->routeIs('inbox.*') ? $activeClass : $inactiveClass }}">
        <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('inbox.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
        Inbox
        @php
            $unreadInbox = 0;
            if (\Illuminate\Support\Facades\Schema::hasTable('inbox_message_recipients')) {
                $unreadInbox = \Illuminate\Support\Facades\DB::table('inbox_message_recipients')
                    ->where('user_id', auth()->id())
                    ->whereNull('read_at')
                    ->where('is_archived', false)
                    ->count();
            }
        @endphp
        @if($unreadInbox > 0)
            <span class="ml-auto px-2 py-0.5 bg-primary-500 text-white text-[9px] font-black rounded-full leading-none shadow-sm shadow-primary-500/30 animate-blink">{{ $unreadInbox }}</span>
        @endif
    </a>
    <style>
        @keyframes custom-blink {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.1); }
            100% { opacity: 1; transform: scale(1); }
        }
        .animate-blink {
            animation: custom-blink 1.5s ease-in-out infinite;
        }
    </style>
</div>

<!-- Management Section -->
<div x-show="activeRole === 'Admin'" x-cloak>
    <div class="mb-4">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Institutional Core</p>
        <div class="space-y-1">
            <a href="{{ route('admin.users.index') }}" class="{{ $navClass }} {{ request()->routeIs('admin.users.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('admin.users.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                User Registry
            </a>
            <a href="{{ route('admin.programs.index') }}" class="{{ $navClass }} {{ request()->routeIs('admin.programs.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('admin.programs.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Programs
            </a>
            <a href="{{ route('admin.cohorts.index') }}" class="{{ $navClass }} {{ request()->routeIs('admin.cohorts.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('admin.cohorts.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Cohorts Center
            </a>
            <a href="{{ route('admin.bulk-schedule.index') }}" class="{{ $navClass }} {{ request()->routeIs('admin.bulk-schedule.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('admin.bulk-schedule.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Bulk Schedule
            </a>
            @if(request()->routeIs('admin.cohorts.*') && isset($cohort))
                <a href="{{ route('admin.cohorts.register-students', $cohort) }}" class="flex items-center pl-12 py-2 text-xs font-bold text-green-600 hover:text-green-800 transition-colors">
                    + Register Students
                </a>
            @endif
            <a href="{{ route('admin.levels.index') }}" class="{{ $navClass }} {{ request()->routeIs('admin.levels.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('admin.levels.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                Levels
            </a>
        </div>
    </div>

    <div class="mb-4">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Research Assets</p>
        <div class="space-y-1">
            <a href="{{ route('admin.milestone-templates.index') }}" class="{{ $navClass }} {{ request()->routeIs('admin.milestone-templates.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('admin.milestone-templates.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                Milestone Templates
            </a>
            <a href="{{ route('admin.templates.index') }}" class="{{ $navClass }} {{ request()->routeIs('admin.templates.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('admin.templates.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Document Templates
            </a>
            <a href="{{ route('admin.internal-examiners.index') }}" class="{{ $navClass }} {{ request()->routeIs('admin.internal-examiners.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('admin.internal-examiners.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                Internal Examiners
            </a>
        </div>
    </div>

    <div class="mb-4">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Governance</p>
        <div class="space-y-1">
            <a href="{{ route('admin.announcements.index') }}" class="{{ $navClass }} {{ request()->routeIs('admin.announcements.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('admin.announcements.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                Announcements
            </a>
            <a href="{{ route('admin.audit.index') }}" class="{{ $navClass }} {{ request()->routeIs('admin.audit.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('admin.audit.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Audit Hub
            </a>
            <a href="{{ route('admin.activity-logs.index') }}" class="{{ $navClass }} {{ request()->routeIs('admin.activity-logs.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('admin.activity-logs.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Activity Intelligence
            </a>
            <a href="{{ route('admin.operations.index') }}" class="{{ $navClass }} {{ request()->routeIs('admin.operations.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('admin.operations.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.556-.426-1.556-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                System Controls
            </a>
            <a href="{{ route('admin.email-templates.index') }}" class="{{ $navClass }} {{ request()->routeIs('admin.email-templates.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('admin.email-templates.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Mail Branding
            </a>
        </div>
    </div>
</div>

<!-- Director Section (Strategic Oversight) -->
@role('Director')
    <div class="mb-4">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Institutional Oversight</p>
        <div class="space-y-1">
            <a href="{{ route('dashboard') }}" class="{{ $navClass }} {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('dashboard') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Strategic Dashboard
            </a>
            <a href="{{ route('reports.index') }}" class="{{ $navClass }} {{ request()->routeIs('reports.index') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('reports.index') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                Advanced Analytics
            </a>
            <a href="{{ route('admin.audit.index') }}" class="{{ $navClass }} {{ request()->routeIs('admin.audit.index') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('admin.audit.index') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Institutional Audit
            </a>
            <a href="{{ route('admin.announcements.index') }}" class="{{ $navClass }} {{ request()->routeIs('admin.announcements.index') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('admin.announcements.index') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path></svg>
                Global Broadcasts
            </a>
        </div>
    </div>
</div>
@endrole

<!-- Coordinator Section -->
<div x-show="activeRole === 'Program Coordinator'" x-cloak>
    <div class="mb-4">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Program</p>
        <div class="space-y-1">
            <a href="{{ route('coordinator.students.index') }}" class="{{ $navClass }} {{ request()->routeIs('coordinator.students.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('coordinator.students.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Students
            </a>
            <a href="{{ route('coordinator.cohorts.index') }}" class="{{ $navClass }} {{ request()->routeIs('coordinator.cohorts.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('coordinator.cohorts.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.503 7.503 0 1 0-7.516 0c.85.493 1.508 1.333 1.508 2.316V18" /></svg>
                Cohorts
            </a>
            <a href="{{ route('coordinator.supervisors.index') }}" class="{{ $navClass }} {{ request()->routeIs('coordinator.supervisors.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('coordinator.supervisors.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Supervisors
            </a>
            <a href="{{ route('coordinator.milestones.index') }}" class="{{ $navClass }} {{ request()->routeIs('coordinator.milestones.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('coordinator.milestones.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5" /></svg>
                Milestones
            </a>
            <a href="{{ route('coordinator.milestone-templates.index') }}" class="{{ $navClass }} {{ request()->routeIs('coordinator.milestone-templates.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('coordinator.milestone-templates.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Batch Scheduling
            </a>
            <a href="{{ route('coordinator.examiners.index') }}" class="{{ $navClass }} {{ request()->routeIs('coordinator.examiners.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('coordinator.examiners.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                Examiners Pool
            </a>

            <a href="{{ route('coordinator.communications.index') }}" class="{{ $navClass }} {{ request()->routeIs('coordinator.communications.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('coordinator.communications.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                Communications
            </a>

        </div>
    </div>
</div>

<!-- Supervisor Section -->
<div x-show="activeRole === 'Supervisor'" x-cloak>
    @php
        $pendingSeminarCount = \App\Models\DefenceEvent::where('type', 'seminar')
            ->whereHas('panelMembers', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->whereDoesntHave('evaluations', function($q) {
                $q->where('evaluator_id', auth()->id());
            })
            ->count();
    @endphp
    <div class="mb-4">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Supervision</p>
        <div class="space-y-1">
            <a href="{{ route('supervisor.students.index') }}" class="{{ $navClass }} {{ request()->routeIs('supervisor.students.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('supervisor.students.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Students
            </a>
            @if($pendingSeminarCount > 0)
                <a href="{{ route('supervisor.seminars.index') }}" class="{{ $navClass }} {{ request()->routeIs('supervisor.seminars.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('supervisor.seminars.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                    Seminar Examination
                    <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold rounded-full bg-primary-100 text-primary-700">{{ $pendingSeminarCount }}</span>
                </a>
            @endif
            <a href="#" class="{{ $navClass }} {{ request()->routeIs('supervisor.evaluations') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('supervisor.evaluations') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Evaluations
            </a>
        </div>
    </div>
</div>

<!-- Student Section -->
<div x-show="activeRole === 'Student'" x-cloak>
    <div class="mb-4">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Research</p>
        <div class="space-y-1">
            <a href="{{ route('milestones.index') }}" class="{{ $navClass }} {{ request()->routeIs('milestones.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('milestones.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                Milestones
            </a>
        </div>
    </div>
</div>


<!-- Examiners Section -->
<div x-show="['Internal Examiner', 'External Examiner'].includes(activeRole)" x-cloak class="mb-4">
    <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Examination</p>
    <div class="space-y-1">
        <a href="{{ route('examiner.theses.index') }}" class="{{ $navClass }} {{ request()->routeIs('examiner.theses.*') ? $activeClass : $inactiveClass }}">
            <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            Assigned Theses
        </a>
        <a href="{{ route('inbox.index') }}" class="{{ $navClass }} {{ request()->routeIs('inbox.*') ? $activeClass : $inactiveClass }}">
            <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            Inbox
        </a>
    </div>
</div>

<!-- Reports Section -->
<div x-show="['Admin', 'Director', 'Program Coordinator'].includes(activeRole)" x-cloak>
    <div class="mb-6">
        <a href="{{ route('reports.index') }}" class="{{ $navClass }} {{ request()->routeIs('reports.*') ? $activeClass : $inactiveClass }}">
            <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('reports.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Reports
        </a>
    </div>
</div>

</div>
