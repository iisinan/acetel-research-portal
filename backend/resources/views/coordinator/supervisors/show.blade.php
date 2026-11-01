@extends('layouts.coordinator')

@section('header', 'Supervisor Details')

@section('content')
<div class="space-y-6 sm:space-y-10 pb-10">
    <!-- Sophisticated Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 sm:gap-6">
        <div class="flex items-center gap-3 sm:gap-6 min-w-0">
            <a href="{{ route('coordinator.supervisors.index') }}" class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-400 hover:border-acetel-500 hover:text-acetel-500 transition-all hover:-translate-x-1 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
            </a>
            <div class="min-w-0">
                <div class="flex items-center gap-2 sm:gap-3 mb-1 sm:mb-2 text-acetel-600">
                    <div class="p-1 rounded-md sm:p-1.5 sm:rounded-lg bg-acetel-50">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] sm:tracking-[0.3em]">Profile</span>
                </div>
                <h1 class="text-xl sm:text-2xl md:text-4xl font-black text-slate-900 tracking-tight truncate">{{ $supervisor->user->name }}</h1>
                <p class="mt-1 sm:mt-2 text-xs sm:text-sm font-medium text-slate-500">Professional profile and current supervision status.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-3 sm:px-5 py-2 sm:py-2.5 rounded-xl sm:rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-2 sm:gap-3">
                <span class="text-[11px] sm:text-xs font-bold text-slate-600">Staff ID: {{ $supervisor->staff_id }}</span>
            </div>
            <form action="{{ route('coordinator.supervisors.reset-password', $supervisor) }}" method="POST"
                data-confirm="Are you sure you want to reset the password for {{ $supervisor->user->name }}? Credentials will be sent via email."
                data-confirm-title="Reset Supervisor Password"
                data-confirm-type="warning"
                data-confirm-btn="Reset Password">
                @csrf
                <button type="submit" class="p-2 sm:p-2.5 text-slate-400 hover:text-rose-500 bg-white border border-slate-200 rounded-xl transition-all shadow-sm group" title="Reset Password">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 group-hover:rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-10">
        <!-- Main Intel Matrix (Spans 8) -->
        <div class="lg:col-span-8 space-y-6 sm:space-y-10">
            <!-- Expert Matrix Card -->
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 p-5 sm:p-8 md:p-10 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-64 h-64 bg-acetel-50 rounded-full blur-[80px] -mr-32 -mt-32 opacity-50 group-hover:opacity-100 transition-opacity duration-1000"></div>
                
                <h3 class="text-lg sm:text-xl font-black text-slate-900 mb-6 sm:mb-8 tracking-tight relative z-10 flex items-center gap-3">
                    Expertise
                    <span class="text-[9px] sm:text-[10px] font-bold text-acetel-500 bg-acetel-50 px-2 py-0.5 rounded-full uppercase tracking-tighter shadow-sm border border-acetel-100/50">Verified Expert</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-10 relative z-10">
                    <div class="space-y-4 sm:space-y-6">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.673.337a4 4 0 01-2.586.343l-1.1-.328a2 2 0 00-1.011.077l-2.335.778A3.5 3.5 0 012 13.147V7a2 2 0 012-2h15a2 2 0 012 2v6.5a3.5 3.5 0 01-1.572 2.928z" /></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Specialization</p>
                                <p class="text-sm font-bold text-slate-900 mt-0.5 truncate">{{ $supervisor->specialization ?? 'General Research' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email Address</p>
                                <p class="text-sm font-bold text-slate-900 mt-0.5 truncate">{{ $supervisor->user->email }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4 sm:space-y-6">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 sm:mb-3">Current Student Load</p>
                            @php 
                                $count = $supervisor->assignments->where('status', 'active')->count();
                                $percent = $supervisor->max_students > 0 ? ($count / $supervisor->max_students) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-4">
                                <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ $count }} <span class="text-slate-300">/ {{ $supervisor->max_students }}</span></span>
                                <div class="flex-1 h-3 bg-slate-100 rounded-full overflow-hidden shadow-inner ring-1 ring-slate-100">
                                    <div class="h-full @if($percent > 85) bg-rose-500 @elseif($percent > 60) bg-amber-500 @else bg-emerald-500 @endif transition-all duration-1000 shadow-[0_0_10px_rgba(16,185,129,0.3)]" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-2 sm:mt-3">Maximum Student Capacity</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Portfolio Matrix -->
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden group">
                <div class="px-5 sm:px-10 py-5 sm:py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
                    <div>
                        <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">Assigned Students</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Supervision Details</p>
                    </div>
                </div>

                {{-- Mobile Card List --}}
                <div class="block sm:hidden divide-y divide-slate-100">
                    @forelse($supervisor->assignments as $assignment)
                        <div class="p-5 space-y-4 hover:bg-slate-50/50 transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 font-black text-sm shrink-0">
                                        {{ substr($assignment->thesis?->student?->user?->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-900 truncate">{{ $assignment->thesis?->student?->user?->name ?? 'Unknown' }}</p>
                                        <p class="text-[10px] font-medium text-slate-400 truncate">{{ $assignment->thesis?->title ?? 'Thesis' }}</p>
                                    </div>
                                </div>
                                @if($assignment->thesis?->currentMilestone?->template)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 text-[9px] font-black uppercase tracking-wider border border-amber-100 shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        {{ $assignment->thesis->currentMilestone->template->name }}
                                    </span>
                                @endif
                            </div>

                            <div class="bg-slate-50 rounded-xl p-3 flex items-center justify-between">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Supervision Role:</span>
                                <form action="{{ route('coordinator.supervisors.update-assignment', [$supervisor, $assignment->thesis]) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" onchange="this.form.submit()" class="bg-white border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-wider text-slate-700 py-1 pl-2.5 pr-7 appearance-none cursor-pointer focus:ring-2 focus:ring-acetel-500 shadow-sm">
                                        <option value="main" @if($assignment->role == 'main') selected @endif>Main</option>
                                        <option value="co-supervisor" @if($assignment->role == 'co-supervisor') selected @endif>Co-Supervisor</option>
                                    </select>
                                </form>
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <a href="{{ route('coordinator.students.show', $assignment->thesis->student) }}" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-slate-900 hover:bg-acetel-600 text-white text-[10px] font-black uppercase tracking-widest transition-all shadow-md shadow-slate-900/10">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    View Student
                                </a>
                                <form action="{{ route('coordinator.supervisors.unassign-student', [$supervisor, $assignment->thesis]) }}" method="POST"
                                    data-confirm="Are you sure you want to remove this student from {{ $supervisor->user->name }}'s supervision?"
                                    data-confirm-title="Unassign Student"
                                    data-confirm-type="danger"
                                    data-confirm-btn="Unassign Student">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-10 h-10 rounded-xl bg-white border border-rose-200 text-rose-500 flex items-center justify-center hover:bg-rose-50 shadow-sm transition-all" title="Unassign Student">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center opacity-40">
                            <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em]">No Students Assigned</p>
                        </div>
                    @endforelse
                </div>

                {{-- Desktop Table View --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-6 lg:px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Student</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Milestone</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Role</th>
                                <th class="px-6 lg:px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($supervisor->assignments as $assignment)
                                <tr class="hover:bg-slate-50/30 transition-colors group/row">
                                    <td class="px-6 lg:px-10 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover/row:bg-acetel-50 group-hover/row:text-acetel-500 transition-all font-black text-sm shadow-sm ring-1 ring-slate-100 shrink-0">
                                                {{ substr($assignment->thesis?->student?->user?->name ?? 'Unknown', 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-slate-900 group-hover/row:text-acetel-600 transition-colors truncate">{{ $assignment->thesis?->student?->user?->name ?? 'Unknown' }}</p>
                                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5 truncate max-w-[200px]" title="{{ $assignment->thesis?->title }}">{{ Str::limit($assignment->thesis?->title, 40) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        @if($assignment->thesis?->currentMilestone?->template)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-widest border border-amber-100/50 shadow-sm">
                                                <div class="w-1 h-1 rounded-full bg-amber-500 animate-pulse"></div>
                                                {{ $assignment->thesis->currentMilestone->template->name }}
                                            </span>
                                        @else
                                            <span class="text-slate-300 text-[10px] font-black uppercase tracking-widest">--</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-6 text-center">
                                        <form action="{{ route('coordinator.supervisors.update-assignment', [$supervisor, $assignment->thesis]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="relative inline-block">
                                                <select name="role" onchange="this.form.submit()" class="bg-acetel-50 border border-acetel-100/50 rounded-xl text-[9px] font-black uppercase tracking-widest text-acetel-600 focus:ring-2 focus:ring-acetel-500 py-1.5 pl-3 pr-8 appearance-none cursor-pointer hover:bg-acetel-100 transition-colors">
                                                    <option value="main" @if($assignment->role == 'main') selected @endif>Main</option>
                                                    <option value="co-supervisor" @if($assignment->role == 'co-supervisor') selected @endif>Co-Supervisor</option>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-acetel-400">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="px-6 lg:px-10 py-6 text-right">
                                        <div class="flex justify-end items-center gap-2">
                                            <a href="{{ route('coordinator.students.show', $assignment->thesis->student) }}" class="inline-flex items-center justify-center p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:border-acetel-500 hover:text-acetel-500 hover:shadow-lg transition-all translate-y-0 hover:-translate-y-1" title="View Student">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </a>
                                            <form action="{{ route('coordinator.supervisors.unassign-student', [$supervisor, $assignment->thesis]) }}" method="POST"
                                                data-confirm="Are you sure you want to remove this student from {{ $supervisor->user->name }}'s supervision?"
                                                data-confirm-title="Unassign Student"
                                                data-confirm-type="danger"
                                                data-confirm-btn="Unassign Student">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center p-2 rounded-xl bg-slate-50 border border-slate-100 text-slate-400 hover:border-rose-500 hover:text-rose-500 transition-all translate-y-0 hover:-translate-y-1" title="Unassign Student">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-10 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center opacity-30">
                                            <svg class="w-12 h-12 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                            <p class="text-[11px] font-black uppercase tracking-[0.3em]">No Students</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Actions (Spans 4) -->
        <div class="lg:col-span-4 space-y-6 sm:space-y-10">
            <!-- Assignment Hub Card -->
            <div class="bg-acetel-900 rounded-2xl sm:rounded-[2.5rem] border border-acetel-800 shadow-2xl p-5 sm:p-8 md:p-10 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 bg-acetel-500/10 rounded-full blur-[40px] -mr-16 -mt-16 group-hover:bg-acetel-500/20 transition-all duration-1000"></div>
                
                <h3 class="text-lg sm:text-xl font-black text-white mb-6 sm:mb-8 tracking-tight relative z-10">Assign Student</h3>
                
                <form action="{{ route('coordinator.supervisors.assign-student', $supervisor) }}" method="POST" class="space-y-5 sm:space-y-6 relative z-10">
                    @csrf
                    <div>
                        <label class="text-[9px] font-black text-acetel-400 uppercase tracking-[0.2em] block mb-2 sm:mb-3 pl-1">Select Student</label>
                        <div class="relative">
                            <select name="student_id" class="w-full bg-white/5 border border-white/10 rounded-xl sm:rounded-2xl text-xs font-bold text-white focus:ring-2 focus:ring-acetel-400 py-3.5 sm:py-4 pl-3.5 sm:pl-4 pr-10 appearance-none backdrop-blur-md">
                                <option value="" class="bg-slate-900">-- Select Student --</option>
                                @foreach($availableStudents as $student)
                                    @if(!$supervisor->assignments->contains('thesis_project_id', $student->thesis->id ?? null) && $student->thesis)
                                        <option value="{{ $student->id }}" class="bg-slate-900">{{ $student->user->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-white/30">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="text-[9px] font-black text-acetel-400 uppercase tracking-[0.2em] block mb-2 sm:mb-3 pl-1">Assign Role</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative block cursor-pointer group/radio">
                                <input type="radio" name="type" value="main" class="peer sr-only" checked>
                                <div class="p-3 sm:p-4 bg-white/5 border border-white/10 rounded-xl sm:rounded-2xl text-center group-hover/radio:border-white/30 peer-checked:bg-white peer-checked:border-white transition-all">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-acetel-400 peer-checked:text-acetel-900">Role</p>
                                    <p class="text-[10px] font-black text-white mt-1 peer-checked:text-acetel-900">Main</p>
                                </div>
                            </label>
                            <label class="relative block cursor-pointer group/radio">
                                <input type="radio" name="type" value="co-supervisor" class="peer sr-only">
                                <div class="p-3 sm:p-4 bg-white/5 border border-white/10 rounded-xl sm:rounded-2xl text-center group-hover/radio:border-white/30 peer-checked:bg-white peer-checked:border-white transition-all">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-acetel-400 peer-checked:text-acetel-900">Role</p>
                                    <p class="text-[10px] font-black text-white mt-1 peer-checked:text-acetel-900">Co-Sup.</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-4 sm:py-5 bg-white text-acetel-900 text-[10px] font-black uppercase tracking-[0.2em] rounded-xl sm:rounded-2xl shadow-xl shadow-black/20 hover:scale-[1.02] active:scale-95 transition-all mt-4">
                        Assign Student
                    </button>
                </form>
                
                <p class="text-[9px] font-medium text-white/30 leading-relaxed italic text-center mt-6 sm:mt-10 relative z-10">All assignments are tracked in the system audit logs.</p>
            </div>

            <!-- Expertise Verification Card -->
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 p-5 sm:p-8 md:p-10 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-acetel-50 rounded-full blur-[30px] -mr-12 -mt-12 group-hover:bg-acetel-100 transition-all duration-1000"></div>
                <h3 class="text-base sm:text-lg font-black text-slate-900 mb-4 sm:mb-6 tracking-tight relative z-10">Verification</h3>
                <div class="space-y-4 relative z-10">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Active</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-lg bg-acetel-50 text-acetel-500 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        </div>
                        <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">PhD Supervision</span>
                    </div>
                </div>
            </div>

            <!-- Examiner Upgrade Hub -->
            <div class="bg-blue-900 rounded-2xl sm:rounded-[2.5rem] border border-blue-800 shadow-2xl p-5 sm:p-8 md:p-10 relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-32 h-32 bg-blue-500/10 rounded-full blur-[40px] -ml-16 -mt-16 group-hover:bg-blue-500/20 transition-all duration-1000"></div>
                <h3 class="text-lg sm:text-xl font-black text-white mb-3 sm:mb-4 tracking-tight relative z-10 flex items-center gap-3">
                    Upgrade Expert Role
                    <span class="text-[9px] font-bold text-blue-400 border border-blue-500/50 px-2 py-0.5 rounded-full uppercase tracking-tighter">Dual Capacity</span>
                </h3>
                <p class="text-[10px] font-medium text-white/60 mb-5 sm:mb-6 leading-relaxed relative z-10">Allow this supervisor to function simultaneously as an examiner for other candidates.</p>

                <div class="space-y-4 relative z-10" x-data="{ upgradeType: '' }">
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <label class="relative block cursor-pointer group/radio">
                            <input type="radio" x-model="upgradeType" name="upgrade_type" value="internal" class="peer sr-only">
                            <div class="p-3 bg-white/5 border border-white/10 rounded-xl sm:rounded-2xl text-center group-hover/radio:border-white/30 peer-checked:bg-white peer-checked:border-white transition-all shadow-sm">
                                <p class="text-[10px] font-black uppercase tracking-widest text-white mt-1 peer-checked:text-blue-900">Internal</p>
                            </div>
                        </label>
                        <label class="relative block cursor-pointer group/radio">
                            <input type="radio" x-model="upgradeType" name="upgrade_type" value="external" class="peer sr-only">
                            <div class="p-3 bg-white/5 border border-white/10 rounded-xl sm:rounded-2xl text-center group-hover/radio:border-white/30 peer-checked:bg-white peer-checked:border-white transition-all shadow-sm">
                                <p class="text-[10px] font-black uppercase tracking-widest text-white mt-1 peer-checked:text-blue-900">External</p>
                            </div>
                        </label>
                    </div>

                    <!-- Internal Form -->
                    <form x-show="upgradeType === 'internal'" action="{{ route('coordinator.examiners.storeInternal') }}" method="POST" class="animate-in-up">
                        @csrf
                        <input type="hidden" name="supervisor_id" value="{{ $supervisor->id }}">
                        <button type="submit" class="w-full py-3.5 sm:py-4 bg-white text-blue-900 text-[10px] font-black uppercase tracking-[0.2em] rounded-xl sm:rounded-2xl shadow-xl shadow-black/20 hover:scale-[1.02] active:scale-95 transition-all">
                            Enable Internal Examiner Profile
                        </button>
                    </form>

                    <!-- External Form -->
                    <form x-show="upgradeType === 'external'" action="{{ route('coordinator.examiners.storeExternalFromSupervisor') }}" method="POST" class="animate-in-up space-y-4">
                        @csrf
                        <input type="hidden" name="supervisor_id" value="{{ $supervisor->id }}">
                        <div>
                            <input type="text" name="institution" class="w-full bg-white/5 border border-white/10 rounded-xl sm:rounded-2xl text-xs font-bold text-white placeholder-white/30 focus:ring-2 focus:ring-blue-400 px-4 py-3.5 sm:py-4 backdrop-blur-md" placeholder="Enter External Institution" required>
                        </div>
                        <button type="submit" class="w-full py-3.5 sm:py-4 bg-white text-blue-900 text-[10px] font-black uppercase tracking-[0.2em] rounded-xl sm:rounded-2xl shadow-xl shadow-black/20 hover:scale-[1.02] active:scale-95 transition-all">
                            Enable External Examiner Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
