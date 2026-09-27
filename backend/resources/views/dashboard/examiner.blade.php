@extends('layouts.dashboard')

@section('header')
    Examination Panel
@endsection

@section('content')
<div class="space-y-8 animate-in">
    {{-- Welcome Hero --}}
    <div class="relative overflow-hidden rounded-3xl bg-white border border-green-100 shadow-sm p-8 lg:p-10">
        <div class="absolute top-0 right-0 w-72 h-72 bg-green-50 rounded-full -mr-20 -mt-20 opacity-60"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-green-50 rounded-full -ml-16 -mb-16 opacity-40"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center gap-8">
            {{-- Left: Greeting --}}
            <div class="flex-1 space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-50 rounded-full border border-green-200">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-600"></span>
                    </span>
                    <span class="text-xs font-semibold text-green-700 uppercase tracking-widest text-[10px]">Evaluation Core Active</span>
                </div>

                <div>
                    <h2 class="text-3xl lg:text-4xl font-black text-slate-800 tracking-tight leading-tight">
                        Welcome back, <span class="text-green-600">{{ auth()->user()->firstName() }}</span>
                    </h2>
                    <p class="mt-2 text-slate-500 font-medium leading-relaxed max-w-xl">
                        Strategic evaluation of postgraduate research trajectories. Synchronize with candidates and certify the integrity of doctoral benchmarks.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3 pt-1">
                    <a href="{{ route('inbox.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-xl shadow-sm transition-all duration-200 group">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        Oversight Intel
                        @if($stats['unread_messages'] > 0)
                            <span class="bg-white text-green-700 text-[10px] font-black rounded-full w-4 h-4 flex items-center justify-center">{{ $stats['unread_messages'] }}</span>
                        @endif
                    </a>
                </div>
            </div>

            {{-- Right: Load Summary --}}
            <div class="shrink-0 flex flex-col items-center justify-center bg-green-50 border border-green-100 rounded-2xl p-8 min-w-[170px] text-center">
                <p class="text-[10px] font-black text-green-600 uppercase tracking-widest mb-2">Portfolio Load</p>
                <p class="text-5xl font-black text-slate-800 tracking-tighter leading-none">
                    {{ $stats['assigned_theses'] }}
                </p>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest mt-2">Active Theses</p>
            </div>
        </div>
    </div>

    {{-- Urgent Operational Alerts --}}
    @if($pending_reviews->count() > 0)
    <div x-data="examinerDashboard" class="space-y-4">
        <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-[2.5rem] p-1 shadow-xl shadow-amber-500/20 relative overflow-visible group z-20">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-30 pointer-events-none"></div>
            <div class="bg-white rounded-[2.4rem] p-6 lg:p-8 relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-start gap-5 cursor-pointer" @click="toggleAlerts">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center shrink-0 border border-amber-200">
                        <span class="relative flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-4 w-4 bg-amber-500"></span>
                        </span>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Examination Candidates List</h3>
                        <p class="text-sm font-medium text-slate-500 mt-1">There are {{ $pending_reviews->count() }} milestones awaiting your internal examiner clearance.</p>
                    </div>
                </div>

                <div class="w-full md:w-auto">
                    <button type="button" @click="toggleAlerts" class="w-full md:w-auto px-8 py-4 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-xl font-black tracking-widest uppercase text-[10px] transition-all border border-amber-200 flex items-center justify-between md:justify-center gap-3">
                        <span x-text="expanded ? 'Minimize Alerts' : 'Review Candidates'"></span>
                        <svg class="w-4 h-4 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                </div>
            </div>

            {{-- Expanded Candidates Grid --}}
            <div x-show="expanded" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-4"
                class="mt-4 pb-4 px-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($pending_reviews as $review)
                    <div class="bg-white border-2 border-amber-100/50 rounded-3xl p-6 hover:shadow-xl hover:border-amber-200 transition-all duration-300 group/card relative overflow-hidden block">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-amber-50 rounded-full -mr-12 -mt-12 opacity-50 group-hover/card:scale-110 transition-transform"></div>
                        
                        <div class="relative z-10">
                            <div class="flex items-center gap-4 mb-5">
                                <div class="w-12 h-12 rounded-2xl bg-amber-600 text-white flex items-center justify-center font-black text-lg">
                                    {{ substr($review->thesis->student->user->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-black text-slate-800 tracking-tight truncate">{{ $review->thesis->student->user->name }}</h4>
                                    <p class="text-[10px] font-bold text-amber-600 uppercase tracking-widest">{{ $review->template->name }}</p>
                                </div>
                            </div>

                            <p class="text-[11px] text-slate-500 font-medium mb-6 line-clamp-2 italic leading-relaxed">"{{ $review->thesis->title }}"</p>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('milestones.review', $review->id) }}" class="flex-1 flex items-center justify-center py-3 bg-slate-900 hover:bg-black text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                    Audit Detail
                                </a>
                                {{-- Quick Approve --}}
                                <button type="button" @click.stop="quickApprove('{{ $review->id }}', $el)" class="px-4 py-3 bg-white border border-amber-200 hover:border-amber-500 text-amber-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Metrics Matrix --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $sCards = [
                ['label' => 'Total Theses', 'value' => $stats['assigned_theses'], 'color' => 'green', 'icon' => 'user-group'],
                ['label' => 'Pending Reviews', 'value' => $stats['pending_milestone_reviews'], 'color' => 'amber', 'icon' => 'clipboard-check'],
                ['label' => 'Viva Queue', 'value' => $stats['pending_evaluations'], 'color' => 'blue', 'icon' => 'academic-cap'],
                ['label' => 'Unread Messages', 'value' => $stats['unread_messages'], 'color' => 'emerald', 'icon' => 'chat-alt-2'],
            ];
            $iconMap = [
                'user-group' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                'clipboard-check' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                'academic-cap' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222',
                'chat-alt-2' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'
            ];
            $colorMap = [
                'green' => 'bg-green-50 border-green-100 text-green-600',
                'amber' => 'bg-amber-50 border-amber-100 text-amber-600',
                'blue' => 'bg-blue-50 border-blue-100 text-blue-600',
                'emerald' => 'bg-emerald-50 border-emerald-100 text-emerald-600',
            ];
        @endphp
        @foreach($sCards as $sc)
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-xl border flex items-center justify-center {{ $colorMap[$sc['color']] }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconMap[$sc['icon']] }}"/></svg>
                </div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest leading-none">{{ $sc['label'] }}</p>
            </div>
            <p class="text-3xl font-black text-slate-800 tracking-tight">{{ $sc['value'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Assigned Theses Roster --}}
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-6 bg-green-500 rounded-full"></div>
                        <h3 class="text-base font-black text-slate-800 tracking-tight">ASSIGNED THESES</h3>
                    </div>
                </div>

                <div class="divide-y divide-slate-50">
                    @forelse($theses as $thesis)
                        <div class="p-8 hover:bg-slate-50/50 transition-colors group">
                             <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                                 <div class="flex items-center gap-5">
                                     <div class="w-14 h-14 rounded-2xl bg-green-50 border border-green-100 flex items-center justify-center text-green-700 font-black text-xl shadow-sm group-hover:bg-green-600 group-hover:text-white transition-all duration-300">
                                         {{ substr($thesis->student->user->name, 0, 1) }}
                                     </div>
                                     <div class="min-w-0">
                                         <h4 class="text-lg font-black text-slate-800 group-hover:text-green-600 transition-colors tracking-tight leading-none mb-2">{{ $thesis->student->user->name }}</h4>
                                         <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">{{ $thesis->student->program->code ?? 'N/A' }} • {{ $thesis->student->student_id_number }}</p>
                                         <p class="text-xs text-slate-500 truncate max-w-sm" title="{{ $thesis->title }}">"{{ $thesis->title }}"</p>
                                     </div>
                                 </div>
                                 <div class="flex items-center gap-3">
                                     <a href="{{ route('inbox.compose', ['reply_to' => $thesis->student->user_id]) }}" class="p-3 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-green-600 hover:border-green-200 transition-all shadow-sm" title="Send Message">
                                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                     </a>
                                     <a href="{{ route('theses.show', $thesis) }}" class="flex items-center justify-center px-6 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 text-xs font-black uppercase tracking-widest hover:bg-green-600 hover:border-green-600 hover:text-white transition-all shadow-sm">
                                         Audit Thesis
                                     </a>
                                 </div>
                             </div>
                        </div>
                    @empty
                        <div class="py-20 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-slate-100">
                                <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest">No Assigned Portfolios Detected</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Viva Evaluations --}}
            @if($pending_evaluations->count() > 0)
            <div class="bg-white rounded-3xl border border-blue-100 shadow-sm overflow-hidden mt-6">
                <div class="px-8 py-6 border-b border-blue-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-6 bg-blue-500 rounded-full"></div>
                        <h3 class="text-base font-black text-slate-800 tracking-tight">VIVA EVALUATIONS CORE</h3>
                    </div>
                </div>

                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($pending_evaluations as $event)
                        <div class="p-6 rounded-2xl bg-blue-50 border border-blue-100 group hover:bg-blue-100 hover:border-blue-200 transition-all duration-300">
                             <div class="space-y-4">
                                 <div class="flex items-center justify-between">
                                     <span class="px-3 py-1 bg-white text-blue-600 border border-blue-100 text-[9px] font-black uppercase tracking-widest rounded-lg">{{ $event->defence_type }} Defence</span>
                                     <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($event->scheduled_at)->format('M d, Y') }}</span>
                                 </div>
                                 <div class="min-w-0">
                                     <h4 class="text-lg font-black text-slate-800 tracking-tight leading-none mb-1 truncate">{{ $event->thesis->student->user->name }}</h4>
                                     <p class="text-xs text-slate-500 font-medium truncate">"{{ $event->thesis->title }}"</p>
                                 </div>
                                 <a href="{{ route('evaluations.create', ['event' => $event->id]) }}" class="w-full flex items-center justify-center py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-sm">
                                     Submit Grade
                                 </a>
                             </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Pending Actions Sidebar --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- Pending Reviews --}}
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="text-xs font-black text-slate-800 tracking-widest">PENDING CLEARANCES</h3>
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[10px] font-black rounded-full border border-amber-100">{{ $pending_reviews->count() }}</span>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($pending_reviews->take(5) as $milestone)
                        <div class="p-4 bg-amber-50/50 border border-amber-100 rounded-2xl group hover:bg-amber-50 transition-all">
                             <p class="text-xs font-black text-slate-800 mb-1 truncate">{{ $milestone->template->name }}</p>
                             <div class="flex items-center justify-between gap-2">
                                 <span class="text-[10px] font-bold text-slate-500 truncate">{{ $milestone->thesis->student->user->name }}</span>
                                 <a href="{{ route('theses.show', ['thesis' => $milestone->thesis_project_id, 'expanded' => $milestone->id]) }}" class="text-[9px] font-black text-amber-600 uppercase tracking-widest flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                                     Review <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                                 </a>
                             </div>
                        </div>
                    @empty
                        <div class="py-10 text-center bg-slate-50 rounded-2xl border border-slate-100">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Audit Backlog Clear</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Institutional Resource Center --}}
            @if(isset($document_templates) && $document_templates->count() > 0)
            <div class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="text-xs font-black text-slate-800 tracking-widest uppercase">Institutional Resources</h3>
                </div>
                <div class="p-2 space-y-1">
                    @foreach($document_templates as $template)
                    <a href="{{ route('templates.download', $template) }}" class="flex items-center gap-3 p-3 hover:bg-green-50 rounded-2xl transition-all group">
                        <div class="w-8 h-8 rounded-xl bg-green-50 text-green-600 flex items-center justify-center shrink-0 group-hover:bg-green-600 group-hover:text-white transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-black text-slate-700 truncate leading-none uppercase">{{ $template->title }}</p>
                            <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase tracking-tighter italic">v{{ $template->version }} • {{ strtoupper($template->type) }}</p>
                        </div>
                        <svg class="w-3 h-3 ml-auto text-slate-300 group-hover:text-green-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Quick Protocol --}}
            <div class="bg-green-800 rounded-3xl p-8 text-white relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 bg-green-500 rounded-full -mr-16 -mt-16 opacity-20 group-hover:scale-110 transition-transform duration-700"></div>
                <h3 class="text-xl font-black tracking-tight leading-tight mb-4">Clearance Protocol</h3>
                <p class="text-xs leading-relaxed opacity-80 mb-8">Authorize doctoral candidate trajectory completions and certify divisional peer reviews.</p>
                <div class="space-y-3 relative z-10">
                    <a href="{{ route('repository.index') }}" class="w-full flex items-center justify-center py-3.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition-all">Research Archive</a>
                    <a href="{{ route('profile.edit') }}" class="w-full flex items-center justify-center py-3.5 bg-green-600 hover:bg-green-500 text-white rounded-xl text-xs font-bold transition-all">System Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('examinerDashboard', () => ({
        expanded: false,
        toggleAlerts() {
            this.expanded = !this.expanded;
        },
        async quickApprove(milestoneId, btn) {
            if (!confirm("Execute Institutional Clearance: As {{ auth()->user()->hasRole('External Examiner') ? 'External Examiner' : 'Internal Examiner' }}, are you authorizing this milestone based on doctoral standards?")) return;
            
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
                        type: 'clear_role',
                        role: "{{ auth()->user()->hasRole('External Examiner') ? 'External Examiner' : 'Internal Examiner' }}"
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.toast.success(data.message || 'Institutional Clearance Executed.');
                    // Remove card with animation
                    const card = btn.closest('.block');
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        card.remove();
                        // If no more cards, check if we should hide the alert system
                        const grid = document.querySelector('.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-3');
                        if (grid && !grid.children.length) {
                            window.location.reload(); // Simple way to refresh counts
                        }
                    }, 500);
                } else {
                    window.toast.error(data.message || 'Authorization failed.');
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                window.toast.error('System Oversight Error occurred.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }
    }));
});
</script>
@endpush
