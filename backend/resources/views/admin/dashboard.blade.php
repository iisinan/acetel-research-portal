@extends('layouts.admin')

@section('header')
    Governance Core
@endsection

@section('content')
<div class="space-y-10">
    {{-- Strategic Governance Header --}}
    <div class="relative overflow-hidden rounded-[2.5rem] p-10 lg:p-14 bg-white border border-green-100 shadow-xl shadow-green-500/5">
        <div class="absolute top-0 right-0 w-[40rem] h-[40rem] bg-green-50/50 blur-[100px] rounded-full -mr-32 -mt-32 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-green-50/30 blur-[80px] rounded-full -ml-32 -mb-32 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-10">
            <div class="flex-1 space-y-8">
                <div class="inline-flex items-center gap-2.5 px-4 py-2 bg-green-50 border border-green-200 rounded-full text-green-700 text-[10px] font-black uppercase tracking-wider shadow-sm">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    Institutional Governance Core
                </div>
                <div>
                    <h1 class="text-4xl lg:text-6xl font-black text-slate-900 tracking-tight leading-none mb-6">
                        System <span class="text-green-600">Administration</span>
                    </h1>
                    <p class="text-lg text-slate-500 font-medium leading-relaxed max-w-2xl">
                        Comprehensive administrative oversight for the ACETEL digital ecosystem. Monitor system health, manage users, and coordinate institutional protocols.
                    </p>
                </div>
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="{{ route('admin.users.index') }}" class="px-8 py-3.5 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold text-sm transition-all shadow-lg shadow-green-600/20 flex items-center gap-2 group">
                        User Portfolio
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

    </div>

    {{-- Urgent Operational Alerts (M9 / Final Phase) --}}
    @if(isset($m9Alerts) && $m9Alerts->count() > 0)
    <div x-data="adminDashboard" class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-[2.5rem] p-1 shadow-xl shadow-amber-500/20 relative overflow-visible group z-20">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-30"></div>
        <div class="bg-white rounded-[2.4rem] p-6 lg:p-8 relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-start gap-5">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center shrink-0 border border-amber-200">
                    <span class="relative flex h-4 w-4">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-4 w-4 bg-amber-500"></span>
                    </span>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Final Archival (M9) Alerts</h3>
                    <p class="text-sm font-medium text-slate-500 mt-1">There are {{ $m9Alerts->count() }} students awaiting institutional clearance at the final graduation phase.</p>
                </div>
            </div>

            <div class="w-full md:w-auto relative group/dropdown">
                <button type="button" class="w-full md:w-auto px-8 py-4 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-xl font-black tracking-widest uppercase text-[10px] transition-all border border-amber-200 flex items-center justify-between md:justify-center gap-3">
                    Review Pending Cases
                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
                
                {{-- Dropdown Container --}}
                <div class="absolute right-0 top-full mt-2 w-full md:w-96 bg-white border border-slate-100 rounded-3xl shadow-2xl opacity-0 invisible group-hover/dropdown:opacity-100 group-hover/dropdown:visible transition-all duration-300 transform origin-top-right z-50 overflow-hidden">
                    <div class="bg-slate-50 border-b border-slate-100 px-6 py-4">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Action Required</span>
                    </div>
                    <div class="max-h-[350px] overflow-y-auto custom-scrollbar divide-y divide-slate-50">
                        @foreach($m9Alerts as $alert)
                        <div class="px-6 py-5 hover:bg-amber-50 transition-colors group/alert relative">
                            <a href="{{ route('milestones.index', ['thesis_id' => $alert->thesis->id]) }}#milestone-{{$alert->id}}">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-black text-slate-800 text-sm tracking-tight">{{ $alert->thesis->student->user->name ?? 'Student' }}</span>
                                    <span class="text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md {{ $alert->status === 'submitted' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">{{ str_replace('_', ' ', $alert->status) }}</span>
                                </div>
                                <span class="text-xs text-slate-500 font-medium line-clamp-1 italic pr-12">{{ $alert->thesis->title }}</span>
                                <div class="text-[9px] uppercase font-bold text-slate-400 tracking-widest mt-3 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Updated {{ $alert->updated_at->diffForHumans() }}
                                </div>
                            </a>

                            {{-- Admin Quick Approve --}}
                            <button type="button" @click.stop="quickApprove('{{ $alert->id }}', $el)" class="absolute right-6 bottom-5 p-2.5 bg-amber-600 text-white rounded-xl shadow-lg shadow-amber-600/30 opacity-0 group-hover/alert:opacity-100 translate-x-4 group-hover/alert:translate-x-0 transition-all duration-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Stats Matrix --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-green-100 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 leading-none">{{ number_format($stats['total_users']) }}</p>
                <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-wider">Total Users</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-green-100 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 leading-none">{{ number_format($stats['program_count']) }}</p>
                <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-wider">Programs</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-green-100 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 leading-none">{{ number_format($project_count ?? 0) }}</p>
                <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-wider">Active Theses</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-green-100 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900 leading-none">{{ number_format($activityStats['logins_today']) }}</p>
                <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-wider">Logins Today</p>
            </div>
        </div>
    </div>

    {{-- Session Intelligence Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-green-500 to-green-700 p-6 rounded-2xl shadow-lg shadow-green-500/20 text-white relative overflow-hidden group">
            <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/10 blur-2xl rounded-full group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <span class="text-[9px] font-black uppercase tracking-widest opacity-80">Active Sessions</span>
                </div>
                <p class="text-3xl font-black leading-none">{{ $activityStats['active_sessions'] }}</p>
                <p class="text-[10px] font-bold mt-2 opacity-70 uppercase tracking-wider">Last 24 Hours</p>
            </div>
        </div>
        <div class="bg-gradient-to-br from-slate-700 to-slate-900 p-6 rounded-2xl shadow-lg shadow-slate-900/20 text-white relative overflow-hidden group">
            <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/5 blur-2xl rounded-full group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="text-[9px] font-black uppercase tracking-widest opacity-80">This Week</span>
                </div>
                <p class="text-3xl font-black leading-none">{{ $activityStats['logins_this_week'] }}</p>
                <p class="text-[10px] font-bold mt-2 opacity-70 uppercase tracking-wider">Total Logins</p>
            </div>
        </div>
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 p-6 rounded-2xl shadow-lg shadow-indigo-500/20 text-white relative overflow-hidden group">
            <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/10 blur-2xl rounded-full group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <span class="text-[9px] font-black uppercase tracking-widest opacity-80">Unique Users</span>
                </div>
                <p class="text-3xl font-black leading-none">{{ $activityStats['unique_users_today'] }}</p>
                <p class="text-[10px] font-bold mt-2 opacity-70 uppercase tracking-wider">Logged In Today</p>
            </div>
        </div>
        <div class="bg-gradient-to-br from-rose-500 to-rose-700 p-6 rounded-2xl shadow-lg shadow-rose-500/20 text-white relative overflow-hidden group">
            <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/10 blur-2xl rounded-full group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span class="text-[9px] font-black uppercase tracking-widest opacity-80">System Errors</span>
                </div>
                <p class="text-3xl font-black leading-none">{{ number_format($stats['failed_jobs'] ?? 0) }}</p>
                <p class="text-[10px] font-bold mt-2 opacity-70 uppercase tracking-wider">Failed Jobs</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- User Activity Feed --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- Recent Login Sessions --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
                <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-6 bg-green-500 rounded-full"></div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight uppercase">User Activity Feed</h3>
                    </div>
                    <a href="{{ route('admin.activity-logs.index') }}" class="text-xs text-green-600 hover:text-green-700 font-bold uppercase tracking-wider flex items-center gap-1.5 group">
                        View Full Activity Log
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </a>
                </div>

                <div class="divide-y divide-slate-50">
                    @forelse($recentLogins as $login)
                        <div class="px-8 py-5 hover:bg-green-50/30 transition-all duration-300 group">
                            <div class="flex items-center gap-5">
                                {{-- User Avatar --}}
                                <div class="relative shrink-0">
                                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-green-100 to-green-200 border border-green-200 flex items-center justify-center text-green-700 font-black text-sm shadow-sm group-hover:scale-105 transition-transform">
                                        {{ substr($login->user->name ?? '?', 0, 1) }}
                                    </div>
                                    @if(!$login->logout_at && $login->login_at->diffInHours(now()) < 24)
                                        <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full shadow-sm"></span>
                                    @endif
                                </div>

                                {{-- Login Details --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-1">
                                        <p class="text-sm font-bold text-slate-800 truncate group-hover:text-green-700 transition-colors">{{ $login->user->name ?? 'Unknown' }}</p>
                                        <span class="text-[8px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest
                                            {{ $login->user?->getRoleNames()?->first() === 'Admin' ? 'bg-red-50 text-red-600 border border-red-100' : '' }}
                                            {{ $login->user?->getRoleNames()?->first() === 'Student' ? 'bg-blue-50 text-blue-600 border border-blue-100' : '' }}
                                            {{ $login->user?->getRoleNames()?->first() === 'Supervisor' ? 'bg-purple-50 text-purple-600 border border-purple-100' : '' }}
                                            {{ $login->user?->getRoleNames()?->first() === 'Program Coordinator' ? 'bg-amber-50 text-amber-600 border border-amber-100' : '' }}
                                            {{ $login->user?->getRoleNames()?->first() === 'Director' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : '' }}
                                            {{ !in_array($login->user?->getRoleNames()?->first(), ['Admin', 'Student', 'Supervisor', 'Program Coordinator', 'Director']) ? 'bg-slate-50 text-slate-500 border border-slate-100' : '' }}
                                        ">{{ $login->user?->getRoleNames()?->first() ?? 'User' }}</span>
                                    </div>
                                    <div class="flex items-center gap-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                            {{ $login->ip_address ?? 'localhost' }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            @if($login->device_type === 'mobile')
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                            @else
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                            @endif
                                            {{ $login->browser ?? 'N/A' }} · {{ $login->platform ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Timestamp --}}
                                <div class="text-right shrink-0">
                                    <p class="text-xs font-bold text-slate-500 italic">{{ $login->login_at->diffForHumans() }}</p>
                                    <p class="text-[9px] font-bold text-slate-300 uppercase tracking-wider mt-0.5">{{ $login->login_at->format('M d, H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-24 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">No recent logins detected.</p>
                            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest mt-2 italic">Activity will appear here as users authenticate</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- System Audit Trail --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
                <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-6 bg-slate-400 rounded-full"></div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight uppercase">System Audit Trail</h3>
                    </div>
                    <a href="{{ route('admin.audit-logs.index') }}" class="text-xs text-green-600 hover:text-green-700 font-bold uppercase tracking-wider">View All Logs →</a>
                </div>

                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Action</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">User</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none text-right">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($recent_logs as $log)
                                <tr class="hover:bg-slate-50/30 transition-colors group">
                                    <td class="px-8 py-5">
                                         <div class="flex items-center gap-4">
                                             <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 group-hover:text-green-600 group-hover:border-green-100 transition-all font-black shadow-sm">
                                                  <svg class="w-5 h-5 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                             </div>
                                             <div>
                                                 <p class="text-sm font-bold text-slate-800 group-hover:text-green-700 transition-colors leading-none mb-1">{{ $log->action }}</p>
                                                 <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none">{{ $log->ip_address ?? '127.0.0.1' }}</p>
                                             </div>
                                         </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="text-sm font-bold text-slate-700 leading-none mb-1 truncate max-w-[150px]">{{ $log->user->name ?? 'SYSTEM' }}</p>
                                        <p class="text-[9px] font-bold text-green-600 uppercase tracking-widest leading-none">{{ $log->user->getRoleNames()->first() ?? 'CORE' }}</p>
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                         <span class="text-xs font-bold text-slate-400 italic">{{ $log->created_at->diffForHumans() }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-24 text-center">
                                        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-8 h-8 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">No recent activity detected.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Control Matrix Sidebar --}}
        <div class="lg:col-span-4 space-y-8">

            {{-- Last Seen Users --}}
            <div class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-50 bg-slate-50/30">
                    <h3 class="text-sm font-black text-slate-900 tracking-tight uppercase">Last Seen Users</h3>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-1">Most recent login activity per user</p>
                </div>

                <div class="divide-y divide-slate-50 max-h-[480px] overflow-y-auto custom-scrollbar">
                    @forelse($usersWithLastLogin as $user)
                        <div class="px-6 py-4 hover:bg-green-50/30 transition-all group">
                            <div class="flex items-center gap-4">
                                <div class="relative shrink-0">
                                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-slate-100 to-slate-200 border border-slate-200 flex items-center justify-center text-slate-600 font-black text-xs group-hover:from-green-100 group-hover:to-green-200 group-hover:text-green-700 group-hover:border-green-200 transition-all">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    @if($user->last_login_at && \Carbon\Carbon::parse($user->last_login_at)->diffInMinutes(now()) < 30)
                                        <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate leading-none">{{ $user->name }}</p>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <span class="text-[8px] font-black px-1.5 py-0.5 rounded uppercase tracking-widest bg-slate-50 text-slate-500 border border-slate-100 leading-none">{{ $user->getRoleNames()->first() ?? 'User' }}</span>
                                        @if($user->total_logins)
                                            <span class="text-[8px] font-bold text-slate-400 leading-none">{{ $user->total_logins }} sessions</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    @if($user->last_login_at)
                                        <p class="text-[10px] font-bold text-slate-500 italic leading-none">{{ \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() }}</p>
                                        <p class="text-[8px] font-bold text-slate-300 uppercase tracking-wider mt-1 leading-none">{{ $user->last_session_browser ?? '' }}</p>
                                    @else
                                        <p class="text-[9px] font-bold text-slate-300 uppercase tracking-wider italic leading-none">Never</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center">
                            <p class="text-xs font-bold text-slate-400 italic">No user login data available.</p>
                        </div>
                    @endforelse
                </div>

                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                    <a href="{{ route('admin.activity-logs.index') }}" class="w-full py-3 bg-green-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-2 transition-all hover:bg-green-700 active:scale-95 shadow-lg shadow-green-600/20 group">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                        Full Activity Report
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </a>
                </div>
            </div>

            {{-- Control Center --}}
            <div class="bg-white border border-slate-100 rounded-3xl p-8 shadow-sm flex flex-col gap-6">
                 <h3 class="text-lg font-black text-slate-900 tracking-tight uppercase border-b border-slate-50 pb-4">Control Center</h3>
                 
                 <div class="space-y-3">
                     @php
                        $ops = [
                            ['name' => 'Identity Core', 'desc' => 'Manage Users', 'route' => 'admin.users.index', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                            ['name' => 'Cohorts Tracking', 'desc' => 'Register Students', 'route' => 'admin.cohorts.index', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                            ['name' => 'Academic Units', 'desc' => 'Manage Programs', 'route' => 'admin.programs.index', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                            ['name' => 'System Audit', 'desc' => 'View Activity', 'route' => 'admin.audit-logs.index', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                        ];
                     @endphp
                     @foreach($ops as $op)
                        <a href="{{ route($op['route']) }}" class="flex items-center gap-4 p-4 bg-slate-50 hover:bg-green-50 rounded-2xl border border-slate-100 hover:border-green-200 transition-all group">
                            <div class="w-10 h-10 bg-white border border-slate-100 flex items-center justify-center text-green-600 rounded-xl group-hover:scale-110 transition-transform shadow-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $op['icon'] }}" /></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-800 transition-colors uppercase leading-none">{{ $op['name'] }}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-1 leading-none">{{ $op['desc'] }}</p>
                            </div>
                        </a>
                     @endforeach
                 </div>
            </div>

            <div class="bg-green-600 rounded-3xl p-8 text-white shadow-xl shadow-green-600/20 relative overflow-hidden group">
                 <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-white/10 blur-2xl rounded-full transition-transform duration-1000 group-hover:scale-125"></div>
                 <h3 class="text-xl font-black italic tracking-tight leading-none mb-6">User Distribution</h3>
                 
                 @php
                     $sRate = $stats['total_users'] > 0 ? (($stats['student_count'] ?? 0) / $stats['total_users']) * 100 : 0;
                     $fRate = $stats['total_users'] > 0 ? (($stats['staff_count'] ?? 0) / $stats['total_users']) * 100 : 0;
                 @endphp

                 <div class="space-y-6">
                     <div class="space-y-2">
                         <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-wider mb-1">
                             <span class="text-green-100">Students</span>
                             <span>{{ number_format($sRate, 0) }}%</span>
                         </div>
                         <div class="h-1.5 w-full bg-black/20 rounded-full overflow-hidden">
                            <div class="h-full bg-white rounded-full transition-all duration-1000" style="width: {{ $sRate }}%"></div>
                         </div>
                     </div>
                     <div class="space-y-2">
                        <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-wider mb-1">
                            <span class="text-green-100">Staff Members</span>
                            <span>{{ number_format($fRate, 0) }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-black/20 rounded-full overflow-hidden">
                           <div class="h-full bg-white rounded-full transition-all duration-1000" style="width: {{ $fRate }}%"></div>
                        </div>
                    </div>
                 </div>

                 <button class="w-full py-4 mt-8 bg-white hover:bg-green-50 text-green-700 rounded-xl font-black text-[10px] uppercase tracking-widest hover:scale-[1.02] transition-all shadow-lg shadow-black/10">Execute System Sync</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('adminDashboard', () => ({
        async quickApprove(milestoneId, btn) {
            const ok = await window.confirmModal({
                title: 'Institutional Clearance',
                message: "Execute Institutional Clearance: As System Admin, are you finalizing this student's doctoral archives?",
                type: 'success',
                confirmText: 'Execute Clearance'
            });
            if (!ok) return;
            
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            btn.disabled = true;

            try {
                const response = await fetch(`/milestones/${milestoneId}/quick-approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        type: 'clear_milestone'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.toast.success('Archival Cleared.');
                    const card = btn.closest('.group\\/alert');
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        card.remove();
                        // Optional: Refresh page if no alerts left
                    }, 500);
                } else {
                    window.toast.error(data.message || 'Clearance failed.');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                window.toast.error('System Oversight Error.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }
    }));
});
</script>
@endpush
