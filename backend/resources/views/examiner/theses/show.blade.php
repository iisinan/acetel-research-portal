@extends('layouts.dashboard')

@section('header', 'Thesis Details')

@section('content')
@php
    $roleLabel = '';
    if (in_array($thesis->internal_examiner_profile_id, $internalProfileIds)) {
        $roleLabel = 'Internal Examiner';
    } elseif (in_array($thesis->external_examiner_profile_id, $externalProfileIds)) {
        $roleLabel = 'External Examiner';
    }
@endphp
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">{{ $thesis->title ?? 'Untitled Thesis' }}</h2>
            <p class="text-slate-500 mt-1">Student: <span class="font-bold text-slate-700">{{ $thesis->studentProfile->user->name ?? 'N/A' }}</span> ({{ $thesis->studentProfile->student_id_number ?? 'N/A' }})</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('milestones.index', ['thesis_id' => $thesis->id, 'expanded' => $thesis->current_milestone_id]) }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-white bg-purple-600 rounded-xl hover:bg-purple-700 transition-colors shadow-xs">
                Open Milestones Workspace
            </a>
            <a href="{{ route('examiner.theses.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                Back to Assigned Theses
            </a>
        </div>
    </div>

    @php
        $workflow = app(\App\Services\MilestoneWorkflowService::class);
        $internalDefenceMilestone = $thesis->milestones->first(fn($m) => $workflow->isInternalDefence($m));
        $isPostDefenceStage = $internalDefenceMilestone && $workflow->isPostDefencePhase($internalDefenceMilestone);
        $postDefenceSub = $isPostDefenceStage ? $workflow->getPostDefenceSubmission($internalDefenceMilestone) : null;
        $canApproveCorrections = $internalDefenceMilestone && ($workflow->isAssignedInternalExaminer($internalDefenceMilestone, auth()->user()) || auth()->user()->hasRole('Admin'));
    @endphp

    @if($isPostDefenceStage && $canApproveCorrections && $internalDefenceMilestone->status !== 'approved')
    <div class="p-6 rounded-3xl border-2 border-purple-200 bg-purple-50/70 space-y-4 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center font-black text-sm shadow-sm">
                    ⚖️
                </div>
                <div>
                    <h3 class="text-sm font-black text-purple-950 uppercase tracking-wider">Post-Defence Correction Verification (Internal Defence)</h3>
                    <p class="text-xs font-medium text-purple-800 mt-0.5">
                        Candidate passed the Internal Defence presentation. Review their corrected thesis manuscript below to advance them to Viva (Milestone 07).
                    </p>
                </div>
            </div>
            <a href="{{ route('milestones.show', $internalDefenceMilestone) }}" class="px-3 py-1.5 bg-white border border-purple-200 text-purple-700 hover:bg-purple-100 rounded-xl text-[10px] font-black uppercase tracking-wider shrink-0 transition-all">
                Open Milestone View
            </a>
        </div>

        @if($postDefenceSub)
            <div class="p-4 bg-white rounded-2xl border border-purple-200 flex items-center justify-between gap-4">
                <div>
                    <span class="px-2 py-0.5 bg-purple-100 text-purple-800 rounded text-[9px] font-black uppercase tracking-wider">Corrected Thesis Uploaded</span>
                    <p class="text-xs font-bold text-slate-900 mt-1">{{ $postDefenceSub->file_meta['original_name'] ?? 'Corrected Thesis Document' }} (v.0{{ $postDefenceSub->version }})</p>
                    @if($postDefenceSub->description)
                        <p class="text-[11px] text-slate-600 mt-0.5 italic">"{{ $postDefenceSub->description }}"</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ Storage::url($postDefenceSub->file_url) }}" target="_blank" class="px-3 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all">
                        Download Corrected Thesis
                    </a>
                    <a href="{{ route('submissions.view', $postDefenceSub->id) }}" class="px-3 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all">
                        View Details
                    </a>
                </div>
            </div>

            <form action="{{ route('milestones.review.update', $internalDefenceMilestone) }}" method="POST" class="space-y-3 bg-white p-5 rounded-2xl border border-purple-100">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-1.5">Internal Examiner Decision</label>
                    <select name="decision" required class="w-full rounded-xl border-slate-200 text-xs font-bold text-slate-800 py-2.5 px-3 focus:ring-purple-500 focus:border-purple-500">
                        <option value="approved">✅ Approve Corrected Thesis & Advance Candidate to Viva</option>
                        <option value="revision_required">🔄 Request Further Corrections</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-600 uppercase tracking-widest mb-1.5">Examiner Remarks / Verification Notes <span class="text-rose-500">*</span></label>
                    <textarea name="remarks" rows="3" required class="w-full rounded-xl border-slate-200 text-xs p-3 focus:ring-purple-500 focus:border-purple-500" placeholder="Provide your verification notes on the candidate's post-defence corrections..."></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md shadow-purple-600/20 transition-all cursor-pointer">
                        Submit Correction Decision
                    </button>
                </div>
            </form>
        @else
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs font-bold text-amber-900">
                ⏳ Waiting for the candidate to upload their post-defence corrected thesis manuscript. Once uploaded, you will be able to review and approve it here.
            </div>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Submissions / Milestones -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-lg">Milestone Submissions</h3>
                </div>
                <div class="p-6">
                    @forelse($thesis->milestones as $milestone)
                        <div class="mb-6 last:mb-0">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-bold text-slate-700">{{ $milestone->template->name }}</h4>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $milestone->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">{{ ucfirst($milestone->status) }}</span>
                            </div>
                            
                            @if($milestone->submissions->isNotEmpty())
                                <ul class="space-y-3">
                                    @foreach($milestone->submissions as $submission)
                                        <li class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl border border-slate-200 bg-slate-50">
                                            <div class="flex items-center gap-3">
                                                <svg class="w-6 h-6 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-700">Version {{ $submission->version }}</p>
                                                    <p class="text-xs text-slate-500">{{ $submission->created_at->format('M d, Y H:i') }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 self-end sm:self-auto">
                                                <a href="{{ Storage::url($submission->file_url) }}" target="_blank" class="px-3 py-1.5 text-xs font-bold text-brand-600 bg-brand-50 border border-brand-100 rounded-lg hover:bg-brand-100 transition-colors">
                                                    Download
                                                </a>
                                                <a href="{{ route('submissions.view', $submission->id) }}" class="px-3 py-1.5 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                                    View Details
                                                </a>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-slate-500 italic p-3 bg-slate-50 rounded-xl border border-slate-100">No submissions uploaded for this milestone.</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 italic p-6 text-center">No milestones are currently defined for this thesis.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Assignment Details -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-lg">Assignment Info</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-xs font-bold tracking-widest text-slate-400 uppercase mb-1">Your Role</p>
                        <p class="font-bold text-slate-800">{{ $roleLabel }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold tracking-widest text-slate-400 uppercase mb-1">Assigned Date</p>
                        <p class="font-semibold text-slate-800">{{ $thesis->updated_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Evaluations -->
            @php
                $defenceEvents = $thesis->defenceEvents()->orderBy('schedule_start', 'desc')->get();
            @endphp
            
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-lg">Evaluation</h3>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($defenceEvents as $event)
                        @php
                            $existingEvaluation = \App\Models\Evaluation::where('defence_event_id', $event->id)
                                ->where('evaluator_id', auth()->id())
                                ->first();
                        @endphp
                        <div class="p-4 rounded-xl border {{ $existingEvaluation ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50' }}">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h4 class="font-bold text-slate-800 capitalize">{{ str_replace('_', ' ', $event->type) }}</h4>
                                    <p class="text-xs text-slate-500">{{ $event->schedule_start->format('M d, Y H:i') }}</p>
                                </div>
                                @if($existingEvaluation)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Submitted</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">Pending Action</span>
                                @endif
                            </div>
                            
                            @if($existingEvaluation)
                                <div class="mt-4">
                                    <a href="{{ route('evaluations.show', $existingEvaluation->id) }}" class="block w-full text-center py-2 bg-white border border-green-200 text-green-700 font-bold rounded-lg hover:bg-green-100 transition-colors">
                                        View Evaluation
                                    </a>
                                </div>
                            @else
                                <div class="mt-4">
                                    <a href="{{ route('evaluations.create', $event->id) }}" class="block w-full text-center py-2 bg-amber-500 text-white font-bold rounded-lg hover:bg-amber-600 transition-colors shadow-sm">
                                        Submit Evaluation
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center p-4">
                            <div class="w-12 h-12 mx-auto bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700 mb-1">No Events Scheduled</p>
                            <p class="text-xs text-slate-500">You will be able to submit your evaluation once the coordinator schedules the defence.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
