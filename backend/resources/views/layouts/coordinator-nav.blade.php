@php
    $navItems = [
        ['name' => 'Dashboard', 'route' => 'coordinator.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['name' => 'Inbox', 'route' => 'inbox.index', 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['name' => 'Students', 'route' => 'coordinator.students.index', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['name' => 'Cohorts', 'route' => 'coordinator.cohorts.index', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
        ['name' => 'Supervisors', 'route' => 'coordinator.supervisors.index', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
        ['name' => 'Assigned Supervisor', 'route' => 'coordinator.assigned-supervisors.index', 'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
        ['name' => 'Examiners', 'route' => 'coordinator.examiners.index', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ];
@endphp

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

@foreach($navItems as $item)
    @php
        $isActive = request()->routeIs($item['route'].'*');
    @endphp
    <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}" 
       class="{{ $isActive ? 'bg-acetel-500/15 text-white shadow-lg shadow-acetel-500/5' : 'text-slate-400 hover:text-white hover:bg-white/5' }} group flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all duration-300 relative overflow-hidden">
        @if($isActive)
            <div class="absolute inset-y-0 left-0 w-1 bg-acetel-500 rounded-full my-3"></div>
        @endif
        <svg class="{{ $isActive ? 'text-acetel-400' : 'text-slate-500 group-hover:text-slate-300' }} mr-4 flex-shrink-0 h-5 w-5 transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
        </svg>
        <span class="tracking-tight">{{ $item['name'] }}</span>
        @if($item['name'] === 'Inbox' && $unreadInbox > 0)
            <span class="ml-auto px-2 py-0.5 bg-acetel-500 text-white text-[9px] font-black rounded-full leading-none shadow-sm shadow-acetel-500/30 animate-blink">{{ $unreadInbox }}</span>
        @endif
    </a>
@endforeach

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

<div class="mt-10 pt-8 border-t border-white/5">
    <div class="px-4">
        <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-4">
            Reports
        </p>
        <a href="{{ route('reports.index') }}" class="group flex items-center px-4 py-3 text-sm font-bold text-slate-400 hover:text-white hover:bg-white/5 rounded-2xl transition-all duration-300">
             <div class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center mr-4 group-hover:bg-slate-700 transition-colors">
                 <svg class="h-4 w-4 text-slate-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                 </svg>
             </div>
             System Reports
        </a>

        @if(auth()->user()->hasRole(['Admin', 'Director']))
        <a href="{{ route('admin.audit.index') }}" class="group flex items-center px-4 py-3 text-sm font-bold text-slate-400 hover:text-white hover:bg-white/5 rounded-2xl transition-all duration-300">
             <div class="w-8 h-8 rounded-xl bg-slate-800 flex items-center justify-center mr-4 group-hover:bg-rose-600 transition-colors">
                 <svg class="h-4 w-4 text-slate-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                 </svg>
             </div>
             Audit Logs
        </a>
        @endif
    </div>
</div>
