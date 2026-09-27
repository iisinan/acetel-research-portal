@extends(auth()->user()->hasRole('Program Coordinator') ? 'layouts.coordinator' : 'layouts.dashboard')

@section('header')
    @if(auth()->user()->hasRole('Program Coordinator'))
        Student Milestones
    @else
        My Milestones
    @endif
@endsection

@section('content')
<div class="space-y-8 pb-20" id="milestones-page">
<script>
    var _expandedMilestone = '{{ $ongoingMilestoneId }}';
    function toggleMilestone(id) {
        var all = document.querySelectorAll('.milestone-body');
        all.forEach(function(el) {
            if (el.id !== 'milestone-body-' + id) {
                el.style.display = 'none';
                var btn = document.getElementById('milestone-chevron-' + el.id.replace('milestone-body-',''));
                if (btn) btn.style.transform = '';
            }
        });
        var panel = document.getElementById('milestone-body-' + id);
        var chevron = document.getElementById('milestone-chevron-' + id);
        if (panel) {
            if (panel.style.display === 'block') {
                panel.style.display = 'none';
                if (chevron) chevron.style.transform = '';
            } else {
                panel.style.display = 'block';
                if (chevron) chevron.style.transform = 'rotate(180deg)';
                panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        if (_expandedMilestone) {
            var el = document.getElementById('milestone-body-' + _expandedMilestone);
            var chevron = document.getElementById('milestone-chevron-' + _expandedMilestone);
            if (el) { el.style.display = 'block'; }
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        }
    });
</script>
    <script>
        window.refreshMilestone = async (id) => {
            const container = document.getElementById('milestone-container-' + id);
            const listContainer = document.getElementById('milestones-list');
            const roadmapContainer = document.querySelector('.ResearchRoadmapContainer');

            if (!container) return;
            
            try {
                const url = new URL(window.location.href);
                url.searchParams.set('refresh_id', id);

                const response = await fetch(url.toString(), {
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // 1. Refresh the Roadmap
                const newRoadmap = doc.querySelector('.ResearchRoadmapContainer');
                if (newRoadmap && roadmapContainer) {
                    roadmapContainer.innerHTML = newRoadmap.innerHTML;
                }

                // 2. Refresh the whole List (Better than single panel because approval may unlock next milestone)
                const newList = doc.getElementById('milestones-list');
                if (newList && listContainer) {
                    listContainer.innerHTML = newList.innerHTML;
                    
                    // Re-initialize Alpine.js for the new content
                    if (window.Alpine) {
                        window.Alpine.discoverUninitializedComponents((el) => {
                            window.Alpine.initTree(el);
                        });
                    }
                } else {
                    // Fallback to single element update if list refresh fails
                    const targetId = 'milestone-container-' + id;
                    const newEl = doc.getElementById(targetId);
                    if (newEl) {
                        container.innerHTML = newEl.innerHTML;
                        if (window.Alpine) window.Alpine.initTree(container);
                    } else {
                        window.location.reload();
                    }
                }
            } catch (e) {
                console.error('Refresh failed:', e);
                window.location.reload();
            } finally {
                // Done
            }
        };
    </script>
    <!-- Sophisticated Context Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2 text-brand-600">
                <div class="p-1.5 rounded-lg bg-brand-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </div>
                <span class="text-xs font-bold uppercase tracking-widest">Verification</span>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 tracking-tight">
                @if(auth()->user()->hasRole('Student'))
                    Research Progress
                @else
                    {{ $thesis->student?->user?->name ?? 'Student' }}'s Progress
                @endif
            </h1>
            <p class="mt-2 text-sm font-medium text-gray-500 italic max-w-2xl">
                Track and manage thesis milestones and submissions.
            </p>
        </div>
    </div>

    <!-- Visual Research Roadmap (Timeline) -->
    <div class="bg-white border border-gray-100 rounded-3xl p-8 shadow-sm ResearchRoadmapContainer">
        <div class="flex items-center gap-4 mb-10">
            <div class="w-1.5 h-8 bg-brand-500 rounded-full"></div>
            <h3 class="text-xl font-bold text-gray-900 tracking-tight">Research Roadmap</h3>
        </div>

        <div class="relative px-4">
            <!-- Timeline Track -->
            <div class="absolute top-[26px] left-0 right-0 h-1.5 bg-gray-100 rounded-full"></div>
            
            <div class="relative flex justify-between items-start gap-4 overflow-x-auto pb-4 custom-scrollbar">
                @foreach($milestones as $m)
                    <div class="flex flex-col items-center min-w-[140px] group">
                        <!-- Connector Node -->
                        <div class="relative z-10 w-14 h-14 rounded-2xl flex items-center justify-center border-4 border-white shadow-lg transition-all duration-500 group-hover:scale-110 cursor-pointer"
                             @click="expanded = expanded === '{{ $m->id }}' ? null : '{{ $m->id }}'; if(expanded) $nextTick(() => document.getElementById('milestone-container-' + '{{ $m->id }}').scrollIntoView({ behavior: 'smooth', block: 'center' }))"
                             :class="{ 'bg-green-500 text-white': '{{ $m->status }}' === 'approved', 'bg-brand-500 text-white ring-8 ring-brand-50/50': '{{ $m->id }}' === ongoingMilestoneId, 'bg-white text-gray-400 border-gray-100': '{{ $m->status }}' !== 'approved' && '{{ $m->id }}' !== ongoingMilestoneId }">
                            @if($m->status === 'approved')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                            @elseif($m->id == $ongoingMilestoneId)
                                <div class="w-3 h-3 bg-white rounded-full animate-pulse"></div>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            @endif
                        </div>

                        <!-- Info Area -->
                        <div class="mt-6 text-center max-w-[120px]">
                            <p class="text-[10px] font-bold uppercase tracking-widest leading-none mb-1.5"
                               class="{{ $m->status === 'approved' ? 'text-green-600' : ($m->id == $ongoingMilestoneId ? 'text-brand-600' : 'text-gray-400') }}">
                                Milestone {{ $m->template?->order }}
                            </p>
                            <h4 class="text-xs font-bold text-gray-900 leading-tight group-hover:text-brand-600 transition-colors line-clamp-2">
                                {{ $m->template?->name }}
                            </h4>
                            
                            @if($m->status === 'approved' && $m->approved_at)
                                <p class="text-[9px] font-medium text-gray-400 mt-2 uppercase tracking-tighter">
                                    Validated {{ $m->approved_at->format('M Y') }}
                                </p>
                            @elseif($m->id == $ongoingMilestoneId)
                                <div class="mt-2 inline-flex items-center gap-1.5 px-2 py-0.5 bg-brand-50 text-brand-600 rounded-full border border-brand-100 animate-pulse-subtle">
                                    <span class="w-1 h-1 bg-brand-600 rounded-full"></span>
                                    <span class="text-[9px] font-bold uppercase tracking-widest">Active</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Milestones List -->
    <div id="milestones-list" class="space-y-6">
        @php $foundActive = false; @endphp
        @foreach($milestones as $index => $milestone)
            @php
                $progressData = $milestone->progress_track;
                $isCompleted = $progressData['is_fully_complete'];

                $isPendingMatch = in_array($milestone->status, ['submitted', 'partially_approved']);
                
                $isActive = false;
                if (!$isCompleted && !$foundActive) { 
                    $isActive = true; 
                    $foundActive = true; 
                }

                if ($isCompleted) {
                    $conf = ['color' => 'emerald', 'label' => 'Approved', 'pulse' => false];
                } elseif ($isPendingMatch) {
                    $conf = ['color' => 'amber', 'label' => 'Under Review', 'pulse' => true];
                } elseif ($isActive) {
                    $conf = ['color' => 'blue', 'label' => 'Ongoing', 'pulse' => true];
                } else {
                    $conf = ['color' => 'slate', 'label' => 'Locked', 'pulse' => false];
                }
            @endphp
            
            <div id="milestone-container-{{ $milestone->id }}" class="group/milestone relative bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 {{ $isActive ? 'hover:shadow-md hover:border-gray-200 ring-2 ring-brand-200' : '' }}">
                <!-- Milestone Header -->
                <button type="button"
                        @if($isCompleted)
                            {{-- Past milestones: not clickable --}}
                            type="button"
                        @elseif($isActive || $isPendingMatch)
                            onclick="toggleMilestone('{{ $milestone->id }}')"
                        @else
                            onclick="alert('Institutional Protocol: This milestone is currently locked. You must complete the ongoing phase first.');"
                        @endif
                        class="w-full text-left flex items-center justify-between px-10 py-10 transition-all duration-300 {{ $isCompleted ? 'cursor-default' : ($isActive || $isPendingMatch ? 'cursor-pointer hover:bg-gray-50/30' : 'cursor-not-allowed') }}"
                        {{ $isCompleted ? 'disabled' : '' }}>
                    <div class="flex items-center gap-6">
                        <div class="relative">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-xl font-bold shadow-lg shadow-{{ $conf['color'] }}-500/10 border {{ $isCompleted ? 'bg-emerald-600 border-emerald-500 text-white' : 'bg-white border-gray-100 text-gray-900' }}">
                                {{ $milestone->template?->order }}
                            </div>
                            @if($conf['pulse'])
                                <span class="absolute -top-1 -right-1 flex h-4 w-4">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-{{ $conf['color'] }}-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-4 w-4 bg-{{ $conf['color'] }}-500 border-2 border-white"></span>
                                </span>
                            @endif
                        </div>
                        <div>
                            <p class="text-lg font-bold text-gray-900 group-hover/milestone:text-brand-600 transition-colors tracking-tight">{{ $milestone->template?->name }} @cannot('view', $milestone) <span class="text-xs font-black text-gray-400 ml-2">🔒</span> @endcannot</p>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1.5">{{ $milestone->template?->description }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-8">
                        @if($milestone->due_date)
                            <div class="hidden lg:flex flex-col items-end">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5">Deadline</span>
                                <span class="text-sm font-bold text-gray-900">{{ $milestone->due_date->format('M d, Y') }}</span>
                            </div>
                        @endif
                        
                        <span class="inline-flex items-center px-4 py-2 rounded-xl bg-{{ $conf['color'] }}-50 text-{{ $conf['color'] }}-700 border border-{{ $conf['color'] }}-100 text-xs font-bold uppercase tracking-widest shadow-sm shadow-{{ $conf['color'] }}-500/5">
                            {{ $conf['label'] }}
                        </span>
                        
                        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 group-hover/milestone:bg-brand-50 group-hover/milestone:text-brand-600 transition-all duration-300 transform" :class="expanded === '{{ $milestone->id }}' ? 'rotate-180' : ''">
                            @cannot('view', $milestone)
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
                            @endcannot
                        </div>
                    </div>
                </button>

                <!-- Milestone Body -->
                <div id="milestone-body-{{ $milestone->id }}" class="milestone-body relative z-10 w-full bg-gray-50/50" style="display:none;">
                    @can('view', $milestone)
                        <div class="px-6 md:px-10 py-12 border-t border-gray-50 w-full relative">
                        @php
                            $progressData = $milestone->progress_track;
                            $progressPercent = $progressData['percentage'];
                            $approvedCount = $progressData['completed'];
                            $requiredCount = $progressData['total'];
                            $tasks = $progressData['tasks'];
                        @endphp
                        
                        <div class="flex flex-col lg:flex-row gap-10 items-start">
                            <!-- Left: Institutional Clearance Dashboard (Consistent Sidebar) -->

                            <div class="w-full lg:w-[350px] {{ $milestone->template?->order == 9 ? 'xl:w-[680px]' : '' }} shrink-0 space-y-6">
                                <div class="p-8 bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-200/40 relative overflow-hidden">
                                    <div class="absolute top-0 right-0 w-32 h-32 bg-brand-50 rounded-full -mr-16 -mt-16 opacity-40"></div>
                                    
                                    <div class="relative z-10">
                                        <div class="flex items-center justify-between mb-6">
                                            <div>
                                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Milestone Progress</h3>
                                                <p class="text-xs font-bold text-gray-900 uppercase mt-1">Completion Track</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-2xl font-bold text-brand-600 tracking-tighter">{{ round($progressPercent) }}%</span>
                                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ $approvedCount }}/{{ $requiredCount }} Requirements</p>
                                            </div>
                                        </div>
                                        
                                        <div class="w-full h-2 bg-gray-50 rounded-full overflow-hidden mb-8 border border-gray-100 shadow-inner">
                                            <div class="h-full bg-gradient-to-r from-brand-500 to-brand-600 rounded-full transition-all duration-1000 ease-out" style="width: {{ $progressPercent }}%"></div>
                                        </div>

                                         <div class="space-y-4 relative">
                                            <!-- Vertical Line for Sequence -->
                                            <div class="absolute left-1 top-2 bottom-2 w-0.5 bg-gray-100"></div>

                                             @php $stepIndex = 1; @endphp
                                             @foreach($tasks as $task)
                                                 @php 
                                                     $visuallyCleared = $task['completed'];
                                                     $canClearShortcut = auth()->user()->hasAnyRole(['Admin', 'Program Coordinator', 'Director']) && !$visuallyCleared;
                                                     // If it's a supervisor role and user IS that supervisor, they can also clear.
                                                     if (auth()->user()->hasRole('Supervisor') && isset($task['action_data']['user_id']) && $task['action_data']['user_id'] == auth()->id()) {
                                                         $canClearShortcut = true;
                                                     }
                                                 @endphp
                                                 <div class="flex items-center justify-between p-4 rounded-2xl {{ $visuallyCleared ? 'bg-brand-50/50 border-brand-100 shadow-sm' : 'bg-gray-50 border-gray-100' }} border transition-all group/approval relative z-10 ml-4 group/trackitem">
                                                     <div class="absolute -left-5 top-1/2 -translate-y-1/2 w-3 h-3 rounded-full bg-white border-2 {{ $visuallyCleared ? 'border-brand-500' : 'border-gray-300' }} flex items-center justify-center text-[6px] font-bold {{ $visuallyCleared ? 'text-brand-500' : 'text-gray-400' }}">
                                                         {{ $stepIndex++ }}
                                                     </div>
                                                     <div class="flex items-center gap-3">
                                                         <div class="w-2 h-2 rounded-full {{ $visuallyCleared ? 'bg-brand-500 shadow-[0_0_8px_rgba(14,165,233,0.4)]' : 'bg-gray-300' }} transition-all group-hover/approval:scale-125"></div>
                                                         <div>
                                                             <p class="text-xs font-bold {{ $visuallyCleared ? 'text-brand-700' : 'text-gray-900' }} tracking-tight">{{ $task['name'] }}</p>
                                                             @if(isset($task['details']))
                                                                 <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ $task['details'] }}</p>
                                                             @endif
                                                         </div>
                                                     </div>
                                                     <div class="flex items-center gap-2">
                                                         @if($visuallyCleared)
                                                             <svg class="w-3 h-3 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                             <span class="text-xs font-bold uppercase tracking-widest text-brand-600">Cleared</span>
                                                         @else
                                                                 <span class="text-xs font-bold uppercase tracking-widest text-gray-400 italic">Pending</span>
                                                             @endif
                                                     </div>
                                                 </div>
                                             @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Supervisor Assignment Logic -->
                                @if($milestone->template?->show_supervisor_assignment)
                                    @php $hasAssignments = $thesis->assignments->where('status', 'active')->count() > 0; @endphp
                                    
                                    @if(auth()->user()->hasRole('Student') && !$hasAssignments)
                                        <div class="p-8 bg-amber-50 rounded-3xl border border-amber-100 shadow-sm relative overflow-hidden">
                                            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-100 rounded-full -mr-16 -mt-16 opacity-40"></div>
                                            <div class="relative z-10 text-center py-4">
                                                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-amber-600 shadow-sm mb-4 mx-auto">
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                </div>
                                                <h3 class="text-sm font-bold text-amber-900 uppercase tracking-tight mb-2">Pending Assignment</h3>
                                                <p class="text-[11px] font-medium text-amber-700 leading-relaxed italic">The Program Coordinator is currently processing your supervisor assignments. You will be notified once the panel has been finalized.</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if(auth()->user()->hasRole('Program Coordinator') && !$hasAssignments)
                                        <div class="p-8 bg-brand-50 rounded-3xl border border-brand-100 shadow-sm relative overflow-hidden">
                                            <div class="absolute top-0 right-0 w-32 h-32 bg-brand-100 rounded-full -mr-16 -mt-16 opacity-40"></div>
                                            <div x-data="{ 
                                                selections: [{{ implode(',', array_map(fn($id) => $id ? "'$id'" : "''", array_pad($thesis->proposed_supervisors ?? [], 3, null))) }}],
                                                loading: false,
                                                randomize() {
                                                    this.loading = true;
                                                    fetch('{{ route('theses.assign_supervisor', $thesis) }}', {
                                                        method: 'POST',
                                                        headers: {
                                                            'Content-Type': 'application/json',
                                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                            'Accept': 'application/json'
                                                        },
                                                        body: JSON.stringify({ action: 'redistribute' })
                                                    })
                                                    .then(async res => {
                                                        const data = await res.json();
                                                        if (res.ok && data.success) {
                                                            this.selections = data.proposed_ids;
                                                        } else {
                                                            alert(data.message || 'Institutional Sampling Failed: Insufficient staff capacity in this program.');
                                                        }
                                                        this.loading = false;
                                                    })
                                                    .catch(err => {
                                                        console.error(err);
                                                        alert('Communication Error: Could not reach the redistribution engine.');
                                                        this.loading = false;
                                                    });
                                                }
                                            }">
                                                <div class="flex items-center justify-between gap-3 mb-6">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-8 h-8 rounded-xl bg-brand-100 flex items-center justify-center text-brand-600">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                                        </div>
                                                        <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wider">Assignment Panel</h3>
                                                    </div>
                                                    
                                                    <button type="button" 
                                                            @click="randomize()" 
                                                            :disabled="loading"
                                                            class="p-2 bg-white rounded-lg border border-brand-200 text-brand-600 hover:bg-brand-600 hover:text-white transition-all shadow-sm flex items-center gap-2 group/rand disabled:opacity-50" title="Randomize Institutional Allocation">
                                                        <svg class="w-4 h-4 group-hover/rand:rotate-180 transition-transform duration-500" :class="loading ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                                        <span class="text-xs font-bold uppercase tracking-widest" x-text="loading ? 'Sampling...' : 'Randomize'">Randomize</span>
                                                    </button>
                                                </div>
                                                
                                                <form @submit.prevent="
                                                    loading = true;
                                                    const formData = new FormData($el);
                                                    fetch('{{ route('theses.assign_supervisor', $thesis) }}', {
                                                        method: 'POST',
                                                        body: formData,
                                                        headers: {
                                                            'Accept': 'application/json',
                                                            'X-Requested-With': 'XMLHttpRequest'
                                                        }
                                                    })
                                                    .then(res => res.json())
                                                    .then(data => {
                                                        if (data.success) {
                                                            window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Institutional supervision panel authorized successfully.', type: 'success' } }));
                                                            toggleMilestone('{{ $milestone->id }}');
                                                            window.refreshMilestone('{{ $milestone->id }}');
                                                        } else {
                                                            window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message || 'Authorization failed', type: 'error' } }));
                                                        }
                                                    })
                                                    .finally(() => loading = false);
                                                " action="{{ route('theses.assign_supervisor', $thesis) }}" method="POST" class="space-y-4">
                                                    @csrf
                                                    @php
                                                        $levelName = strtoupper($thesis->student->level->name ?? '');
                                                        $targetCount = str_contains($levelName, 'PHD') ? 3 : 2;
                                                    @endphp
                                                    <p class="text-xs font-bold text-brand-700 mb-4 uppercase tracking-widest">Select Panel Members (Institutional Target: {{ $targetCount }})</p>
                                                    
                                                    @for($i=0; $i < $targetCount; $i++)
                                                        <div class="space-y-1">
                                                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                                                                {{ $i == 0 ? 'Lead (Professor Required)' : ($i == 1 ? 'Secondary Member' : 'Associate Member') }}
                                                            </label>
                                                            <select name="supervisor_ids[]" required 
                                                                    x-model="selections[{{ $i }}]"
                                                                    class="w-full px-4 py-3 rounded-xl bg-white border border-brand-100 text-xs font-semibold focus:ring-2 focus:ring-acetel-500 outline-none">
                                                                <option value="">Choose Facilitator...</option>
                                                                @foreach($allSupervisors as $sup)
                                                                    <option value="{{ $sup->id }}" 
                                                                            {{ ($i == 0 && ($sup->rank ?? '') !== 'Professor') ? 'disabled' : '' }}>
                                                                        {{ $sup->user->name }} [{{ $sup->rank ?? 'Lecturer' }}] (Load: {{ $sup->current_load }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @endfor
                                                    
                                                    <button type="submit" :disabled="loading" class="w-full py-4 mt-4 bg-brand-600 hover:bg-gray-900 text-white rounded-2xl text-xs font-bold uppercase tracking-widest transition-all shadow-lg active:scale-95 disabled:opacity-50">
                                                        <span x-text="loading ? 'Authorizing...' : 'Authorize Completed Panel'"></span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                <!-- Supervisor Details Card (conditionally shown) -->
                                @if($milestone->template?->show_supervisor_details && $thesis->assignments->where('status', 'active')->count() > 0)
                                    <div class="p-8 bg-white rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden">
                                        <div class="absolute top-0 right-0 w-24 h-24 bg-brand-50 rounded-full -mr-12 -mt-12 opacity-40"></div>
                                        <div class="relative z-10">
                                            <div class="flex items-center gap-3 mb-6">
                                                <div class="w-8 h-8 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                                </div>
                                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Supervision Panel</h3>
                                            </div>
                                            <div class="space-y-4">
                                                @foreach($thesis->assignments->where('status', 'active') as $assignment)
                                                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                                                        <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center text-brand-700 text-sm font-bold shrink-0">
                                                            {{ strtoupper(substr($assignment->supervisor->user->name, 0, 2)) }}
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-xs font-bold text-gray-900 tracking-tight truncate">{{ $assignment->supervisor->user->name }}</p>
                                                            <p class="text-xs font-medium text-gray-500 truncate">{{ $assignment->supervisor->user->email }}</p>
                                                            <p class="text-xs font-bold text-brand-500 uppercase tracking-widest mt-1">{{ $assignment->role ?? 'Supervisor' }}</p>
                                                        </div>
                                                        <a href="{{ route('inbox.compose', ['reply_to' => $assignment->supervisor->user_id]) }}" class="p-2 bg-white border border-gray-100 rounded-lg text-gray-400 hover:text-brand-600 hover:border-brand-100 transition-all shadow-sm group-hover:scale-110" title="Send Private Message">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @php
                                    $canUserApprove = auth()->user()->can('review', $milestone) && $milestone->status !== 'approved';
                                @endphp

                                <!-- Dynamic Institutional Progress Tracker -->
                                @php
                                    $dynamicSteps = [];
                                    
                                    // 1. Initial Unlock State
                                    if ($milestone->template?->submission_requires_approval) {
                                        $dynamicSteps[] = [
                                            'id' => 'unlock',
                                            'label' => 'Access',
                                            'responsible' => $milestone->template?->submission_approver_roles[0] ?? 'Supervisor',
                                            'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                                            'check' => function($m) { return $m->is_submission_unlocked; }
                                        ];
                                    }

                                    // 2. Submission Phases (Manuscript & Publication)
                                    if ($milestone->template?->requires_submission) {
                                        $subTypes = $milestone->template?->submission_type ?? ['file'];
                                        
                                        // Manuscript Deposit
                                        if (in_array('file', $subTypes)) {
                                            $dynamicSteps[] = [
                                                'id' => 'submit_manuscript',
                                                'label' => 'Deposit',
                                                'responsible' => 'Student',
                                                'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',
                                                'check' => function($m) { return $m->submissions->where('type', 'manuscript')->count() > 0; }
                                            ];
                                        }

                                        // Publication Deposit (Mandatory for some milestones like M9)
                                        if (in_array('publication', $subTypes)) {
                                            $dynamicSteps[] = [
                                                'id' => 'submit_publication',
                                                'label' => 'Publication',
                                                'responsible' => 'Student',
                                                'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                                                'check' => function($m) { return $m->submissions->where('type', 'publication')->count() > 0; }
                                            ];
                                        }
                                    }

                                    // 3. Similarity Protocol (Plagiarism Result)
                                    if ($milestone->template?->allow_plagiarism_report) {
                                        $dynamicSteps[] = [
                                            'id' => 'plagiarism',
                                            'label' => 'Plagiarism Result',
                                            'responsible' => $milestone->template?->plagiarism_report_role ?? 'Admin',
                                            'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                                            'check' => function($m) { 
                                                return $m->submissions->where('type', 'plagiarism_report')->count() > 0 || 
                                                       $m->submissions->where('type', 'manuscript')->whereNotNull('plagiarism_data')->count() > 0;
                                            }
                                        ];
                                    }

                                    // 4. Examiner Assignment
                                    if ($milestone->template?->show_internal_examiner_assignment) {
                                        $dynamicSteps[] = [
                                            'id' => 'examiner',
                                            'label' => 'Internal Ex.',
                                            'responsible' => 'Admin',
                                            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                                            'check' => function($m) { return !empty($m->thesis->internal_examiner_profile_id); }
                                        ];
                                    }

                                    if ($milestone->template?->show_external_examiner_assignment) {
                                        $dynamicSteps[] = [
                                            'id' => 'ext_examiner',
                                            'label' => 'External Ex.',
                                            'responsible' => 'Admin',
                                            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                                            'check' => function($m) { return !empty($m->thesis->external_examiner_profile_id); }
                                        ];
                                    }

                                    // 5. Defence Protocol
                                    if ($milestone->template?->allow_defence_date) {
                                        $dynamicSteps[] = [
                                            'id' => 'defence',
                                            'label' => 'Defence',
                                            'responsible' => $milestone->template?->defence_date_role ?? 'Admin',
                                            'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                                            'check' => function($m) { return isset($m->metadata['defence_date']); }
                                        ];
                                    }

                                    // 6. Final Clearance (Approval)
                                    if ($milestone->template?->requires_approval) {
                                        $dynamicSteps[] = [
                                            'id' => 'approval',
                                            'label' => 'Clearance',
                                            'responsible' => 'Committee',
                                            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                            'check' => function($m) { return $m->status === 'approved'; }
                                        ];
                                    }

                                    $currentStepIdx = 0;
                                    foreach($dynamicSteps as $idx => $step) {
                                        if ($step['check']($milestone)) {
                                            $currentStepIdx = $idx + 1;
                                        } else {
                                            break;
                                        }
                                    }

                                    $totalSteps = count($dynamicSteps);
                                    $smartProgress = $totalSteps > 0 ? round(($currentStepIdx / $totalSteps) * 100) : 100;
                                    $isTerminal = ($milestone->template?->order == 9 || $milestone->template?->is_final_archival);
                                    $headerLabel = $isTerminal ? 'Final Institutional Graduation Protocol' : 'Standard Progress Track';
                                    $headerSub = $isTerminal ? 'Terminal Archival & Certification' : 'Phase Sequence Index';
                                @endphp

                                @if($totalSteps > 0)
                                    <!-- Dynamic Protocol Card -->
                                    <div class="w-full mb-8 bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                                        <!-- Header -->
                                        <div class="px-8 py-6 bg-gradient-to-r {{ $milestone->template?->order == 9 ? 'from-gray-900 to-gray-800' : 'from-brand-900 to-brand-800' }} text-white relative overflow-hidden">
                                            <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
                                            <div class="relative z-10 flex items-center justify-between">
                                                <div>
                                                    <h2 class="text-lg font-black tracking-tight">{{ $headerLabel }}</h2>
                                                    <p class="text-[10px] font-bold text-white/50 uppercase tracking-[0.2em] mt-1">{{ $headerSub }}</p>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <div class="text-right">
                                                        <p class="text-[9px] font-black text-white/40 uppercase tracking-widest">Progress</p>
                                                        <p class="text-xl font-black tracking-tighter">{{ $smartProgress }}%</p>
                                                    </div>
                                                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/10 flex items-center justify-center">
                                                        @if($milestone->status === 'approved')
                                                            <svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                        @else
                                                            <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Progress Bar -->
                                            <div class="mt-5 w-full h-1.5 bg-white/10 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-1000 ease-out {{ $milestone->status === 'approved' ? 'bg-emerald-400' : 'bg-brand-400' }}" style="width: {{ $smartProgress }}%"></div>
                                            </div>
                                        </div>

                                        <!-- Horizontal Stepper -->
                                        <div class="px-4 py-8">
                                            <div class="flex items-start w-full">
                                                @foreach($dynamicSteps as $idx => $step)
                                                    @php
                                                        $isActive = ($currentStepIdx == $idx);
                                                        $isCompleted = ($currentStepIdx > $idx);
                                                        
                                                        $manuscript = $milestone->submissions->where('type', 'manuscript')->sortByDesc('created_at')->first();
                                                        $pResult = ($step['id'] === 'plagiarism' && $isCompleted && $manuscript) ? $manuscript->plagiarism_data : null;
                                                    @endphp
                                                    <div class="flex-1 flex flex-col items-center text-center group relative">
                                                        <!-- Connector Line -->
                                                        @if($idx > 0)
                                                            <div class="absolute top-5 -left-1/2 w-full h-0.5 {{ $isCompleted ? 'bg-brand-500' : ($isActive ? 'bg-brand-200' : 'bg-gray-100') }} transition-colors"></div>
                                                        @endif

                                                        <!-- Step Circle -->
                                                        <div class="relative z-10 w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 mb-2.5
                                                            {{ $isCompleted ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/25' : ($isActive ? 'bg-white border-2 border-brand-500 text-brand-600 shadow-md ring-4 ring-brand-50' : 'bg-gray-50 border border-gray-200 text-gray-300') }}">
                                                            @if($isCompleted)
                                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                            @else
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}" /></svg>
                                                            @endif
                                                        </div>

                                                        <!-- Label -->
                                                        <p class="text-[10px] font-black uppercase tracking-wider {{ $isCompleted ? 'text-brand-700' : ($isActive ? 'text-brand-600' : 'text-gray-300') }}">{{ $step['label'] }}</p>
                                                        
                                                        @if($pResult)
                                                            <div class="mt-1 flex items-center gap-1 justify-center whitespace-nowrap">
                                                                <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-600 text-[8px] font-black uppercase tracking-tighter border border-emerald-100">{{ $pResult['similarity_score'] ?? '0' }}% Index</span>
                                                            </div>
                                                        @else
                                                            <p class="text-[8px] font-bold uppercase tracking-widest mt-0.5 {{ $isActive ? 'text-brand-400' : 'text-gray-200' }}">{{ $step['responsible'] }}</p>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- ═══ Advanced Institutional Workflows ═══ -->
                                @if(auth()->user()->hasAnyRole(['Admin', 'Director', 'Program Coordinator', 'Internal Examiner']))
                                    @php
                                        $hasDefenceDate = $milestone->template?->allow_defence_date;
                                        $hasExaminerAssign = $milestone->template?->show_internal_examiner_assignment;
                                        $hasExternalExaminerAssign = $milestone->template?->show_external_examiner_assignment;
                                        $showAdminPanel = $hasDefenceDate || $hasExaminerAssign || $hasExternalExaminerAssign;
                                    @endphp

                                    @if($showAdminPanel)
                                    <div class="mb-8 bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-xl bg-brand-100 flex items-center justify-center text-brand-600">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-black text-gray-900 uppercase tracking-widest">Admin Command Center</h4>
                                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Configure institutional workflow settings for this specific protocol</p>
                                            </div>
                                        </div>

                                        <div class="p-6 space-y-5" x-data="{ scheduling: false }">
                                            @php
                                                $lowerName = strtolower($milestone->template?->name);
                                                $dType = 'General';
                                                $dColor = 'brand';
                                                $dIcon = 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z';
                                                
                                                if ($milestone->template?->defence_type === 'proposal') {
                                                    $dType = 'Proposal';
                                                    $dColor = 'blue';
                                                } elseif ($milestone->template?->defence_type === 'internal') {
                                                    $dType = 'Internal';
                                                    $dColor = 'emerald';
                                                } elseif ($milestone->template?->defence_type === 'external') {
                                                    $dType = 'External';
                                                    $dColor = 'rose';
                                                } else {
                                                    if (str_contains($lowerName, 'proposal')) {
                                                        $dType = 'Proposal';
                                                        $dColor = 'blue';
                                                    } elseif (str_contains($lowerName, 'internal') || $milestone->template?->order == 9) {
                                                        $dType = 'Internal';
                                                        $dColor = 'emerald';
                                                    } elseif (str_contains($lowerName, 'external') || $milestone->template?->is_final_archival) {
                                                        $dType = 'External';
                                                        $dColor = 'rose';
                                                    }
                                                }
                                                
                                                // Respect template settings for defence date visibility
                                                $canSetDate = $milestone->template?->allow_defence_date && (auth()->user()->hasRole('Admin') || auth()->user()->hasRole($milestone->template?->defence_date_role ?? 'Program Coordinator'));
                                            @endphp

                                            @if($canSetDate)
                                                <div class="p-8 rounded-[2rem] bg-{{ $dColor }}-50/50 border border-{{ $dColor }}-100 group/defence text-center">
                                                    <div class="flex flex-col items-center max-w-sm mx-auto">
                                                        <div class="w-20 h-20 rounded-[2rem] bg-white border border-{{ $dColor }}-200 flex items-center justify-center text-{{ $dColor }}-600 shadow-xl mb-6 group-hover/defence:rotate-6 transition-all duration-500">
                                                            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                        </div>
                                                        
                                                        @php
                                                            $isInternal = ($dType === 'Internal' || $milestone->template?->order == 9);
                                                            $examLabel = $isInternal ? 'Internal Examination' : ($dType === 'External' ? 'External Defence' : ($dType === 'Proposal' ? 'Proposal Defence' : 'Examination'));
                                                            $isDateExpired = $milestone->defence_date && \Carbon\Carbon::parse($milestone->defence_date)->endOfDay()->isPast() && $milestone->status !== 'approved';
                                                        @endphp
                                                        <div class="px-5 py-2 rounded-xl bg-{{ $isDateExpired ? 'red' : $dColor }}-100 mb-6 border border-{{ $isDateExpired ? 'red' : $dColor }}-200">
                                                            <span class="text-[10px] font-black text-{{ $isDateExpired ? 'red' : $dColor }}-700 uppercase tracking-[0.2em]">{{ $isDateExpired ? 'Schedule Expired' : $examLabel . ' Protocol' }}</span>
                                                        </div>

                                                        <h5 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-2">{{ $isDateExpired ? 'Set New Date' : 'Authorize & Schedule Protocol' }}</h5>
                                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-8 leading-relaxed">
                                                            @if($isDateExpired)
                                                                <span class="text-red-500 font-bold">The scheduled date has passed without approval.</span><br> Please schedule a new date to continue.
                                                            @else
                                                                Grant institutional authorization for the <span class="text-{{ $dColor }}-600 font-black">{{ $examLabel }}</span>. <br> Stakeholders will be notified upon confirmation.
                                                            @endif
                                                        </p>

                                                        <form @submit.prevent="
                                                            scheduling = true;
                                                            fetch('{{ route('milestones.set_defence_date', $milestone) }}', {
                                                                method: 'POST',
                                                                headers: {
                                                                    'Content-Type': 'application/json',
                                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                                    'Accept': 'application/json'
                                                                },
                                                                body: JSON.stringify({ 
                                                                    defence_date: $el.querySelector('input[name=defence_date]').value
                                                                })
                                                            })
                                                            .then(res => res.json())
                                                            .then(data => {
                                                                if (data.success) {
                                                                    toggleMilestone('{{ $milestone->id }}');
                                                                    window.refreshMilestone('{{ $milestone->id }}'); 
                                                                } else {
                                                                    window.dispatchEvent(new CustomEvent('notify', { 
                                                                        detail: { message: data.message || 'Verification failed', type: 'error' } 
                                                                    }));
                                                                }
                                                            })
                                                            .finally(() => scheduling = false)
                                                        " x-data="{ localDate: '{{ $milestone->defence_date && !$isDateExpired ? $milestone->defence_date->format('Y-m-d') : '' }}' }" class="w-full">
                                                            
                                                            <div class="relative group/input">
                                                                <!-- Visual Trigger Button -->
                                                                <button type="button" 
                                                                    @click="$refs.datePicker.showPicker()"
                                                                    class="w-full py-5 px-8 bg-{{ $isDateExpired ? 'red' : $dColor }}-600 text-white text-xs font-black uppercase tracking-[0.3em] rounded-2xl hover:bg-gray-900 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-[0_15px_30px_-10px_rgba(0,0,0,0.2)] flex items-center justify-center gap-3">
                                                                    <span x-text="localDate ? 'Reschedule Protocol' : 'Authorize & Select Date'"></span>
                                                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                                                </button>

                                                                <!-- Hidden actual date input that triggers on button click -->
                                                                <input type="date" 
                                                                    x-ref="datePicker"
                                                                    name="defence_date" 
                                                                    x-model="localDate"
                                                                    @change="if(localDate) $event.target.form.requestSubmit()"
                                                                    class="absolute inset-0 opacity-0 pointer-events-none" 
                                                                    required>
                                                            </div>

                                                            <div x-show="scheduling" class="mt-4 flex items-center justify-center gap-2 text-{{ $dColor }}-600">
                                                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                                <span class="text-[10px] font-black uppercase tracking-widest">Broadcasting to stakeholders...</span>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endif

                                            @if($hasExaminerAssign && empty($thesis->internal_examiner_profile_id))
                                            <!-- Internal Examiner Assignment -->
                                            <div class="p-5 rounded-2xl bg-amber-50/60 border border-amber-100">
                                                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 rounded-xl bg-white border border-amber-200 flex items-center justify-center text-amber-600 shadow-sm shrink-0">
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                        </div>
                                                        <div>
                                                            <h5 class="text-xs font-bold text-amber-900 uppercase tracking-widest">Assign Internal Examiner</h5>
                                                            <p class="text-[9px] font-bold text-amber-500 uppercase tracking-widest mt-0.5">Required before defence</p>
                                                        </div>
                                                    </div>
                                                    <form action="{{ route('theses.assign_internal_examiner', $thesis) }}" method="POST" 
                                                        @submit.prevent="
                                                            fetch($event.target.action, {
                                                                method: 'POST',
                                                                body: new FormData($event.target),
                                                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                                                            }).then(res => res.json()).then(data => {
                                                                if (data.success) window.refreshMilestone('{{ $milestone->id }}');
                                                            });
                                                        " class="flex items-center gap-2 w-full md:w-auto">
                                                        @csrf
                                                        <select name="internal_examiner_profile_id" required class="flex-1 md:w-52 bg-white border border-amber-200 rounded-xl px-3 py-2.5 text-sm focus:border-amber-500 shadow-sm">
                                                            <option value="" disabled selected>Select Examiner...</option>
                                                            @php $examiners = \App\Models\InternalExaminerProfile::with('user')->where('active', true)->get(); @endphp
                                                            @foreach($examiners as $examiner)
                                                                <option value="{{ $examiner->id }}">{{ $examiner->user->name ?? 'Unknown' }} ({{ $examiner->department ?? 'N/A' }})</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="px-5 py-2.5 bg-amber-600 text-white text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-amber-700 transition-all shadow-sm shrink-0">
                                                            Assign
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            @endif

                                            @if($hasExternalExaminerAssign && empty($thesis->external_examiner_profile_id))
                                            <!-- External Examiner Assignment -->
                                            <div class="p-5 rounded-2xl bg-rose-50/60 border border-rose-100 mt-4">
                                                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 rounded-xl bg-white border border-rose-200 flex items-center justify-center text-rose-600 shadow-sm shrink-0">
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                        </div>
                                                        <div>
                                                            <h5 class="text-xs font-bold text-rose-900 uppercase tracking-widest">Assign External Examiner</h5>
                                                            <p class="text-[9px] font-bold text-rose-500 uppercase tracking-widest mt-0.5">Required before defence</p>
                                                        </div>
                                                    </div>
                                                    <form action="{{ route('theses.assign_external_examiner', $thesis) }}" method="POST" 
                                                        @submit.prevent="
                                                            fetch($event.target.action, {
                                                                method: 'POST',
                                                                body: new FormData($event.target),
                                                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                                                            }).then(res => res.json()).then(data => {
                                                                if (data.success) window.refreshMilestone('{{ $milestone->id }}');
                                                            });
                                                        " class="flex items-center gap-2 w-full md:w-auto">
                                                        @csrf
                                                        <select name="external_examiner_profile_id" required class="flex-1 md:w-52 bg-white border border-rose-200 rounded-xl px-3 py-2.5 text-sm focus:border-rose-500 shadow-sm">
                                                            <option value="" disabled selected>Select Examiner...</option>
                                                            @php $externalExaminers = \App\Models\ExternalExaminerProfile::with('user')->get(); @endphp
                                                            @foreach($externalExaminers as $examiner)
                                                                <option value="{{ $examiner->id }}">{{ $examiner->user->name ?? 'Unknown' }} ({{ $examiner->institution ?? 'N/A' }})</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="px-5 py-2.5 bg-rose-600 text-white text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-rose-700 transition-all shadow-sm shrink-0">
                                                            Assign
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            @endif

                                            @php
                                                $isPlagiarismEnabled = $milestone->template?->allow_plagiarism_report;
                                                $canUploadPlagiarism = $isPlagiarismEnabled && (auth()->user()->hasRole('Admin') || auth()->user()->hasRole($milestone->template?->plagiarism_report_role ?? 'Admin'));
                                                $hasPlagiarismReport = $milestone->submissions->where('type', 'plagiarism_report')->count() > 0 || 
                                                                       $milestone->submissions->where('type', 'manuscript')->whereNotNull('plagiarism_data')->count() > 0;
                                            @endphp
                                            
                                            @if($isPlagiarismEnabled)
                                            <!-- Plagiarism Verification Protocol -->
                                            <div class="p-6 rounded-3xl bg-amber-50/50 border border-amber-100/50 relative overflow-hidden group/plagiarism">
                                                <div class="flex items-center gap-4 relative z-10">
                                                    <div class="w-12 h-12 rounded-2xl bg-white border border-amber-200 flex items-center justify-center text-amber-600 shadow-sm group-hover/plagiarism:scale-110 transition-transform">
                                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <h5 class="text-xs font-black text-amber-900 uppercase tracking-widest">Similarity Certification</h5>
                                                        <p class="text-[9px] font-bold text-amber-600/60 uppercase tracking-widest mt-0.5">
                                                            {{ $hasPlagiarismReport ? 'Protocol Cleared: Valid Result Indexed' : 'Official Plagiarism Verification Protocol Required' }}
                                                        </p>
                                                    </div>
                                                    
                                                    @if($hasPlagiarismReport)
                                                        @php 
                                                            $latestPlag = $milestone->submissions->where('type', 'plagiarism_report')->sortByDesc('created_at')->first() ?? 
                                                                         $milestone->submissions->where('type', 'manuscript')->whereNotNull('plagiarism_data')->sortByDesc('created_at')->first();
                                                            $score = null;
                                                            $url = null;
                                                            if ($latestPlag) {
                                                                if ($latestPlag->type === 'plagiarism_report') {
                                                                    $score = $latestPlag->file_meta['similarity_score'] ?? 'N/A';
                                                                    $url = Storage::url($latestPlag->file_url);
                                                                } else {
                                                                    $score = $latestPlag->plagiarism_data['similarity_score'] ?? 'N/A';
                                                                    $url = Storage::url($latestPlag->plagiarism_data['report_path'] ?? $latestPlag->file_url);
                                                                }
                                                            }
                                                        @endphp
                                                        <div class="flex items-center gap-2">
                                                            <div class="px-3 py-1.5 bg-white border border-amber-200 rounded-xl shadow-sm">
                                                                <span class="text-[10px] font-black text-amber-900 uppercase tracking-widest">{{ $score }}% Index</span>
                                                            </div>
                                                            <button type="button" 
                                                                @click.prevent="$dispatch('open-document-preview', { 
                                                                    url: '{{ route('submissions.view', $latestPlag) }}', 
                                                                    title: 'Similarity Certificate',
                                                                    type: 'pdf'
                                                                })"
                                                                class="p-2.5 bg-amber-600 text-white rounded-xl hover:bg-gray-900 transition-all shadow-sm">
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                            </button>
                                                        </div>
                                                    @elseif($canUploadPlagiarism)
                                                        <form x-data="{ uploading: false }" @submit.prevent="
                                                            uploading = true;
                                                            let formData = new FormData($event.target);
                                                            fetch('{{ route('milestones.upload_plagiarism', $milestone) }}', {
                                                                method: 'POST',
                                                                body: formData,
                                                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                                                            })
                                                            .then(res => res.json())
                                                            .then(data => {
                                                                if (data.success) {
                                                                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Similarity Certified: Protocol cleared.', type: 'success' } }));
                                                                    toggleMilestone('{{ $milestone->id }}');
                                                                    window.refreshMilestone('{{ $milestone->id }}');
                                                                } else {
                                                                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.message || 'Upload failed', type: 'error' } }));
                                                                }
                                                            })
                                                            .finally(() => uploading = false);
                                                        " class="flex items-center gap-3">
                                                            <div class="flex items-center bg-white border border-amber-200 rounded-xl px-3 py-2 shadow-sm focus-within:ring-2 focus-within:ring-amber-500/20 transition-all">
                                                                <input type="number" step="0.1" name="similarity_score" min="0" max="100" placeholder="Index %" class="w-16 border-0 p-0 text-[10px] font-black tracking-widest text-amber-900 focus:ring-0 bg-transparent" required>
                                                                <div class="w-px h-3 bg-amber-100 mx-2"></div>
                                                                <div class="relative">
                                                                    <input type="file" name="plagiarism_report" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="if($event.target.files[0]) { /* only submit if score is also present */ if($el.closest('form').querySelector('input[name=similarity_score]').value) $el.closest('form').requestSubmit() }">
                                                                    <div class="text-[10px] font-black uppercase tracking-widest text-amber-700 hover:text-amber-900 transition-all flex items-center gap-2">
                                                                        <svg x-show="!uploading" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                                                        <svg x-show="uploading" style="display: none;" class="animate-spin h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                                        <span x-text="uploading ? 'Processing...' : 'Certify Index'"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    @else
                                                        <span class="text-[9px] font-black text-amber-400 uppercase tracking-widest italic">Awaiting Inst. Audit</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                @endif

                                <!-- Assigned Examiner Display (All Roles) -->
                                @if($milestone->template?->show_internal_examiner_assignment && $thesis->internalExaminer)
                                    <div class="mb-6 flex items-center gap-4 p-5 bg-white rounded-2xl border border-gray-100 shadow-sm">
                                        <div class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600 shrink-0">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[9px] font-black text-brand-500 uppercase tracking-[0.2em]">Internal Examiner</p>
                                            <p class="text-sm font-bold text-gray-900 truncate">{{ $thesis->internalExaminer->user->name ?? 'Not Available' }}</p>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ $thesis->internalExaminer->department ?? 'Department' }}</p>
                                        </div>
                                        <a href="{{ route('inbox.compose', ['reply_to' => $thesis->internalExaminer->user_id]) }}" class="p-2.5 bg-gray-50 border border-gray-100 rounded-xl text-gray-400 hover:text-brand-600 hover:bg-brand-50 hover:border-brand-100 transition-all" title="Message Examiner">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        </a>
                                    </div>
                                @endif

                                @if($milestone->template?->show_external_examiner_assignment && $thesis->externalExaminer)
                                    <div class="mb-6 flex items-center gap-4 p-5 bg-white rounded-2xl border border-gray-100 shadow-sm">
                                        <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[9px] font-black text-rose-500 uppercase tracking-[0.2em]">External Examiner</p>
                                            <p class="text-sm font-bold text-gray-900 truncate">{{ $thesis->externalExaminer->user->name ?? 'Not Available' }}</p>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ $thesis->externalExaminer->institution ?? 'Institution' }}</p>
                                        </div>
                                        <a href="{{ route('inbox.compose', ['reply_to' => $thesis->externalExaminer->user_id]) }}" class="p-2.5 bg-gray-50 border border-gray-100 rounded-xl text-gray-400 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-100 transition-all" title="Message Examiner">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        </a>
                                    </div>
                                @endif

                                <!-- Scheduled Defence Date Display (All Roles) -->
                                @if($milestone->template?->allow_defence_date && $milestone->defence_date)
                                    <div class="mb-6 flex items-center gap-4 p-5 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                                        <div class="w-12 h-12 rounded-2xl bg-white border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm shrink-0">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                        <div>
                                            <p class="text-[9px] font-black text-emerald-600 uppercase tracking-[0.2em]">Scheduled Defence</p>
                                            <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $milestone->defence_date->format('l, F j, Y') }}</p>
                                        </div>
                                    </div>
                                @endif

                                <!-- Post Submission Approval Command (Unlock) -->
                                @if(auth()->user()->can('unlock', $milestone)  && !$milestone->is_submission_unlocked)
                                    <div class="p-6 bg-gray-50 rounded-3xl border border-amber-100 border-dashed">
                                        <form x-data="{ unlocking: false }" @submit.prevent="
                                            unlocking = true;
                                            fetch('{{ route('milestones.unlock', $milestone) }}', {
                                                method: 'POST',
                                                body: new FormData($event.target),
                                                headers: {
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                }
                                            }).then(res => res.json()).then(data => {
                                                if (data.success) {
                                                    window.refreshMilestone('{{ $milestone->id }}');
                                                } else {
                                                    window.dispatchEvent(new CustomEvent('notify', { 
                                                        detail: { message: data.message || 'Unlock failed', type: 'error' } 
                                                    }));
                                                }
                                            }).finally(() => {
                                                unlocking = false;
                                            })
                                        " action="{{ route('milestones.unlock', $milestone) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                :disabled="unlocking"
                                                class="w-full py-5 bg-amber-600 hover:bg-gray-900 text-white rounded-3xl text-xs font-bold uppercase tracking-wider transition-all shadow-xl shadow-amber-500/20 active:scale-95 flex items-center justify-center gap-3 group disabled:opacity-50 disabled:cursor-not-allowed">
                                                <svg x-show="!unlocking" class="w-4 h-4 group-hover:rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                                <svg x-show="unlocking" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span x-text="unlocking ? 'Authenticating...' : 'Unlock Submission'"></span>
                                            </button>
                                        </form>
                                        <p class="mt-3 text-xs font-bold text-gray-400 text-center uppercase tracking-widest leading-relaxed">Required before student can submit</p>
                                    </div>
                                @endif

                                @php
                                    $userApproverRoles = $milestone->template?->required_approvers ?? ['Program Coordinator'];
                                    $userCanEverApprove = false;
                                    $matchedRole = null;
                                    foreach($userApproverRoles as $r) {
                                        if(auth()->user()->hasRole($r) || auth()->user()->hasRole('Admin')) { 
                                            $userCanEverApprove = true; 
                                            $matchedRole = auth()->user()->hasRole('Admin') ? $r : $r;
                                            break; 
                                        }
                                    }
                                @endphp

                                @if($userCanEverApprove && $milestone->status !== 'approved')
                                    @if($canUserApprove)
                                        @php
                                            $blockApprovalUnlock = $milestone->template?->submission_requires_approval && !$milestone->is_submission_unlocked;
                                            $blockApprovalSubmission = $milestone->template?->requires_submission && $milestone->submissions->count() === 0;
                                            $blockApproval = $blockApprovalUnlock || $blockApprovalSubmission;
                                            $blockReason = $blockApprovalSubmission ? 'Awaiting Student Submission' : 'Submit form not unlocked';
                                        @endphp
                                        
                                        @if($blockApproval)
                                            <div class="w-full p-5 bg-gray-100 text-gray-400 rounded-3xl text-xs font-bold uppercase tracking-wider flex flex-col items-center justify-center gap-2 border border-gray-200 cursor-not-allowed opacity-60" title="Prerequisites must be met before institutional clearance.">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                                    <span>Approval Gated</span>
                                                </div>
                                                <p class="text-xs tracking-widest font-bold opacity-60 italic">{{ $blockReason }}</p>
                                            </div>
                                        @else
                                            <form x-data="{ approving: false }" @submit.prevent="
                                                approving = true;
                                                fetch('{{ route('milestones.approve', $milestone) }}', {
                                                    method: 'POST',
                                                    body: new FormData($event.target),
                                                    headers: {
                                                        'Accept': 'application/json',
                                                        'X-Requested-With': 'XMLHttpRequest'
                                                    }
                                                }).then(res => res.json()).then(data => {
                                                    if (data.success) {
                                                        toggleMilestone('{{ $milestone->id }}');
                                                        window.refreshMilestone('{{ $milestone->id }}');
                                                    }
                                                }).finally(() => {
                                                    approving = false;
                                                })
                                            " action="{{ route('milestones.approve', $milestone) }}" method="POST">
                                                @csrf
                                                <button type="submit" 
                                                    :disabled="approving"
                                                    class="w-full py-5 bg-emerald-600 hover:bg-gray-900 text-white rounded-3xl text-xs font-bold uppercase tracking-wider transition-all shadow-xl shadow-emerald-500/20 active:scale-95 flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <svg x-show="!approving" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                                    <svg x-show="approving" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <span x-text="approving ? 'Authorizing...' : 'Approve Milestone'"></span>
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        @php
                                            $blockReason = app(\App\Services\MilestoneWorkflowService::class)->getApprovalBlockReason($milestone, auth()->user(), $matchedRole);
                                        @endphp
                                        <div class="w-full p-6 bg-amber-50 rounded-3xl border border-amber-100 flex flex-col items-center justify-center gap-3 text-center">
                                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-amber-500 shadow-sm border border-amber-100">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                            </div>
                                            <div>
                                                <h5 class="text-[10px] font-black text-amber-900 uppercase tracking-widest">Workflow Sequence Gated</h5>
                                                <p class="text-[9px] font-bold text-amber-600 uppercase tracking-widest mt-1 leading-relaxed">{{ $blockReason ?? 'Prerequisites Pending' }}</p>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                                

                            </div>

                            <!-- Right: Activity & Artifact Hub -->
                            <div class="flex-1 space-y-8">


                                <!-- Files Repository -->
                                <div class="p-10 bg-white rounded-[3rem] border border-gray-100 shadow-sm relative overflow-hidden">
                                     <div class="absolute top-0 right-0 w-48 h-48 bg-gray-50 rounded-full -mr-24 -mt-24 opacity-30"></div>
                                     
                                     <div class="relative z-10 space-y-8">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h4 class="text-xs font-bold uppercase tracking-widest text-gray-400">Files Repository</h4>
                                                <p class="text-xs font-bold text-gray-900 uppercase mt-1">File Management</p>
                                            </div>
                                            <div class="px-4 py-2 bg-gray-50 rounded-xl border border-gray-100 text-xs font-bold text-gray-400 uppercase tracking-widest">
                                                {{ $milestone->submissions->count() }} Submissions
                                            </div>
                                        </div>

                                        @if(auth()->user()->hasRole('Student') && $milestone->thesis->student->user_id === auth()->id() && $milestone->status !== 'approved' && $milestone->template?->requires_submission)
                                            @php
                                                $isUnlocked = !$milestone->template?->submission_requires_approval || $milestone->is_submission_unlocked;
                                            @endphp
                                            
                                            @if($isUnlocked)
                                                <!-- Dynamic Institutional Submission Bar -->
                                                <div class="p-8 bg-brand-50/30 rounded-[2.5rem] border border-brand-100 border-dashed transition-all hover:bg-brand-50/50 shadow-inner">
                                                    <form x-data="{ uploading: false, progress: 0, fileLoaded: false, publicationLoaded: false, pptLoaded: false }" @submit.prevent="
                                                        const maxBytes = 50 * 1024 * 1024;
                                                        const files = $el.querySelectorAll('input[type=file]');
                                                        for (let f of files) {
                                                            if (f.files[0] && f.files[0].size > maxBytes) {
                                                                alert('Institutional Restriction: File size exceeds the 50MB limit. Please optimize your document and try again.');
                                                                return;
                                                            }
                                                        }

                                                        uploading = true;
                                                        progress = 0;
                                                        
                                                        const xhr = new XMLHttpRequest();
                                                        const formData = new FormData($event.target);

                                                        xhr.upload.onprogress = (e) => {
                                                            if (e.lengthComputable) {
                                                                progress = Math.round((e.loaded / e.total) * 100);
                                                            }
                                                        };

                                                        xhr.onload = function() {
                                                            uploading = false;
                                                            if (xhr.status >= 200 && xhr.status < 300) {
                                                                const data = JSON.parse(xhr.responseText);
                                                                window.dispatchEvent(new CustomEvent('notify', { 
                                                                    detail: { 
                                                                        message: 'Scholarly Artifact Indexed: Your submission has been successfully uploaded and broadcasted.',
                                                                        type: 'success' 
                                                                    } 
                                                                }));
                                                                window.refreshMilestone('{{ $milestone->id }}');
                                                            } else {
                                                                let errorMsg = 'Submission Failed';
                                                                if (xhr.status === 413) {
                                                                    errorMsg = 'Institutional Error (413): File is too large for the current server configuration. Even if the system allows 50MB, the web server (Nginx/PHP) might be restricted to a lower value (e.g. 2MB or 8MB). Please contact Admin to increase php.ini limits.';
                                                                } else {
                                                                    try {
                                                                        const data = JSON.parse(xhr.responseText);
                                                                        errorMsg = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Unknown Validation Error');
                                                                    } catch(e) {
                                                                        errorMsg = 'System Error (' + xhr.status + '): ' + xhr.statusText;
                                                                    }
                                                                }
                                                                window.dispatchEvent(new CustomEvent('notify', { detail: { message: errorMsg, type: 'error' } }));
                                                            }
                                                        };

                                                        xhr.onerror = function() {
                                                            uploading = false;
                                                            window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Network or System Connection Error', type: 'error' } }));
                                                        };

                                                        xhr.open('POST', '{{ route('milestones.store', $milestone) }}');
                                                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                                                        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                                                        xhr.send(formData);
                                                    ">
                                                        @csrf
                                                        <div class="flex flex-col gap-6 relative">
                                                            <!-- Progress Tracker (Absolute Top) -->
                                                            <div x-show="uploading" style="display: none;" class="absolute -top-12 left-0 right-0 bg-white/80 backdrop-blur-md rounded-2xl p-4 shadow-xl border border-brand-100 z-50 animate-in fade-in slide-in-from-top-4 duration-300">
                                                                <div class="flex items-center justify-between mb-2">
                                                                    <div class="flex items-center gap-2">
                                                                        <svg class="animate-spin h-3.5 w-3.5 text-brand-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                                        <span class="text-[10px] font-black text-brand-900 uppercase tracking-widest">Transmitting Schorlarly Data...</span>
                                                                    </div>
                                                                    <span class="text-[10px] font-black text-brand-600" x-text="progress + '%'"></span>
                                                                </div>
                                                                <div class="w-full h-1.5 bg-brand-50 rounded-full overflow-hidden">
                                                                    <div class="h-full bg-brand-600 rounded-full transition-all duration-300" :style="'width: ' + progress + '%'"></div>
                                                                </div>
                                                            </div>

                                                        <div class="grid grid-cols-1 {{ count($milestone->template?->submission_type ?? []) > 1 || $milestone->template?->is_final_archival ? 'md:grid-cols-2' : '' }} gap-6">
                                                            <!-- Milestone 13 Extra Fields -->
                                                            @if($milestone->template?->is_final_archival)
                                                                <div class="md:col-span-2 space-y-6 bg-white/40 p-10 rounded-[2.5rem] border border-brand-100/50 shadow-sm">
                                                                    <div>
                                                                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Final Thesis Title</label>
                                                                        <input type="text" name="title" value="{{ $milestone->thesis->title }}" required
                                                                            class="w-full px-6 py-4 bg-white border border-gray-100 rounded-[1.5rem] text-sm font-bold text-gray-900 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all outline-none">
                                                                    </div>
                                                                    
                                                                    <!-- Supervisors (Read Only) -->
                                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                                                        @foreach($milestone->thesis->assignments as $assignment)
                                                                            <div>
                                                                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">{{ $assignment->role }}</label>
                                                                                <input type="text" value="{{ $assignment->supervisor?->user?->name ?? 'N/A' }}" readonly disabled
                                                                                    class="w-full px-6 py-4 bg-gray-50 border border-gray-200 rounded-[1.5rem] text-sm font-bold text-gray-500 cursor-not-allowed outline-none">
                                                                            </div>
                                                                        @endforeach
                                                                    </div>

                                                                    <div>
                                                                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Extended Abstract</label>
                                                                        <textarea name="abstract" rows="8" required
                                                                            class="w-full px-6 py-6 bg-white border border-gray-100 rounded-[1.5rem] text-sm font-medium text-gray-600 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all outline-none leading-relaxed">{{ $milestone->thesis->abstract }}</textarea>
                                                                    </div>

                                                                    <div>
                                                                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Keywords (Comma separated)</label>
                                                                        <input type="text" name="keywords" value="{{ $milestone->thesis->keywords }}" placeholder="e.g., Artificial Intelligence, Machine Learning, Education"
                                                                            class="w-full px-6 py-4 bg-white border border-gray-100 rounded-[1.5rem] text-sm font-medium text-gray-600 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all outline-none">
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <!-- PPT / Presentation Upload -->
                                                            @if(in_array('ppt', $milestone->template?->submission_type ?? []))
                                                            <div class="relative group/ppt">
                                                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">
                                                                    PPT / Presentation Slide Deck (Mandatory)
                                                                </label>
                                                                <input type="file" name="ppt" accept=".pdf"
                                                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" required 
                                                                    @change="pptLoaded = !!$event.target.files[0]">
                                                                <div class="px-6 py-6 bg-white rounded-3xl border border-gray-100 border-dotted flex flex-col items-center justify-center gap-2 group-hover/ppt:border-brand-500 transition-all shadow-sm"
                                                                    :class="pptLoaded ? 'bg-brand-50 border-brand-500' : ''">
                                                                    <svg class="w-6 h-6" :class="pptLoaded ? 'text-brand-600' : 'text-gray-300'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 12l3-3 3 3M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                                                    <span class="text-[10px] font-bold uppercase tracking-widest" :class="pptLoaded ? 'text-brand-800' : 'text-gray-400'" x-text="pptLoaded ? 'Presentation Ready' : 'Attach Presentation'"></span>
                                                                </div>
                                                            </div>
                                                            @endif

                                                            <!-- File Upload -->
                                                            @if(in_array('file', $milestone->template?->submission_type ?? []))
                                                            <div class="relative group/ms">
                                                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">
                                                                    @if($milestone->template?->is_final_archival)
                                                                        Final Library Copy (PDF Only)
                                                                    @else
                                                                        {{ count($milestone->template?->submission_type) > 1 ? 'Thesis Manuscript' : 'Working Document' }} (Mandatory)
                                                                    @endif
                                                                </label>
                                                                <input type="file" name="file" accept=".pdf"
                                                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" required 
                                                                    @change="fileLoaded = !!$event.target.files[0]">
                                                                <div class="px-6 py-6 bg-white rounded-3xl border border-gray-100 border-dotted flex flex-col items-center justify-center gap-2 group-hover/ms:border-brand-500 transition-all shadow-sm"
                                                                    :class="fileLoaded ? 'bg-brand-50 border-brand-500' : ''">
                                                                    <svg class="w-6 h-6" :class="fileLoaded ? 'text-brand-600' : 'text-gray-300'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                                                    <span class="text-[10px] font-bold uppercase tracking-widest" :class="fileLoaded ? 'text-brand-800' : 'text-gray-400'" x-text="fileLoaded ? 'File Ready' : 'Attach Document'"></span>
                                                                </div>
                                                            </div>
                                                            @endif

                                                            <!-- Publication Upload: Smart Multi-File Manager -->
                                                            @if(in_array('publication', $milestone->template?->submission_type ?? []))
                                                            @php
                                                                $pubCountStored = $milestone->submissions->where('type', 'publication')->count();
                                                                $maxRemaining = 5 - $pubCountStored;
                                                            @endphp
                                                            <div class="flex flex-col space-y-4" x-data="{ 
                                                                files: [], 
                                                                maxFiles: {{ $maxRemaining }},
                                                                addFiles(e) {
                                                                    let newFiles = Array.from(e.target.files);
                                                                    if (this.files.length + newFiles.length > this.maxFiles) {
                                                                        window.dispatchEvent(new CustomEvent('notify', { 
                                                                            detail: { message: 'Capacity Violation: You can only upload ' + this.maxFiles + ' more publications.', type: 'error' } 
                                                                        }));
                                                                        return;
                                                                    }
                                                                    this.files = [...this.files, ...newFiles];
                                                                    this.sync();
                                                                },
                                                                removeFile(index) {
                                                                    this.files.splice(index, 1);
                                                                    this.sync();
                                                                },
                                                                sync() {
                                                                    const dt = new DataTransfer();
                                                                    this.files.forEach(f => dt.items.add(f));
                                                                    this.$refs.pubInput.files = dt.files;
                                                                    publicationLoaded = this.files.length > 0;
                                                                },
                                                                formatSize(bytes) {
                                                                    if (bytes === 0) return '0 Bytes';
                                                                    const k = 1024;
                                                                    const sizes = ['Bytes', 'KB', 'MB'];
                                                                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                                                                    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
                                                                }
                                                            }">
                                                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Institutional Publications Repository</label>
                                                                
                                                                <div class="relative group/pub">
                                                                    <input type="file" name="publications[]" multiple x-ref="pubInput"
                                                                        accept=".pdf"
                                                                        class="absolute inset-0 w-full h-full opacity-0 {{ $maxRemaining <= 0 ? 'cursor-not-allowed' : 'cursor-pointer' }} z-20" 
                                                                        {{ $pubCountStored > 0 ? '' : 'required' }}
                                                                        {{ $maxRemaining <= 0 ? 'disabled' : '' }}
                                                                        @change="addFiles($event)">
                                                                    
                                                                    <div class="px-8 py-10 bg-white rounded-[2.5rem] border-2 border-gray-100 border-dashed flex flex-col items-center justify-center gap-4 group-hover/pub:border-emerald-500 transition-all duration-500 shadow-sm relative overflow-hidden"
                                                                        :class="files.length > 0 ? 'bg-emerald-50/30 border-emerald-500' : ({{ $maxRemaining <= 0 ? 'true' : 'false' }} ? 'bg-gray-50 opacity-50' : '')">
                                                                        
                                                                        @if($maxRemaining > 0)
                                                                        <!-- Animated Background Element -->
                                                                        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-100 rounded-full blur-3xl opacity-0 group-hover/pub:opacity-40 transition-opacity"></div>
                                                                        
                                                                        <div class="w-16 h-16 rounded-2xl bg-white border border-gray-100 flex items-center justify-center text-gray-300 group-hover/pub:text-emerald-600 group-hover/pub:scale-110 group-hover/pub:rotate-3 transition-all duration-500 shadow-sm">
                                                                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                                                        </div>
                                                                        
                                                                        <div class="text-center">
                                                                            <span class="text-sm font-black text-gray-900 block" x-text="files.length > 0 ? 'Add More Artifacts' : 'Deposit Scholarly Publications'"></span>
                                                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1 block">
                                                                                {{ $pubCountStored > 0 ? $pubCountStored . ' already indexed • Capacity: ' . $maxRemaining . ' more' : 'Maximum 5 files • PDF, DOC, Image formats' }}
                                                                            </span>
                                                                        </div>
                                                                        @else
                                                                        <div class="text-center py-4">
                                                                            <svg class="w-12 h-12 text-rose-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                                                            <span class="text-xs font-black text-rose-500 uppercase tracking-widest">Repository Capacity Reached</span>
                                                                        </div>
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                                <!-- Selection Preview List -->
                                                                <template x-if="files.length > 0">
                                                                    <div class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar pr-2">
                                                                        <template x-for="(file, index) in files" :key="index">
                                                                            <div class="group/item flex items-center gap-3 p-3 bg-white border border-emerald-100 rounded-2xl shadow-sm hover:border-emerald-300 transition-all animate-fade-in-up">
                                                                                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0">
                                                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                                                </div>
                                                                                <div class="flex-1 min-w-0">
                                                                                    <p class="text-xs font-bold text-gray-900 truncate" x-text="file.name"></p>
                                                                                    <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest" x-text="formatSize(file.size)"></p>
                                                                                </div>
                                                                                <button type="button" @click.stop="removeFile(index)" 
                                                                                    class="p-2 text-gray-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all group-hover/item:opacity-100">
                                                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                                                                </button>
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                            @endif
                                                        </div>

                                                        <button type="submit" :disabled="uploading"
                                                            class="w-full py-5 bg-brand-600 text-white rounded-3xl text-xs font-bold uppercase tracking-widest hover:bg-gray-900 transition-all shadow-xl shadow-brand-500/20 active:scale-95 disabled:opacity-50">
                                                            <span x-text="uploading ? 'Transmitting Records...' : 'Submit Institutional Artifacts'"></span>
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <div class="p-12 bg-gray-50 rounded-3xl border border-gray-100 flex flex-col items-center justify-center text-center shadow-inner">
                                                    <div class="w-16 h-16 rounded-3xl bg-white border border-gray-100 flex items-center justify-center text-gray-200 mb-6 shadow-sm">
                                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                                    </div>
                                                    <h5 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Submission Locked</h5>
                                                    <p class="text-[11px] text-gray-500 font-medium mt-3 max-w-sm leading-relaxed">Submission gate is currently locked. Access requires approval from your internal committee.</p>
                                                </div>
                                            @endif
                                          @endif

                                        @if($milestone->submissions->count() > 0)
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                                @foreach($milestone->submissions->sortByDesc('created_at') as $submission)
                                                    <div class="p-6 bg-gray-50/50 rounded-3xl border border-gray-100 shadow-sm hover:border-brand-200 transition-all group relative">
                                                        <div class="flex items-center justify-between mb-4">
                                                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                                                <div class="w-10 h-10 rounded-xl bg-white border border-gray-100 text-brand-600 flex items-center justify-center shrink-0 shadow-sm">
                                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                                </div>
                                                                <div class="min-w-0 cursor-pointer group/text" 
                                                                     @click.prevent="$dispatch('open-document-preview', { 
                                                                        url: '{{ route('submissions.view', $submission) }}', 
                                                                        title: '{{ addslashes($submission->file_meta['original_name'] ?? 'Version ' . $submission->version) }}',
                                                                        type: '{{ str_contains($submission->file_meta['mime_type'] ?? '', 'image/') ? 'image' : (($submission->file_meta['mime_type'] ?? '') === 'application/pdf' ? 'pdf' : 'other') }}'
                                                                     })">
                                                                    <div class="flex items-center gap-2">
                                                                        <h5 class="text-xs font-bold text-gray-900 uppercase group-hover/text:text-brand-600 transition-colors truncate tracking-widest">Version {{ $submission->version }}.0</h5>
                                                                        @if($submission->type)
                                                                            <span class="px-1.5 py-0.5 rounded bg-gray-100 border border-gray-200 text-[8px] font-black uppercase text-gray-500 tracking-tighter">{{ $submission->type }}</span>
                                                                        @endif
                                                                    </div>
                                                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ $submission->created_at->format('M d, Y') }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                @if(auth()->user()->hasAnyRole(['Admin', 'Program Coordinator']) && $milestone->template?->order == 9)
                                                                    <form x-data="{ uploading: false }" action="{{ route('submissions.plagiarism', $submission) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2 mr-2" @submit.prevent="
                                                                        uploading = true;
                                                                        fetch($event.target.action, {
                                                                            method: 'POST',
                                                                            body: new FormData($event.target),
                                                                            headers: { 'Accept': 'text/html' }
                                                                        }).then(res => res.json()).then(data => {
                                                                            if (data.success) window.refreshMilestone('{{ $milestone->id }}');
                                                                        }).finally(() => { uploading = false; })
                                                                    ">
                                                                        @csrf
                                                                        <div class="relative w-20 border border-gray-200 rounded-xl overflow-hidden shadow-sm bg-white focus-within:border-brand-500 focus-within:ring-1 focus-within:ring-brand-500">
                                                                            <input type="number" step="0.1" name="similarity_score" min="0" max="100" placeholder="%" class="w-full pl-3 pr-2 py-1.5 text-xs font-bold border-0 focus:ring-0" required title="Similarity Score (%)">
                                                                        </div>
                                                                        <div class="relative group/plag">
                                                                            <input type="file" name="report_file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept=".pdf"
                                                                                onchange="this.nextElementSibling.classList.add('bg-brand-50', 'text-brand-600', 'border-brand-200'); this.nextElementSibling.classList.remove('bg-white', 'text-gray-400', 'border-gray-100')">
                                                                            <div class="p-2 bg-white border border-gray-100 rounded-xl text-gray-400 group-hover/plag:border-brand-200 transition-all shadow-sm flex items-center justify-center" title="Attach Report (Optional)">
                                                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                                                            </div>
                                                                        </div>
                                                                        <button type="submit" :disabled="uploading" class="p-2 bg-brand-600 text-white rounded-xl hover:bg-gray-900 transition-all cursor-pointer shadow-sm disabled:opacity-50 flex items-center justify-center" title="Upload Result">
                                                                            <svg x-show="!uploading" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                                                            <svg x-show="uploading" style="display: none;" class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                                @if($submission->plagiarism_data)
                                                                    @php 
                                                                        $score = $submission->plagiarism_data['similarity_score'] ?? 0;
                                                                        $reportUrl = $submission->plagiarism_data['report_url'] ?? null;
                                                                    @endphp
                                                                    <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl {{ $score > 20 ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100' }} border shadow-sm">
                                                                        <span class="text-[10px] font-black uppercase tracking-tighter">{{ $score }}% Index</span>
                                                                        @if($reportUrl)
                                                                            <span class="w-px h-2.5 bg-current opacity-20"></span>
                                                                            <button type="button" 
                                                                                @click.prevent="$dispatch('open-document-preview', { 
                                                                                    url: '{{ Storage::url($reportUrl) }}', 
                                                                                    title: 'Internal Plagiarism Report',
                                                                                    type: 'pdf'
                                                                                })"
                                                                                class="text-[9px] font-bold uppercase tracking-widest hover:underline">
                                                                                View Report
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                @endif

                                                                <button type="button" title="Secure Preview"
                                                                    @click.prevent="$dispatch('open-document-preview', { 
                                                                        url: '{{ route('submissions.view', $submission) }}', 
                                                                        title: '{{ addslashes($submission->file_meta['original_name'] ?? 'Version ' . $submission->version) }}',
                                                                        type: '{{ str_contains($submission->file_meta['mime_type'] ?? '', 'image/') ? 'image' : (($submission->file_meta['mime_type'] ?? '') === 'application/pdf' ? 'pdf' : 'other') }}'
                                                                    })" 
                                                                    class="p-2.5 bg-white border border-gray-100 rounded-xl text-gray-400 hover:bg-brand-600 hover:text-white transition-all cursor-pointer shadow-sm">
                                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                                </button>
                                                                <a href="{{ route('submissions.view', $submission) }}" download title="Secure Download" class="p-2.5 bg-white border border-gray-100 rounded-xl text-gray-400 hover:bg-gray-900 hover:text-white transition-all shadow-sm">
                                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                                                </a>
                                                                @if(Auth::id() === $submission->submitted_by && $milestone->status !== 'approved')
                                                                    <form action="{{ route('submissions.destroy', $submission) }}" method="POST" 
                                                                        @submit.prevent="
                                                                            if (!confirm('Are you sure you want to delete this institutional artifact? This action cannot be undone.')) return;
                                                                            fetch($event.target.action, {
                                                                                method: 'DELETE',
                                                                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                                                            }).then(res => res.json()).then(data => {
                                                                                if (data.success) window.refreshMilestone('{{ $milestone->id }}');
                                                                            });
                                                                        " class="inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" title="Delete Artifact" class="p-2.5 bg-white border border-gray-100 rounded-xl text-gray-400 hover:bg-rose-600 hover:text-white transition-all cursor-pointer shadow-sm">
                                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="py-16 bg-gray-50 rounded-[3rem] border border-dashed border-gray-200 flex flex-col items-center justify-center text-center px-10">
                                                <div class="w-16 h-16 rounded-3xl bg-white flex items-center justify-center shadow-sm mb-6 border border-gray-100">
                                                    <svg class="w-8 h-8 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                </div>
                                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Awaiting Scholarly Documentation</p>
                                                <p class="text-[9px] text-gray-300 font-medium mt-2">Historical versions will be indexed here upon submission.</p>
                                            </div>
                                        @endif
                                     </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
