@extends('layouts.coordinator')

@section('header', 'Cohort Tracker')

@section('content')
<div class="space-y-10 pb-10" x-data="cohortTracker()" x-init="startPolling">
    <!-- Sophisticated Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <div class="p-1.5 rounded-lg bg-acetel-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Cohort Management</span>
            </div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Cohort Overview</h1>
            <p class="mt-2 text-sm font-medium text-slate-500">Overview of academic intake progress and student milestones.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-6 py-3 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-xs font-black text-slate-900 tracking-tighter">{{ $cohorts->count() }} <span class="text-slate-400">Active Cohorts</span></span>
            </div>
        </div>
    </div>

    <!-- Cohort Intelligence Grid -->
    <div id="cohort-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8 relative">
        <div x-show="loading" class="absolute top-0 right-0 -mt-8 text-acetel-500 flex items-center justify-center p-2">
             <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        </div>
        @forelse($cohorts as $cohort)
            <a href="{{ route('coordinator.cohorts.show', $cohort) }}" class="group block relative">
                <div class="bg-white rounded-[2.5rem] border border-slate-100 p-8 h-full flex flex-col justify-between transition-all duration-500 hover:-translate-y-2 shadow-xl shadow-slate-200/40 hover:shadow-acetel-500/10 hover:border-acetel-200 overflow-hidden">
                    <!-- Glassy Background Accent -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-slate-50 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-acetel-50 transition-colors duration-500"></div>

                    <div class="relative z-10">
                        <!-- Card Header -->
                        <div class="flex justify-between items-start mb-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-white shadow-lg shadow-slate-900/10 group-hover:scale-110 group-hover:rotate-6 transition-all duration-500">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 leading-none group-hover:text-acetel-600 transition-colors">{{ $cohort->name }}</h3>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-2">Intake {{ $cohort->intake_year }} <span class="mx-1 text-slate-200">|</span> {{ $cohort->code }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Section -->
                        <div class="space-y-8">
                            <div>
                                <div class="flex justify-between items-end mb-3">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Average Progress</span>
                                    <span class="text-2xl font-black text-slate-900 tracking-tighter">{{ $cohort->average_progress }}%</span>
                                </div>
                                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden shadow-inner ring-1 ring-slate-100">
                                    <div class="h-full bg-slate-900 group-hover:bg-acetel-500 transition-all duration-1000" style="width: {{ $cohort->average_progress }}%"></div>
                                </div>
                            </div>

                            <!-- Milestone Matrix -->
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 block mb-4">Milestone Completion</p>
                                <div class="flex gap-1.5">
                                    @foreach($cohort->progress_distribution as $order => $data)
                                        <div class="flex-1 flex flex-col items-center gap-2 relative group/tooltip">
                                            <div class="w-full h-2 rounded-full transition-all duration-500 {{ $data['percentage'] >= 100 ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.3)]' : ($data['percentage'] > 0 ? 'bg-amber-400 shadow-[0_0_8px_rgba(251,191,36,0.2)]' : 'bg-slate-100 ring-1 ring-slate-100') }}"></div>
                                            <span class="text-[8px] font-black text-slate-400 group-hover/tooltip:text-slate-900 transition-colors uppercase">M{{ $order }}</span>
                                            
                                            <!-- High-Fidelity Tooltip -->
                                            <div class="absolute bottom-full mb-3 left-1/2 -translate-x-1/2 px-3 py-2 bg-slate-900 text-white rounded-xl opacity-0 invisible group-hover/tooltip:opacity-100 group-hover/tooltip:visible transition-all duration-300 whitespace-nowrap z-20 pointer-events-none shadow-xl border border-white/10 translate-y-2 group-hover/tooltip:translate-y-0 text-center">
                                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ $data['name'] }}</p>
                                                <p class="text-xs font-black">{{ $data['percentage'] }}% Completion</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Intelligence -->
                    <div class="mt-10 pt-6 border-t border-slate-50 flex items-center justify-between relative z-10">
                        <div class="flex items-center gap-3 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-100">
                            <div class="flex -space-x-1.5">
                                @for($i = 0; $i < min($cohort->students_count, 3); $i++)
                                    <div class="w-5 h-5 rounded-full bg-white border border-slate-200 flex items-center justify-center text-[7px] font-black text-slate-400 shadow-sm overflow-hidden">
                                        <div class="w-full h-full bg-slate-100 flex items-center justify-center ring-1 ring-inset ring-slate-200">
                                            <svg class="w-3 h-3 translate-y-0.5 opacity-50" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" /></svg>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            <span class="text-[10px] font-black text-slate-900 tracking-tighter">{{ $cohort->students_count }} <span class="text-slate-400">Students</span></span>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest @if($cohort->status == 'active') bg-emerald-50 text-emerald-600 border border-emerald-100/50 @else bg-slate-50 text-slate-400 border border-slate-100 @endif shadow-sm">
                            <div class="w-1 h-1 rounded-full @if($cohort->status == 'active') bg-emerald-500 animate-pulse @else bg-slate-300 @endif"></div>
                            {{ $cohort->status }}
                        </span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full py-32 text-center bg-white rounded-[2.5rem] border-2 border-dashed border-slate-200">
                <div class="w-20 h-20 rounded-[2rem] bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-8 shadow-inner">
                    <svg class="w-10 h-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
                <h3 class="text-xl font-black text-slate-900 tracking-tight">No Cohorts Found</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-3 max-w-sm mx-auto leading-relaxed px-10">
                    No cohorts found for your assigned programs.
                </p>
            </div>
        @endforelse
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('cohortTracker', () => ({
        loading: false,
        timer: null,
        startPolling() {
            this.timer = setInterval(() => {
                this.refreshGrid();
            }, 10000); // 10 seconds update
        },
        async refreshGrid() {
            this.loading = true;
            try {
                const response = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newGrid = doc.getElementById('cohort-grid');
                if (newGrid) {
                    // Update innerHTML without destroying the alpine component wrapping it
                    const currentGrid = document.getElementById('cohort-grid');
                    // manually replacing child nodes to keep the loading spinner stable
                    Array.from(newGrid.children).forEach(child => {
                         if (!child.hasAttribute('x-show')) {
                             // find matching child or append
                             // Simpler: just replace the whole HTML since x-show will re-evaluate if we are careful,
                             // but since we are doing static replacement, Livewire is better for this.
                             // We'll just replace the whole inner HTML.
                         }
                    });
                    currentGrid.innerHTML = newGrid.innerHTML;
                }
            } catch (e) {
                console.error('Failed to update cohort data', e);
            } finally {
                setTimeout(() => this.loading = false, 500);
            }
        }
    }))
})
</script>
@endpush
@endsection
