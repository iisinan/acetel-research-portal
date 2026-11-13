@extends('layouts.coordinator')

@section('header', 'Thesis Review')

@section('content')
<div class="space-y-8 pb-20">
    <!-- Audit Context Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <div class="p-1.5 rounded-lg bg-acetel-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor font-black"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Review</span>
            </div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Review Theses</h1>
            <p class="mt-2 text-sm font-medium text-slate-500 italic max-w-2xl">
                Restricted access to theses that have cleared Internal Defense.
            </p>
        </div>

        <div class="relative group">
            <form action="{{ route('admin.audit.index') }}" method="GET">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Filter by Title or Student..." 
                       class="pl-12 pr-6 py-4 bg-white rounded-2xl border border-slate-200 outline-none w-80 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-500 transition-all shadow-sm">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-acetel-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor font-black"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit List Matrix -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden divide-y divide-slate-50">
        @forelse($theses as $thesis)
            <div class="group/audit px-10 py-8 hover:bg-slate-50/50 transition-all duration-300">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                    <div class="max-w-3xl">
                        <div class="flex items-center gap-4 mb-3">
                            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 text-[8px] font-black uppercase tracking-widest border border-emerald-100 rounded-full">Defense Cleared</span>
                            <span class="text-[9px] font-bold text-slate-300 uppercase underline">{{ $thesis->student->program->code }}</span>
                        </div>
                        <h3 class="text-xl font-black text-slate-900 group-hover/audit:text-acetel-600 transition-colors uppercase leading-tight tracking-tight">
                            {{ $thesis->title }}
                        </h3>
                        <div class="flex flex-wrap items-center gap-6 mt-4">
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Student:</span>
                                @if($thesis->student?->user && auth()->user()?->hasRole('Admin') && $thesis->student->user_id !== auth()->id())
                                    <form action="{{ route('admin.users.impersonate', $thesis->student->user) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="role" value="Student">
                                        <button type="submit" class="text-[10px] font-bold text-slate-900 hover:text-emerald-600 underline decoration-dotted underline-offset-4 uppercase italic cursor-pointer" title="View Student Portal">
                                            {{ $thesis->student->user->name }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[10px] font-bold text-slate-900 uppercase italic">{{ $thesis->student->user->name }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Supervisor:</span>
                                @php $primarySupervisorUser = $thesis->assignments->first()?->supervisor?->user; @endphp
                                @if($primarySupervisorUser && auth()->user()?->hasRole('Admin') && $primarySupervisorUser->id !== auth()->id())
                                    <form action="{{ route('admin.users.impersonate', $primarySupervisorUser) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="role" value="Supervisor">
                                        <button type="submit" class="text-[10px] font-bold text-slate-600 hover:text-emerald-600 underline decoration-dotted underline-offset-4 uppercase italic cursor-pointer" title="View Supervisor Portal">
                                            {{ $primarySupervisorUser->name }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[10px] font-bold text-slate-500 uppercase italic">{{ $primarySupervisorUser->name ?? 'TBD' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-8 shrink-0">
                        <div class="text-right hidden sm:block">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Review Entry Date</p>
                            <p class="text-xs font-bold text-slate-900 italic uppercase">
                                {{ $thesis->milestones->first(fn($m) => optional($m->template)->order == 6)?->updated_at?->format('M d, Y') ?? 'N/A' }}
                            </p>
                        </div>
                        <a href="{{ route('admin.audit.show', $thesis) }}" class="px-8 py-4 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-acetel-600 transition-all active:scale-95 shadow-lg shadow-slate-900/10">
                            Review Thesis
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-32 text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300 mx-auto mb-6">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                </div>
                <h3 class="text-lg font-black text-slate-400 uppercase tracking-widest">No Submitted Theses</h3>
                <p class="text-xs font-medium text-slate-400 mt-2 italic px-10">There are currently no theses ready for review.</p>
            </div>
        @endforelse
    </div>
    
    <div class="mt-8">
        {{ $theses->links() }}
    </div>
</div>
@endsection
