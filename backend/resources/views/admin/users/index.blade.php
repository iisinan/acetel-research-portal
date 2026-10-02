@extends('layouts.admin')

@section('header')
    Identity Registry
@endsection

@section('content')
<style>
    .brand-card {
        background: white;
        border: 1px solid #e2e8f0;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .brand-card:hover {
        border-color: #86efac;
        box-shadow: 0 15px 45px rgba(22, 163, 74, 0.08);
        transform: translateY(-4px);
    }
    .btn-create {
        background: linear-gradient(135deg, #16a34a, #15803d);
        color: white;
        box-shadow: 0 4px 16px rgba(22, 163, 74, 0.3);
    }
    .btn-create:hover {
        box-shadow: 0 8px 32px rgba(22, 163, 74, 0.45);
        transform: translateY(-1px);
    }
</style>

<div class="space-y-10 animate-in-up pb-24">
    <!-- Premium Global Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="brand-card rounded-[2.5rem] p-8 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-green-500/5 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-700"></div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Total Accounts</p>
            <div class="flex items-end justify-between">
                <h3 class="text-4xl font-black text-slate-900 tracking-tighter leading-none">{{ \App\Models\User::count() }}</h3>
                <div class="w-12 h-12 rounded-2xl bg-green-50 flex items-center justify-center text-green-600 shadow-sm">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
            </div>
            <p class="mt-4 text-[10px] font-bold text-green-600 uppercase tracking-widest flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                System Wide Registry
            </p>
        </div>

        <div class="brand-card rounded-[2.5rem] p-8 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-700"></div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Staff Members</p>
            <div class="flex items-end justify-between">
                <h3 class="text-4xl font-black text-slate-900 tracking-tighter leading-none">{{ \App\Models\User::role(['Supervisor', 'Program Coordinator'])->count() }}</h3>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
            </div>
            <p class="mt-4 text-[10px] font-bold text-blue-500 uppercase tracking-widest flex items-center gap-2">Academic & Admin</p>
        </div>

        <div class="brand-card rounded-[2.5rem] p-8 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-700"></div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Active Students</p>
            <div class="flex items-end justify-between">
                <h3 class="text-4xl font-black text-slate-900 tracking-tighter leading-none">{{ \App\Models\User::role('Student')->count() }}</h3>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477-4.5 1.253v13C19.832 18.247 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                </div>
            </div>
            <p class="mt-4 text-[10px] font-bold text-emerald-500 uppercase tracking-widest flex items-center gap-2">Scholarly Community</p>
        </div>

        <div class="bg-slate-50 rounded-[2.5rem] p-6 border border-slate-200 flex flex-col justify-center space-y-3">
            <a href="{{ route('admin.users.create') }}" class="btn-create py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Register New User
            </a>
            <a href="{{ route('admin.users.import-form') }}" class="py-4 bg-white border border-green-200 text-green-700 rounded-2xl text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-green-50 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                Bulk Import
            </a>
        </div>
    </div>

    <!-- Interface Dashboard -->
    <div class="bg-white rounded-[3rem] border border-slate-100 shadow-sm overflow-hidden">
        <!-- Dashboard Filters -->
        <div class="p-8 lg:p-12 border-b border-slate-50 bg-slate-50/20">
            <form action="{{ route('admin.users.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                <div class="md:col-span-4 space-y-3">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Search Identity</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-green-600 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email address..." class="block w-full pl-11 pr-4 py-4 bg-white border-slate-200 rounded-2xl text-sm font-bold text-slate-900 placeholder-slate-400 focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all shadow-sm">
                    </div>
                </div>

                <div class="md:col-span-3 space-y-3">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Institutional Role</label>
                    <select name="role" class="block w-full px-5 py-4 bg-white border-slate-200 rounded-2xl text-sm font-bold text-slate-900 shadow-sm focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all cursor-pointer">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>{{ Str::title($role) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3 space-y-3">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Account Status</label>
                    <select name="status" class="block w-full px-5 py-4 bg-white border-slate-200 rounded-2xl text-sm font-bold text-slate-900 shadow-sm focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition-all cursor-pointer">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Accounts</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive Accounts</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 py-4 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-green-600 transition-all shadow-lg shadow-slate-900/10">
                        Apply
                    </button>
                    @if(request()->anyFilled(['search', 'role', 'status']))
                        <a href="{{ route('admin.users.index') }}" class="p-4 bg-slate-100 text-slate-400 rounded-2xl hover:bg-rose-50 hover:text-rose-600 transition-all shadow-sm border border-transparent hover:border-rose-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/30">
                        <th class="px-10 py-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-50">Identity Profile</th>
                        <th class="px-8 py-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-50">Roles</th>
                        <th class="px-8 py-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-50 text-center">Security Status</th>
                        <th class="px-10 py-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-50 text-right">Governance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($users as $user)
                    <tr class="hover:bg-green-50/10 transition-all duration-300 group">
                        <td class="px-10 py-8">
                            <div class="flex items-center gap-6">
                                <div class="relative shrink-0">
                                    <div class="w-14 h-14 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-xl font-black text-slate-900 shadow-sm group-hover:scale-105 group-hover:border-green-300 transition-all duration-500">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div class="absolute -right-1 -bottom-1 w-5 h-5 rounded-full border-4 border-white shadow-sm {{ $user->is_active ? 'bg-green-500' : 'bg-rose-500' }}"></div>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-base font-black text-slate-900 tracking-tight leading-none mb-2 group-hover:text-green-700 transition-colors">{{ $user->name }}</p>
                                    <div class="flex items-center gap-2">
                                        <p class="text-[11px] font-bold text-slate-400 truncate">{{ $user->email }}</p>
                                        @if($user->creator_id)
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest whitespace-nowrap">Manual</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-8">
                            <div class="flex gap-2 flex-wrap">
                                @forelse($user->roles as $role)
                                    @php
                                        $isStaff = in_array($role->name, ['Admin', 'Director', 'Program Coordinator']);
                                    @endphp
                                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest border transition-all {{ $isStaff ? 'bg-slate-900 text-white border-slate-900' : 'bg-green-50 text-green-700 border-green-100' }}">
                                        {{ $role->name }}
                                        @if($role->name === 'Supervisor' && $user->supervisorProfile)
                                            <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1.5 rounded-md bg-green-200 text-green-800 text-[10px]" title="{{ $user->supervisorProfile->assignments_count }} Active Students Supervising">
                                                {{ $user->supervisorProfile->assignments_count }}
                                                <svg class="w-2.5 h-2.5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                            </span>
                                        @endif
                                    </span>
                                @empty
                                    <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest italic">Not Specified</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-8 py-8 text-center">
                            @if($user->is_active)
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-green-50 text-green-700 text-[9px] font-black tracking-widest border border-green-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                    ACTIVE
                                </span>
                            @else
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-50 text-rose-600 text-[9px] font-black tracking-widest border border-rose-100">
                                    OFFLINE
                                </span>
                            @endif
                        </td>
                        <td class="px-10 py-8 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 translate-x-4 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-500">
                                <form action="{{ route('admin.users.reset_password', $user) }}" method="POST" class="contents" onsubmit="return confirm('Securely reset password for this user?');">
                                    @csrf
                                    <button type="submit" class="p-3 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-green-600 hover:border-green-300 hover:bg-green-50 transition-all" title="Reset Secret">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                                    </button>
                                </form>
                                
                                <a href="{{ route('admin.users.edit', $user) }}" class="p-3 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 transition-all" title="Edit Profile">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>

                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="contents">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-3 {{ $user->is_active ? 'bg-amber-50 text-amber-500 border-amber-200 hover:bg-amber-100' : 'bg-green-50 text-green-600 border-green-200 hover:bg-green-100' }} border rounded-xl transition-all shadow-sm">
                                        @if($user->is_active)
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-10 py-32 text-center">
                            <div class="flex flex-col items-center justify-center gap-6">
                                <div class="w-20 h-20 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-200 shadow-inner">
                                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight leading-none uppercase italic">No Matches Found</h3>
                                    <p class="text-sm font-medium text-slate-400 mt-2 max-w-sm mx-auto italic">Institutional query returned zero records. Adjust parameters or add a new profile.</p>
                                </div>
                                <a href="{{ route('admin.users.index') }}" class="px-10 py-4 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-green-600 transition-all shadow-xl">
                                    Reset Query
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-10 py-10 bg-slate-50/20 border-t border-slate-50">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
