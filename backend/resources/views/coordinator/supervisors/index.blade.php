@extends('layouts.coordinator')

@section('header', 'Supervisors')

@section('content')
<div class="space-y-10 pb-10">
    <!-- Sophisticated Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <div class="p-1.5 rounded-lg bg-acetel-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Staff</span>
            </div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Supervisors</h1>
            <p class="mt-2 text-sm font-medium text-slate-500">Directory of supervisors and their current student load.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('coordinator.supervisors.create') }}" class="group inline-flex items-center gap-3 px-6 py-4 bg-slate-900 text-white rounded-[2rem] font-black uppercase tracking-[0.1em] text-xs hover:bg-acetel-600 transition-all shadow-xl shadow-slate-900/10 hover:shadow-acetel-500/20">
                <svg class="w-4 h-4 group-hover:rotate-90 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                Add Supervisor
            </a>
        </div>
    </div>

    <!-- Premium Table Container -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden min-h-[500px] flex flex-col">
        <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
            <div>
                <h3 class="text-xl font-black text-slate-900 tracking-tight">Supervisors</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Staff Pool</p>
            </div>
            
            <div class="flex items-center gap-4 flex-wrap">
                <form action="{{ route('coordinator.supervisors.index') }}" method="GET" class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="relative w-full sm:w-64">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, rank, expertise..." class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-acetel-500/20 focus:border-acetel-500 text-slate-900 font-medium transition-all shadow-sm">
                    </div>
                
                    @if(count($programs) > 1)
                        <select name="program_id" onchange="this.form.submit()" class="bg-white border-slate-200 rounded-xl text-[10px] font-bold uppercase tracking-widest text-slate-500 focus:ring-acetel-500 focus:border-acetel-500 transition-all shadow-sm">
                            <option value="">All Programs</option>
                            @foreach($programs as $prog)
                                <option value="{{ $prog->id }}" {{ request('program_id') == $prog->id ? 'selected' : '' }}>{{ $prog->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    
                    @if(request('program_id') || request('search'))
                        <a href="{{ route('coordinator.supervisors.index') }}" class="p-2 text-slate-400 hover:text-rose-500 bg-white border border-slate-200 rounded-xl transition-all shadow-sm" title="Clear Filters">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                    <button type="submit" class="hidden"></button>
                </form>
            </div>
        </div>

        <div class="flex-1">
            @if(isset($supervisors) && $supervisors->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Supervisor</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Specialization</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Programs</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Load</th>
                                <th class="px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($supervisors as $supervisor)
                                <tr class="hover:bg-slate-50/30 transition-colors group">
                                    <td class="px-10 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-acetel-50 group-hover:text-acetel-500 transition-all font-black text-sm shadow-sm ring-1 ring-slate-100 group-hover:ring-acetel-100">
                                                {{ substr($supervisor->user?->name ?? 'U', 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-900 leading-none group-hover:text-acetel-600 transition-colors">{{ $supervisor->user?->name ?? 'Deleted User' }}</p>
                                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5">{{ $supervisor->user?->email ?? 'N/A' }}</p>
                                                <div class="flex flex-wrap gap-1 mt-2">
                                                    @if($supervisor->user)
@foreach($supervisor->user->getRoleNames() as $role)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-brand-50 text-brand-700 text-[8px] font-black uppercase tracking-wider border border-brand-100">
                                                            {{ $role }}
                                                        </span>
                                                    @endforeach
@else
<span class="inline-flex items-center px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 text-[8px] font-black uppercase tracking-wider border border-rose-100">No Roles</span>
@endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <span class="inline-flex items-center px-4 py-1.5 rounded-xl bg-acetel-50 text-acetel-600 text-[10px] font-black uppercase tracking-widest border border-acetel-100/50">
                                            {{ Str::limit($supervisor->specialization ?? 'General Research', 25) }}
                                        </span>
                                    <td class="px-6 py-6">
                                        <div class="flex flex-wrap gap-1.5">
                                            @forelse($supervisor->programs as $program)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600 text-[9px] font-black uppercase tracking-widest border border-slate-200/50">
                                                    {{ $program->name }}
                                                </span>
                                            @empty
                                                <span class="text-[9px] font-bold text-slate-400 italic">--</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        @php 
                                            $count = $supervisor->assignments->where('status', 'active')->count();
                                            $percent = $supervisor->max_students > 0 ? ($count / $supervisor->max_students) * 100 : 0;
                                        @endphp
                                        <div class="flex flex-col items-center gap-2">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-[10px] font-black text-slate-900 tracking-tighter">{{ $count }} <span class="text-slate-400">/ {{ $supervisor->max_students }}</span></span>
                                                <span class="text-[8px] font-bold text-slate-400 uppercase">Active</span>
                                            </div>
                                            <div class="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden shadow-inner ring-1 ring-slate-100">
                                                <div class="h-full @if($percent > 85) bg-rose-500 @elseif($percent > 60) bg-amber-500 @else bg-emerald-500 @endif transition-all duration-1000 shadow-[0_0_8px_rgba(16,185,129,0.3)]" style="width: {{ $percent }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-10 py-6 text-right">
                                        <div class="flex justify-end items-center gap-2">
                                            <a href="{{ route('coordinator.supervisors.show', $supervisor) }}" class="inline-flex items-center justify-center p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:border-acetel-500 hover:text-acetel-500 hover:shadow-lg hover:shadow-acetel-500/10 transition-all translate-y-0 hover:-translate-y-1" title="View Profile">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </a>
                                            @if($supervisor->user)
<form action="{{ route('coordinator.supervisors.reset-password', $supervisor) }}" method="POST" class="inline-block"
                                                data-confirm="Are you sure you want to reset the password for {{ $supervisor->user?->name ?? 'Deleted User' }}? Credentials will be sent via email."
                                                data-confirm-title="Reset Supervisor Password"
                                                data-confirm-type="warning"
                                                data-confirm-btn="Reset Password">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center p-2 rounded-xl bg-slate-50 border border-slate-100 text-slate-400 hover:border-rose-500 hover:text-rose-500 transition-all translate-y-0 hover:-translate-y-1" title="Reset Password">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                                                </button>
                                            </form>
@endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-[400px] text-center px-10">
                    <div class="w-20 h-20 rounded-[2rem] bg-slate-50 border border-slate-100 flex items-center justify-center mb-6 shadow-inner">
                        <svg class="w-10 h-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" /></svg>
                    </div>
                    <h4 class="text-lg font-black text-slate-900 tracking-tight">No Supervisors Found</h4>
                    <p class="text-sm text-slate-500 mt-2 max-w-xs mx-auto">No supervisors found in the system.</p>
                </div>
            @endif
        </div>

        @if($supervisors->hasPages())
            <div class="px-10 py-6 bg-slate-50/50 border-t border-slate-50">
                {{ $supervisors->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
