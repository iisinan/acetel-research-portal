@php
    $workflow = app(\App\Services\MilestoneWorkflowService::class);
    $slug = $milestone->template?->slug;
    $isSeminar = ($slug === 'seminar_as_a_course');
    $isInternalDefence = ($slug === 'internal_defence' || $milestone->template?->order == 6);
    $isPresentationMilestone = in_array($slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2', 'internal_defence']) || $isInternalDefence;
@endphp

@if($isPresentationMilestone)
    @php
        $hasPpt = $workflow->hasUploadedPresentation($milestone);
        $isSupervisorApproved = $workflow->isSupervisorApproved($milestone);
        $defenceDateStr = $milestone->defence_date;
        $defenceDate = $defenceDateStr ? \Carbon\Carbon::parse($defenceDateStr) : null;
        $hasSchedule = !empty($defenceDate);
        $isConducted = $hasSchedule && ($defenceDate->startOfDay()->lte(now()->startOfDay()) || $workflow->hasBeenGraded($milestone));
        $isGraded = $workflow->hasBeenGraded($milestone);
        $isApproved = ($milestone->status === 'approved');
        $isPostDefence = $isInternalDefence && $workflow->isPostDefencePhase($milestone);
        $postDefenceSub = $isInternalDefence ? $workflow->getPostDefenceSubmission($milestone) : null;
        $hasPostDefenceSub = $postDefenceSub !== null;
        $postDefenceUrl = $postDefenceSub ? route('submissions.view', $postDefenceSub) : null;
        $ieDecision = $milestone->approvals['internal_examiner_review']['decision'] ?? null;
        $isAssignedInternalExaminerUser = $isInternalDefence && $workflow->isAssignedInternalExaminer($milestone, auth()->user());

        $endSessionBlockReason = $workflow->getEndSessionBlockReason($milestone);
        $canEndPresentation = is_null($endSessionBlockReason) && !$isPostDefence;
        $latestPptSub = $milestone->submissions()->where('type', 'ppt')->latest()->first() 
            ?? $milestone->submissions()->latest()->first();
        $pptUrl = $latestPptSub ? route('submissions.view', $latestPptSub) : null;

        // Next destination name
        $nextDestination = match($slug) {
            'seminar_as_a_course' => 'Supervisors Assigned',
            'proposal_defence' => 'Progress Report 1',
            'progress_report_1' => ($milestone->thesis?->isMsc() ? 'Internal Defence' : 'Progress Report 2'),
            'progress_report_2' => 'Internal Defence',
            'internal_defence' => 'Viva Voce',
            default => $isInternalDefence ? 'Viva Voce' : 'Next Milestone'
        };

        $gradingOutcome = $workflow->getAverageGradingOutcome($milestone);
        $isPass = ($gradingOutcome === 'pass');
        $isFail = ($gradingOutcome === 'fail');

        $checklistEventType = $milestone->template?->defence_type ?? match($slug) {
            'seminar_as_a_course' => 'seminar',
            'proposal_defence' => 'proposal',
            'progress_report_1' => 'progress_report_1',
            'progress_report_2' => 'progress_report_2',
            'internal_defence' => 'internal',
            default => $isInternalDefence ? 'internal' : null
        };
        $checklistEvent = $checklistEventType && $milestone->thesis 
            ? $milestone->thesis->defenceEvents->firstWhere('type', $checklistEventType) 
            : null;
        $evaluatorId = $checklistEvent?->evaluations?->first()?->evaluator_id;

        $totalSteps = $isSeminar ? 3 : ($isInternalDefence ? 6 : 5);
        $completedSteps = 0;
        if ($isSeminar) {
            if ($hasSchedule) $completedSteps++;
            if ($isGraded) $completedSteps++;
            if ($isApproved) $completedSteps++;
        } elseif ($isInternalDefence) {
            if ($hasPpt || $isPostDefence || $isApproved) $completedSteps++;
            if ($isSupervisorApproved || $isPostDefence || $isApproved) $completedSteps++;
            if (($isGraded && $isPass) || $isPostDefence || $isApproved) $completedSteps++;
            if ($isPostDefence || $isApproved) $completedSteps++;
            if (($hasPostDefenceSub && $ieDecision !== 'revision_required') || $isApproved) $completedSteps++;
            if ($isApproved) $completedSteps++;
        } else {
            if ($hasPpt) $completedSteps++;
            if ($isSupervisorApproved) $completedSteps++;
            if ($isConducted) $completedSteps++;
            if ($isGraded && $isPass) $completedSteps++;
            if ($isApproved) $completedSteps++;
        }
    @endphp

    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 p-6 sm:p-8 mb-8 relative overflow-hidden">
        {{-- Header of Checklist --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">
                            {{ $isSeminar ? 'Seminar Course Requirements' : 'Milestone Progression Requirements' }}
                        </h3>
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-700">
                            {{ $completedSteps }}/{{ $totalSteps }} Steps Met
                        </span>
                    </div>
                    <p class="text-xs font-semibold text-slate-500 mt-0.5">
                        Fulfill these conditions to advance candidate to <strong class="text-indigo-600">{{ $nextDestination }}</strong>.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                @if($isApproved)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-black uppercase tracking-wider">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Cleared & Advanced</span>
                    </span>
                @elseif($canEndPresentation && auth()->user()->hasRole('Admin'))
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 text-xs font-black uppercase tracking-wider animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        <span>Ready to End Presentation</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 text-xs font-black uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>In Progress</span>
                    </span>
                @endif
            </div>
        </div>

        {{-- Checklist Steps Grid --}}
        @if($isSeminar)
            {{-- Seminar as a course: 3 Steps --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-6">
                {{-- 1. Scheduled --}}
                <div class="p-5 rounded-2xl border transition-all {{ $hasSchedule ? 'bg-emerald-50/50 border-emerald-200' : 'bg-slate-50 border-slate-200' }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black {{ $hasSchedule ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-700' }}">1</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $hasSchedule ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $hasSchedule ? 'Scheduled' : 'Pending' }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">Scheduled for Presentation</h4>
                        <p class="text-xs text-slate-500 mt-1">
                            @if($hasSchedule)
                                {{ $defenceDate->format('d M Y') }} {{ $milestone->defence_time ? 'at ' . \Carbon\Carbon::parse($milestone->defence_time)->format('g:i A') : '' }}
                            @else
                                Presentation has not been scheduled yet.
                            @endif
                        </p>
                    </div>
                </div>

                {{-- 2. Graded --}}
                <div class="p-5 rounded-2xl border transition-all {{ $isGraded ? 'bg-emerald-50/50 border-emerald-200' : 'bg-slate-50 border-slate-200' }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black {{ $isGraded ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-700' }}">2</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $isGraded ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $isGraded ? 'Graded' : 'Awaiting Grade' }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">Graded by Examiner</h4>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ $isGraded ? 'Examiner scores officially recorded.' : 'Awaiting presentation evaluation from examiner.' }}
                        </p>
                    </div>
                </div>

                {{-- 3. End Presentation --}}
                <div class="p-5 rounded-2xl border transition-all {{ $isApproved ? 'bg-emerald-50/50 border-emerald-200' : ($canEndPresentation ? 'bg-blue-50/60 border-blue-200' : 'bg-slate-50 border-slate-200') }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black {{ $isApproved ? 'bg-emerald-500 text-white' : ($canEndPresentation ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700') }}">3</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $isApproved ? 'bg-emerald-100 text-emerald-800' : ($canEndPresentation ? 'bg-blue-100 text-blue-800' : 'bg-slate-200 text-slate-700') }}">
                                {{ $isApproved ? 'Approved' : ($canEndPresentation ? 'Ready' : 'Pending') }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">End Presentation Session</h4>
                        <p class="text-xs text-slate-500 mt-1 mb-3">
                            {{ $isApproved ? 'Presentation ended. Candidate advanced to Supervisors Assigned.' : 'Admin ends session to advance student.' }}
                        </p>
                    </div>

                    @if(!$isApproved && auth()->user()->hasRole('Admin'))
                        @if($canEndPresentation)
                            <form action="{{ route('milestones.end_presentation', $milestone) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                    data-confirm="Are you sure you want to end the presentation session for {{ addslashes($milestone->thesis->student->user->name) }}? The student will be advanced to Supervisors Assigned."
                                    data-confirm-title="End Presentation Session"
                                    data-confirm-type="success"
                                    data-confirm-btn="End Presentation"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-blue-600/30 active:scale-95 cursor-pointer">
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span>End Presentation</span>
                                </button>
                            </form>
                        @else
                            <div>
                                <button disabled class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-200 text-slate-500 rounded-xl text-xs font-bold uppercase tracking-wider cursor-not-allowed">
                                    <span>End Presentation</span>
                                </button>
                                <p class="text-[10px] text-amber-700 font-bold mt-1">Missing: {{ ucfirst($endSessionBlockReason) }}</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @elseif($isInternalDefence)
            {{-- Internal Defence: 6 Steps (including Post-Defence Correction Upload & Internal Examiner Approval) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-6">
                {{-- Step 1: Pre-Defence Manuscript Upload --}}
                @php
                    $preDefSub = $milestone->submissions->whereIn('type', ['manuscript', 'file'])->sortByDesc('created_at')->first()
                        ?? $milestone->submissions->sortByDesc('created_at')->first();
                    if ($isPostDefence && $postDefenceSub && $milestone->submissions->whereIn('type', ['manuscript', 'file'])->count() > 1) {
                        $preDefSub = $milestone->submissions->whereIn('type', ['manuscript', 'file'])->where('id', '!=', $postDefenceSub->id)->sortByDesc('created_at')->first() ?? $preDefSub;
                    }
                    $preDefUrl = $preDefSub?->file_url ? (str_starts_with($preDefSub->file_url, 'http') ? $preDefSub->file_url : \Illuminate\Support\Facades\Storage::url($preDefSub->file_url)) : null;
                    $pubSubsCount = $milestone->submissions->where('type', 'publication')->count();
                @endphp
                <div class="p-5 rounded-2xl border transition-all {{ ($hasPpt || $isPostDefence || $isApproved) ? 'bg-emerald-50/50 border-emerald-200' : 'bg-slate-50 border-slate-200' }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black {{ ($hasPpt || $isPostDefence || $isApproved) ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-700' }}">1</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ ($hasPpt || $isPostDefence || $isApproved) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ($hasPpt || $isPostDefence || $isApproved) ? 'Uploaded' : 'Pending Upload' }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">1. Pre-Defence Thesis Upload</h4>
                        <p class="text-xs text-slate-500 mt-1 mb-3">
                            {{ ($hasPpt || $isPostDefence || $isApproved) ? 'Pre-defence thesis manuscript uploaded by candidate.' : 'Candidate must upload thesis manuscript (PDF) before presentation.' }}
                            @if($pubSubsCount > 0)
                                <span class="block mt-1 font-semibold text-purple-700">• {{ $pubSubsCount }} Scopus {{ \Illuminate\Support\Str::plural('publication', $pubSubsCount) }} attached</span>
                            @endif
                        </p>
                    </div>

                    @if($preDefSub && $preDefUrl)
                        <div class="flex flex-col gap-1.5 mt-2">
                            <button type="button" 
                                @click.prevent="$dispatch('open-document-preview', { 
                                    url: '{{ $preDefUrl }}', 
                                    title: 'Thesis Manuscript (v{{ $preDefSub->version }}) - {{ addslashes($milestone->thesis->student->user->name) }}', 
                                    type: 'pdf' 
                                })"
                                class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-white hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 rounded-xl text-xs font-bold text-indigo-700 transition-colors shadow-sm cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>View Thesis Manuscript (v{{ $preDefSub->version }})</span>
                            </button>
                            <a href="{{ $preDefUrl }}" target="_blank" class="text-[10px] font-bold text-slate-500 hover:text-indigo-600 text-center underline">
                                Open / Download PDF in New Tab &rarr;
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Step 2: Supervisor Pre-Defence Approval --}}
                @php
                    $supSummaryInt = $workflow->getSupervisorReviewSummary($milestone);
                    $mySupReviewInt = collect($supSummaryInt['supervisors'] ?? [])->first(fn($s) => (string)($s['user_id'] ?? '') === (string)auth()->id());
                    $canReviewPreDefence = !$isPostDefence && !$isApproved && (auth()->user()->hasRole('Admin') || (
                        auth()->user()->hasRole('Supervisor') && 
                        auth()->user()->supervisorProfile && 
                        $milestone->thesis->assignments->where('supervisor_profile_id', auth()->user()->supervisorProfile->id)->where('status', 'active')->isNotEmpty()
                    ));
                    $needsMyReviewInt = $mySupReviewInt
                        ? in_array($mySupReviewInt['status'], ['pending', 'pending_re_review'])
                        : !$isSupervisorApproved;
                @endphp
                <div class="p-5 rounded-2xl border transition-all {{ ($isSupervisorApproved || $isPostDefence || $isApproved) ? 'bg-emerald-50/50 border-emerald-200' : ($milestone->status === 'revision_required' && !$isPostDefence ? 'bg-rose-50/50 border-rose-200' : 'bg-slate-50 border-slate-200') }} flex flex-col justify-between" x-data="{ editingDecision: {{ $needsMyReviewInt ? 'true' : 'false' }}, remarks: {{ \Illuminate\Support\Js::from($mySupReviewInt['remarks'] ?? '') }} }">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black {{ ($isSupervisorApproved || $isPostDefence || $isApproved) ? 'bg-emerald-500 text-white' : ($milestone->status === 'revision_required' && !$isPostDefence ? 'bg-rose-500 text-white' : 'bg-slate-200 text-slate-700') }}">2</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ ($isSupervisorApproved || $isPostDefence || $isApproved) ? 'bg-emerald-100 text-emerald-800' : ($milestone->status === 'revision_required' && !$isPostDefence ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ ($isSupervisorApproved || $isPostDefence || $isApproved) ? 'Approved' : ($milestone->status === 'revision_required' && !$isPostDefence ? 'Revision Needed' : 'Awaiting Review') }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">2. Supervisor Pre-Defence Approval</h4>
                        <p class="text-xs text-slate-500 mt-1 mb-2">
                            @if($isSupervisorApproved || $isPostDefence || $isApproved)
                                {{ $supSummaryInt['has_supervisors'] ? "{$supSummaryInt['approved_count']} of {$supSummaryInt['total_assigned']} supervisors approved — Candidate eligible for Internal Defence presentation." : 'Supervisor approved the candidate\'s thesis for internal defence presentation.' }}
                            @elseif($supSummaryInt['rejected_count'] > 0)
                                Supervisor requested revisions on the thesis manuscript.
                            @else
                                Assigned supervisor must review and approve (or reject) the uploaded thesis manuscript.
                            @endif
                        </p>

                        @if($supSummaryInt['has_supervisors'] && !$isPostDefence)
                            <div class="space-y-1.5 my-2.5">
                                @foreach($supSummaryInt['supervisors'] as $sItem)
                                    <div class="flex items-center justify-between px-2.5 py-1.5 bg-white/90 rounded-lg border border-slate-200/70 text-[11px]">
                                        <span class="font-bold text-slate-700 truncate mr-2">
                                            {{ $sItem['name'] }}
                                            @if((string)$sItem['user_id'] === (string)auth()->id())
                                                <span class="text-[9px] font-black text-indigo-600 uppercase">(You)</span>
                                            @endif
                                        </span>
                                        @if($sItem['status'] === 'approved')
                                            <span class="text-[10px] font-black text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 shrink-0">✓ Approved</span>
                                        @elseif($sItem['status'] === 'rejected')
                                            <span class="text-[10px] font-black text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200 shrink-0">✕ Rejected</span>
                                        @elseif($sItem['status'] === 'pending_re_review')
                                            <span class="text-[10px] font-black text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 shrink-0">↻ Re-Review</span>
                                        @else
                                            <span class="text-[10px] font-semibold text-slate-400 italic shrink-0">Pending</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if($canReviewPreDefence && $milestone->submissions->isNotEmpty())
                        <div class="mt-2 pt-3 border-t border-slate-200/70">
                            <div x-show="!editingDecision" class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold {{ ($mySupReviewInt['status'] ?? '') === 'approved' ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ ($mySupReviewInt['status'] ?? '') === 'approved' ? '✓ Your approval is recorded' : (($mySupReviewInt['status'] ?? '') === 'rejected' ? '✕ You requested revisions' : 'Decision recorded') }}
                                </span>
                                <button type="button" @click="editingDecision = true" class="text-[10px] font-black uppercase tracking-wider text-indigo-600 hover:text-indigo-800 underline cursor-pointer">
                                    Update Decision
                                </button>
                            </div>

                            <form x-show="editingDecision" method="POST" @submit="if(!remarks.trim()) { alert('A comment is required to approve or reject the thesis.'); event.preventDefault(); }" class="space-y-2">
                                @csrf
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-slate-700">Supervisor Decision & Remarks <span class="text-rose-500">*</span></label>
                                    <button type="button" x-show="{{ $needsMyReviewInt ? 'false' : 'true' }}" @click="editingDecision = false" class="text-[10px] font-bold text-slate-400 hover:text-slate-600">Cancel</button>
                                </div>
                                <textarea name="remarks" x-model="remarks" rows="2" class="w-full text-xs rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 p-2.5 shadow-sm resize-none bg-white placeholder:text-slate-400" placeholder="Enter your review comments (required to approve or reject)..." required></textarea>
                                <div class="flex gap-2">
                                    <button type="submit" formaction="{{ route('milestones.accept_upload', $milestone) }}" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-2 px-2 rounded-xl text-xs font-bold transition-colors shadow-xs cursor-pointer">
                                        ✓ Approve Thesis
                                    </button>
                                    <button type="submit" formaction="{{ route('milestones.reject_upload', $milestone) }}" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white py-2 px-2 rounded-xl text-xs font-bold transition-colors shadow-xs cursor-pointer">
                                        ✕ Reject / Revise
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>

                {{-- Step 3: Presentation & Examiner Grading --}}
                <div class="p-5 rounded-2xl border transition-all {{ (($isGraded && $isPass) || $isPostDefence || $isApproved) ? 'bg-emerald-50/50 border-emerald-200' : ($isGraded && $isFail ? 'bg-rose-50/50 border-rose-200' : 'bg-slate-50 border-slate-200') }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black {{ (($isGraded && $isPass) || $isPostDefence || $isApproved) ? 'bg-emerald-500 text-white' : ($isGraded && $isFail ? 'bg-rose-500 text-white' : 'bg-slate-200 text-slate-700') }}">3</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ (($isGraded && $isPass) || $isPostDefence || $isApproved) ? 'bg-emerald-100 text-emerald-800' : ($isGraded && $isFail ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ ($isPostDefence || $isApproved) ? 'Passed' : ($isGraded ? ($isPass ? 'Passed' : 'Failed') : ($hasSchedule ? 'Scheduled' : 'Pending')) }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">3. Presentation & Grading</h4>
                        <p class="text-xs text-slate-500 mt-1 mb-3">
                            @if($isPostDefence || $isApproved || ($isGraded && $isPass))
                                Candidate passed internal defence evaluation.
                            @elseif($isGraded && $isFail)
                                Candidate received a FAIL grade and must repeat internal defence.
                            @elseif($hasSchedule)
                                Scheduled for {{ $defenceDate->format('d M Y') }} {{ $milestone->defence_time ? '• ' . \Carbon\Carbon::parse($milestone->defence_time)->format('g:i A') : '' }}
                            @else
                                Awaiting presentation schedule and examiner evaluation.
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Step 4: Admin Concludes Presentation Session --}}
                <div class="p-5 rounded-2xl border transition-all {{ ($isPostDefence || $isApproved) ? 'bg-emerald-50/50 border-emerald-200' : ($canEndPresentation ? ($isFail ? 'bg-rose-50/60 border-rose-200' : 'bg-blue-50/60 border-blue-200') : 'bg-slate-50 border-slate-200') }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black {{ ($isPostDefence || $isApproved) ? 'bg-emerald-500 text-white' : ($canEndPresentation ? ($isFail ? 'bg-rose-600 text-white' : 'bg-blue-600 text-white') : 'bg-slate-200 text-slate-700') }}">4</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ ($isPostDefence || $isApproved) ? 'bg-emerald-100 text-emerald-800' : ($canEndPresentation ? ($isFail ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800') : 'bg-slate-200 text-slate-700') }}">
                                {{ ($isPostDefence || $isApproved) ? 'Session Ended' : ($canEndPresentation ? 'Ready' : 'Pending') }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">4. End Presentation Session</h4>
                        <p class="text-xs text-slate-500 mt-1 mb-3">
                            @if($isPostDefence || $isApproved)
                                Presentation session ended by Admin. Candidate entered Post-Defence Correction stage.
                            @else
                                Admin ends the presentation session after PASS grade to unlock post-defence corrections.
                            @endif
                        </p>
                    </div>

                    @if(!$isPostDefence && !$isApproved && auth()->user()->hasRole('Admin'))
                        @if($canEndPresentation)
                            <form action="{{ route('milestones.end_presentation', $milestone) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                    data-confirm="Are you sure you want to end the internal defence presentation session for {{ addslashes($milestone->thesis->student->user->name) }}? {{ $isFail ? 'The candidate received a FAIL grade and will repeat this milestone.' : 'The candidate will enter the Post-Defence Correction stage and must upload their corrected thesis for Internal Examiner approval before moving to Viva.' }}"
                                    data-confirm-title="End Internal Defence Presentation"
                                    data-confirm-type="{{ $isFail ? 'danger' : 'success' }}"
                                    data-confirm-btn="{{ $isFail ? 'End & Repeat Milestone' : 'End Presentation & Request Corrections' }}"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 {{ $isFail ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/30' : 'bg-blue-600 hover:bg-blue-700 shadow-blue-600/30' }} text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md active:scale-95 cursor-pointer">
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span>{{ $isFail ? 'Repeat Milestone' : 'End Presentation' }}</span>
                                </button>
                            </form>
                        @else
                            <div>
                                <button disabled class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-200 text-slate-500 rounded-xl text-xs font-bold uppercase tracking-wider cursor-not-allowed">
                                    <span>End Presentation</span>
                                </button>
                                <p class="text-[10px] text-amber-700 font-bold mt-1">Pending: {{ ucfirst($endSessionBlockReason) }}</p>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Step 5: Post-Defence Corrected Thesis Upload --}}
                @php
                    $step5Completed = ($hasPostDefenceSub && $ieDecision !== 'revision_required') || $isApproved;
                @endphp
                <div class="p-5 rounded-2xl border transition-all {{ $step5Completed ? 'bg-emerald-50/50 border-emerald-200' : ($isPostDefence ? 'bg-amber-50/60 border-amber-200' : 'bg-slate-50 border-slate-200') }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black {{ $step5Completed ? 'bg-emerald-500 text-white' : ($isPostDefence ? 'bg-amber-500 text-white' : 'bg-slate-200 text-slate-700') }}">5</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $step5Completed ? 'bg-emerald-100 text-emerald-800' : ($ieDecision === 'revision_required' ? 'bg-rose-100 text-rose-800' : ($isPostDefence ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-700')) }}">
                                {{ $step5Completed ? 'Uploaded' : ($ieDecision === 'revision_required' ? 'Re-Upload Required' : ($isPostDefence ? 'Awaiting Upload' : 'Locked')) }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">5. Corrected Thesis Submission</h4>
                        <p class="text-xs text-slate-500 mt-1 mb-3">
                            @if($step5Completed)
                                Candidate uploaded the corrected thesis manuscript after presentation for Internal Examiner review.
                            @elseif($ieDecision === 'revision_required')
                                Internal Examiner requested further corrections. Candidate must upload a revised manuscript.
                            @elseif($isPostDefence)
                                Candidate must upload the corrected thesis manuscript incorporating defence feedback.
                            @else
                                Unlocks after the presentation session is concluded with a PASS grade.
                            @endif
                        </p>
                    </div>

                    @if($hasPostDefenceSub && $postDefenceUrl)
                        <button type="button" 
                            @click.prevent="$dispatch('open-document-preview', { 
                                url: '{{ $postDefenceUrl }}', 
                                title: 'Corrected Thesis Manuscript - {{ addslashes($milestone->thesis->student->user->name) }}', 
                                type: 'pdf' 
                            })"
                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-indigo-700 transition-colors shadow-sm cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>View Corrected Thesis (v{{ $postDefenceSub->version }})</span>
                        </button>
                    @elseif($isPostDefence && auth()->user()->hasRole('Student'))
                        <a href="#artifact-upload-section" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition-colors">
                            <span>Upload Corrected Thesis Below &darr;</span>
                        </a>
                    @endif
                </div>

                {{-- Step 6: Internal Examiner Approval -> Advance to Viva --}}
                <div class="p-5 rounded-2xl border transition-all {{ $isApproved ? 'bg-emerald-50/50 border-emerald-200' : ($hasPostDefenceSub ? 'bg-indigo-50/60 border-indigo-200' : 'bg-slate-50 border-slate-200') }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black {{ $isApproved ? 'bg-emerald-500 text-white' : ($hasPostDefenceSub ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700') }}">6</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $isApproved ? 'bg-emerald-100 text-emerald-800' : ($hasPostDefenceSub ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-200 text-slate-700') }}">
                                {{ $isApproved ? 'Approved & Advanced' : ($hasPostDefenceSub ? ($ieDecision === 'revision_required' ? 'Revision Requested' : 'Ready for Review') : 'Pending') }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">6. Internal Examiner Approval</h4>
                        <p class="text-xs text-slate-500 mt-1 mb-3">
                            @if($isApproved)
                                Internal Examiner approved the corrected thesis. Candidate advanced to Viva Voce.
                            @elseif($hasPostDefenceSub && $ieDecision !== 'revision_required')
                                Assigned Internal Examiner reviews the corrected thesis and approves it to advance candidate to Viva Voce.
                            @elseif($ieDecision === 'revision_required')
                                Internal Examiner requested further corrections ({{ $milestone->approvals['internal_examiner_review']['remarks'] ?? '' }}).
                            @else
                                Internal Examiner will review and approve the corrected thesis once uploaded by the student.
                            @endif
                        </p>
                    </div>

                    @if(!$isApproved && $isPostDefence && $hasPostDefenceSub && ($isAssignedInternalExaminerUser || auth()->user()->hasRole('Admin')))
                        <form method="POST" x-data="{ remarks: '' }" @submit="if(!remarks.trim()) { alert('Please provide review comments explaining your decision.'); event.preventDefault(); }" class="mt-3 pt-3 border-t border-indigo-100">
                            @csrf
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Internal Examiner Verdict <span class="text-rose-500">*</span></label>
                            <textarea name="remarks" x-model="remarks" rows="2" class="w-full text-xs rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 p-2.5 mb-2 shadow-sm resize-none bg-white placeholder:text-slate-400" placeholder="Enter your review of the post-defence corrections..." required></textarea>
                            <div class="flex flex-col gap-1.5">
                                <button type="submit" formaction="{{ route('milestones.accept_upload', $milestone) }}" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 px-3 rounded-xl text-xs font-black uppercase tracking-wider transition-colors cursor-pointer">
                                    Approve Corrections & Advance to Viva
                                </button>
                                <button type="submit" formaction="{{ route('milestones.reject_upload', $milestone) }}" class="w-full bg-rose-500 hover:bg-rose-600 text-white py-2 px-3 rounded-xl text-xs font-bold transition-colors cursor-pointer">
                                    Request Further Corrections
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        @else
            {{-- Proposal Defence / Progress Report 1 / Progress Report 2: 5 Steps --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 pt-6">
                {{-- Step 1: Upload Presentation / Document (PDF Only) --}}
                @php
                    $hasPptType = $milestone->submissions()->whereIn('type', ['ppt', 'presentation'])->exists();
                    $hasDocType = $milestone->submissions()->whereIn('type', ['manuscript', 'file'])->exists() || ($milestone->submissions()->exists() && !$hasPptType);

                    $step1Title = match($slug) {
                        'proposal_defence' => ($hasDocType && !$hasPptType ? 'Upload Proposal Document (PDF)' : ($hasPptType && !$hasDocType ? 'Upload Presentation (PDF)' : 'Upload Proposal / Presentation (PDF)')),
                        'progress_report_1', 'progress_report_2' => ($hasDocType && !$hasPptType ? 'Upload Progress Report (PDF)' : ($hasPptType && !$hasDocType ? 'Upload Presentation (PDF)' : 'Upload Report / Presentation (PDF)')),
                        default => ($hasPptType ? 'Upload Presentation (PDF)' : 'Upload Submission Document (PDF)'),
                    };

                    $step1UploadedText = match($slug) {
                        'proposal_defence' => ($hasDocType && !$hasPptType ? 'Proposal document uploaded by student.' : ($hasPptType && !$hasDocType ? 'Presentation slides (PDF) uploaded by student.' : 'Proposal document / presentation PDF uploaded.')),
                        'progress_report_1', 'progress_report_2' => ($hasDocType && !$hasPptType ? 'Progress report uploaded by student.' : ($hasPptType && !$hasDocType ? 'Presentation slides (PDF) uploaded by student.' : 'Progress report / presentation PDF uploaded.')),
                        default => 'Submission document uploaded by student.',
                    };

                    $step1PendingText = match($slug) {
                        'proposal_defence' => 'Candidate must upload proposal document or presentation slides (PDF).',
                        'progress_report_1', 'progress_report_2' => 'Candidate must upload progress report or presentation slides (PDF).',
                        default => 'Candidate must upload required document or presentation slides (PDF).',
                    };

                    $previewBtnLabel = ($hasPptType && !$hasDocType) ? 'Preview Slides' : 'View Document';
                @endphp
                <div class="p-5 rounded-2xl border transition-all {{ $hasPpt ? 'bg-emerald-50/50 border-emerald-200' : 'bg-slate-50 border-slate-200' }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black {{ $hasPpt ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-700' }}">1</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $hasPpt ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $hasPpt ? 'Uploaded' : 'Pending Upload' }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">{{ $step1Title }}</h4>
                        <p class="text-xs text-slate-500 mt-1 mb-3">
                            {{ $hasPpt ? $step1UploadedText : $step1PendingText }}
                        </p>
                    </div>

                    @if($hasPpt && $pptUrl)
                        <button type="button" 
                            @click.prevent="$dispatch('open-document-preview', { 
                                url: '{{ $pptUrl }}', 
                                title: '{{ addslashes($step1Title) }} - {{ addslashes($milestone->thesis->student->user->name) }}', 
                                type: '{{ (str_contains(strtolower($pptUrl), '.pdf') || str_contains(strtolower($latestPptSub?->file_meta['mime_type'] ?? ''), 'pdf')) ? 'pdf' : 'document' }}' 
                            })"
                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 transition-colors shadow-sm cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>{{ $previewBtnLabel }}</span>
                        </button>
                    @elseif(auth()->user()->hasRole('Student'))
                        <a href="#artifact-upload-section" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl text-xs font-bold transition-colors">
                            <span>Upload Below &darr;</span>
                        </a>
                    @endif
                </div>

                {{-- Step 2: Supervisor Approval --}}
                @php
                    $supSummary = $workflow->getSupervisorReviewSummary($milestone);
                    $mySupReviewGen = collect($supSummary['supervisors'] ?? [])->first(fn($s) => (string)($s['user_id'] ?? '') === (string)auth()->id());
                    $canReviewUpload = !$isApproved && (auth()->user()->hasRole('Admin') || (
                        auth()->user()->hasRole('Supervisor') && 
                        auth()->user()->supervisorProfile && 
                        $milestone->thesis->assignments->where('supervisor_profile_id', auth()->user()->supervisorProfile->id)->where('status', 'active')->isNotEmpty()
                    ));
                    $needsMyReviewGen = $mySupReviewGen
                        ? in_array($mySupReviewGen['status'], ['pending', 'pending_re_review'])
                        : !$isSupervisorApproved;
                @endphp
                <div class="p-5 rounded-2xl border transition-all {{ $isSupervisorApproved ? 'bg-emerald-50/50 border-emerald-200' : ($milestone->status === 'revision_required' ? 'bg-rose-50/50 border-rose-200' : 'bg-slate-50 border-slate-200') }} flex flex-col justify-between" x-data="{ editingDecision: {{ $needsMyReviewGen ? 'true' : 'false' }}, remarks: {{ \Illuminate\Support\Js::from($mySupReviewGen['remarks'] ?? '') }} }">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black {{ $isSupervisorApproved ? 'bg-emerald-500 text-white' : ($milestone->status === 'revision_required' ? 'bg-rose-500 text-white' : 'bg-slate-200 text-slate-700') }}">2</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $isSupervisorApproved ? 'bg-emerald-100 text-emerald-800' : ($milestone->status === 'revision_required' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ $isSupervisorApproved ? 'Approved' : ($milestone->status === 'revision_required' ? 'Revision Needed' : 'Awaiting Review') }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">Supervisor Approval</h4>
                        <p class="text-xs text-slate-500 mt-1 mb-2">
                            @if($isSupervisorApproved)
                                {{ $supSummary['has_supervisors'] ? "{$supSummary['approved_count']} of {$supSummary['total_assigned']} supervisors approved (0 rejections) — Candidate eligible to present." : "Supervisor approved the candidate's upload." }}
                            @elseif($supSummary['rejected_count'] > 0)
                                Revision requested by assigned supervisor. Revisions required before candidate can present.
                            @elseif($supSummary['pending_rereview_count'] > 0)
                                Re-upload awaiting review by the supervisor who requested revisions (standing approvals preserved).
                            @else
                                Awaiting supervisor review and approval.
                            @endif
                        </p>

                        @if($supSummary['has_supervisors'])
                            <div class="space-y-1.5 my-2.5">
                                @foreach($supSummary['supervisors'] as $sItem)
                                    <div class="flex items-center justify-between px-2.5 py-1.5 bg-white/90 rounded-lg border border-slate-200/70 text-[11px]">
                                        <span class="font-bold text-slate-700 truncate mr-2">
                                            {{ $sItem['name'] }}
                                            @if((string)$sItem['user_id'] === (string)auth()->id())
                                                <span class="text-[9px] font-black text-indigo-600 uppercase">(You)</span>
                                            @endif
                                        </span>
                                        @if($sItem['status'] === 'approved')
                                            <span class="text-[10px] font-black text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 shrink-0">✓ Approved</span>
                                        @elseif($sItem['status'] === 'rejected')
                                            <span class="text-[10px] font-black text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200 shrink-0">✕ Rejected</span>
                                        @elseif($sItem['status'] === 'pending_re_review')
                                            <span class="text-[10px] font-black text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 shrink-0">↻ Re-Review</span>
                                        @else
                                            <span class="text-[10px] font-semibold text-slate-400 italic shrink-0">Pending</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if($canReviewUpload && $milestone->submissions->isNotEmpty())
                        <div class="mt-2 pt-3 border-t border-slate-200/70">
                            <div x-show="!editingDecision" class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold {{ ($mySupReviewGen['status'] ?? '') === 'approved' ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ ($mySupReviewGen['status'] ?? '') === 'approved' ? '✓ Your approval is recorded' : (($mySupReviewGen['status'] ?? '') === 'rejected' ? '✕ You requested revisions' : 'Decision recorded') }}
                                </span>
                                <button type="button" @click="editingDecision = true" class="text-[10px] font-black uppercase tracking-wider text-indigo-600 hover:text-indigo-800 underline cursor-pointer">
                                    Update Decision
                                </button>
                            </div>

                            <form x-show="editingDecision" method="POST" @submit="if(!remarks.trim()) { alert('A comment is required to accept or reject the upload.'); event.preventDefault(); }" class="space-y-2">
                                @csrf
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-slate-700">Review Decision <span class="text-rose-500">*</span></label>
                                    <button type="button" x-show="{{ $needsMyReviewGen ? 'false' : 'true' }}" @click="editingDecision = false" class="text-[10px] font-bold text-slate-400 hover:text-slate-600">Cancel</button>
                                </div>
                                <textarea name="remarks" x-model="remarks" rows="2" class="w-full text-xs rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 p-2.5 shadow-sm resize-none bg-white placeholder:text-slate-400" placeholder="Required: Explain your decision..." required></textarea>
                                
                                <div class="flex gap-2">
                                    <button type="submit" formaction="{{ route('milestones.accept_upload', $milestone) }}" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-xl text-xs font-bold transition-colors cursor-pointer">
                                        ✓ Approve
                                    </button>
                                    <button type="submit" formaction="{{ route('milestones.reject_upload', $milestone) }}" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white py-2 rounded-xl text-xs font-bold transition-colors cursor-pointer">
                                        ✕ Reject
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>

                {{-- Step 3: Presentation Conducted --}}
                <div class="p-5 rounded-2xl border transition-all {{ $isConducted ? 'bg-emerald-50/50 border-emerald-200' : ($hasSchedule ? 'bg-blue-50/50 border-blue-200' : 'bg-slate-50 border-slate-200') }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black {{ $isConducted ? 'bg-emerald-500 text-white' : ($hasSchedule ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700') }}">3</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $isConducted ? 'bg-emerald-100 text-emerald-800' : ($hasSchedule ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ $isConducted ? 'Conducted' : ($hasSchedule ? 'Scheduled' : 'Pending Date') }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">Presentation Scheduled</h4>
                        <p class="text-xs text-slate-500 mt-1 mb-3">
                            @if($hasSchedule)
                                {{ $defenceDate->format('d M Y') }} {{ $milestone->defence_time ? '• ' . \Carbon\Carbon::parse($milestone->defence_time)->format('g:i A') : '' }}
                            @else
                                Presentation date not scheduled yet.
                            @endif
                        </p>
                    </div>

                    @if($milestone->meeting_link)
                        <a href="{{ $milestone->meeting_link }}" target="_blank" rel="noopener noreferrer"
                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span>Join Zoom Link</span>
                        </a>
                    @endif
                </div>

                {{-- Step 4: Examiner Evaluation / Grading --}}
                <div class="p-5 rounded-2xl border transition-all {{ $isGraded ? ($isPass ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50/50 border-rose-200') : 'bg-slate-50 border-slate-200' }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black {{ $isGraded ? ($isPass ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white') : 'bg-slate-200 text-slate-700' }}">4</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $isGraded ? ($isPass ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800') : 'bg-amber-100 text-amber-800' }}">
                                {{ $isGraded ? ($isPass ? 'Passed' : 'Failed') : 'Awaiting Grade' }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">Examiner Grading</h4>
                        <p class="text-xs text-slate-500 mt-1 mb-3">
                            @if($isGraded)
                                @if($isPass)
                                    Candidate passed examiner evaluation. Comments delivered to student inbox.
                                @else
                                    Candidate failed examiner evaluation. Must repeat milestone.
                                @endif
                            @else
                                Assigned examiners must submit Pass or Fail grade.
                            @endif
                        </p>
                    </div>

                    @if($isGraded)
                        <div class="p-2.5 rounded-xl {{ $isPass ? 'bg-emerald-100/70 text-emerald-900 border border-emerald-200' : 'bg-rose-100/70 text-rose-900 border border-rose-200' }} text-center">
                            <span class="text-xs font-black uppercase tracking-wider">Verdict: {{ strtoupper($gradingOutcome ?? 'Graded') }}</span>
                        </div>
                        @if(auth()->user()->hasRole('Student'))
                            <a href="{{ $evaluatorId ? route('inbox.index', ['user_id' => $evaluatorId]) : route('inbox.index') }}" class="mt-2 inline-flex items-center justify-center gap-1.5 w-full py-1.5 px-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 rounded-xl text-[10px] font-bold transition-colors">
                                <svg class="w-3 h-3 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span>View Examiner Comments in Inbox</span>
                            </a>
                        @endif
                    @endif
                </div>

                {{-- Step 5: Admin Clearance / Advancement --}}
                <div class="p-5 rounded-2xl border transition-all {{ $isApproved ? 'bg-emerald-50/50 border-emerald-200' : ($canEndPresentation ? ($isFail ? 'bg-rose-50/60 border-rose-200' : 'bg-blue-50/60 border-blue-200') : 'bg-slate-50 border-slate-200') }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black {{ $isApproved ? 'bg-emerald-500 text-white' : ($canEndPresentation ? ($isFail ? 'bg-rose-600 text-white' : 'bg-blue-600 text-white') : 'bg-slate-200 text-slate-700') }}">5</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $isApproved ? 'bg-emerald-100 text-emerald-800' : ($canEndPresentation ? ($isFail ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800') : 'bg-slate-200 text-slate-700') }}">
                                {{ $isApproved ? 'Cleared' : ($canEndPresentation ? ($isFail ? 'Repeat Required' : 'Ready') : 'Pending') }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">Milestone Clearance</h4>
                        <p class="text-xs text-slate-500 mt-1 mb-3">
                            @if($isApproved)
                                Session ended. Advanced to {{ $nextDestination }}.
                            @elseif($isFail)
                                Candidate failed grading and must repeat this milestone.
                            @else
                                Admin ends presentation session to advance candidate.
                            @endif
                        </p>
                    </div>

                    @if(!$isApproved && auth()->user()->hasRole('Admin'))
                        @if($canEndPresentation)
                            <form action="{{ route('milestones.end_presentation', $milestone) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                    data-confirm="Are you sure you want to end the presentation session for {{ addslashes($milestone->thesis->student->user->name) }}? {{ $isFail ? 'The candidate received a FAIL grade and will repeat this milestone.' : 'The student will be advanced to ' . $nextDestination . '.' }}"
                                    data-confirm-title="End Presentation Session"
                                    data-confirm-type="{{ $isFail ? 'danger' : 'success' }}"
                                    data-confirm-btn="{{ $isFail ? 'End & Repeat Milestone' : 'End Presentation' }}"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 {{ $isFail ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/30' : 'bg-blue-600 hover:bg-blue-700 shadow-blue-600/30' }} text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md active:scale-95 cursor-pointer">
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span>{{ $isFail ? 'Repeat Milestone' : 'End Presentation' }}</span>
                                </button>
                            </form>
                        @else
                            <div>
                                <button disabled class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-200 text-slate-500 rounded-xl text-xs font-bold uppercase tracking-wider cursor-not-allowed">
                                    <span>End Presentation</span>
                                </button>
                                <p class="text-[10px] text-amber-700 font-bold mt-1">Pending: {{ ucfirst($endSessionBlockReason) }}</p>
                            </div>
                        @endif
                    @elseif(!$isApproved)
                        <div class="p-2.5 bg-slate-100 rounded-xl text-center">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Awaiting Admin Clearance</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endif
