@php
    $workflow = app(\App\Services\MilestoneWorkflowService::class);
    $slug = $milestone->template?->slug;
    $isSeminar = ($slug === 'seminar_as_a_course');
    $isPresentationMilestone = in_array($slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2']);
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
        $endSessionBlockReason = $workflow->getEndSessionBlockReason($milestone);
        $canEndPresentation = is_null($endSessionBlockReason);
        $latestPptSub = $milestone->submissions->where('type', 'ppt')->sortByDesc('created_at')->first() 
            ?? $milestone->submissions->sortByDesc('created_at')->first();
        $pptUrl = $latestPptSub?->file_url ? (str_starts_with($latestPptSub->file_url, 'http') ? $latestPptSub->file_url : \Illuminate\Support\Facades\Storage::url($latestPptSub->file_url)) : null;

        // Next destination name
        $nextDestination = match($slug) {
            'seminar_as_a_course' => 'Supervisors Assigned',
            'proposal_defence' => 'Progress Report 1',
            'progress_report_1' => 'Progress Report 2',
            'progress_report_2' => 'Internal Defence',
            default => 'Next Milestone'
        };

        $gradingOutcome = $workflow->getAverageGradingOutcome($milestone);
        $isPass = ($gradingOutcome === 'pass');
        $isFail = ($gradingOutcome === 'fail');

        $checklistEventType = $milestone->template?->defence_type ?? match($slug) {
            'seminar_as_a_course' => 'seminar',
            'proposal_defence' => 'proposal',
            'progress_report_1' => 'progress_report_1',
            'progress_report_2' => 'progress_report_2',
            default => null
        };
        $checklistEvent = $checklistEventType && $milestone->thesis 
            ? ($milestone->thesis->relationLoaded('defenceEvents') 
                ? $milestone->thesis->defenceEvents->firstWhere('type', $checklistEventType) 
                : $milestone->thesis->defenceEvents()->where('type', $checklistEventType)->first()) 
            : null;
        $evaluatorId = $checklistEvent?->evaluations?->first()?->evaluator_id;

        $totalSteps = $isSeminar ? 3 : 5;
        $completedSteps = 0;
        if ($isSeminar) {
            if ($hasSchedule) $completedSteps++;
            if ($isGraded) $completedSteps++;
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
                                    data-confirm="Are you sure you want to end the presentation session for {{ addslashes($milestone->thesis?->student?->user?->name ?? 'Candidate') }}? The student will be advanced to Supervisors Assigned."
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
        @else
            {{-- Proposal Defence / Progress Report 1 / Progress Report 2: 5 Steps --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 pt-6">
                {{-- Step 1: Upload PPT / Document --}}
                @php
                    $hasPptType = $milestone->submissions->whereIn('type', ['ppt', 'presentation'])->isNotEmpty();
                    $hasDocType = $milestone->submissions->whereIn('type', ['manuscript', 'file'])->isNotEmpty() || ($milestone->submissions->isNotEmpty() && !$hasPptType);

                    $step1Title = match($slug) {
                        'proposal_defence' => ($hasDocType && !$hasPptType ? 'Upload Proposal Document' : ($hasPptType && !$hasDocType ? 'Upload Presentation (PPT)' : 'Upload Proposal / Presentation')),
                        'progress_report_1', 'progress_report_2' => ($hasDocType && !$hasPptType ? 'Upload Progress Report' : ($hasPptType && !$hasDocType ? 'Upload Presentation (PPT)' : 'Upload Report / Presentation')),
                        default => ($hasPptType ? 'Upload Presentation (PPT)' : 'Upload Submission Document'),
                    };

                    $step1UploadedText = match($slug) {
                        'proposal_defence' => ($hasDocType && !$hasPptType ? 'Proposal document uploaded by student.' : ($hasPptType && !$hasDocType ? 'Slide deck uploaded by student.' : 'Proposal document / slide deck uploaded.')),
                        'progress_report_1', 'progress_report_2' => ($hasDocType && !$hasPptType ? 'Progress report uploaded by student.' : ($hasPptType && !$hasDocType ? 'Slide deck uploaded by student.' : 'Progress report / slide deck uploaded.')),
                        default => 'Submission document uploaded by student.',
                    };

                    $step1PendingText = match($slug) {
                        'proposal_defence' => 'Candidate must upload proposal document or presentation slides.',
                        'progress_report_1', 'progress_report_2' => 'Candidate must upload progress report or presentation slides.',
                        default => 'Candidate must upload required document or presentation slides.',
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
                                title: '{{ addslashes($step1Title) }} - {{ addslashes($milestone->thesis?->student?->user?->name ?? 'Candidate') }}', 
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
                <div class="p-5 rounded-2xl border transition-all {{ $isSupervisorApproved ? 'bg-emerald-50/50 border-emerald-200' : ($milestone->status === 'revision_required' ? 'bg-rose-50/50 border-rose-200' : 'bg-slate-50 border-slate-200') }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-black {{ $isSupervisorApproved ? 'bg-emerald-500 text-white' : ($milestone->status === 'revision_required' ? 'bg-rose-500 text-white' : 'bg-slate-200 text-slate-700') }}">2</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $isSupervisorApproved ? 'bg-emerald-100 text-emerald-800' : ($milestone->status === 'revision_required' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ $isSupervisorApproved ? 'Approved' : ($milestone->status === 'revision_required' ? 'Revision Needed' : 'Awaiting Review') }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900">Supervisor Approval</h4>
                        @php
                            $supSummary = $workflow->getSupervisorReviewSummary($milestone);
                        @endphp
                        <p class="text-xs text-slate-500 mt-1 mb-3">
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
                    </div>

                    @if(!$isSupervisorApproved && (auth()->user()->hasRole('Supervisor') || auth()->user()->hasRole('Admin')) && $milestone->submissions->isNotEmpty())
                        <div class="flex gap-2">
                            <form action="{{ route('milestones.accept_upload', $milestone) }}" method="POST" class="flex-1"
                                data-confirm="Are you sure you want to accept this document?"
                                data-confirm-title="Accept Document"
                                data-confirm-type="success"
                                data-confirm-btn="Accept Upload">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span>Accept</span>
                                </button>
                            </form>
                            <form action="{{ route('milestones.reject_upload', $milestone) }}" method="POST" class="flex-1"
                                data-confirm="Are you sure you want to reject this document? The student will be notified to revise it."
                                data-confirm-title="Reject Document"
                                data-confirm-type="danger"
                                data-confirm-btn="Reject Upload">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    <span>Reject</span>
                                </button>
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
                                    data-confirm="Are you sure you want to end the presentation session for {{ addslashes($milestone->thesis?->student?->user?->name ?? 'Candidate') }}? {{ $isFail ? 'The candidate received a FAIL grade and will repeat this milestone.' : 'The student will be advanced to ' . $nextDestination . '.' }}"
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
