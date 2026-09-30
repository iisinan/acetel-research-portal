@extends('layouts.dashboard')

@section('header')
    User Profile: {{ $user->name }}
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2 text-brand-600">
                <a href="{{ route('admin.users.index') }}" class="p-1.5 rounded-lg bg-brand-50 hover:bg-brand-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Directory / User Profile</span>
            </div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">{{ $user->name }}</h1>
            <p class="mt-2 text-sm font-medium text-slate-500">{{ $user->email }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-bold rounded-xl hover:bg-brand-700 transition-colors shadow-lg shadow-brand-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit Profile
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 col-span-1">
            <div class="flex flex-col items-center text-center">
                <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-brand-100 to-brand-50 text-brand-600 flex items-center justify-center text-3xl font-black mb-4 shadow-inner">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <h3 class="text-xl font-bold text-slate-900">{{ $user->name }}</h3>
                <div class="mt-3 flex flex-wrap justify-center gap-2">
                    @foreach($user->roles as $role)
                        <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-lg text-[10px] font-black uppercase tracking-wider">{{ $role->name }}</span>
                    @endforeach
                </div>
            </div>
            <div class="mt-8 space-y-4 border-t border-slate-100 pt-6">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status</p>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        {{ $user->is_active ? 'Active Account' : 'Deactivated' }}
                    </span>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Joined</p>
                    <p class="text-sm font-medium text-slate-800">{{ $user->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 col-span-2">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Profile Configuration</h3>
            
            @if($user->hasRole('Student') && $user->studentProfile)
                <div class="space-y-6">
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Matric Number</p>
                                <p class="text-base font-bold text-slate-800">{{ $user->studentProfile->student_id_number }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Program</p>
                                <p class="text-sm font-medium text-slate-800">{{ $user->studentProfile->program->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Cohort</p>
                                <p class="text-sm font-medium text-slate-800">{{ $user->studentProfile->cohort->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($user->hasRole('Supervisor') && $user->supervisorProfile)
                <div class="space-y-6">
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Staff ID</p>
                                <p class="text-base font-bold text-slate-800">{{ $user->supervisorProfile->staff_id }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Rank</p>
                                <p class="text-sm font-medium text-slate-800">{{ $user->supervisorProfile->rank ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Capacity</p>
                                <p class="text-sm font-medium text-slate-800">{{ $user->supervisorProfile->current_load }} / {{ $user->supervisorProfile->max_students }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($user->hasRole('Program Coordinator') && $user->coordinatorProfiles->isNotEmpty())
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest">Coordinated Programs</h4>
                    <div class="grid gap-3">
                        @foreach($user->coordinatorProfiles as $cp)
                            <div class="px-4 py-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                                <span class="text-sm font-medium text-slate-800">{{ $cp->program->name ?? 'N/A' }}</span>
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-md text-[9px] font-black uppercase tracking-wider">Active</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
