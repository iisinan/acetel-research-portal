@extends('layouts.dashboard')

@section('header')
    Divisional Governance
@endsection

@section('content')
<div class="space-y-16 animate-in-up">
    {{-- Tactical Dashboard Orientation --}}
    <div class="relative overflow-hidden rounded-[4rem] bg-gradient-to-br from-slate-900 to-slate-950 p-16 lg:p-20 text-white shadow-3xl border border-white/5">
        <div class="absolute top-0 right-0 w-[50rem] h-[50rem] bg-emerald-600/10 blur-[130px] rounded-full -mr-40 -mt-40 animate-pulse-subtle"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-brand-600/10 blur-[100px] rounded-full -ml-40 -mb-40"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-16 text-center md:text-left">
            <div class="flex-1 space-y-12">
                <div class="inline-flex items-center gap-4 px-6 py-3 bg-white/5 rounded-2xl border border-white/10 text-emerald-500 text-[11px] font-black uppercase tracking-[0.4em] italic shadow-2xl">
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]"></span>
                    </span>
                    Divisional Oversight Matrix Active
                </div>
                <div>
                    <h1 class="text-7xl lg:text-9xl font-black text-white tracking-tighter leading-[0.8] mb-10 italic uppercase">
                        Center <br/><span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-brand-500 tracking-[-0.05em]">Governance Hub</span>
                    </h1>
                    <p class="text-2xl text-slate-400 font-medium leading-relaxed max-w-4xl mx-auto md:mx-0 opacity-80 italic italic">
                        Strategic coordination of divisional research nodes. Manage student allocations, authorize faculty panels, and synchronize institutional datasets.
                    </p>
                </div>
                <div class="flex flex-wrap justify-center md:justify-start gap-8 pt-8">
                    <a href="{{ route('admin.users.index') }}" class="px-14 py-7 bg-brand-600 rounded-3xl text-sm font-black uppercase tracking-[0.4em] shadow-3xl shadow-brand-900/40 border border-brand-500 hover:bg-brand-500 transition-all duration-300 flex items-center gap-5 text-white italic group">
                         Identity Portfolio
                         <svg class="w-6 h-6 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

            <div class="shrink-0 relative">
                 <div class="relative w-[28rem] h-[28rem] rounded-[5rem] bg-white/5 border-[1px] border-white/10 flex flex-col items-center justify-center text-center p-12 backdrop-blur-3xl rotate-3 shadow-3xl">
                    <p class="text-[11px] font-black text-emerald-500 uppercase tracking-[0.4em] mb-4 leading-none italic">Institutional Flux</p>
                    <p class="text-9xl font-black text-white tracking-tighter italic leading-none uppercase">{{ $stats['active_theses'] }}<span class="text-3xl text-emerald-500 mix-blend-overlay">_active</span></p>
                    <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.3em] mt-10 italic leading-none">Trajectories under Coordination</p>
                 </div>
         </div>
    </div>

    {{-- Alerts System for Program Coordinator --}}
    @if(isset($pending_reviews) && $pending_reviews->count() > 0)
    <div class="relative rounded-[3rem] bg-gradient-to-r from-amber-600 to-amber-700 shadow-2xl shadow-amber-900/30 font-sans" 
         x-data="coordinatorDashboard()">
        <div class="absolute inset-0 opacity-10 pointer-events-none rounded-[3rem]" style="background-image: url(\"data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='white' fill-opacity='1' fill-rule='evenodd'%3E%3Ccircle cx='3' cy='3' r='1'/%3E%3C/g%3E%3C/svg%3E\");"></div>
        
        <!-- Header / Toggle -->
        <div class="relative z-10 p-8 md:p-12 flex flex-col lg:flex-row items-center justify-between gap-8 cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 rounded-[2rem] bg-white/10 border border-white/20 flex items-center justify-center shrink-0 backdrop-blur-sm">
                    <span class="relative flex h-5 w-5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-40"></span>
                      <span class="relative inline-flex rounded-full h-5 w-5 bg-white shadow-[0_0_15px_rgba(255,255,255,0.8)]"></span>
                    </span>
                </div>
                <div>
                    <h3 class="text-3xl font-black text-white tracking-tighter uppercase italic leading-none">Attention Required</h3>
                    <p class="text-sm font-medium text-amber-200 mt-2 tracking-wide font-sans normal-case">{{ $pending_reviews->count() }} {{ Str::plural('milestone', $pending_reviews->count()) }} require your immediate review and approval.</p>
                </div>
            </div>
            <div class="flex-shrink-0 w-full lg:w-auto">
                 <button type="button" 
                    @click.stop="expanded = !expanded"
                    class="w-full lg:w-auto px-10 py-5 bg-white text-amber-700 hover:bg-amber-50 rounded-[2rem] font-black tracking-[0.2em] uppercase text-[10px] transition-all shadow-xl flex items-center justify-center gap-3 italic outline-none focus:outline-none">
                    <span x-text="expanded ? 'Hide Submissions' : 'Review Submissions'"></span>
                    <svg class="w-4 h-4 opacity-70 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </button>
            </div>
        </div>

        <!-- Expanded Students Container -->
        <div x-show="expanded" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="px-8 pb-8 md:px-12 md:pb-12 pt-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($pending_reviews as $review)
                    <a href="{{ route('coordinator.students.show', $review->thesis?->student_profile_id) }}#milestone-{{$review->id}}" class="block bg-amber-800/40 border border-amber-500/30 rounded-[2rem] p-6 hover:bg-amber-800/80 transition-all group backdrop-blur-md relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="flex items-start gap-4 relative z-10">
                            <div class="w-12 h-12 rounded-[1.2rem] bg-amber-500/20 text-amber-100 flex items-center justify-center font-black text-lg border border-amber-400/30 group-hover:scale-110 transition-transform shadow-inner shrink-0">
                                {{ substr($review->thesis?->student?->user?->name ?? '?', 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0 pt-1">
                                <h4 class="text-white font-black text-base uppercase tracking-tight truncate leading-none mb-2">{{ $review->thesis?->student?->user?->name ?? 'Student' }}</h4>
                                <div class="inline-block px-3 py-1.5 bg-amber-900/50 border border-amber-700/50 rounded-lg text-[10px] font-bold text-amber-200 mt-1 uppercase tracking-[0.1em] truncate max-w-full shadow-inner">{{ $review->template->name }}</div>
                            </div>
                        </div>
                        <div class="mt-6 pt-5 flex items-center justify-between border-t border-amber-500/20 relative z-10">
                            <span class="text-[9px] font-black text-amber-300/70 uppercase tracking-[0.2em] italic">{{ $review->submitted_at ? $review->submitted_at->diffForHumans() : 'Recently' }}</span>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('coordinator.students.show', $review->thesis?->student_profile_id) }}#milestone-{{$review->id}}" class="px-4 py-2 bg-white/10 hover:bg-white text-white hover:text-amber-700 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all border border-white/10 hover:border-white">
                                    Review
                                </a>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Metrics Matrix --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
        <x-stats-card title="Tactical Students" :value="$stats['total_students']" color="emerald" icon="user-group" subtitle="Verified Candidates" />
        <x-stats-card title="Scholarly Roster" :value="$stats['total_supervisors']" color="blue" icon="academic-cap" subtitle="Faculty Oversight Nodes" />
        <x-stats-card title="Audit Backlog" :value="$stats['pending_reviews']" color="amber" icon="clipboard-list" subtitle="Awaiting Clearance" />
        <x-stats-card title="Incoming Intel" :value="$stats['unread_messages']" color="rose" icon="chat-alt-2" subtitle="Unread Transmissions" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
        {{-- Surveillance Stream --}}
        <div class="lg:col-span-8 space-y-12">
            <div class="bg-white/5 rounded-[4rem] border border-white/5 p-12 lg:p-16 shadow-3xl relative overflow-hidden">
                <div class="flex items-center gap-8 mb-16">
                    <div class="w-1.5 h-12 bg-emerald-600 rounded-full shadow-lg shadow-emerald-900/50 transition-all"></div>
                    <h3 class="text-4xl font-black text-white uppercase tracking-tighter italic leading-none uppercase">Divisional <span class="opacity-50 tracking-[0.1em]">Trajectory Feed</span></h3>
                </div>

                {{-- Strategic Roster --}}
                <div class="space-y-8">
                    @forelse($recent_theses as $thesis)
                        <div class="p-12 rounded-[3.5rem] bg-white/5 border border-white/5 group hover:bg-white/10 hover:border-emerald-500/30 hover:shadow-3xl transition-all duration-500 relative overflow-hidden cursor-pointer">
                             <div class="flex flex-col md:flex-row md:items-center justify-between gap-12">
                                 <div class="flex items-center gap-10">
                                     <div class="w-24 h-24 rounded-[2.5rem] bg-slate-900 border border-white/5 flex items-center justify-center text-slate-500 font-black text-4xl shadow-3xl transition-all group-hover:scale-110 group-hover:rotate-12 group-hover:text-emerald-400 italic">
                                         {{ substr($thesis?->student?->user?->name ?? "Unknown", 0, 1) }}
                                     </div>
                                     <div class="min-w-0">
                                         <h4 class="text-3xl font-black text-white group-hover:text-emerald-400 transition-colors italic tracking-tight uppercase leading-none mb-4">{{ $thesis?->student?->user?->name ?? "Unknown" }}</h4>
                                         <div class="flex items-center gap-6">
                                            <span class="px-5 py-2 bg-slate-950 text-slate-600 border border-white/5 text-[9px] font-black uppercase tracking-[0.3em] rounded-xl italic">{{ $thesis?->student?->program?->code ?? "N/A" }}</span>
                                            <span class="text-xs text-slate-600 font-black uppercase tracking-[0.4em] italic opacity-60">Status Flux: {{ $thesis->status }}</span>
                                         </div>
                                         <p class="text-sm text-slate-700 mt-4 line-clamp-1 italic italic leading-none opacity-80 group-hover:opacity-100 transition-opacity">"{{ $thesis->title }}"</p>
                                     </div>
                                 </div>
                                 <div class="flex items-center gap-6">
                                     <a href="{{ route('theses.show', $thesis) }}" class="flex items-center justify-center w-20 h-20 bg-slate-950 border border-white/5 rounded-[2.5rem] text-slate-700 hover:bg-emerald-600 hover:text-white transition-all shadow-3xl group/btn transform hover:scale-110">
                                         <svg class="w-8 h-8 transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                     </a>
                                 </div>
                             </div>
                        </div>
                    @empty
                        <p class="text-center py-48 text-sm font-black text-slate-700 uppercase tracking-[0.5em] italic opacity-30">Archive Stream Silent</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Tactical Intelligence --}}
        <div class="lg:col-span-4 space-y-12">
            {{-- Allocation Matrix --}}
             <div class="bg-indigo-600/10 border border-indigo-500/20 rounded-[4rem] p-12 lg:p-14 shadow-3xl relative overflow-hidden group">
                 <div class="absolute -top-10 -right-10 w-72 h-72 bg-indigo-500/10 blur-3xl rounded-full transition-all group-hover:scale-150 duration-1000"></div>
                 <h3 class="text-2xl font-black text-white tracking-tighter mb-14 relative z-10 italic uppercase leading-none px-4">Cohort <span class="opacity-50 tracking-[0.1em]">Matrix</span></h3>
                 
                 <div class="space-y-10 relative z-10 px-4">
                      <div class="space-y-5">
                          <div class="flex justify-between items-center text-[11px] font-black uppercase tracking-[0.4em] italic mb-1">
                              <span class="text-indigo-400">Assignment Saturation</span>
                              <span class="text-white">88%</span>
                          </div>
                          <div class="h-2.5 w-full bg-white/5 rounded-full overflow-hidden p-0.5 shadow-inner">
                             <div class="h-full bg-indigo-500 rounded-full shadow-lg shadow-indigo-900/50 transition-all duration-1000" style="width: 88%"></div>
                          </div>
                      </div>
                      <div class="space-y-5">
                        <div class="flex justify-between items-center text-[11px] font-black uppercase tracking-[0.4em] italic mb-1">
                            <span class="text-emerald-400">Audit Completion Flux</span>
                            <span class="text-white">65%</span>
                        </div>
                        <div class="h-2.5 w-full bg-white/5 rounded-full overflow-hidden p-0.5 shadow-inner">
                           <div class="h-full bg-emerald-500 rounded-full shadow-lg shadow-emerald-900/50 transition-all duration-1000" style="width: 65%"></div>
                        </div>
                    </div>
                 </div>

                 <button class="w-full py-8 mt-20 bg-white/5 hover:bg-white text-slate-600 hover:text-slate-950 border border-white/5 rounded-[3rem] font-black text-[12px] uppercase tracking-[0.5em] transition-all shadow-3xl italic group/btn flex items-center justify-center gap-6 leading-none">
                    Sync Global Flux
                 </button>
            </div>

            {{-- Operational Node Control --}}
            <div class="bg-slate-950 rounded-[4rem] border border-white/5 p-12 lg:p-14 text-white shadow-3xl relative overflow-hidden group">
                 <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-brand-600/5 blur-3xl rounded-full transition-transform duration-1000 group-hover:scale-150"></div>
                 <h3 class="text-3xl font-black italic tracking-tighter leading-none mb-12 uppercase px-2">Divisional <br/><span class="opacity-50">Authorized</span> Controls</h3>
                 <div class="space-y-6 relative z-10">
                     <a href="{{ route('admin.users.index') }}" class="flex items-center gap-8 p-10 bg-white/5 border border-white/5 rounded-[3rem] hover:bg-brand-600 transition-all duration-500 group/btn shadow-xl shadow-black/20">
                         <div class="w-16 h-16 bg-slate-900 border border-white/5 flex items-center justify-center text-brand-500 rounded-[1.5rem] group-hover/btn:bg-white transition-all shadow-inner transform group-hover/btn:-rotate-6">
                            <svg class="w-8 h-8 font-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                         </div>
                         <div>
                            <p class="text-xl font-black text-white italic group-hover/btn:scale-110 transition-transform leading-none uppercase">Profile Roster</p>
                            <p class="text-[9px] font-black text-slate-700 uppercase tracking-[0.3em] font-medium italic mt-2 group-hover/btn:text-white/50">Manage Identity Sets</p>
                         </div>
                     </a>
                     <a href="{{ route('milestone-templates.index') }}" class="flex items-center gap-8 p-10 bg-white/5 border border-white/5 rounded-[3rem] hover:bg-emerald-600 transition-all duration-500 group/btn shadow-xl shadow-black/20">
                        <div class="w-16 h-16 bg-slate-900 border border-white/5 flex items-center justify-center text-emerald-500 rounded-[1.5rem] group-hover/btn:bg-white transition-all shadow-inner transform group-hover/btn:-rotate-6">
                           <svg class="w-8 h-8 font-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                        </div>
                        <div>
                           <p class="text-xl font-black text-white italic group-hover/btn:scale-110 transition-transform leading-none uppercase">Benchmark Core</p>
                           <p class="text-[9px] font-black text-slate-700 uppercase tracking-[0.3em] font-medium italic mt-2 group-hover/btn:text-white/50">Tactical Objective Presets</p>
                        </div>
                    </a>
                 </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('coordinatorDashboard', () => ({
        expanded: false
    }));
});
</script>
@endpush
@endsection
