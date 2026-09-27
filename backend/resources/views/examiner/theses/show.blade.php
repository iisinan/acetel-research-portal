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
        <a href="{{ route('examiner.theses.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
            Back to Assigned Theses
        </a>
    </div>

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
                                        <li class="flex items-center justify-between p-3 rounded-xl border border-slate-200 bg-slate-50">
                                            <div class="flex items-center gap-3">
                                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-700">Version {{ $submission->version }}</p>
                                                    <p class="text-xs text-slate-500">{{ $submission->created_at->format('M d, Y H:i') }}</p>
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <a href="{{ Storage::url($submission->file_url) }}" target="_blank" class="px-3 py-1 text-xs font-bold text-brand-600 bg-brand-50 border border-brand-100 rounded-lg hover:bg-brand-100 transition-colors">
                                                    Download
                                                </a>
                                                <a href="{{ route('submissions.view', $submission->id) }}" class="px-3 py-1 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
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

            <!-- Placeholder for Evaluations -->
            <div class="bg-white border border-amber-200 rounded-2xl shadow-sm overflow-hidden relative">
                <div class="absolute top-0 right-0 p-3 opacity-10 pointer-events-none">
                    <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L1 21h22L12 2zm0 3.83l8.27 14.17H3.73L12 5.83zM11 10h2v5h-2v-5zm0 6h2v2h-2v-2z"/></svg>
                </div>
                <div class="p-6 relative z-10">
                    <h3 class="font-bold text-amber-800 text-lg mb-2">Evaluation Form</h3>
                    <p class="text-sm text-amber-700 mb-4 leading-relaxed">You will be able to submit your official evaluation report here once the final defence event is scheduled by the coordinator.</p>
                    <button disabled class="w-full py-2.5 bg-amber-100/50 text-amber-700 font-bold rounded-xl cursor-not-allowed opacity-70 border border-amber-200">
                        Pending Scheduling
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
