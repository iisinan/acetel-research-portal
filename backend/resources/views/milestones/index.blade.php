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
            <h1 class="text-2xl md:text-4xl font-bold text-gray-900 tracking-tight">
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
    <div class="bg-white border border-gray-100 rounded-3xl p-4 md:p-8 shadow-sm ResearchRoadmapContainer">
        <div class="flex items-center gap-4 mb-6 md:mb-10">
            <div class="w-1.5 h-8 bg-brand-500 rounded-full"></div>
            <h3 class="text-lg md:text-xl font-bold text-gray-900 tracking-tight">Research Roadmap</h3>
        </div>

        <div class="relative px-4">
            <!-- Timeline Track -->
            <div class="absolute top-[26px] left-0 right-0 h-1.5 bg-gray-100 rounded-full"></div>
            
            <div class="relative flex justify-between items-start gap-4 overflow-x-auto pb-4 custom-scrollbar">
                @php $foundActiveTimeline = false; @endphp
                @foreach($milestones as $m)
                    @php
                        $isCompletedTimeline = false;
                        $isActiveTimeline = false;
                        if ($m->id == $ongoingMilestoneId) {
                            $isActiveTimeline = true;
                            $foundActiveTimeline = true;
                        } elseif (!$foundActiveTimeline && $ongoingMilestoneId) {
                            $isCompletedTimeline = true;
                        } elseif (!$ongoingMilestoneId) {
                            $isCompletedTimeline = true;
                        }
                    @endphp
                    <div class="flex flex-col items-center min-w-[140px] group">
                        <!-- Connector Node -->
                        <div class="relative z-10 w-14 h-14 rounded-2xl flex items-center justify-center border-4 border-white shadow-lg transition-all duration-500 group-hover:scale-110 cursor-pointer"
                             @click="expanded = expanded === '{{ $m->id }}' ? null : '{{ $m->id }}'; if(expanded) $nextTick(() => document.getElementById('milestone-container-' + '{{ $m->id }}').scrollIntoView({ behavior: 'smooth', block: 'center' }))"
                             :class="{ 'bg-green-500 text-white': '{{ $isCompletedTimeline }}' === '1', 'bg-brand-500 text-white ring-8 ring-brand-50/50': '{{ $isActiveTimeline }}' === '1', 'bg-white text-gray-400 border-gray-100': '{{ !$isCompletedTimeline && !$isActiveTimeline }}' === '1' }">
                            @if($isCompletedTimeline)
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                            @elseif($isActiveTimeline)
                                <div class="w-3 h-3 bg-white rounded-full animate-pulse"></div>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            @endif
                        </div>

                        <!-- Info Area -->
                        <div class="mt-6 text-center max-w-[120px]">
                            <p class="text-[10px] font-bold uppercase tracking-widest leading-none mb-1.5"
                               class="{{ $isCompletedTimeline ? 'text-green-600' : ($isActiveTimeline ? 'text-brand-600' : 'text-gray-400') }}">
                                Milestone {{ $m->template?->order }}
                            </p>
                            <h4 class="text-xs font-bold text-gray-900 leading-tight group-hover:text-brand-600 transition-colors line-clamp-2">
                                {{ $m->template?->name }}
                            </h4>
                            
                            @if($isCompletedTimeline && $m->approved_at)
                                <p class="text-[9px] font-medium text-gray-400 mt-2 uppercase tracking-tighter">
                                    Validated {{ $m->approved_at->format('M Y') }}
                                </p>
                            @elseif($isActiveTimeline)
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
                
                // Force sequential state based on the controller's ongoingMilestoneId
                $isCompleted = false;
                $isActive = false;
                $isPendingMatch = false;
                $isLocked = false;

                if ($milestone->id == $ongoingMilestoneId) {
                    $isActive = true;
                    $foundActive = true;
                    $isPendingMatch = in_array($milestone->status, ['submitted', 'partially_approved']);
                } elseif (!$foundActive && $ongoingMilestoneId) {
                    // Before the ongoing milestone, it must be considered completed.
                    $isCompleted = true;
                } elseif (!$ongoingMilestoneId) {
                    // If there is no ongoing milestone at all, it means ALL are completed
                    $isCompleted = true;
                } else {
                    // After the ongoing milestone, it must strictly be locked.
                    $isLocked = true;
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
                <div @if($isCompleted || $isActive || $isPendingMatch)
                            onclick="toggleMilestone('{{ $milestone->id }}')"
                        @else
                            onclick="window.toast.warning('Institutional Protocol: This milestone is currently locked. You must complete the ongoing phase first.');"
                        @endif
                        class="w-full text-left flex items-center justify-between px-4 md:px-10 py-6 md:py-10 transition-all duration-300 {{ ($isCompleted || $isActive || $isPendingMatch) ? 'cursor-pointer hover:bg-gray-50/30' : 'cursor-not-allowed' }}">
                    <div class="flex items-center gap-3 md:gap-6">
                        <div class="relative">
                            <div class="w-10 h-10 md:w-14 md:h-14 rounded-2xl flex items-center justify-center text-base md:text-xl font-bold shadow-lg shadow-{{ $conf['color'] }}-500/10 border {{ $isCompleted ? 'bg-emerald-600 border-emerald-500 text-white' : 'bg-white border-gray-100 text-gray-900' }}">
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
                            <p class="text-sm md:text-lg font-bold text-gray-900 group-hover/milestone:text-brand-600 transition-colors tracking-tight">{{ $milestone->template?->name }} @cannot('view', $milestone) <span class="text-xs font-black text-gray-400 ml-2">🔒</span> @endcannot</p>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1.5 hidden sm:block">{{ $milestone->template?->description }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 md:gap-8 shrink-0">

                        @if(auth()->user()->hasRole('Admin') && $milestone->status !== 'approved' && (!empty($milestone->defence_date) || in_array($milestone->template?->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2'])))
                            <form action="{{ route('milestones.end_presentation', $milestone) }}" method="POST" class="inline-block relative z-20" onclick="event.stopPropagation();">
                                @csrf
                                <button type="submit" 
                                    data-confirm="Are you sure you want to end the presentation for {{ addslashes($thesis->student?->user?->name ?? 'Candidate') }}? If all requirements are met (presentation conducted, PPT uploaded, and supervisor approved), the candidate will be advanced to the next milestone."
                                    data-confirm-title="End Presentation Session" 
                                    data-confirm-type="success" 
                                    data-confirm-btn="End Presentation" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-blue-700 transition-colors shadow-sm cursor-pointer border border-blue-700">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    End Presentation
                                </button>
                            </form>
                        @elseif(auth()->user()->can('review', $milestone))
                            @php
                                $canQuickApprove = !$milestone->template->requires_submission || 
                                                   ($milestone->submissions->last() && 
                                                    $milestone->submissions->last()->feedback && 
                                                    $milestone->submissions->last()->feedback->decision === 'approved');
                            @endphp
                            @if($canQuickApprove && $milestone->status !== 'approved')
                            <form action="{{ route('milestones.review.update', $milestone) }}" method="POST" class="inline-block relative z-20" onclick="event.stopPropagation();">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="decision" value="approved">
                                <input type="hidden" name="remarks" value="Approved directly from the roadmap summary.">
                                <button type="submit" data-confirm="Are you sure you want to officially approve this milestone?" data-confirm-title="Approve Milestone" data-confirm-type="success" data-confirm-btn="Approve" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500 text-white rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-emerald-600 transition-colors shadow-sm cursor-pointer border border-emerald-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                    Approve
                                </button>
                            </form>
                            @endif
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
                </div>

                <!-- Milestone Body -->
                <div id="milestone-body-{{ $milestone->id }}" class="milestone-body relative z-10 w-full bg-gray-50/50" style="display:none;">
                    @can('view', $milestone)
                        <div class="px-6 py-8 border-t border-gray-100 bg-white w-full relative">
                            @include('milestones.partials.details', ['milestone' => $milestone])
                        </div>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
