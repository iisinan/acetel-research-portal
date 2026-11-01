<div id="milestone-details-container-{{ $milestone->id }}" class="space-y-6" x-data="{ showStudentMessageModal: false, messageRecipient: '', showUploadForm: {{ in_array($milestone->status, ['submitted', 'in_review']) ? 'false' : 'true' }} }">
    <!-- Sophisticated Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <a href="{{ route('theses.show', $milestone->thesis_project_id) }}" class="p-1.5 rounded-lg bg-acetel-50 hover:bg-acetel-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Milestone Details</span>
            </div>
            <h1 class="text-2xl md:text-4xl font-black text-slate-900 tracking-tight">{{ $milestone->template->name }}</h1>
            <p class="mt-2 text-sm font-medium text-slate-500 max-w-2xl">{{ $milestone->template->description }}</p>
        </div>
        <div class="flex items-center gap-4">
            @php
                $statusBadge = match($milestone->status) {
                    'not_started' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'in_progress' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                    'submitted' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'revision_required' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                    'approved' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'M5 13l4 4L19 7'],
                    default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z']
                };
            @endphp
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl {{ $statusBadge['bg'] }} border {{ $statusBadge['border'] }} shadow-xl shadow-slate-200/40">
                <svg class="w-4 h-4 {{ $statusBadge['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $statusBadge['icon'] }}"></path></svg>
                <span class="text-[10px] font-black {{ $statusBadge['text'] }} uppercase tracking-widest">{{ str_replace('_', ' ', $milestone->status) }}</span>
            </div>
            
            @if(auth()->user()->hasRole('Admin') && $milestone->status !== 'approved' && (!empty($milestone->defence_date) || in_array($milestone->template?->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2'])))
            <form action="{{ route('milestones.end_presentation', $milestone) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                    data-confirm="Are you sure you want to end the presentation session for {{ addslashes($milestone->thesis->student->user?->name ?? 'User') }}? If all requirements are met (presentation conducted, presentation PDF uploaded, and supervisor approved), the candidate will be advanced to the next milestone."
                    data-confirm-title="End Presentation Session"
                    data-confirm-type="success"
                    data-confirm-btn="End Presentation"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-xl shadow-blue-500/20 text-[10px] font-black uppercase tracking-widest transition-all active:scale-95">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>End Presentation</span>
                </button>
            </form>
            @endif

            @if(auth()->id() !== $milestone->thesis->student->user_id && ($milestone->template->has_chat || $milestone->template->slug === 'supervisors_assigned'))
            <button type="button" @click.prevent.stop="setTimeout(() => { showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($milestone->thesis->student->user?->name ?? 'User') }}; }, 50)" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl shadow-xl shadow-slate-200/40 transition-colors">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span class="text-[10px] font-black uppercase tracking-widest hidden sm:inline">Message Student</span>
            </button>
            @endif
            
            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-lg font-black text-slate-900 shadow-xl shadow-slate-200/40">
                0{{ $milestone->template->order }}
            </div>
        </div>
    </div>

    @if($milestone->template?->slug === 'supervisors_assigned')
        @include('milestones.partials.supervisors-assigned-card', ['milestone' => $milestone])
    @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Column: Submission Area -->
        <div class="lg:col-span-2 space-y-6">
            {{-- Milestone Progression Checklist & End Presentation Banner --}}
            @include('milestones.partials.progression-checklist', ['milestone' => $milestone])

            <!-- Scheduled Date Section -->
            @php
                $hasDefenceDateAllowed = $milestone->template->allow_defence_date;
                $defenceDateStr = $milestone->defence_date;
                $defenceDate = $defenceDateStr ? \Carbon\Carbon::parse($defenceDateStr) : null;
                $isApproved = !is_null($milestone->date_approved_at);
                $isDateExpired = $defenceDate && $defenceDate->endOfDay()->isPast() && $milestone->status !== 'approved';
                
                $isInternalDefence = ($milestone->template?->slug === 'internal_defence' || $milestone->template?->order == 6);
                $hasDefenceOccurred = $defenceDate && ($defenceDate->isPast() || $defenceDate->isToday());
                $isPostDefenceStage = $isInternalDefence && $hasDefenceOccurred && $milestone->status !== 'approved';

                $latestSubForBanner = $milestone->submissions->sortByDesc('created_at')->first();
                $latestManuscript = $milestone->submissions->whereIn('type', ['manuscript', 'file'])->sortByDesc('created_at')->first();
                $hasPostDefenceManuscript = $isPostDefenceStage && $latestManuscript && $latestManuscript->created_at->gte($defenceDate->copy()->startOfDay());
                $postDefenceDecision = $hasPostDefenceManuscript && $latestManuscript->feedback ? $latestManuscript->feedback->decision : null;

                // Institutional Mandate: Supervisors cannot schedule presentations, only Admin can
                $canSetDate = auth()->user()->hasRole('Admin') && !request()->is('supervisor*');
                if (auth()->user()->hasRole('Supervisor') && !auth()->user()->hasRole('Admin')) {
                    $canSetDate = false;
                }
                $isUploadAccepted = $latestSubForBanner && $latestSubForBanner->feedback && $latestSubForBanner->feedback->decision === 'approved';
                $isScheduled = $defenceDate && !$isDateExpired;
            @endphp

            {{-- Post-Defence Corrections Banner for Internal Defence --}}
            @if($isPostDefenceStage)
                <div class="overflow-hidden rounded-[2rem] {{ $postDefenceDecision === 'revision_required' ? 'bg-amber-50 border-amber-200' : ($hasPostDefenceManuscript ? 'bg-indigo-50 border-indigo-200' : 'bg-purple-50 border-purple-200') }} border shadow-xl shadow-slate-200/40 relative mb-6">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ $postDefenceDecision === 'revision_required' ? 'bg-amber-500' : ($hasPostDefenceManuscript ? 'bg-indigo-500' : 'bg-purple-600') }}"></div>
                    <div class="p-6 sm:p-7">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl {{ $postDefenceDecision === 'revision_required' ? 'bg-amber-100 text-amber-600' : ($hasPostDefenceManuscript ? 'bg-indigo-100 text-indigo-600' : 'bg-purple-100 text-purple-600') }} flex items-center justify-center flex-shrink-0 shadow-sm">
                                @if($hasPostDefenceManuscript && $postDefenceDecision !== 'revision_required')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full {{ $postDefenceDecision === 'revision_required' ? 'bg-amber-200/80 text-amber-900' : ($hasPostDefenceManuscript ? 'bg-indigo-200/80 text-indigo-900' : 'bg-purple-200/80 text-purple-900') }} text-[10px] font-black uppercase tracking-wider mb-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $postDefenceDecision === 'revision_required' ? 'bg-amber-600' : ($hasPostDefenceManuscript ? 'bg-indigo-600' : 'bg-purple-600') }}"></span>
                                    {{ $postDefenceDecision === 'revision_required' ? 'Further Corrections Required' : ($hasPostDefenceManuscript ? 'Corrections Under Review' : 'Post-Defence Corrections Required') }}
                                </div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight leading-snug">
                                    @if($hasPostDefenceManuscript && $postDefenceDecision !== 'revision_required')
                                        Post-Defence Corrected Thesis Submitted
                                    @elseif($postDefenceDecision === 'revision_required')
                                        Internal Examiner Requested Additional Corrections
                                    @else
                                        Internal Defence Concluded — Submit Corrected Thesis
                                    @endif
                                </h3>
                                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                                    @if($hasPostDefenceManuscript && $postDefenceDecision !== 'revision_required')
                                        Your revised thesis incorporating defence comments was uploaded on {{ $latestManuscript->created_at->format('d M Y, H:i') }}. Awaiting clearance by Assigned Internal Examiner ({{ $milestone->thesis->internalExaminer?->user?->name ?? 'Examiner' }}). Once approved, you will advance to Milestone 7 (Viva Voce).
                                    @elseif($postDefenceDecision === 'revision_required')
                                        The Internal Examiner has reviewed your submission and requested adjustments. Please see the comments below and upload an updated thesis file.
                                    @else
                                        Your defence took place on {{ $defenceDate->format('d M Y') }}. Please incorporate all corrections and observations received from the defence session and upload your corrected thesis manuscript below for final Internal Examiner approval.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 1. If upload is accepted and NOT yet scheduled, tell student to await schedule --}}
            @if($isUploadAccepted && !$isScheduled && !$isPostDefenceStage && auth()->user()->hasRole('Student'))
                <div class="overflow-hidden rounded-[2rem] bg-emerald-50 border border-emerald-200 shadow-xl shadow-slate-200/40 relative mb-6">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-500"></div>
                    <div class="p-6 sm:p-7">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center flex-shrink-0 text-emerald-600 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-emerald-200/70 text-emerald-800 text-[10px] font-black uppercase tracking-wider mb-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    Upload Accepted
                                </div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight leading-snug">
                                    @if($hasDefenceDateAllowed)
                                        Your upload has been accepted. Please await your presentation schedule.
                                    @else
                                        Your upload has been accepted by your supervisor.
                                    @endif
                                </h3>
                                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                                    @if($hasDefenceDateAllowed)
                                        The administration is currently preparing your presentation session. Once scheduled, your date, time, and Zoom meeting link will appear right here.
                                    @else
                                        Your submission has been verified and is proceeding for milestone clearance.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($hasDefenceDateAllowed)
                @if(!$defenceDate || ($isDateExpired && !$isInternalDefence))
                    @if($canSetDate)
                        <div class="bg-white rounded-[2.5rem] border border-indigo-100 shadow-xl shadow-slate-200/40 p-6 sm:p-8 mb-6 relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 to-white pointer-events-none"></div>
                            <div class="relative flex flex-col gap-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-black text-slate-900 tracking-tight">
                                            {{ $isDateExpired ? 'Schedule Expired' : 'Schedule Presentation' }}
                                        </h3>
                                        <p class="text-xs font-bold text-slate-500 mt-1 uppercase tracking-wider">
                                            {{ $isDateExpired ? 'The previous date passed. Please set a new date and link.' : 'Set the date, time, and meeting link for this presentation.' }}
                                        </p>
                                    </div>
                                </div>

                                <form x-data="{ scheduling: false, localDate: '', localTime: '', localLink: '' }" @submit.prevent="
                                    scheduling = true;
                                    fetch('{{ route('milestones.set_defence_date', $milestone) }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({ defence_date: localDate, defence_time: localTime, meeting_link: localLink })
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) {
                                            window.location.reload();
                                        } else {
                                            (window.toast ? window.toast.error(data.message || 'Scheduling failed') : alert(data.message));
                                        }
                                    })
                                    .finally(() => scheduling = false)
                                " class="w-full">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Presentation Date</label>
                                            <input type="date" 
                                                name="defence_date" 
                                                x-model="localDate"
                                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 w-full" 
                                                required>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Time (Optional)</label>
                                            <input type="time" 
                                                name="defence_time" 
                                                x-model="localTime"
                                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 w-full">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Zoom / Meeting Link (Optional)</label>
                                            <input type="url" 
                                                name="meeting_link" 
                                                x-model="localLink"
                                                placeholder="https://zoom.us/j/..."
                                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 w-full">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        @if(auth()->user()->hasRole('Admin') && $milestone->status !== 'approved' && $isDateExpired)
                                            <button type="button" 
                                                onclick="if (confirm('Are you sure you want to end the presentation session for {{ addslashes($milestone->thesis->student->user?->name ?? 'User') }}? If all requirements are met, the candidate will be advanced to the next milestone.')) { document.getElementById('end-pres-form-expired-{{ $milestone->id }}').submit(); }"
                                                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black uppercase tracking-[0.2em] rounded-xl active:scale-95 transition-all shadow-lg shadow-blue-600/20 flex items-center justify-center gap-2 cursor-pointer">
                                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>End Presentation</span>
                                            </button>
                                        @else
                                            <div></div>
                                        @endif
                                        <button type="submit" 
                                            :disabled="scheduling || !localDate"
                                            class="px-6 py-2.5 bg-indigo-600 text-white text-xs font-black uppercase tracking-[0.2em] rounded-xl hover:bg-indigo-700 active:scale-95 transition-all shadow-lg shadow-indigo-600/20 flex items-center justify-center gap-2 disabled:opacity-50">
                                            <span x-text="scheduling ? 'Saving...' : 'Authorize Schedule'"></span>
                                        </button>
                                    </div>
                                </form>
                                @if(auth()->user()->hasRole('Admin') && $milestone->status !== 'approved' && $isDateExpired)
                                    <form id="end-pres-form-expired-{{ $milestone->id }}" action="{{ route('milestones.end_presentation', $milestone) }}" method="POST" class="hidden">
                                        @csrf
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif

                @if($defenceDate && !$isDateExpired)
                    <div class="bg-white rounded-[2.5rem] border border-emerald-100 shadow-xl shadow-slate-200/40 p-6 sm:p-8 mb-6 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-50/40 via-white to-blue-50/30 pointer-events-none"></div>
                        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                            <div class="flex items-start sm:items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                            Scheduled Presentation
                                        </span>
                                    </div>
                                    <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                                        {{ $defenceDate->format('l, F j, Y') }}
                                        @if($milestone->defence_time)
                                            <span class="text-emerald-700 font-extrabold text-lg sm:text-xl ml-1">
                                                at {{ \Carbon\Carbon::parse($milestone->defence_time)->format('g:i A') }}
                                            </span>
                                        @endif
                                    </h3>
                                    <p class="text-xs font-medium text-slate-500 mt-1">
                                        {{ $milestone->defence_location ?: 'Online Presentation Session' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                @if($milestone->meeting_link)
                                    <a href="{{ $milestone->meeting_link }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center justify-center gap-2.5 px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-lg shadow-emerald-600/30 hover:shadow-xl hover:shadow-emerald-600/40 active:scale-95 group">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        <span>Join Presentation Meeting</span>
                                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                @endif
                                <a href="{{ route('presentations.show', $milestone->milestone_template_id) }}"
                                    class="inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-md active:scale-95">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>Full Presentation Schedule</span>
                                </a>

                                @if(auth()->user()->hasRole('Admin') && $milestone->status !== 'approved')
                                    <form action="{{ route('milestones.end_presentation', $milestone) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                            data-confirm="{{ $isInternalDefence ? 'Are you sure you want to conclude the internal defence presentation for ' . addslashes($milestone->thesis->student->user?->name ?? 'Candidate') . '? The candidate will enter the Post-Defence Correction stage to upload their revised thesis for Internal Examiner approval before advancing to Viva Voce.' : 'Are you sure you want to end the presentation for ' . addslashes($milestone->thesis->student->user?->name ?? 'User') . '? If all requirements are met (presentation conducted, presentation PDF uploaded, and supervisor approved), the candidate will be advanced to the next milestone.' }}"
                                            data-confirm-title="{{ $isInternalDefence ? 'Conclude Internal Defence Session' : 'End Presentation Session' }}"
                                            data-confirm-type="success"
                                            data-confirm-btn="{{ $isInternalDefence ? 'Conclude & Require Corrections' : 'End Presentation' }}"
                                            class="inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-blue-600/20 active:scale-95">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>{{ $isInternalDefence ? 'Conclude Defence Session' : 'End Presentation' }}</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endif


            <!-- Latest Feedback -->
            @if($milestone->status === 'revision_required' && auth()->user()->hasRole('Student'))
                @php
                    $rejectFeedback = \App\Models\Feedback::whereIn('submission_id', $milestone->submissions()->pluck('id'))
                        ->where('decision', 'revision_required')
                        ->latest()
                        ->first();
                    $rejectedBy = $rejectFeedback ? \App\Models\User::find($rejectFeedback->created_by) : null;
                @endphp
                <div class="overflow-hidden rounded-2xl bg-red-50 border border-red-100 shadow-xl shadow-slate-200/40 relative mb-6">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0 text-red-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold text-red-600 uppercase tracking-wider mb-1">
                                    {{ str_contains(strtolower($milestone->remark ?? ''), 'fail') ? 'Milestone Defence Failed — Repeat Stage Required' : 'Upload Rejected' }}
                                </h3>
                                <div class="text-sm font-medium text-red-900 leading-relaxed">
                                    @if($milestone->remark && str_contains(strtolower($milestone->remark), 'fail'))
                                        {{ $milestone->remark }}
                                    @else
                                        Your recent upload was rejected. Please contact
                                        <strong>{{ $rejectedBy?->name ?? 'your supervisor' }}</strong>
                                        for more information, or upload a revised document below for another review.
                                    @endif
                                </div>
                                @if($milestone->remark && !str_starts_with($milestone->remark, 'Document requires revision') && !str_contains(strtolower($milestone->remark), 'fail'))
                                    <div class="mt-2 text-xs text-red-800"><span class="font-bold">Comment:</span> {{ $milestone->remark }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Submission Form -->
            @if(in_array($milestone->status, ['not_started', 'in_progress', 'revision_required', 'submitted', 'in_review']))
                @if($milestone->template->requires_submission)
                    @if($hasDefenceDateAllowed && $isDateExpired && !$isInternalDefence)
                        <div class="overflow-hidden rounded-[2.5rem] bg-white border border-slate-100 shadow-xl shadow-slate-200/40 p-10 flex flex-col items-center justify-center text-center">
                            <div class="w-16 h-16 bg-slate-100 text-slate-500 rounded-2xl flex items-center justify-center mb-6">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 tracking-tight mb-2">Schedule Expired</h3>
                            <p class="text-sm font-medium text-slate-500 max-w-md leading-relaxed mb-6">The presentation date has passed. Please contact the administrator to reschedule.</p>
                        </div>
                    @elseif($milestone->template->submission_requires_approval && !$milestone->is_submission_unlocked)
                        <!-- Locked State -->
                        <div class="overflow-hidden rounded-[2.5rem] bg-white border border-slate-100 shadow-xl shadow-slate-200/40 p-10 flex flex-col items-center justify-center text-center">
                            <div class="w-16 h-16 bg-slate-100 text-slate-500 rounded-2xl flex items-center justify-center mb-6">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 tracking-tight mb-2">Submission Locked</h3>
                            <p class="text-sm font-medium text-slate-500 max-w-md leading-relaxed mb-6">This phase requires approval before you can submit your work. Please consult with your assigned committee.</p>
                            
                            @can('unlock', $milestone)
                                <form x-data="{ unlocking: false }" @submit.prevent="
                                    unlocking = true;
                                    fetch('{{ route('milestones.unlock', $milestone) }}', {
                                        method: 'POST',
                                        body: new FormData($event.target),
                                        headers: {
                                            'Accept': 'text/html'
                                        }
                                    }).then(res => res.text()).then(html => {
                                        let parser = new DOMParser();
                                        let doc = parser.parseFromString(html, 'text/html');
                                        let newContent = doc.getElementById('milestone-details-container-{{ $milestone->id }}').innerHTML;
                                        document.getElementById('milestone-details-container-{{ $milestone->id }}').innerHTML = newContent;
                                    }).finally(() => {
                                        unlocking = false;
                                    })
                                " action="{{ route('milestones.unlock', $milestone) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                        :disabled="unlocking"
                                        class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white text-sm font-bold rounded-xl hover:bg-emerald-700 transition-colors shadow-xl shadow-slate-200/40 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg x-show="!unlocking" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                                        <svg x-show="unlocking" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span x-text="unlocking ? 'Authenticating...' : 'Unlock Submission Access'"></span>
                                    </button>
                                </form>
                            @else
                                <div class="flex flex-wrap justify-center gap-2">
                                    @foreach($milestone->template->submission_approver_roles ?? [] as $gatekeeperRole)
                                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                                            {{ $gatekeeperRole }} Clearance Required
                                        </span>
                                    @endforeach
                                </div>
                            @endcan
                        </div>
                    @elseif(auth()->user()->hasRole('Student'))
                        <div id="artifact-upload-section" x-show="showUploadForm" x-transition class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 p-8">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                                    <div class="w-1 h-6 bg-emerald-500 rounded-full"></div>
                                    Submission Upload
                                </h3>
                                <button type="button" @click="showUploadForm = false" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 rounded-full transition-colors" x-show="!{{ in_array($milestone->status, ['not_started', 'in_progress', 'revision_required']) ? 'true' : 'false' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                            
                            @if($milestone->is_submission_unlocked)
                                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center gap-4">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-emerald-900 uppercase tracking-wider">Access Granted</p>
                                        <p class="text-xs text-emerald-700 mt-0.5">Unlocked by {{ $milestone->unlockedBy?->name }} at {{ $milestone->submission_unlocked_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                            @php
                                $subTypes = $milestone->template?->submission_type ?? ['file'];
                                $bothAllowed = in_array('ppt', $subTypes) && in_array('file', $subTypes);
                                $hasPptSub = $milestone->submissions->where('type', 'ppt')->count() > 0;
                                $hasFileSub = $milestone->submissions->whereIn('type', ['file', 'manuscript'])->count() > 0;
                                $hasPubRequirement = in_array('publication', $subTypes) || in_array('publications', $subTypes);
                                $student = $milestone->thesis?->student;
                                $isPhdCandidate = $student ? $student->isPhd() : false;
                                $requiredPubs = $student ? $student->requiredPublicationsCount() : 1;
                                $existingPubs = $milestone->submissions->where('type', 'publication');
                                $existingPubsCount = $existingPubs->count();
                                $initialSlotsNeeded = max(1, $requiredPubs - $existingPubsCount);
                                if ($isPostDefenceStage) {
                                    $initialSlotsNeeded = 0;
                                }
                            @endphp
                            <form x-data="{
                                uploading: false,
                                fileError: '',
                                hasPubRequirement: {{ $hasPubRequirement ? 'true' : 'false' }},
                                isPostDefenceStage: {{ $isPostDefenceStage ? 'true' : 'false' }},
                                requiredPubs: {{ $requiredPubs }},
                                existingPubsCount: {{ $existingPubsCount }},
                                isPhd: {{ $isPhdCandidate ? 'true' : 'false' }},
                                pubSlots: [
                                    @for($i = 0; $i < $initialSlotsNeeded; $i++)
                                        { id: {{ $i }}, title: '', doi: '', fileName: '', checking: false, verified: false, scopusIndexed: false, statusText: '', journal: '', error: '' },
                                    @endfor
                                ],
                                addSlot() {
                                    if (this.pubSlots.length + this.existingPubsCount < 5) {
                                        this.pubSlots.push({
                                            id: Date.now(),
                                            title: '',
                                            doi: '',
                                            fileName: '',
                                            checking: false,
                                            verified: false,
                                            scopusIndexed: false,
                                            statusText: '',
                                            journal: '',
                                            error: ''
                                        });
                                    }
                                },
                                removeSlot(index) {
                                    if (this.pubSlots.length > 1 || (this.existingPubsCount >= this.requiredPubs)) {
                                        this.pubSlots.splice(index, 1);
                                    }
                                },
                                verifyDoi(slot) {
                                    if (!slot.doi || !slot.doi.trim()) return;
                                    slot.checking = true;
                                    slot.error = '';
                                    fetch('{{ route('publications.verify_doi') }}?doi=' + encodeURIComponent(slot.doi.trim()), {
                                        headers: { 'Accept': 'application/json' }
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.valid) {
                                            slot.verified = true;
                                            slot.scopusIndexed = !!data.is_scopus_indexed;
                                            slot.statusText = data.status_label || (data.is_scopus_indexed ? 'Indexed in Scopus' : 'DOI Verified');
                                            slot.journal = data.journal || data.publisher || '';
                                            if (!slot.title && data.title) {
                                                slot.title = data.title;
                                            }
                                        } else {
                                            slot.verified = false;
                                            slot.scopusIndexed = false;
                                            slot.statusText = data.error || 'Invalid DOI';
                                            slot.error = data.error || 'Invalid or unrecognized DOI';
                                        }
                                    })
                                    .catch(err => {
                                        slot.error = 'Verification service unreachable';
                                    })
                                    .finally(() => {
                                        slot.checking = false;
                                    });
                                }
                            }" @submit.prevent="
                                const fileInputs = $event.target.querySelectorAll('input[type=file]');
                                for (let fi of fileInputs) {
                                    if (fi.files[0] && fi.files[0].size > 50 * 1024 * 1024) {
                                        fileError = 'The selected file exceeds the 50MB maximum size limit.';
                                        (window.toast ? window.toast.error(fileError) : alert(fileError));
                                        return;
                                    }
                                }
                                if (hasPubRequirement && !isPostDefenceStage) {
                                    let validSlots = pubSlots.filter(s => s.fileName || s.title || s.doi);
                                    if ((validSlots.length + existingPubsCount) < requiredPubs) {
                                        fileError = 'As a ' + (isPhd ? 'PhD' : 'MSc') + ' candidate, you must submit at least ' + requiredPubs + ' publication' + (requiredPubs > 1 ? 's' : '') + ' (with Title, DOI, and PDF).';
                                        (window.toast ? window.toast.error(fileError) : alert(fileError));
                                        return;
                                    }
                                }
                                uploading = true;
                                fetch('{{ route('milestones.store', $milestone) }}', {
                                    method: 'POST',
                                    body: new FormData($event.target),
                                    headers: {
                                        'Accept': 'text/html'
                                    }
                                }).then(res => res.text()).then(html => {
                                    let parser = new DOMParser();
                                    let doc = parser.parseFromString(html, 'text/html');
                                    let newContent = doc.getElementById('milestone-details-container-{{ $milestone->id }}').innerHTML;
                                    document.getElementById('milestone-details-container-{{ $milestone->id }}').innerHTML = newContent;
                                }).finally(() => {
                                    uploading = false;
                                })
                            " action="{{ route('milestones.store', $milestone) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                                @csrf

                                @if(in_array('ppt', $subTypes))
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1 flex items-center justify-between">
                                        <span>Upload Presentation Slide Deck (PDF Only)</span>
                                        @if($bothAllowed && !$hasPptSub && $hasFileSub)
                                            <span class="text-xs font-normal text-slate-400">(Optional if manuscript already uploaded)</span>
                                        @endif
                                    </label>
                                    <div class="relative w-full">
                                        <input type="file" name="ppt" accept=".pdf" {{ (!$bothAllowed && !$hasPptSub) ? 'required' : '' }} class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                                            @change="
                                                const file = $event.target.files[0];
                                                document.getElementById('ppt-name-{{ $milestone->id }}').textContent = file ? file.name : 'Click or drop to select presentation PDF';
                                            "/>
                                        <div class="w-full flex flex-col items-center justify-center gap-3 px-4 py-8 bg-slate-50 border-2 border-slate-200 border-dashed rounded-2xl hover:border-emerald-300 hover:bg-emerald-50/50 transition-colors">
                                            <div class="w-10 h-10 rounded-full bg-white text-slate-400 flex items-center justify-center shadow-xl shadow-slate-200/40">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                            </div>
                                            <span id="ppt-name-{{ $milestone->id }}" class="text-sm font-medium text-slate-600">Click or drop to select presentation PDF</span>
                                        </div>
                                    </div>
                                    @error('ppt') <span class="text-xs font-bold text-red-500 mt-2 block">{{ $message }}</span> @enderror
                                </div>
                                @endif

                                @if(in_array('file', $subTypes))
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1 flex items-center justify-between">
                                        <span>
                                            @if($isPostDefenceStage)
                                                Upload Corrected Thesis Manuscript (PDF Only)
                                            @elseif($milestone->template->slug === 'supervisors_assigned')
                                                Upload Tentative Proposal (PDF Only)
                                            @else
                                                Upload {{ $milestone->template->name }} Document (PDF Only)
                                            @endif
                                        </span>
                                        @if($bothAllowed && !$hasFileSub && $hasPptSub)
                                            <span class="text-xs font-normal text-slate-400">(Optional if presentation already uploaded)</span>
                                        @endif
                                    </label>
                                    @if($isPostDefenceStage)
                                        <p class="text-xs text-indigo-700 font-semibold mb-2">Please upload your revised thesis document incorporating all feedback and corrections received during your internal defence session for Internal Examiner review.</p>
                                    @elseif($milestone->template->slug === 'supervisors_assigned')
                                        <p class="text-xs text-slate-500 mb-2">Please upload your tentative proposal document for supervisor allocation review.</p>
                                    @endif
                                    <div class="relative w-full">
                                        <input type="file" name="file" accept=".pdf" {{ (!$bothAllowed && !$hasFileSub && !$isPostDefenceStage) ? 'required' : '' }} class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                                            @change="
                                                const file = $event.target.files[0];
                                                document.getElementById('file-name-{{ $milestone->id }}').textContent = file ? file.name : 'Click or drop to select file';
                                                if (file && file.size > 50 * 1024 * 1024) {
                                                    fileError = 'The selected file exceeds the 50MB maximum size limit.';
                                                    (window.toast ? window.toast.error(fileError) : alert(fileError));
                                                    $event.target.value = '';
                                                    document.getElementById('file-name-{{ $milestone->id }}').textContent = 'Click or drop to select file';
                                                } else {
                                                    fileError = '';
                                                }
                                            "/>
                                        <div class="w-full flex flex-col items-center justify-center gap-3 px-4 py-8 bg-slate-50 border-2 border-slate-200 border-dashed rounded-2xl hover:border-emerald-300 hover:bg-emerald-50/50 transition-colors">
                                            <div class="w-10 h-10 rounded-full bg-white text-slate-400 flex items-center justify-center shadow-xl shadow-slate-200/40">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                            </div>
                                            <span id="file-name-{{ $milestone->id }}" class="text-sm font-medium text-slate-600">Click or drop to select file</span>
                                        </div>
                                    </div>
                                    @error('file') <span class="text-xs font-bold text-red-500 mt-2 block">{{ $message }}</span> @enderror
                                    <span x-show="fileError" x-text="fileError" class="text-xs font-bold text-red-500 mt-2 block" style="display: none;"></span>
                                </div>
                                @endif

                                @if(in_array('publication', $subTypes) || in_array('publications', $subTypes))
                                <div class="p-6 bg-slate-50/80 rounded-2xl border border-slate-200/80 space-y-5">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 pb-4">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-wider">Required Publications</h4>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $isPhdCandidate ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' }}">
                                                    {{ $isPhdCandidate ? 'PhD: 2 Publications Required' : 'MSc: 1 Publication Required' }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-500 mt-1">
                                                {{ $isPhdCandidate ? 'Doctoral (PhD) candidates are required to submit at least 2 institutional publications.' : 'Master\'s (MSc) candidates are required to submit at least 1 institutional publication.' }}
                                                Please provide the Title, DOI, and PDF file. The portal automatically checks Scopus indexing.
                                            </p>
                                        </div>
                                        @if($existingPubsCount > 0)
                                            <div class="px-3 py-1.5 rounded-xl {{ $existingPubsCount >= $requiredPubs ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }} text-xs font-bold flex items-center gap-1.5 shrink-0">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>{{ $existingPubsCount }}/{{ $requiredPubs }} Submitted</span>
                                            </div>
                                        @endif
                                    </div>

                                    @if($isPostDefenceStage && $existingPubsCount >= $requiredPubs)
                                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            <span>Publication requirement satisfied ({{ $existingPubsCount }} on file). You only need to submit your corrected thesis manuscript above.</span>
                                        </div>
                                    @endif

                                    <!-- Dynamic Publication Cards -->
                                    <template x-for="(slot, index) in pubSlots" :key="slot.id">
                                        <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-4 relative transition-all hover:border-purple-300">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-700 font-black text-xs flex items-center justify-center" x-text="index + 1"></span>
                                                    <span class="text-xs font-black uppercase tracking-wider text-slate-800" x-text="'Publication #' + (index + 1) + ((index + existingPubsCount < requiredPubs) ? ' (Required)' : ' (Additional)')"></span>
                                                </div>
                                                <button type="button" 
                                                    x-show="pubSlots.length > 1 || (existingPubsCount >= requiredPubs)"
                                                    @click="removeSlot(index)" 
                                                    class="text-slate-400 hover:text-rose-600 transition-colors text-xs flex items-center gap-1 font-bold cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    <span>Remove</span>
                                                </button>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <!-- Publication Title -->
                                                <div class="md:col-span-2">
                                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                                        Publication Title <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input type="text" 
                                                        :name="'publications[' + index + '][title]'" 
                                                        x-model="slot.title" 
                                                        placeholder="e.g. Deep Learning Architectures for Autonomous Decision Systems" 
                                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 bg-white">
                                                </div>

                                                <!-- DOI input with live verification -->
                                                <div class="md:col-span-2">
                                                    <div class="flex items-center justify-between mb-1.5">
                                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                                            Digital Object Identifier (DOI) <span class="text-rose-500">*</span>
                                                        </label>
                                                        <span class="text-[11px] text-slate-400 font-normal">e.g. 10.1016/j.future.2018.04.090 or https://doi.org/...</span>
                                                    </div>
                                                    <div class="flex gap-2">
                                                        <div class="relative flex-1">
                                                            <input type="text" 
                                                                :name="'publications[' + index + '][doi]'" 
                                                                x-model="slot.doi" 
                                                                @blur="if (slot.doi && !slot.verified && !slot.checking) verifyDoi(slot)"
                                                                @keydown.enter.prevent="verifyDoi(slot)"
                                                                placeholder="10.xxxx/..." 
                                                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 bg-white">
                                                        </div>
                                                        <button type="button" 
                                                            @click="verifyDoi(slot)" 
                                                            :disabled="slot.checking || !slot.doi"
                                                            class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 transition-all disabled:opacity-40 disabled:cursor-not-allowed shrink-0 shadow-sm cursor-pointer">
                                                            <svg x-show="!slot.checking" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                            <svg x-show="slot.checking" class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                            <span x-text="slot.checking ? 'Checking...' : 'Check Scopus'"></span>
                                                        </button>
                                                    </div>

                                                    <!-- Live Verification Feedback Box -->
                                                    <div x-show="slot.checking" class="mt-2 text-xs text-indigo-600 font-semibold flex items-center gap-1.5" style="display: none;">
                                                        <svg class="animate-spin w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                        <span>Querying Scopus & Crossref indexing databases...</span>
                                                    </div>

                                                    <div x-show="slot.verified && slot.scopusIndexed" class="mt-2.5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-2.5 text-xs text-emerald-900" style="display: none;">
                                                        <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                        <div>
                                                            <span class="font-black uppercase tracking-wider text-emerald-800">✓ Verified: Indexed in Scopus</span>
                                                            <p class="text-[11px] text-emerald-700 mt-0.5 font-medium" x-show="slot.journal" x-text="'Journal / Source: ' + slot.journal"></p>
                                                        </div>
                                                    </div>

                                                    <div x-show="slot.verified && !slot.scopusIndexed" class="mt-2.5 p-3 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-2.5 text-xs text-amber-900" style="display: none;">
                                                        <svg class="w-4 h-4 text-amber-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        <div>
                                                            <span class="font-bold text-amber-800">✓ Valid DOI (Not indexed in Scopus)</span>
                                                            <p class="text-[11px] text-amber-700 mt-0.5 font-medium" x-show="slot.journal" x-text="'Source / Publisher: ' + slot.journal"></p>
                                                        </div>
                                                    </div>

                                                    <div x-show="slot.error" class="mt-2 text-xs text-rose-600 font-semibold flex items-center gap-1.5" style="display: none;">
                                                        <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        <span x-text="slot.error"></span>
                                                    </div>
                                                </div>

                                                <!-- PDF File Upload Dropzone -->
                                                <div class="md:col-span-2">
                                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                                        Publication PDF Manuscript <span class="text-rose-500">*</span>
                                                    </label>
                                                    <div class="relative w-full">
                                                        <input type="file" 
                                                            :name="'publications[' + index + '][file]'" 
                                                            accept=".pdf" 
                                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                                                            @change="
                                                                const file = $event.target.files[0];
                                                                if (file) {
                                                                    slot.fileName = file.name;
                                                                }
                                                            "/>
                                                        <div class="w-full flex items-center gap-3 px-4 py-3 bg-slate-50 border-2 border-slate-200 border-dashed rounded-xl hover:border-purple-300 hover:bg-purple-50/30 transition-colors">
                                                            <div class="w-8 h-8 rounded-lg bg-white text-purple-600 border border-slate-200 flex items-center justify-center shrink-0 shadow-sm">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                                            </div>
                                                            <span class="text-xs font-medium text-slate-600 truncate" x-text="slot.fileName ? slot.fileName : 'Click or drop to select publication PDF (Max 50MB)'"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Add Slot Button -->
                                    <div class="flex items-center justify-between pt-2">
                                        <button type="button" 
                                            x-show="pubSlots.length + existingPubsCount < 5"
                                            @click="addSlot()" 
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-xl text-xs font-bold transition-all active:scale-95 shadow-sm cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                            <span>Add Another Publication</span>
                                        </button>
                                        <span class="text-[11px] font-medium text-slate-400">Up to 5 publications allowed</span>
                                    </div>

                                    @error('publications') <span class="text-xs font-bold text-red-500 mt-2 block">{{ $message }}</span> @enderror
                                </div>
                                @endif

                                <div class="flex justify-end pt-2">
                                    <button type="submit" 
                                        :disabled="uploading"
                                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-emerald-600 transition-colors shadow-xl shadow-slate-200/40 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span x-text="uploading ? 'Transmitting...' : '{{ $isPostDefenceStage ? 'Submit Corrected Thesis' : (in_array('publication', $subTypes) && count($subTypes) > 1 ? 'Submit Documentation & Publication' : (in_array('publication', $subTypes) ? 'Submit Publication' : 'Upload Document')) }}'"></span>
                                        <svg x-show="!uploading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                        <svg x-show="uploading" class="animate-spin -ml-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                @else
                    <div class="bg-white border border-slate-100 shadow-xl shadow-slate-200/40 p-10 rounded-[2.5rem] flex flex-col items-center justify-center text-center">
                        <div class="w-12 h-12 bg-slate-50 border border-slate-200 text-slate-400 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>

                        </div>
                        <h3 class="text-lg font-bold text-slate-900 tracking-tight mb-2">No Document Upload Required</h3>
                        <p class="text-sm font-medium text-slate-500 max-w-sm">This specific phase does not require any file submissions to proceed.</p>
                        
                        @if(str_contains(strtolower($milestone->template?->name ?? ''), 'assigned supervisor'))
                            <div class="mt-6 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl text-sm font-semibold text-emerald-700">
                                Please view the Scholarly Oversight panel to verify assigned administrative leaders.
                            </div>
                        @endif
                    </div>
                @endif
            @elseif($milestone->status === 'submitted')
                <div class="rounded-[2.5rem] bg-emerald-50 border border-emerald-100 shadow-xl shadow-slate-200/40 p-10 flex flex-col items-center justify-center text-center">
                    <div class="w-14 h-14 bg-white border border-emerald-200 text-emerald-600 rounded-2xl flex items-center justify-center mb-4 shadow-xl shadow-slate-200/40 animate-pulse">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 tracking-tight mb-2">Documentation Under Audit</h3>
                    <p class="text-sm font-medium text-emerald-800 max-w-sm">Your submission has been securely transmitted and is currently under review by your committee.</p>
                </div>
            @elseif($milestone->status === 'approved')
                <div class="rounded-[2.5rem] bg-emerald-50 border border-emerald-100 shadow-xl shadow-slate-200/40 p-8 flex items-center gap-6">
                    <div class="w-14 h-14 bg-white border border-emerald-200 text-emerald-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-xl shadow-slate-200/40">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 tracking-tight mb-1">Clearance Approved</h3>
                        <p class="text-sm font-medium text-emerald-800">This milestone has been thoroughly reviewed and successfully approved. You may proceed to the next phase.</p>
                    </div>
                </div>
            @endif

            <!-- Submission History -->
            @if($milestone->submissions->count() > 0)
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 p-8 mt-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <h3 class="text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                            <div class="w-1 h-6 bg-emerald-500 rounded-full"></div>
                            Submission History
                        </h3>
                        @if(auth()->user()->hasRole('Student') && in_array($milestone->status, ['submitted', 'in_review']))
                        <button type="button" @click="showUploadForm = true" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold transition-colors w-fit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Upload New Version
                        </button>
                        @endif
                    </div>
                    
                    <div class="relative pl-6 border-l-2 border-slate-100 py-2">
                        @foreach($milestone->submissions->sortByDesc('created_at') as $submission)
                            <div class="relative mb-6 last:mb-0 group">
                                <div class="absolute -left-[35px] top-1.5 w-6 h-6 rounded-full bg-white border-2 border-slate-200 flex items-center justify-center">
                                    <div class="w-2 h-2 rounded-full bg-slate-300 group-hover:bg-emerald-500 transition-colors"></div>
                                </div>
                                
                                <div class="bg-slate-50 border border-slate-100 rounded-2xl p-5 hover:bg-white hover:border-slate-200 hover:shadow-xl shadow-slate-200/40 transition-all">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="flex flex-col gap-1.5 flex-1">
                                            <div class="flex items-center gap-4">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="px-2.5 py-1 {{ $submission->type === 'publication' ? 'bg-purple-100 text-purple-800' : ($submission->type === 'ppt' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800') }} rounded-md text-[11px] font-black uppercase tracking-wider">
                                                        {{ $submission->type === 'publication' ? 'Publication' : ($submission->type === 'ppt' ? 'Slides' : ($isPostDefenceStage && $defenceDate && $submission->created_at->gte($defenceDate->copy()->startOfDay()) ? 'Corrected Thesis' : 'Thesis Manuscript')) }} v.0{{ $submission->version }}
                                                    </span>
                                                    @if($submission->type === 'publication' && !empty($submission->file_meta['original_name']))
                                                        <span class="text-[11px] text-slate-500 font-medium truncate max-w-[180px]" title="{{ $submission->file_meta['original_name'] }}">
                                                            {{ $submission->file_meta['original_name'] }}
                                                        </span>
                                                    @endif
                                                    <span class="text-xs font-semibold text-slate-400">• {{ $submission->created_at->format('M d, Y • H:i') }}</span>
                                                </div>
                                                
                                                @if($submission->plagiarism_data)
                                                    @php 
                                                        $score = $submission->plagiarism_data['similarity_score'] ?? 0;
                                                        $reportUrl = $submission->plagiarism_data['report_url'] ?? null;
                                                    @endphp
                                                    <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg {{ $score > 20 ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100' }} border">
                                                        <span class="text-[9px] font-black uppercase tracking-tighter">{{ $score }}% Index</span>
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
                                            </div>

                                            @if($submission->type === 'publication')
                                                <div class="mt-1 space-y-1">
                                                    @if($submission->publication_title)
                                                        <h5 class="text-xs font-bold text-slate-900 leading-snug">{{ $submission->publication_title }}</h5>
                                                    @endif
                                                    <div class="flex flex-wrap items-center gap-2 pt-0.5">
                                                        @if($submission->doi)
                                                            <a href="https://doi.org/{{ $submission->doi }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-mono text-indigo-600 hover:text-indigo-800 hover:underline">
                                                                <span>DOI: {{ $submission->doi }}</span>
                                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                            </a>
                                                        @endif

                                                        @if($submission->is_scopus_indexed)
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                                <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                                Scopus Indexed
                                                            </span>
                                                        @elseif($submission->doi)
                                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider bg-amber-50 text-amber-800 border border-amber-200">
                                                                <svg class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                DOI Verified (Not Indexed in Scopus)
                                                            </span>
                                                        @endif

                                                        @if(!empty($submission->scopus_data['journal']))
                                                            <span class="text-[10px] text-slate-500 font-medium italic">({{ $submission->scopus_data['journal'] }})</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex flex-col items-end gap-3 w-full">
                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                    @click.prevent="$dispatch('open-document-preview', { 
                                                        url: '{{ Storage::url($submission->file_url) }}', 
                                                        title: 'Submission v.0{{ $submission->version }}',
                                                        type: '{{ str_ends_with(strtolower($submission->file_url), '.pdf') ? 'pdf' : 'document' }}'
                                                    })"
                                                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors shadow-xl shadow-slate-200/40 w-fit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                    View Document
                                                </button>
                                                
                                                @if($submission->feedback && $submission->feedback->decision === 'approved')
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                                        Upload Accepted
                                                    </span>
                                                @elseif($submission->feedback && $submission->feedback->decision === 'revision_required')
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        Upload Rejected
                                                    </span>
                                                @endif
                                            </div>

                                            @if($submission->feedback && in_array($submission->feedback->decision, ['approved', 'revision_required']) && !empty($submission->feedback->remarks))
                                                <div class="w-full max-w-sm text-xs rounded-xl p-3 border {{ $submission->feedback->decision === 'approved' ? 'bg-emerald-50/60 border-emerald-100 text-emerald-900' : 'bg-rose-50/60 border-rose-100 text-rose-900' }}">
                                                    <p class="text-[10px] font-bold uppercase tracking-widest opacity-70 mb-1">
                                                        {{ $submission->feedback->user && $submission->feedback->user->hasRole('Internal Examiner') ? 'Internal Examiner Verdict' : 'Supervisor / Examiner Comment' }}
                                                    </p>
                                                    <p class="whitespace-pre-line">{{ $submission->feedback->remarks }}</p>
                                                </div>
                                            @endif

                                            @php
                                                $isAssignedInternalExaminer = auth()->user()->hasRole('Internal Examiner') && 
                                                    ($milestone->template?->slug === 'internal_defence' || $milestone->template?->order == 6) &&
                                                    $milestone->thesis->internal_examiner_profile_id &&
                                                    auth()->user()->internalExaminerProfiles()->where('id', $milestone->thesis->internal_examiner_profile_id)->exists();

                                                $canReviewUpload = auth()->user()->hasRole('Admin') || (
                                                    auth()->user()->hasRole('Supervisor') && 
                                                    auth()->user()->supervisorProfile && 
                                                    $milestone->thesis->assignments->where('supervisor_profile_id', auth()->user()->supervisorProfile->id)->where('status', 'active')->isNotEmpty()
                                                ) || $isAssignedInternalExaminer;
                                            @endphp
                                            @if($canReviewUpload && (!$submission->feedback || !in_array($submission->feedback->decision, ['approved', 'revision_required'])))
                                                <form method="POST" x-data="{ remarks: '' }" @submit="if(!remarks.trim()) { alert('A comment is required to accept or reject the upload.'); $event.preventDefault(); }" class="bg-slate-50 p-3 rounded-2xl border border-slate-200/80 shadow-sm w-full max-w-sm mt-1">
                                                    @csrf
                                                    <textarea name="remarks" x-model="remarks" rows="2" class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 mb-2 p-2.5 shadow-sm resize-none bg-white placeholder:text-slate-400" placeholder="{{ $isAssignedInternalExaminer ? 'Enter approval feedback or required corrections...' : 'Required: Explain your decision...' }}" required></textarea>
                                                    <div class="flex gap-2">
                                                        <button type="submit" formaction="{{ route('milestones.accept_upload', $milestone) }}" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-xl text-xs font-bold transition-colors">
                                                            {{ $isAssignedInternalExaminer ? 'Approve Corrected Thesis' : 'Accept Upload' }}
                                                        </button>
                                                        <button type="submit" formaction="{{ route('milestones.reject_upload', $milestone) }}" class="flex-1 bg-rose-500 hover:bg-rose-600 text-white py-2 rounded-xl text-xs font-bold transition-colors">
                                                            {{ $isAssignedInternalExaminer ? 'Request Further Corrections' : 'Reject Upload' }}
                                                        </button>
                                                    </div>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            @can('review', $milestone)
                @php
                    $isUploadAccepted = !$milestone->template->requires_submission || 
                                        ($milestone->submissions->last() && 
                                         $milestone->submissions->last()->feedback && 
                                         $milestone->submissions->last()->feedback->decision === 'approved');
                @endphp
                @if($isUploadAccepted)
                    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 p-8 mt-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
                                <div class="w-1 h-6 bg-indigo-500 rounded-full"></div>
                                Scholarly Evaluation
                            </h3>
                        </div>
                    
                    <form action="{{ route('milestones.review.update', $milestone) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PATCH')
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-3">Institutional Decision</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="relative flex cursor-pointer rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-emerald-500 hover:bg-emerald-50/30 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-500 transition-all">
                                    <input type="radio" name="decision" value="approved" class="peer sr-only" required>
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-black text-slate-900 uppercase tracking-wide">Accept</span>
                                            <span class="block text-xs font-medium text-slate-500 mt-0.5">Proceed to scheduling</span>
                                        </div>
                                    </div>
                                    <div class="absolute top-4 right-4 text-emerald-500 opacity-0 peer-checked:opacity-100 transition-opacity">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    </div>
                                </label>

                                <label class="relative flex cursor-pointer rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:border-rose-500 hover:bg-rose-50/30 has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50/50 has-[:checked]:ring-2 has-[:checked]:ring-rose-500 transition-all">
                                    <input type="radio" name="decision" value="rejected" class="peer sr-only" required>
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-black text-slate-900 uppercase tracking-wide">Reject</span>
                                            <span class="block text-xs font-medium text-slate-500 mt-0.5">Return for revisions</span>
                                        </div>
                                    </div>
                                    <div class="absolute top-4 right-4 text-rose-500 opacity-0 peer-checked:opacity-100 transition-opacity">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Rationale / Explanation</label>
                            <textarea name="remarks" rows="4" required class="block w-full rounded-xl border-slate-200 bg-slate-50 p-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Please provide detailed feedback explaining why you are accepting or rejecting this submission..."></textarea>
                        </div>

                        <div class="flex justify-end border-t border-slate-100 pt-6">
                            <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-500/30">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Register Decision
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            @endcan
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 p-6">
                <h3 class="text-sm font-bold text-slate-900 tracking-tight mb-4 uppercase">Guidelines</h3>
                <div class="flex gap-4">
                    <div class="w-8 h-8 rounded-lg bg-slate-50 text-slate-500 flex items-center justify-center flex-shrink-0 border border-slate-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="text-sm font-medium text-slate-600 leading-relaxed">
                        @php
                            $subTypes = $milestone->template?->submission_type ?? [];
                        @endphp
                        @if(in_array('file', $subTypes) && in_array('publication', $subTypes))
                            <p class="mb-2">Please upload a comprehensive package containing both your working thesis chapter and your verified publication record. PDF format is mandatory.</p>
                        @elseif(in_array('publication', $subTypes))
                            <p class="mb-2">Please upload a verified copy of your peer-reviewed publication record. PDF format is required for archiving purposes.</p>
                        @else
                            <p class="mb-2">Ensure your submission meets the core program requirements. Acceptable format is strictly PDF (max 50MB).</p>
                        @endif
                        <p>Upon submission, your designated committee will be notified automatically.</p>
                    </div>
                </div>
            </div>

            <!-- Contacts Section -->
            <div class="bg-white rounded-[2.5rem] border-t-4 border-emerald-500 shadow-xl shadow-slate-200/40 p-6">
                <h3 class="text-sm font-bold text-slate-900 tracking-tight mb-5 uppercase">Scholarly Oversight</h3>
                
                @php
                    $milestoneEventType = $milestone->template?->defence_type ?? match($milestone->template?->slug) {
                        'seminar_as_a_course' => 'seminar',
                        'proposal_defence' => 'proposal',
                        'progress_report_1' => 'progress_report_1',
                        'progress_report_2' => 'progress_report_2',
                        default => null,
                    };
                    $milestoneDefEvent = $milestoneEventType && $milestone->thesis 
                        ? $milestone->thesis->defenceEvents->firstWhere('type', $milestoneEventType) 
                        : null;
                    $milestoneExaminers = $milestoneDefEvent ? $milestoneDefEvent->panelMembers->where('role', 'examiner') : collect();
                @endphp
                @if($milestoneExaminers->count() > 0)
                    <div class="mb-6">
                        <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mb-2">Assigned Defence Examiner(s)</p>
                        <div class="space-y-2">
                            @foreach($milestoneExaminers as $ex)
                                <div class="p-3 bg-indigo-50/50 rounded-2xl border border-indigo-100 flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-xl bg-white text-indigo-600 font-black flex items-center justify-center text-xs border border-indigo-100 shadow-sm">
                                            {{ substr($ex->user?->name ?? 'E', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-800 leading-tight">{{ $ex->user?->name ?? 'Examiner' }}</p>
                                            <p class="text-[10px] text-slate-400 font-medium mt-0.5">Examiner</p>
                                        </div>
                                    </div>
                                    @if($ex->is_present)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Present
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-slate-100 text-slate-500">
                                            Assigned
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                @php
                    $showExaminerSection = $milestone->template?->show_internal_examiner_assignment || $milestone->template?->slug === 'internal_defence' || $milestone->template?->order == 6;
                    $canAssignExaminer = auth()->user()->hasAnyRole(['Admin', 'Program Coordinator']);
                    $examinersList = $allInternalExaminers ?? collect();
                    if ($examinersList->isEmpty() && $canAssignExaminer && $milestone->thesis->student) {
                        $examinersList = \App\Models\InternalExaminerProfile::with('user')
                            ->where('program_id', $milestone->thesis->student->program_id)
                            ->where('active', true)
                            ->get();
                    }
                @endphp
                @if($showExaminerSection)
                    <div class="mb-6 p-4 rounded-2xl border {{ $milestone->thesis->internalExaminer ? 'bg-indigo-50/40 border-indigo-100' : 'bg-amber-50/50 border-amber-200' }}">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[10px] font-black {{ $milestone->thesis->internalExaminer ? 'text-indigo-600' : 'text-amber-700' }} uppercase tracking-widest">
                                Assigned Internal Examiner
                            </p>
                            @if($milestone->thesis->internalExaminer)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    Designated
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                                    Pending
                                </span>
                            @endif
                        </div>

                        @if($milestone->thesis->internalExaminer)
                            <div class="p-3 bg-white rounded-xl border border-indigo-100 flex items-center gap-3 shadow-sm group hover:border-indigo-200 transition-colors cursor-pointer"
                                @click.prevent.stop="setTimeout(() => { showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($milestone->thesis->internalExaminer->user?->name ?? 'User') }}; }, 50)">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-sm font-bold shadow-sm">
                                    {{ substr($milestone->thesis->internalExaminer->user?->name ?? 'User', 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate-900 truncate">{{ $milestone->thesis->internalExaminer->user?->name ?? 'Internal Examiner' }}</p>
                                    <p class="text-[11px] font-medium text-slate-500 truncate">{{ $milestone->thesis->internalExaminer->department ?? 'Institutional Department' }}</p>
                                </div>
                                <div class="p-2 bg-slate-50 border border-slate-100 rounded-lg text-slate-400 group-hover:text-indigo-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                </div>
                            </div>
                        @else
                            <p class="text-xs text-amber-800 mb-2 font-medium">An Internal Examiner has not yet been designated for this candidate.</p>
                        @endif

                        @if($canAssignExaminer)
                            <div class="mt-3 pt-3 border-t border-slate-200/60" x-data="{ showAssignForm: {{ $milestone->thesis->internalExaminer ? 'false' : 'true' }} }">
                                <div class="flex items-center justify-between" x-show="!showAssignForm">
                                    <span class="text-[11px] text-slate-500 font-medium">Need to designate or change examiner?</span>
                                    <button type="button" @click="showAssignForm = true" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 underline">
                                        {{ $milestone->thesis->internalExaminer ? 'Reassign Examiner' : 'Assign Examiner' }}
                                    </button>
                                </div>
                                <form x-show="showAssignForm" action="{{ route('theses.assign_internal_examiner', $milestone->thesis) }}" method="POST" class="space-y-2 mt-2">
                                    @csrf
                                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">
                                        {{ $milestone->thesis->internalExaminer ? 'Reassign' : 'Designate' }} Internal Examiner
                                    </label>
                                    <div class="flex gap-2">
                                        <select name="internal_examiner_profile_id" required class="flex-1 text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-2 bg-white">
                                            <option value="">-- Select Faculty / Examiner --</option>
                                            @foreach($examinersList as $examiner)
                                                <option value="{{ $examiner->id }}" {{ $milestone->thesis->internal_examiner_profile_id == $examiner->id ? 'selected' : '' }}>
                                                    {{ $examiner->user?->name ?? 'Faculty' }} ({{ $examiner->department ?? 'Examiner' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors shrink-0">
                                            Save
                                        </button>
                                        @if($milestone->thesis->internalExaminer)
                                            <button type="button" @click="showAssignForm = false" class="px-2.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition-colors">
                                                ✕
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                @endif
                <div class="space-y-4">
                    @if(isset($supervisors) && $supervisors->count() > 0)
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Assigned Committee</p>
                        @foreach($supervisors as $supervisor)
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 group hover:border-slate-200 hover:bg-white transition-colors cursor-pointer"
                                @if($milestone->template->has_chat) @click.prevent.stop="setTimeout(() => { showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($supervisor->user?->name ?? 'User') }}; }, 50)" @endif>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-emerald-600 text-sm font-bold shadow-xl shadow-slate-200/40">
                                        {{ substr($supervisor->user?->name ?? 'User', 0, 1) }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-slate-900 leading-tight">{{ $supervisor->user?->name ?? 'User' }}</p>
                                        <p class="text-xs font-medium text-slate-500 mt-0.5">{{ $supervisor->specialization ?? 'Supervisor' }}</p>
                                    </div>
                                    <div class="p-2 bg-white border border-slate-100 rounded-lg text-slate-400 group-hover:text-emerald-600 transition-colors shadow-xl shadow-slate-200/40">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @elseif(isset($coordinators) && $coordinators->count() > 0)
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl mb-4 flex gap-3">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <p class="text-xs font-semibold text-amber-800">Supervisors are not assigned yet. Contact your coordinator.</p>
                        </div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Program Coordinator</p>
                        @foreach($coordinators as $coordinator)
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 group hover:border-slate-200 hover:bg-white transition-colors cursor-pointer"
                                @if($milestone->template->has_chat) @click.prevent.stop="setTimeout(() => { showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($coordinator->user?->name ?? 'User') }}; }, 50)" @endif>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-emerald-600 text-sm font-bold shadow-xl shadow-slate-200/40">
                                        {{ substr($coordinator->user?->name ?? 'User', 0, 1) }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-slate-900 leading-tight">{{ $coordinator->user?->name ?? 'User' }}</p>
                                        <p class="text-xs font-medium text-slate-500 mt-0.5">Coordinator</p>
                                    </div>
                                    <div class="p-2 bg-white border border-slate-100 rounded-lg text-slate-400 group-hover:text-emerald-600 transition-colors shadow-xl shadow-slate-200/40">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                            <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unassigned</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
    
    @if($milestone->template->has_chat)
    <!-- Alpine JS Modal for Messaging -->
    <template x-teleport="body">
    <div x-show="showStudentMessageModal" class="fixed z-50 inset-0 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showStudentMessageModal" 
                 @click.self="setTimeout(() => { showStudentMessageModal = false }, 50)"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm cursor-pointer" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div x-show="showStudentMessageModal" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-100 relative z-10">
                 
                <form action="{{ route('messages.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="thesis_project_id" value="{{ $milestone->thesis_project_id }}">
                    
                    <div class="bg-white px-6 pt-8 pb-6 sm:p-8">
                        <div class="sm:flex sm:items-start gap-5">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-xl bg-emerald-50 sm:mx-0 shadow-xl shadow-slate-200/40 border border-emerald-100">
                                <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <div class="mt-4 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-xl font-bold text-slate-900 tracking-tight" id="modal-title-{{ $milestone->id }}">
                                    Send Message
                                </h3>
                                <div class="mt-1 text-sm font-medium text-slate-500 mb-5">
                                    Direct communication to <span x-text="messageRecipient" class="font-bold text-slate-900 border-b border-slate-200"></span>.
                                </div>
                                
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Message Content</label>
                                <textarea name="content" rows="4" class="w-full focus:ring-emerald-500 focus:border-emerald-500 text-sm border-slate-200 rounded-xl p-4 bg-slate-50 transition-colors resize-none" placeholder="Type your message here..." required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-6 py-4 sm:px-8 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="showStudentMessageModal = false" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors shadow-xl shadow-slate-200/40">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 rounded-xl text-sm font-semibold text-white hover:bg-emerald-700 transition-colors shadow-xl shadow-slate-200/40 flex items-center gap-2">
                            Send Message
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </template>
    @endif
</div>
