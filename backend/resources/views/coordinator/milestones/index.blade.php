@extends('layouts.coordinator')

@section('header', 'Validation Queue')

@section('content')
<div class="space-y-10 pb-10">
    <!-- Sophisticated Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <div class="p-1.5 rounded-lg bg-acetel-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Institutional Validation Hub</span>
            </div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Milestone Oversight</h1>
            <p class="mt-2 text-sm font-medium text-slate-500">Authorized review protocols for candidate submissions and academic validation.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-6 py-3 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-acetel-500 animate-pulse"></div>
                <span class="text-xs font-black text-slate-900 tracking-tighter">{{ $milestones->total() }} <span class="text-slate-400">Total Protocols</span></span>
            </div>
        </div>
    </div>

    <!-- Premium Table Container -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden min-h-[500px] flex flex-col">
        <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
            <div>
                <h3 class="text-xl font-black text-slate-900 tracking-tight">Validation Feed</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Live Submission Matrix</p>
            </div>
            @if(request('thesis_id'))
                <div class="flex items-center gap-3">
                    <div class="px-4 py-2 rounded-xl bg-acetel-50 border border-acetel-100 flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-acetel-500"></div>
                        <span class="text-[10px] font-black text-acetel-700 uppercase tracking-widest">Identity Filter Active</span>
                    </div>
                    <a href="{{ route('coordinator.milestones.index') }}" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-900 transition-colors">Reset View</a>
                </div>
            @endif
        </div>

        <div class="flex-1">
            @if(isset($milestones) && $milestones->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Candidate</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Milestone Vector</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Submission Timestamp</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status Protocol</th>
                                <th class="px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Access</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($milestones as $milestone)
                                <tr class="hover:bg-slate-50/30 transition-colors group">
                                    <td class="px-10 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-acetel-50 group-hover:text-acetel-500 transition-all font-black text-sm shadow-sm ring-1 ring-slate-100 group-hover:ring-acetel-100">
                                                {{ substr($milestone->thesis->student->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-900 leading-none group-hover:text-acetel-600 transition-colors">{{ $milestone->thesis->student->user->name }}</p>
                                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5">{{ $milestone->thesis->student->user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-slate-900 tracking-tight">{{ $milestone->template->name }}</span>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Academic Level Target</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="flex items-center gap-2">
                                            <div class="p-1 rounded bg-slate-50 text-slate-400">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            </div>
                                            <span class="text-sm font-medium text-slate-600">
                                                {{ $milestone->submitted_at ? $milestone->submitted_at->format('M d, Y') : 'Pending Signal' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 text-center">
                                        @php
                                            $statusClasses = [
                                                'approved' => 'bg-emerald-50 text-emerald-600 border-emerald-100/50',
                                                'submitted' => 'bg-acetel-50 text-acetel-600 border-acetel-100/50',
                                                'revision_required' => 'bg-rose-50 text-rose-600 border-rose-100/50',
                                            ];
                                            $dotClasses = [
                                                'approved' => 'bg-emerald-500',
                                                'submitted' => 'bg-acetel-500',
                                                'revision_required' => 'bg-rose-500',
                                            ];
                                            $class = $statusClasses[$milestone->status] ?? 'bg-slate-50 text-slate-400 border-slate-100';
                                            $dotClass = $dotClasses[$milestone->status] ?? 'bg-slate-300';
                                        @endphp
                                        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-xl {{ $class }} text-[10px] font-black uppercase tracking-widest border shadow-sm">
                                            <div class="w-1 h-1 rounded-full {{ $dotClass }} @if($milestone->status == 'submitted') animate-pulse @endif"></div>
                                            {{ str_replace('_', ' ', $milestone->status) }}
                                        </span>
                                    </td>
                                    <td class="px-10 py-6 text-right">
                                        <a href="{{ route('theses.show', ['thesis' => $milestone->thesis_project_id, 'expanded' => $milestone->id]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-acetel-600 text-white text-[10px] font-black uppercase tracking-widest transition-all shadow-xl shadow-slate-900/10 hover:shadow-acetel-500/20 translate-y-0 hover:-translate-y-1">
                                            View Thesis
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-[400px] text-center px-10">
                    <div class="w-20 h-20 rounded-[2rem] bg-slate-50 border border-slate-100 flex items-center justify-center mb-6 shadow-inner">
                        <svg class="w-10 h-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h4 class="text-lg font-black text-slate-900 tracking-tight">Oversight Queue Empty</h4>
                    <p class="text-sm text-slate-500 mt-2 max-w-xs mx-auto">No recent candidate submissions detected within the authorized program vector.</p>
                </div>
            @endif
        </div>

        @if($milestones->hasPages())
            <div class="px-10 py-6 bg-slate-50/50 border-t border-slate-50">
                {{ $milestones->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
