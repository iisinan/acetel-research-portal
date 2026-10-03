@extends('layouts.dashboard')

@section('header')
    View Thesis
@endsection

@section('content')
<div class="space-y-8 animate-in-up">
    <!-- Prestige Header -->
    <div class="relative overflow-hidden rounded-[2.5rem] p-12 bg-grad-premium border border-white/20 shadow-premium group mb-8">
        <div class="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 bg-white/10 blur-[100px] rounded-full group-hover:bg-white/15 transition-all duration-1000"></div>
        <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 bg-acetel-400/10 blur-[80px] rounded-full"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-10">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-3 mb-6">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 backdrop-blur-md">
                        <span class="w-2.5 h-2.5 rounded-full bg-acetel-400 animate-pulse shadow-[0_0_8px_rgba(129,140,248,0.8)]"></span>
                        <span class="text-[10px] font-black text-white uppercase tracking-[0.2em]">{{ $thesis->student->program->code }} Thesis</span>
                    </div>
                    <x-badge type="info" :label="ucfirst($thesis->status)" />
                    
                    @if($thesis->cleared_for_internal_at)
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-500/20 border border-emerald-400/30 backdrop-blur-md">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-[10px] font-black text-emerald-100 uppercase tracking-[0.2em]">Ready for Internal Defense</span>
                    </div>
                    @endif
                </div>
                
                <h1 class="text-4xl md:text-5xl font-black text-white tracking-tighter mb-4 leading-tight">{{ $thesis->title }}</h1>
                <div class="flex items-center gap-4 text-white/80">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center border border-white/20 text-xs font-bold text-white">
                            {{ substr($thesis->student->user->name, 0, 1) }}
                        </div>
                        <span class="text-sm font-semibold tracking-wide">{{ $thesis->student->user->name }}</span>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col gap-3">
                @if(Auth::user()->hasRole('Supervisor') && !$thesis->cleared_for_internal_at)
                    <form action="{{ route('theses.clear_internal', $thesis) }}" method="POST" data-confirm="Authorize this Thesis for Internal Defense? This is an irreversible administrative action." data-confirm-title="Authorize Internal Defense" data-confirm-type="warning" data-confirm-btn="Authorize Defense">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all duration-300 shadow-lg hover:shadow-emerald-500/30">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                             Authorize Internal Defense
                        </button>
                    </form>
                @endif

                @if(!Auth::user()->hasRole('Student'))
                    <a href="{{ route('events.create', ['thesis_project_id' => $thesis->id]) }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-[10px] font-black uppercase tracking-widest rounded-xl backdrop-blur-md transition-all duration-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Schedule Event
                    </a>
                @endif
            </div>
        </div>
    </div>

        @can('update', $thesis)

        @endcan

        @if(Auth::user()->hasRole('Program Coordinator') || Auth::user()->hasRole('Admin'))
        <x-card title="Supervisory Matrix" class="mb-8 border-l-4 !border-l-primary-500">
            <div class="mb-6">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Active Supervisory Board</p>
                <div class="flex flex-wrap gap-3">
                     @forelse($thesis->supervisors as $assignment)
                          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 shadow-sm group hover:border-primary-300 transition-colors">
                             <div class="w-6 h-6 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center text-[10px] font-bold">
                                 {{ substr($assignment->supervisor->user->name, 0, 1) }}
                             </div>
                             <span class="text-sm font-semibold text-slate-900">{{ $assignment->supervisor->user->name }}</span>
                             <span class="px-2 py-0.5 rounded-md bg-slate-50 border border-slate-100 text-[9px] font-black text-slate-500 uppercase tracking-widest">{{ $assignment->role }}</span>
                          </div>
                    @empty
                        <div class="px-4 py-2 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 text-xs font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Unassigned Matrix - Action Required
                        </div>
                    @endforelse
                </div>
            </div>

            <details class="group mt-4 pt-4 border-t border-slate-100">
                <summary class="cursor-pointer flex items-center gap-2 font-black text-xs text-primary-600 uppercase tracking-[0.2em] hover:text-primary-800 list-none transition-colors">
                    <svg class="w-4 h-4 transition-transform duration-300 group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                    Reconfigure Matrix
                </summary>
                
                <form action="{{ route('theses.assign_supervisor', $thesis) }}" method="POST" class="mt-6 p-6 bg-slate-50/50 rounded-2xl border border-slate-100 animate-in-up">
                    @csrf
                    
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-900 mb-1">Select Supervisors</label>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest mb-4">
                            Hold <kbd class="px-1.5 py-0.5 bg-slate-200 rounded text-slate-700 font-mono">Cmd/Ctrl</kbd> to multi-select. Primary designation awarded to first selection.
                        </p>
                        <select name="supervisors[]" multiple class="w-full h-56 rounded-xl border-slate-200 shadow-inner focus:border-primary-500 focus:ring focus:ring-primary-200 p-2 text-sm text-slate-700 custom-scrollbar">
                            @foreach($allSupervisors as $sup)
                                 <option value="{{ $sup->id }}" {{ $thesis->supervisors->pluck('supervisor_profile_id')->contains($sup->id) ? 'selected' : '' }} class="p-2 rounded-lg mb-1 hover:bg-slate-100 focus:bg-primary-50 focus:text-primary-700">
                                    {{ $sup->user->name }} » Load: {{ $sup->current_load }}/{{ $sup->max_students }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-3 bg-primary-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-primary-700 transition-all duration-300 shadow-md">
                            Synchronize Matrix
                        </button>
                    </div>
                </form>
            </details>
        </x-card>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="col-span-1 md:col-span-2">
            <x-card title="Milestone Progression">
                <div class="-mx-6 -my-4 overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 backdrop-blur-md border-b border-slate-100">
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Phase</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Current State</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Last Signature</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50/50">
                            @foreach($thesis->milestones->sortBy('template.order') as $milestone)
                                @if(Auth::user()->hasRole('Internal Examiner') && $milestone->template->order < 9)
                                    @continue
                                @endif
                                
                            <tr class="hover:bg-primary-50/30 transition-all duration-300 group/row">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center font-black text-slate-400 group-hover/row:bg-primary-50 group-hover/row:text-primary-600 transition-colors shadow-sm">
                                            0{{ $milestone->template->order }}
                                        </div>
                                        <span class="text-sm font-bold text-slate-900 tracking-tight">{{ $milestone->template->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @php
                                        $statusColors = [
                                            'approved' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                            'submitted' => 'bg-amber-50 text-amber-600 border-amber-200',
                                            'changes_required' => 'bg-rose-50 text-rose-600 border-rose-200',
                                            'pending' => 'bg-slate-50 text-slate-500 border-slate-200'
                                        ];
                                        $statusClass = $statusColors[$milestone->status] ?? $statusColors['pending'];
                                    @endphp
                                    <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-md border {{ $statusClass }}">
                                        {{ str_replace('_', ' ', $milestone->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-xs font-semibold text-slate-500">
                                    {{ $milestone->updated_at ? $milestone->updated_at->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <a href="{{ route('milestones.show', $milestone) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-slate-200 text-primary-600 text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-primary-50 hover:border-primary-200 transition-all duration-300 shadow-sm">
                                        @if($milestone->status === 'submitted')
                                            Audit Phase
                                        @else
                                            Access
                                        @endif
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
        <!-- System Events & defense -->
        <div class="col-span-1 md:col-span-3">
            <x-card title="Defense Events">
                @if($thesis->defenceEvents && $thesis->defenceEvents->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($thesis->defenceEvents as $event)
                            <div class="p-6 rounded-[2rem] glass border border-slate-100 shadow-sm relative group overflow-hidden">
                                <div class="absolute inset-0 bg-primary-50/50 transform -translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-in-out"></div>
                                <div class="relative z-10">
                                    <div class="flex justify-between items-start mb-4">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-primary-600 bg-primary-50 px-3 py-1 rounded-full border border-primary-100">{{ $event->type }}</span>
                                        <span class="text-xs font-bold text-slate-500">{{ $event->event_date ? $event->event_date->format('M d, Y') : 'TBD' }}</span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700 mb-6 italic">{{ $event->location }}</p>
                                    
                                    @if(Auth::user()->hasRole(['Internal Examiner', 'External Examiner', 'Program Coordinator', 'Director', 'Admin']))
                                        @php
                                            $isPanelMember = $event->panelMembers()->where('user_id', Auth::id())->exists();
                                            $evalExists = \App\Models\Evaluation::where('defence_event_id', $event->id)->where('evaluator_id', Auth::id())->first();
                                        @endphp
                                        @if($isPanelMember)
                                            @if($evalExists && $evalExists->submitted_at)
                                                <a href="{{ route('evaluations.show', $evalExists) }}" class="inline-flex w-full items-center justify-center gap-2 px-4 py-3 bg-white border-2 border-emerald-100 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-50 transition-all shadow-sm">
                                                    View Evaluation
                                                </a>
                                            @else
                                                <a href="{{ route('evaluations.create', $event) }}" class="inline-flex w-full items-center justify-center gap-2 px-4 py-3 bg-primary-600 border border-transparent text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-primary-700 transition-all shadow-sm">
                                                    Start Evaluation
                                                </a>
                                            @endif
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 bg-slate-50/50 border border-dashed border-slate-200 rounded-2xl">
                        <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">No events scheduled.</p>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Correction Protocol -->
        <div class="col-span-1 md:col-span-3 lg:col-span-1">
            <x-card title="Corrections" class="!border-t-4 !border-t-rose-500 h-full">
                <div class="space-y-4 max-h-[500px] overflow-y-auto custom-scrollbar pr-2">
                    @forelse($action_items as $item)
                        <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl group hover:border-rose-200 hover:bg-white transition-all duration-300">
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-8 h-8 rounded-lg bg-white border border-slate-100 flex items-center justify-center text-rose-500 shadow-sm shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-800 leading-snug line-clamp-2">{{ $item->content }}</p>
                                    @if($item->due_date)
                                        <p class="text-[8px] font-black text-rose-600 uppercase tracking-widest mt-1">Due: {{ $item->due_date->format('M d') }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-slate-100/50">
                                @php
                                    $aiStatusColors = [
                                        'pending' => 'bg-slate-100 text-slate-500 border-slate-200',
                                        'completed' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                        'verified' => 'bg-acetel-50 text-acetel-600 border-acetel-200',
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-widest border {{ $aiStatusColors[$item->status] ?? $aiStatusColors['pending'] }}">
                                    {{ $item->status }}
                                </span>
                                
                                @if($item->status === 'completed' && !Auth::user()->hasRole('Student'))
                                    <form action="{{ route('action-items.verify', $item) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[9px] font-black text-primary-600 uppercase tracking-widest hover:text-emerald-600 transition-colors">Verify Correction</button>
                                    </form>
                                @endif
                                
                                @if($item->status === 'pending' && Auth::user()->hasRole('Student'))
                                    <form action="{{ route('action-items.complete', $item) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[9px] font-black text-primary-600 uppercase tracking-widest hover:text-emerald-600 transition-colors">Mark Completed</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 bg-slate-50 border border-dashed border-slate-200 rounded-2xl">
                             <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">No corrections required.</p>
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>

        <!-- WhatsApp-Style Institutional Comm-Link -->
        <div class="col-span-1 md:col-span-3 lg:col-span-1">
            <x-comm-link :messages="$thesis->messages" :thesisId="$thesis->id" :potentialMentions="$potentialMentions" />
        </div>
    </div>
</div>
@endsection
