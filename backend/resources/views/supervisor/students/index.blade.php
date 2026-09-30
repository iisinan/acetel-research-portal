@extends('layouts.dashboard')

@section('header')
    Supervised Candidates
@endsection

@section('content')
<div class="space-y-8 animate-in-up">
    <!-- Header Summary Card -->
    <div class="relative overflow-hidden rounded-[2.5rem] p-6 md:p-10 bg-grad-premium border border-white/20 shadow-premium group">
        <div class="absolute top-0 right-0 -mt-24 -mr-24 w-80 h-80 bg-white/10 blur-[100px] rounded-full group-hover:bg-white/15 transition-all duration-1000"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
            <div>
                <h2 class="text-3xl font-black text-white tracking-tighter mb-2 leading-tight">Assigned Students</h2>
                <p class="text-black/80 font-medium text-sm max-w-lg">Manage your assigned students and monitor their progress.</p>
            </div>
            <div class="flex gap-4">
                <div class="px-6 py-4 glass text-center border-white/10 min-w-[140px]">
                    <p class="text-[10px] font-black text-black uppercase tracking-widest mb-1">Active Students</p>
                    <p class="text-3xl font-black text-white leading-tight">{{ $assignments->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter/Search Area -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 glass p-4 rounded-3xl border border-white/10">
        <form method="GET" action="{{ route('supervisor.students.index') }}" class="relative w-full sm:w-96">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, program, matric no, or thesis title..." class="w-full bg-white border border-slate-200 rounded-2xl pl-12 pr-4 py-3 placeholder-slate-400 text-sm focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-slate-900 font-medium">
            <button type="submit" class="hidden"></button>
        </form>
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-4">Portfolio</span>
        </div>
    </div>

    <!-- Candidates Grid/List -->
    <div class="grid grid-cols-1 gap-6">
        @forelse($assignments as $assignment)
            <div class="group relative glass p-5 md:p-8 rounded-[2.5rem] border border-slate-100 hover:border-primary-200 hover:shadow-premium transition-all duration-500 hover:-translate-y-1 bg-white">
                <div class="flex flex-col lg:flex-row items-start lg:items-center gap-5 md:gap-8">
                    <!-- Identity Section -->
                    <div class="flex items-center gap-4 md:gap-8 flex-1 w-full min-w-0">
                        <div class="relative shrink-0">
                            <div class="w-24 h-24 rounded-3xl bg-green-50 border border-green-100 flex items-center justify-center text-green-600 text-4xl font-black shadow-inner group-hover:bg-green-600 group-hover:text-white group-hover:rotate-3 transition-all duration-500 overflow-hidden">
                                {{ substr($assignment->thesis->student->user->name, 0, 1) }}
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-10 h-10 rounded-2xl bg-white border border-slate-100 flex items-center justify-center shadow-lg transform group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477-4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-4 mb-3">
                                <h3 class="text-lg md:text-2xl font-black text-slate-900 tracking-tighter">{{ $assignment->thesis->student->user->name }}</h3>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 bg-green-50 text-[10px] font-black text-green-600 rounded-full border border-green-100 uppercase tracking-widest">{{ $assignment->thesis->student->program->code ?? 'N/A' }}</span>
                                    <span class="px-3 py-1 bg-slate-50 text-[10px] font-black text-slate-500 rounded-full border border-slate-100 uppercase tracking-widest">{{ $assignment->role }}</span>
                                </div>
                            </div>
                            <p class="text-slate-600 font-medium text-base mb-4 line-clamp-1 italic">"{{ $assignment->thesis->title }}"</p>
                            
                            <div class="flex flex-wrap items-center gap-6">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                                    <span class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em]">Phase: {{ $assignment->thesis->currentMilestone->template->name ?? 'Completed/Archived' }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="text-[10px] font-black uppercase tracking-widest">{{ $assignment->thesis->student->student_id_number ?? 'Matric Pending' }}</span>
                                </div>
                                @if($assignment->thesis->milestones->count() > 0)
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                    <span class="text-[9px] font-black text-rose-600 uppercase tracking-widest">{{ $assignment->thesis->milestones->count() }} Pending Reviews</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-4 w-full lg:w-auto lg:pl-10 lg:border-l border-slate-100/50">
                        <div class="flex flex-col items-center px-6">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Progress</p>
                            <p class="text-2xl font-black text-green-600 tracking-tighter">{{ $assignment->thesis->progress_percentage }}%</p>
                        </div>
                        <a href="{{ route('theses.show', $assignment->thesis) }}" class="flex-1 lg:flex-none flex items-center justify-center gap-3 px-8 py-5 bg-white border-2 border-slate-100 rounded-[1.5rem] text-slate-900 hover:bg-green-600 hover:text-white hover:border-transparent transition-all duration-500 shadow-sm group/btn group-hover:shadow-green-500/10">
                            <span class="text-xs font-black uppercase tracking-[0.2em]">View Profile</span>
                            <svg class="w-5 h-5 transition-transform duration-300 group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-24 bg-slate-50 border-2 border-dashed border-slate-200 rounded-[2.5rem]">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-slate-900 mb-2">Portfolio Empty</h3>
                <p class="text-slate-500 font-medium">No results matched your search.</p>
            </div>
        @endforelse
    </div>
    
    <div class="mt-6">
        {{ $assignments->links() }}
    </div>
</div>
@endsection
