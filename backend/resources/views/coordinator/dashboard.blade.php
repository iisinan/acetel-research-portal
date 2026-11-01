@extends('layouts.coordinator')

@section('header', 'Program Overview')

@section('content')
<div class="space-y-10 pb-10">
    <!-- Sophisticated Header -->
    <div class="relative overflow-hidden rounded-2xl sm:rounded-[2.5rem] p-6 sm:p-10 lg:p-14 bg-white border border-green-100 shadow-xl shadow-green-500/5">
        <div class="absolute top-0 right-0 w-[40rem] h-[40rem] bg-green-50/50 blur-[100px] rounded-full -mr-32 -mt-32 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-green-50/30 blur-[80px] rounded-full -ml-32 -mb-32 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-end justify-between gap-6 sm:gap-8">
            <div class="space-y-4 sm:space-y-6">
                <div class="flex items-center gap-2.5 sm:gap-3 text-green-600">
                    <div class="p-1.5 sm:p-2 rounded-xl bg-green-50 border border-green-100">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>
                    </div>
                    <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.25em] sm:tracking-[0.3em]">Coordinator Dashboard</span>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-slate-900 tracking-tight">Program <span class="text-green-600">Status</span></h1>
                    <p class="mt-2 sm:mt-4 text-xs sm:text-base font-medium text-slate-500 leading-relaxed max-w-xl">Comprehensive overview of students and their supervision trajectory across your assigned programs.</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3 sm:gap-4">
                <form action="{{ route('coordinator.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2.5 sm:gap-4">
                    @if(count($programs) > 1)
                        <div class="flex-1 sm:flex-none px-4 sm:px-5 py-2.5 sm:py-3 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-2 hover:border-green-300 transition-colors">
                            <select name="program_id" onchange="this.form.submit()" class="w-full sm:w-auto bg-transparent border-none text-[11px] sm:text-xs font-bold uppercase tracking-widest text-slate-500 focus:ring-0 cursor-pointer">
                                <option value="">All Programs</option>
                                @foreach($programs as $prog)
                                    <option value="{{ $prog->id }}" {{ request('program_id') == $prog->id ? 'selected' : '' }}>{{ $prog->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if(count($levels) > 1)
                        <div class="flex-1 sm:flex-none px-4 sm:px-5 py-2.5 sm:py-3 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-2 hover:border-green-300 transition-colors">
                            <select name="level_id" onchange="this.form.submit()" class="w-full sm:w-auto bg-transparent border-none text-[11px] sm:text-xs font-bold uppercase tracking-widest text-slate-500 focus:ring-0 cursor-pointer">
                                <option value="">All Levels</option>
                                @foreach($levels as $lvl)
                                    <option value="{{ $lvl->id }}" {{ request('level_id') == $lvl->id ? 'selected' : '' }}>{{ $lvl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if(request('program_id') || request('level_id'))
                        <a href="{{ route('coordinator.dashboard') }}" class="p-2.5 sm:p-3 text-slate-400 hover:text-rose-500 bg-white border border-slate-200 rounded-2xl transition-all shadow-sm" title="Reset Filters">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </form>

                <div class="px-5 sm:px-6 py-2.5 sm:py-3 rounded-2xl bg-green-600 text-white shadow-lg shadow-green-600/20 flex items-center justify-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                    <span class="text-[10px] sm:text-xs font-black uppercase tracking-widest">Active State</span>
                </div>
            </div>
        </div>
    </div>

    <!-- High-Impact Analytics -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-6">
        <div class="bg-white p-4 sm:p-7 rounded-2xl sm:rounded-3xl border border-green-50 shadow-sm flex flex-col justify-between hover:border-green-200 transition-colors group">
            <div class="flex items-center justify-between mb-4 sm:mb-8">
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-green-50 text-green-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
                <span class="text-[8px] sm:text-[9px] font-black text-slate-400 uppercase tracking-widest">Total</span>
            </div>
            <div>
                <p class="text-xl sm:text-3xl font-black text-slate-900 leading-none tracking-tight">{{ number_format($totalStudents) }}</p>
                <p class="text-[8px] sm:text-[10px] font-bold text-slate-400 mt-1.5 sm:mt-2 uppercase tracking-widest">Students Registered</p>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-7 rounded-2xl sm:rounded-3xl border border-green-50 shadow-sm flex flex-col justify-between hover:border-green-200 transition-colors group">
            <div class="flex items-center justify-between mb-4 sm:mb-8">
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" /></svg>
                </div>
                <span class="text-[8px] sm:text-[9px] font-black text-slate-400 uppercase tracking-widest">Faculty</span>
            </div>
            <div>
                <p class="text-xl sm:text-3xl font-black text-slate-900 leading-none tracking-tight">{{ number_format($totalSupervisors) }}</p>
                <p class="text-[8px] sm:text-[10px] font-bold text-slate-400 mt-1.5 sm:mt-2 uppercase tracking-widest">Active Supervisors</p>
            </div>
        </div>

        <div class="bg-slate-900 p-4 sm:p-7 rounded-2xl sm:rounded-3xl shadow-xl shadow-slate-900/10 flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-green-500/10 rounded-full blur-2xl"></div>
            <div class="flex items-center justify-between mb-4 sm:mb-8 relative z-10">
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-white/10 text-green-400 flex items-center justify-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                </div>
                <span class="text-[8px] sm:text-[9px] font-black text-white/40 uppercase tracking-widest">Research</span>
            </div>
            <div class="relative z-10">
                <p class="text-xl sm:text-3xl font-black text-white leading-none tracking-tight">{{ number_format($activeTheses) }}</p>
                <p class="text-[8px] sm:text-[10px] font-bold text-slate-400 mt-1.5 sm:mt-2 uppercase tracking-widest">Active Theses</p>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-7 rounded-2xl sm:rounded-3xl border border-green-50 shadow-sm flex flex-col justify-between hover:border-green-200 transition-colors group">
            <div class="flex items-center justify-between mb-4 sm:mb-8">
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                </div>
                <span class="text-[8px] sm:text-[9px] font-black text-slate-400 uppercase tracking-widest">Pending</span>
            </div>
            <div>
                <p class="text-xl sm:text-3xl font-black text-slate-900 leading-none tracking-tight">{{ number_format($pending_reviews->count()) }}</p>
                <p class="text-[8px] sm:text-[10px] font-bold text-slate-400 mt-1.5 sm:mt-2 uppercase tracking-widest">Pending Reviews</p>
            </div>
        </div>

        <div class="col-span-2 sm:col-span-1 bg-white p-4 sm:p-7 rounded-2xl sm:rounded-3xl border border-green-50 shadow-sm flex flex-col justify-between hover:border-green-200 transition-colors group">
            <div class="flex items-center justify-between mb-4 sm:mb-8">
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                </div>
                <span class="text-[8px] sm:text-[9px] font-black text-slate-400 uppercase tracking-widest">Inbox</span>
            </div>
            <div>
                <p class="text-xl sm:text-3xl font-black text-slate-900 leading-none tracking-tight">{{ number_format($unreadMessages ?? 0) }}</p>
                <p class="text-[8px] sm:text-[10px] font-bold text-slate-400 mt-1.5 sm:mt-2 uppercase tracking-widest">Unread Messages</p>
            </div>
        </div>
    </div>

    <!-- Main Operational Grid -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 items-start">
        
        <!-- Left Sidebar: Actions & Distribution (Spans 4) -->
        <div class="lg:col-span-4 space-y-8">
            
            <!-- Redesigned Strategic Alert Hub -->
            <div x-data="{ activeTab: 'milestones' }" class="bg-white border border-slate-100 rounded-[2.5rem] shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="px-8 py-6 bg-slate-900 border-b border-slate-800">
                    <h3 class="text-sm font-black text-white tracking-widest uppercase flex items-center gap-3">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                        </span>
                        Operational Alerts
                    </h3>
                </div>

                <div class="flex border-b border-slate-100">
                    <button @click="activeTab = 'milestones'" :class="activeTab === 'milestones' ? 'border-green-500 text-green-600' : 'border-transparent text-slate-400 hover:text-slate-600'" class="flex-1 py-4 text-[10px] font-black uppercase tracking-widest border-b-2 transition-all">
                        Milestones ({{ $milestoneAlerts->count() }})
                    </button>
                    <button @click="activeTab = 'supervisors'" :class="activeTab === 'supervisors' ? 'border-green-500 text-green-600' : 'border-transparent text-slate-400 hover:text-slate-600'" class="flex-1 py-4 text-[10px] font-black uppercase tracking-widest border-b-2 transition-all">
                        Faculty ({{ $inactiveSupervisors->count() }})
                    </button>
                </div>

                <div class="p-6">
                    <!-- Milestones Tab -->
                    <div x-show="activeTab === 'milestones'" class="space-y-3">
                        @forelse($milestoneAlerts as $alert)
                            <a href="{{ route('milestones.index', ['thesis_id' => $alert->thesis_project_id]) }}" class="group relative z-10 flex items-center p-4 bg-white hover:bg-green-50/50 rounded-2xl border border-slate-100 hover:border-green-200 transition-all cursor-pointer shadow-sm hover:shadow-md">
                                <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 text-slate-400 group-hover:text-green-600 flex items-center justify-center shrink-0 font-black text-xs shadow-sm transition-all uppercase">
                                    {{ substr($alert->thesis->student->user->name, 0, 1) }}
                                </div>
                                <div class="ml-4 flex-1">
                                    <p class="text-xs font-black text-slate-800 leading-none group-hover:text-green-700 transition-colors">{{ $alert->thesis->student->user->name }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase tracking-widest italic opacity-70">Pending: {{ $alert->template->name }}</p>
                                </div>
                                <div class="p-2 rounded-lg bg-green-500 text-white opacity-0 group-hover:opacity-100 transition-all -translate-x-2 group-hover:translate-x-0 shadow-sm">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                </div>
                            </a>
                        @empty
                            <div class="py-10 text-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No Priority Milestones</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Inactive Supervisors Tab -->
                    <div x-show="activeTab === 'supervisors'" class="space-y-3">
                        @forelse($inactiveSupervisors as $sup)
                            <a href="{{ route('coordinator.supervisors.index') }}" class="group relative z-10 flex items-center p-4 bg-white hover:bg-amber-50/50 rounded-2xl border border-slate-100 hover:border-amber-200 transition-all cursor-pointer shadow-sm hover:shadow-md">
                                <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 text-slate-400 group-hover:text-amber-600 flex items-center justify-center shrink-0 font-black text-xs shadow-sm transition-all uppercase">
                                    {{ substr($sup->user->name, 0, 1) }}
                                </div>
                                <div class="ml-4 flex-1">
                                    <p class="text-xs font-black text-slate-800 leading-none group-hover:text-amber-700 transition-colors">{{ $sup->user->name }}</p>
                                    <span class="inline-flex mt-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[8px] font-black uppercase tracking-widest">Zero Workload</span>
                                </div>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-amber-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" /></svg>
                            </a>
                        @empty
                            <div class="py-10 text-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Faculty Optimized</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-100 rounded-3xl p-8 shadow-sm">
                <h3 class="text-lg font-black text-slate-900 mb-6 tracking-tight uppercase border-b border-slate-50 pb-4">Quick Governance</h3>
                
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('coordinator.students.index') }}" class="group flex items-center p-4 bg-slate-50 hover:bg-green-50 rounded-2xl border border-slate-100 hover:border-green-100 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 text-green-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs font-black text-slate-800 uppercase tracking-wider leading-none">Manage Students</p>
                            <p class="text-[9px] text-slate-400 mt-1 font-bold uppercase tracking-widest">Full Directory</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 ml-auto group-hover:text-green-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                    </a>

                    <a href="{{ route('coordinator.supervisors.index') }}" class="group flex items-center p-4 bg-slate-50 hover:bg-green-50 rounded-2xl border border-slate-100 hover:border-green-100 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 text-green-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" /></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs font-black text-slate-800 uppercase tracking-wider leading-none">Supervisors</p>
                            <p class="text-[9px] text-slate-400 mt-1 font-bold uppercase tracking-widest">Assignment Engine</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 ml-auto group-hover:text-green-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                    </a>

                    <a href="{{ route('coordinator.cohorts.index') }}" class="group flex items-center p-4 bg-slate-50 hover:bg-green-50 rounded-2xl border border-slate-100 hover:border-green-100 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 text-green-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" /></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-xs font-black text-slate-800 uppercase tracking-wider leading-none">Cohorts</p>
                            <p class="text-[9px] text-slate-400 mt-1 font-bold uppercase tracking-widest">Growth Analytics</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 ml-auto group-hover:text-green-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            </div>

            {{-- Institutional Resource Center --}}
            @if(isset($document_templates) && $document_templates->count() > 0)
            <div class="bg-white border border-slate-100 rounded-3xl p-8 shadow-sm">
                <h3 class="text-lg font-black text-slate-900 mb-6 tracking-tight uppercase border-b border-slate-50 pb-4">Resource Center</h3>
                <div class="space-y-4">
                    @foreach($document_templates as $template)
                    <a href="{{ route('templates.download', $template) }}" class="flex items-center gap-4 p-4 bg-slate-50 hover:bg-green-50 rounded-2xl border border-slate-100 hover:border-green-200 transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 text-slate-400 group-hover:text-green-600 flex items-center justify-center shrink-0 transition-all shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-black text-slate-800 truncate leading-none uppercase">{{ $template->title }}</p>
                            <p class="text-[9px] font-bold text-slate-400 mt-1.5 uppercase tracking-tighter italic opacity-70">Version {{ $template->version }} • {{ strtoupper($template->type) }}</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 ml-auto group-hover:text-green-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="bg-white border border-slate-100 rounded-3xl p-8 shadow-sm">
                <h3 class="text-lg font-black text-slate-900 mb-8 tracking-tight uppercase border-b border-slate-50 pb-4">Academic Mix</h3>
                
                <div class="space-y-8">
                    @php
                        $mscCount = $students->filter(fn($s) => str_contains(strtolower($s->level->name ?? ''), 'msc'))->count();
                        $phdCount = $students->filter(fn($s) => str_contains(strtolower($s->level->name ?? ''), 'phd'))->count();
                        $total = $students->count() ?: 1;
                        $mscPercent = ($mscCount / $total) * 100;
                        $phdPercent = ($phdCount / $total) * 100;
                    @endphp
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-xs font-black text-slate-800 leading-none">MSc Trajectory</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5">Masters Program</p>
                            </div>
                            <span class="text-xs font-black text-green-600">{{ number_format($mscPercent, 1) }}%</span>
                        </div>
                        <div class="h-2 w-full bg-slate-50 rounded-full overflow-hidden border border-slate-100 shadow-inner">
                            <div class="h-full bg-green-500 rounded-full transition-all duration-1000 shadow-sm" style="width: {{ $mscPercent }}%"></div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-xs font-black text-slate-800 leading-none">PhD Residency</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5">Doctoral Core</p>
                            </div>
                            <span class="text-xs font-black text-blue-600">{{ number_format($phdPercent, 1) }}%</span>
                        </div>
                        <div class="h-2 w-full bg-slate-50 rounded-full overflow-hidden border border-slate-100 shadow-inner">
                            <div class="h-full bg-blue-500 rounded-full transition-all duration-1000 shadow-sm" style="width: {{ $phdPercent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content: Student Directory (Spans 8) -->
        <div class="lg:col-span-8">
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden min-h-[500px] flex flex-col">
                <div class="px-5 sm:px-10 py-5 sm:py-8 border-b border-slate-50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0 bg-slate-50/30">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">Student Directory</h3>
                        <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5 sm:mt-1">Institutional Roster</p>
                    </div>
                    <a href="{{ route('coordinator.students.index') }}" class="w-full sm:w-auto text-center justify-center group flex items-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 rounded-2xl bg-green-50 text-green-700 hover:bg-green-100 transition-all font-black text-xs uppercase tracking-widest">
                        Full Roster
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>

                <div class="flex-1">
                    @if(isset($students) && $students->count() > 0)
                        <!-- Mobile Card View (screens < 640px) -->
                        <div class="block sm:hidden divide-y divide-slate-100 p-3 space-y-3">
                            @foreach($students->take(10) as $student)
                                @php
                                    $daysSinceLastUpdate = $student->thesis ? $student->thesis->updated_at->diffInDays(now()) : 0;
                                    $statusClass = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
                                    $statusText = 'On Track';
                                    $dotClass = 'bg-emerald-500';
                                    
                                    if ($student->thesis) {
                                        if ($daysSinceLastUpdate > 30) {
                                            $statusClass = 'bg-rose-50 text-rose-600 border border-rose-100';
                                            $statusText = 'Stalled (' . $daysSinceLastUpdate . 'd)';
                                            $dotClass = 'bg-rose-500 animate-pulse';
                                        } elseif ($daysSinceLastUpdate > 14) {
                                            $statusClass = 'bg-amber-50 text-amber-600 border border-amber-100';
                                            $statusText = 'Delayed';
                                            $dotClass = 'bg-amber-500';
                                        }

                                        if($student->thesis->status === 'completed') {
                                            $statusClass = 'bg-blue-50 text-blue-600 border border-blue-100';
                                            $statusText = 'Archived';
                                            $dotClass = 'bg-blue-500';
                                        }
                                    }
                                @endphp
                                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs space-y-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-xl bg-green-50 text-green-700 font-black text-sm flex items-center justify-center shrink-0 border border-green-100">
                                                {{ substr($student->user->name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <a href="{{ route('coordinator.students.show', $student->id) }}" class="text-sm font-bold text-slate-900 truncate block hover:text-green-600 transition-colors">
                                                    {{ $student->user->name }}
                                                </a>
                                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $student->program->name ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                        @if($student->thesis)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full {{ $statusClass }} text-[8px] font-black uppercase tracking-wider shrink-0">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                                {{ $statusText }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 text-[8px] font-black uppercase tracking-wider shrink-0">
                                                Pending Init
                                            </span>
                                        @endif
                                    </div>

                                    @if($student->thesis)
                                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100/80 space-y-1">
                                            <p class="text-xs font-semibold text-slate-800 line-clamp-2">{{ $student->thesis->title }}</p>
                                            @php $current = $student->thesis->currentMilestone; @endphp
                                            @if($current)
                                                <p class="text-[9px] font-bold text-green-600 uppercase tracking-wider">
                                                    Current: M{{ $current->template->order ?? '?' }} - {{ Str::limit($current->template->name ?? 'Unknown', 28) }}
                                                </p>
                                            @else
                                                <p class="text-[9px] font-bold text-emerald-600 uppercase tracking-wider">COMPLETED</p>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between pt-2 border-t border-slate-50 gap-2">
                                        @if($student->thesis)
                                            <a href="{{ route('milestones.index', ['thesis_id' => $student->thesis->id]) }}" class="flex-1 text-center py-2 px-3 rounded-xl bg-green-50 text-green-700 text-xs font-bold hover:bg-green-100 transition-colors">
                                                Milestones
                                            </a>
                                        @endif
                                        <a href="{{ route('coordinator.students.show', $student->id) }}" class="flex-1 text-center py-2 px-3 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-green-600 transition-colors">
                                            View Student
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Desktop Table (screens >= 640px) -->
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-slate-50/50">
                                    <tr>
                                        <th class="px-6 lg:px-10 py-4 lg:py-5 text-[9px] font-black text-slate-400 uppercase tracking-widest">Student</th>
                                        <th class="px-4 lg:px-6 py-4 lg:py-5 text-[9px] font-black text-slate-400 uppercase tracking-widest">Progress</th>
                                        <th class="px-4 lg:px-6 py-4 lg:py-5 text-[9px] font-black text-slate-400 uppercase tracking-widest">Alert Status</th>
                                        <th class="px-6 lg:px-10 py-4 lg:py-5 text-right"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($students->take(10) as $student)
                                        <tr class="hover:bg-slate-50/30 transition-colors group">
                                            <td class="px-6 lg:px-10 py-4 lg:py-6">
                                                <div class="flex items-center gap-3 lg:gap-4">
                                                    <div class="w-10 h-10 rounded-2xl bg-slate-50 border border-slate-100 text-slate-400 group-hover:text-green-600 group-hover:border-green-100 transition-all font-black text-sm flex items-center justify-center shadow-sm shrink-0">
                                                        {{ substr($student->user->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                    <a href="{{ route('milestones.index', ['thesis_id' => $student->thesis?->id]) }}" class="group/name block relative z-10 cursor-pointer">
                                                        <p class="text-sm font-bold text-slate-900 leading-none group-hover/name:text-green-700 transition-colors">{{ $student->user->name }}</p>
                                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5">{{ $student->program->name ?? 'N/A' }}</p>
                                                    </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 lg:px-6 py-4 lg:py-6">
                                                @if($student->thesis)
                                                    <div class="flex flex-col">
                                                        <span class="text-xs font-bold text-slate-900 truncate max-w-[200px]" title="{{ $student->thesis->title }}">{{ $student->thesis->title }}</span>
                                                        @php
                                                            $current = $student->thesis->currentMilestone;
                                                        @endphp
                                                        @if($current)
                                                            <span class="text-[8px] font-black text-green-600 uppercase tracking-tighter mt-1 italic">
                                                                Current: M{{ $current->template->order ?? '?' }} - {{ Str::limit($current->template->name ?? 'Unknown', 25) }}
                                                            </span>
                                                        @else
                                                            <a href="{{ route('milestones.index', ['thesis_id' => $student->thesis->id]) }}" class="text-[8px] font-black text-emerald-600 uppercase tracking-tighter mt-1 italic hover:underline">
                                                                COMPLETED
                                                            </a>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest italic">No Thesis Init</span>
                                                @endif
                                            </td>
                                            <td class="px-4 lg:px-6 py-4 lg:py-6">
                                                @if($student->thesis)
                                                    @php
                                                        $daysSinceLastUpdate = $student->thesis->updated_at->diffInDays(now());
                                                        $statusClass = 'bg-emerald-50 text-emerald-600';
                                                        $statusText = 'On Track';
                                                        $dotClass = 'bg-emerald-500';
                                                        
                                                        if ($daysSinceLastUpdate > 30) {
                                                            $statusClass = 'bg-rose-50 text-rose-600';
                                                            $statusText = 'Stalled (' . $daysSinceLastUpdate . 'd)';
                                                            $dotClass = 'bg-rose-500 animate-pulse';
                                                        } elseif ($daysSinceLastUpdate > 14) {
                                                            $statusClass = 'bg-amber-50 text-amber-600';
                                                            $statusText = 'Delayed';
                                                            $dotClass = 'bg-amber-500';
                                                        }

                                                        if($student->thesis->status === 'completed') {
                                                            $statusClass = 'bg-blue-50 text-blue-600';
                                                            $statusText = 'Archived';
                                                            $dotClass = 'bg-blue-500';
                                                        }
                                                    @endphp
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full {{ $statusClass }} text-[8px] font-black uppercase tracking-widest shadow-sm ring-1 ring-current/10 whitespace-nowrap">
                                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                                                        {{ $statusText }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-[8px] font-black uppercase tracking-widest shadow-sm ring-1 ring-current/10 whitespace-nowrap">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                        Pending Init
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 lg:px-10 py-4 lg:py-6 text-right">
                                                <a href="{{ route('coordinator.students.show', $student->id) }}" class="inline-flex items-center justify-center p-2 rounded-xl bg-white border border-slate-100 text-slate-400 hover:border-green-500 hover:text-green-600 hover:shadow-lg hover:shadow-green-500/5 transition-all">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-16 text-center">
                            <p class="text-sm font-bold text-slate-400">No students found matching current criteria.</p>
                        </div>
                    @endif
                </div>

                <!-- Strategic Progress Metrics -->
                <div class="mt-auto px-5 sm:px-10 py-6 sm:py-10 bg-slate-50/50 border-t border-slate-50 grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-10">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-[9px] font-black text-slate-400 uppercase tracking-widest">
                            Milestone 1 Completion
                            <span class="text-slate-900">{{ number_format(($clearanceMetrics['m1'] ?? 0), 1) }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-white rounded-full overflow-hidden border border-slate-200 shadow-inner">
                            <div class="h-full bg-green-500 rounded-full shadow-sm" style="width: {{ $clearanceMetrics['m1'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-[9px] font-black text-slate-400 uppercase tracking-widest">
                            Milestone 2 Completion
                            <span class="text-slate-900">{{ number_format(($clearanceMetrics['m2'] ?? 0), 1) }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-white rounded-full overflow-hidden border border-slate-200 shadow-inner">
                            <div class="h-full bg-blue-500 rounded-full shadow-sm" style="width: {{ $clearanceMetrics['m2'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-[9px] font-black text-slate-400 uppercase tracking-widest">
                            VIVA Readiness
                            <span class="text-slate-900">{{ number_format(($clearanceMetrics['m6'] ?? 0), 1) }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-white rounded-full overflow-hidden border border-slate-200 shadow-inner">
                            <div class="h-full bg-green-600 rounded-full shadow-sm" style="width: {{ $clearanceMetrics['m6'] ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
