@extends('layouts.dashboard')

@section('header')
    View Thesis
@endsection

@section('content')
<div class="space-y-8 animate-in-up">
    <!-- Prestige Header -->
    <div class="relative overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] p-6 sm:p-10 md:p-12 bg-grad-premium border border-white/20 shadow-premium group mb-8">
        <div class="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 bg-white/10 blur-[100px] rounded-full group-hover:bg-white/15 transition-all duration-1000"></div>
        <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 bg-acetel-400/10 blur-[80px] rounded-full"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-6 sm:gap-10">
            <div class="max-w-3xl">
                <div class="inline-flex flex-wrap items-center gap-2.5 sm:gap-3 mb-4 sm:mb-6">
                    <div class="inline-flex items-center gap-2 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full bg-white/10 border border-white/20 backdrop-blur-md">
                        <span class="w-2.5 h-2.5 rounded-full bg-acetel-400 animate-pulse shadow-[0_0_8px_rgba(129,140,248,0.8)]"></span>
                        <span class="text-[10px] font-black text-white uppercase tracking-[0.2em]">{{ $thesis->student->program->code }} Thesis</span>
                    </div>
                    <x-badge type="info" :label="ucfirst($thesis->status)" />
                    
                    @if($thesis->cleared_for_internal_at)
                    <div class="inline-flex items-center gap-2 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full bg-emerald-500/20 border border-emerald-400/30 backdrop-blur-md">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-[10px] font-black text-emerald-100 uppercase tracking-[0.2em]">Ready for Internal Defense</span>
                    </div>
                    @endif
                </div>
                
                <h1 class="text-2xl sm:text-3xl md:text-5xl font-black text-white tracking-tight mb-4 leading-snug sm:leading-tight">{{ $thesis->title }}</h1>
                <div class="flex items-center gap-4 text-white/80">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center border border-white/20 text-xs font-bold text-white">
                            {{ substr($thesis->student->user->name, 0, 1) }}
                        </div>
                        <span class="text-sm font-semibold tracking-wide">{{ $thesis->student->user->name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $workflowForThesis = app(\App\Services\MilestoneWorkflowService::class);
        $supIdsForThesis = Auth::user()->supervisorProfiles()->pluck('id');
        $isAssignedSupOnThesis = Auth::user()->hasRole('Supervisor') && $supIdsForThesis->isNotEmpty() && $thesis->assignments->whereIn('supervisor_profile_id', $supIdsForThesis)->where('status', 'active')->isNotEmpty();
        $pendingSupMilestoneOnThesis = null;
        if ($isAssignedSupOnThesis) {
            $pendingSupMilestoneOnThesis = $thesis->milestones->sortBy('template.order')->first(function($m) use ($workflowForThesis) {
                if ($m->status === 'approved' || $m->status === 'revision_required') return false;
                if ($workflowForThesis->isPostDefencePhase($m)) return false;
                $hasSub = $m->relationLoaded('submissions') ? $m->submissions->isNotEmpty() : $m->submissions()->exists();
                if (!$hasSub) return false;
                $summary = $workflowForThesis->getSupervisorReviewSummary($m);
                $myR = collect($summary['supervisors'] ?? [])->first(fn($s) => (string)($s['user_id'] ?? '') === (string)Auth::id());
                if ($myR && in_array($myR['status'], ['approved', 'rejected'])) return false;
                if ($myR && $myR['status'] === 'pending_re_review') return true;
                return !$workflowForThesis->isSupervisorApproved($m);
            });
        }
    @endphp

    @if($pendingSupMilestoneOnThesis)
        @php
            $pendingSubs = $pendingSupMilestoneOnThesis->submissions->sortByDesc('id');
        @endphp
        <div class="rounded-3xl bg-amber-50 border border-amber-200 p-6 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start sm:items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded-full bg-amber-200/80 text-amber-900 text-[10px] font-black uppercase tracking-wider mb-1">Supervisor Approval Needed</span>
                        <h3 class="text-base sm:text-lg font-black text-slate-900">{{ $thesis->student->user->name }} uploaded a submission for {{ $pendingSupMilestoneOnThesis->template->name }}</h3>
                        <p class="text-xs font-medium text-slate-600 mt-0.5">Preview the uploaded thesis document(s) below and approve or request revisions directly.</p>
                    </div>
                </div>
                <a href="{{ route('milestones.show', $pendingSupMilestoneOnThesis) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-xl text-[11px] font-black uppercase tracking-wider transition-all shadow-xs shrink-0">
                    <span>Open Full Workspace</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>

            @if($pendingSubs->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($pendingSubs->take(4) as $sub)
                        <div class="bg-white rounded-2xl border border-amber-200/80 p-3.5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[9px] font-black uppercase tracking-wider">v.0{{ $sub->version }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $sub->type ?? 'document' }}</span>
                                </div>
                                <p class="text-xs font-black text-slate-800 truncate mt-1">{{ $sub->file_meta['original_name'] ?? ('Submission v.0' . $sub->version) }}</p>
                                <p class="text-[10px] font-medium text-slate-400">{{ $sub->created_at?->format('M d, Y · H:i') }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button"
                                    @click.prevent="$dispatch('open-document-preview', {
                                        url: '{{ route('submissions.view', $sub) }}',
                                        title: '{{ addslashes($sub->file_meta['original_name'] ?? ('Submission v.0' . $sub->version)) }}',
                                        type: '{{ str_ends_with(strtolower($sub->file_url), '.pdf') ? 'pdf' : 'document' }}'
                                    })"
                                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[10px] font-black uppercase tracking-wider transition-colors">
                                    Preview
                                </button>
                                <a href="{{ route('submissions.view', $sub) }}" target="_blank" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[10px] font-black uppercase tracking-wider transition-colors">
                                    Open
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" x-data="{ remarks: '' }" class="bg-white/90 rounded-2xl border border-amber-200/80 p-4 space-y-3">
                @csrf
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Supervisor Review Remarks (Required when requesting revisions)</label>
                    <textarea name="remarks" x-model="remarks" rows="2" placeholder="Optional approval note, or required explanation if requesting revisions..." class="w-full rounded-xl border border-slate-200 text-xs font-medium text-slate-800 focus:border-amber-400 focus:ring-amber-400"></textarea>
                    @error('remarks')
                        <p class="text-[11px] font-bold text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2">
                    <button type="submit"
                        formaction="{{ route('milestones.reject_upload', $pendingSupMilestoneOnThesis) }}"
                        @click="if (!remarks.trim()) { $event.preventDefault(); alert('Please enter remarks explaining the revision required before rejecting.'); }"
                        class="px-4 py-2 bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white border border-rose-200 rounded-xl text-[11px] font-black uppercase tracking-wider transition-all">
                        Reject / Request Revision
                    </button>
                    <button type="submit"
                        formaction="{{ route('milestones.accept_upload', $pendingSupMilestoneOnThesis) }}"
                        class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[11px] font-black uppercase tracking-wider transition-all shadow-sm">
                        Approve Submission
                    </button>
                </div>
            </form>
        </div>
    @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="col-span-1 md:col-span-2">
            <x-card title="Milestone Progression">
                @php
                    $statusColors = [
                        'approved' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                        'submitted' => 'bg-amber-50 text-amber-600 border-amber-200',
                        'revision_required' => 'bg-rose-50 text-rose-600 border-rose-200',
                        'changes_required' => 'bg-rose-50 text-rose-600 border-rose-200',
                        'pending' => 'bg-slate-50 text-slate-500 border-slate-200'
                    ];
                @endphp

                <!-- Mobile Milestone Cards View -->
                <div class="block md:hidden space-y-3 p-1">
                    @foreach($thesis->milestones->sortBy('template.order') as $milestone)
                        @if(Auth::user()->hasRole('Internal Examiner') && !Auth::user()->hasAnyRole(['Admin', 'Director', 'Program Coordinator']) && !(Auth::user()->hasRole('Supervisor') && $thesis->assignments->where('supervisor_profile_id', Auth::user()->supervisorProfile?->id)->isNotEmpty()) && $milestone->template->slug !== 'internal_defence' && $milestone->template->order < 6)
                            @continue
                        @endif
                        @php
                            $statusClass = $statusColors[$milestone->status] ?? $statusColors['pending'];
                            $isPendingThisSup = $pendingSupMilestoneOnThesis && $pendingSupMilestoneOnThesis->id === $milestone->id;
                        @endphp
                        <div id="milestone-{{ $milestone->id }}" class="bg-white rounded-2xl border {{ $isPendingThisSup ? 'border-amber-300 ring-2 ring-amber-200/60' : 'border-slate-200/80' }} p-4 space-y-3 shadow-xs">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center font-black text-xs text-slate-600 shrink-0">
                                        0{{ $milestone->template->order }}
                                    </div>
                                    <h4 class="text-sm font-black text-slate-900 leading-snug">{{ $milestone->template->name }}</h4>
                                </div>
                                <span class="px-2.5 py-1 text-[9px] font-black uppercase tracking-widest rounded-md border shrink-0 {{ $isPendingThisSup ? 'bg-amber-100 text-amber-800 border-amber-300' : $statusClass }}">
                                    {{ $isPendingThisSup ? 'Awaiting Your Approval' : str_replace('_', ' ', $milestone->status) }}
                                </span>
                            </div>

                            @if($milestone->submissions && $milestone->submissions->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($milestone->submissions->sortByDesc('id')->take(2) as $mSub)
                                        <a href="{{ route('submissions.view', $mSub) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-[10px] font-bold transition-colors">
                                            <span>v.0{{ $mSub->version }} · {{ \Illuminate\Support\Str::limit($mSub->file_meta['original_name'] ?? 'Document', 24) }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100">
                                <span>Updated: {{ $milestone->updated_at ? $milestone->updated_at->format('M d, Y') : 'N/A' }}</span>
                                <a href="{{ route('milestones.show', $milestone) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $isPendingThisSup ? 'bg-amber-500 text-white hover:bg-amber-600' : 'bg-primary-50 text-primary-700 hover:bg-primary-100' }} font-bold text-xs rounded-lg transition-colors">
                                    {{ $isPendingThisSup ? 'Review & Approve' : ($milestone->status === 'submitted' ? 'Audit Phase' : 'Access') }}
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Milestone Table View -->
                <div class="hidden md:block -mx-6 -my-4 overflow-x-auto custom-scrollbar">
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
                                @if(Auth::user()->hasRole('Internal Examiner') && !Auth::user()->hasAnyRole(['Admin', 'Director', 'Program Coordinator']) && !(Auth::user()->hasRole('Supervisor') && $thesis->assignments->where('supervisor_profile_id', Auth::user()->supervisorProfile?->id)->isNotEmpty()) && $milestone->template->slug !== 'internal_defence' && $milestone->template->order < 6)
                                    @continue
                                @endif
                                @php
                                    $statusClass = $statusColors[$milestone->status] ?? $statusColors['pending'];
                                    $isPendingThisSup = $pendingSupMilestoneOnThesis && $pendingSupMilestoneOnThesis->id === $milestone->id;
                                @endphp
                            <tr id="milestone-row-{{ $milestone->id }}" class="{{ $isPendingThisSup ? 'bg-amber-50/40' : 'hover:bg-primary-50/30' }} transition-all duration-300 group/row">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center font-black text-slate-400 group-hover/row:bg-primary-50 group-hover/row:text-primary-600 transition-colors shadow-sm shrink-0">
                                            0{{ $milestone->template->order }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="text-sm font-bold text-slate-900 tracking-tight block">{{ $milestone->template->name }}</span>
                                            @if($milestone->submissions && $milestone->submissions->isNotEmpty())
                                                <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                                    @foreach($milestone->submissions->sortByDesc('id')->take(2) as $mSub)
                                                        <a href="{{ route('submissions.view', $mSub) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-[10px] font-bold transition-colors">
                                                            <span>v.0{{ $mSub->version }} · {{ \Illuminate\Support\Str::limit($mSub->file_meta['original_name'] ?? 'Submission', 26) }}</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-md border {{ $isPendingThisSup ? 'bg-amber-100 text-amber-800 border-amber-300' : $statusClass }}">
                                        {{ $isPendingThisSup ? 'Awaiting Your Approval' : str_replace('_', ' ', $milestone->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-xs font-semibold text-slate-500">
                                    {{ $milestone->updated_at ? $milestone->updated_at->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <a href="{{ route('milestones.show', $milestone) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 {{ $isPendingThisSup ? 'bg-amber-500 hover:bg-amber-600 text-white border border-amber-600' : 'bg-white border border-slate-200 text-primary-600 hover:bg-primary-50 hover:border-primary-200' }} text-[10px] font-black uppercase tracking-widest rounded-lg transition-all duration-300 shadow-sm">
                                        @if($isPendingThisSup)
                                            Review & Approve
                                        @elseif($milestone->status === 'submitted')
                                            Audit Phase
                                        @else
                                            Access
                                        @endif
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
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
                                        <span class="text-[10px] font-black uppercase tracking-widest text-primary-600 bg-primary-50 px-3 py-1 rounded-full border border-primary-100">{{ match($event->type) { 'proposal' => 'Proposal Defence', 'progress_report_1' => 'Progress Report 1', 'progress_report_2' => 'Progress Report 2', 'seminar' => 'Seminar as a Course', default => ucfirst(str_replace('_', ' ', $event->type)) } }}</span>
                                        <span class="text-xs font-bold text-slate-500">{{ $event->event_date ? $event->event_date->format('M d, Y') : ($event->schedule_start ? $event->schedule_start->format('M d, Y') : 'TBD') }}</span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700 mb-6 italic">{{ $event->location }}</p>
                                    
                                    @php
                                        $canEval = $event->isAuthorizedEvaluator(Auth::id());
                                        $evalExists = \App\Models\Evaluation::where('defence_event_id', $event->id)->where('evaluator_id', Auth::id())->first();
                                    @endphp
                                    @if($canEval)
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
