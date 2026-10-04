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
                    data-confirm="Are you sure you want to end the presentation session for {{ addslashes($milestone->thesis->student->user->name) }}? If all requirements are met (presentation conducted, PPT uploaded, and supervisor approved), the candidate will be advanced to the next milestone."
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

            @if(auth()->id() !== $milestone->thesis->student->user_id && $milestone->template->has_chat)
            <button type="button" @click.prevent.stop="setTimeout(() => { showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($milestone->thesis->student->user->name) }}; }, 50)" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl shadow-xl shadow-slate-200/40 transition-colors">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <span class="text-[10px] font-black uppercase tracking-widest hidden sm:inline">Message Student</span>
            </button>
            @endif
            
            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-lg font-black text-slate-900 shadow-xl shadow-slate-200/40">
                0{{ $milestone->template->order }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Column: Submission Area -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Scheduled Date Section -->
            @php
                $hasDefenceDateAllowed = $milestone->template->allow_defence_date;
                $defenceDateStr = $milestone->defence_date;
                $defenceDate = $defenceDateStr ? \Carbon\Carbon::parse($defenceDateStr) : null;
                $isApproved = !is_null($milestone->date_approved_at);
                $isDateExpired = $defenceDate && $defenceDate->endOfDay()->isPast() && $milestone->status !== 'approved';
                $canSetDate = auth()->user()->hasRole('Admin') || auth()->user()->hasRole($milestone->template->defence_date_role ?? 'Program Coordinator');
            @endphp

            @php
                $latestSubForBanner = $milestone->submissions->sortByDesc('created_at')->first();
                $isUploadAccepted = $latestSubForBanner && $latestSubForBanner->feedback && $latestSubForBanner->feedback->decision === 'approved';
                $isScheduled = $defenceDate && !$isDateExpired;
            @endphp

            {{-- 1. If upload is accepted and NOT yet scheduled, tell student to await schedule --}}
            @if($isUploadAccepted && !$isScheduled && auth()->user()->hasRole('Student'))
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
                @if(!$defenceDate || $isDateExpired)
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
                                                onclick="if (confirm('Are you sure you want to end the presentation session for {{ addslashes($milestone->thesis->student->user->name) }}? If all requirements are met, the candidate will be advanced to the next milestone.')) { document.getElementById('end-pres-form-expired-{{ $milestone->id }}').submit(); }"
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
                                            data-confirm="Are you sure you want to end the presentation for {{ addslashes($milestone->thesis->student->user->name) }}? If all requirements are met (presentation conducted, PPT uploaded, and supervisor approved), the candidate will be advanced to the next milestone."
                                            data-confirm-title="End Presentation Session"
                                            data-confirm-type="success"
                                            data-confirm-btn="End Presentation"
                                            class="inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-blue-600/20 active:scale-95">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>End Presentation</span>
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
                                <h3 class="text-xs font-bold text-red-600 uppercase tracking-wider mb-1">Upload Rejected</h3>
                                <div class="text-sm font-medium text-red-900 leading-relaxed">
                                    Your recent upload was rejected. Please contact
                                    <strong>{{ $rejectedBy?->name ?? 'your supervisor' }}</strong>
                                    for more information, or upload a revised document below for another review.
                                </div>
                                @if($milestone->remark && !str_starts_with($milestone->remark, 'Document requires revision'))
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
                    @if($hasDefenceDateAllowed && $isDateExpired)
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
                        <div x-show="showUploadForm" x-transition class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 p-8">
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
                            <form x-data="{ uploading: false, fileError: '' }" @submit.prevent="
                                const fileInput = $event.target.querySelector('input[type=file]');
                                if (fileInput && fileInput.files[0] && fileInput.files[0].size > 30 * 1024 * 1024) {
                                    fileError = 'The selected file exceeds the 30MB maximum size limit.';
                                    (window.toast ? window.toast.error(fileError) : alert(fileError));
                                    return;
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
                                
                                @php
                                    $subTypes = $milestone->template->submission_type ?? ['file'];
                                    $bothAllowed = in_array('ppt', $subTypes) && in_array('file', $subTypes);
                                    $hasPptSub = $milestone->submissions->where('type', 'ppt')->count() > 0;
                                    $hasFileSub = $milestone->submissions->whereIn('type', ['file', 'manuscript'])->count() > 0;
                                @endphp

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
                                                document.getElementById('ppt-name-{{ $milestone->id }}').textContent = file ? file.name : 'Click or drop to select PPT';
                                            "/>
                                        <div class="w-full flex flex-col items-center justify-center gap-3 px-4 py-8 bg-slate-50 border-2 border-slate-200 border-dashed rounded-2xl hover:border-emerald-300 hover:bg-emerald-50/50 transition-colors">
                                            <div class="w-10 h-10 rounded-full bg-white text-slate-400 flex items-center justify-center shadow-xl shadow-slate-200/40">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                            </div>
                                            <span id="ppt-name-{{ $milestone->id }}" class="text-sm font-medium text-slate-600">Click or drop to select PPT</span>
                                        </div>
                                    </div>
                                    @error('ppt') <span class="text-xs font-bold text-red-500 mt-2 block">{{ $message }}</span> @enderror
                                </div>
                                @endif

                                @if(in_array('file', $subTypes))
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1 flex items-center justify-between">
                                        <span>{{ $milestone->template->slug === 'supervisors_assigned' ? 'Upload Tentative Proposal (PDF Only)' : 'Upload ' . $milestone->template->name . ' Document (PDF Only)' }}</span>
                                        @if($bothAllowed && !$hasFileSub && $hasPptSub)
                                            <span class="text-xs font-normal text-slate-400">(Optional if PPT already uploaded)</span>
                                        @endif
                                    </label>
                                    @if($milestone->template->slug === 'supervisors_assigned')
                                        <p class="text-xs text-slate-500 mb-2">Please upload your tentative proposal document for supervisor allocation review.</p>
                                    @endif
                                    <div class="relative w-full">
                                        <input type="file" name="file" accept=".pdf" {{ (!$bothAllowed && !$hasFileSub) ? 'required' : '' }} class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                                            @change="
                                                const file = $event.target.files[0];
                                                document.getElementById('file-name-{{ $milestone->id }}').textContent = file ? file.name : 'Click or drop to select file';
                                                if (file && file.size > 30 * 1024 * 1024) {
                                                    fileError = 'The selected file exceeds the 30MB maximum size limit.';
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

                                @if(in_array('publication', $milestone->template->submission_type) || in_array('publications', $milestone->template->submission_type))
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                                        Upload Publications (PDF Only, Select one or more)
                                    </label>
                                    <div class="relative w-full">
                                        <input type="file" name="publications[]" accept=".pdf" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                                            @change="
                                                const files = $event.target.files;
                                                document.getElementById('pub-name-{{ $milestone->id }}').textContent = files.length > 0 ? files.length + ' files selected' : 'Click or drop to select publications';
                                            "/>
                                        <div class="w-full flex flex-col items-center justify-center gap-3 px-4 py-8 bg-slate-50 border-2 border-slate-200 border-dashed rounded-2xl hover:border-emerald-300 hover:bg-emerald-50/50 transition-colors">
                                            <div class="w-10 h-10 rounded-full bg-white text-slate-400 flex items-center justify-center shadow-xl shadow-slate-200/40">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                            </div>
                                            <span id="pub-name-{{ $milestone->id }}" class="text-sm font-medium text-slate-600">Click or drop to select publications</span>
                                        </div>
                                    </div>
                                    @error('publications') <span class="text-xs font-bold text-red-500 mt-2 block">{{ $message }}</span> @enderror
                                </div>
                                @endif

                                <div class="flex justify-end pt-2">
                                    <button type="submit" 
                                        :disabled="uploading"
                                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-emerald-600 transition-colors shadow-xl shadow-slate-200/40 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span x-text="uploading ? 'Transmitting...' : '{{ in_array('publication', $milestone->template->submission_type) && count($milestone->template->submission_type) > 1 ? 'Submit Documentation & Publication' : (in_array('publication', $milestone->template->submission_type) ? 'Submit Publication' : 'Upload Document') }}'"></span>
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
                        
                        @if(str_contains(strtolower($milestone->template->name), 'assigned supervisor'))
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
                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center gap-3">
                                                <span class="px-2.5 py-1 bg-slate-200 text-slate-700 rounded-md text-xs font-bold uppercase">v.0{{ $submission->version }}</span>
                                                <span class="text-xs font-semibold text-slate-500">{{ $submission->created_at->format('M d, Y • H:i') }}</span>
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
                                            
                                            @if(auth()->user()->hasRole(['Supervisor', 'Admin']))
                                                @if(!$submission->feedback || !in_array($submission->feedback->decision, ['approved', 'revision_required']))
                                                    <div class="flex gap-2">
                                                        <form action="{{ route('milestones.accept_upload', $milestone) }}" method="POST" class="inline" data-confirm="Are you sure you want to accept this document?" data-confirm-title="Accept Document" data-confirm-type="success" data-confirm-btn="Accept Upload">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white rounded-xl text-xs font-bold hover:bg-emerald-600 transition-colors shadow-xl shadow-emerald-500/20">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                                                Accept Upload
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('milestones.reject_upload', $milestone) }}" method="POST" class="inline" data-confirm="Are you sure you want to reject this document? The student will be notified to revise it." data-confirm-title="Reject Document" data-confirm-type="danger" data-confirm-btn="Reject Upload">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-500 text-white rounded-xl text-xs font-bold hover:bg-rose-600 transition-colors shadow-xl shadow-rose-500/20">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                                Reject Upload
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    @if($submission->feedback->decision === 'approved')
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                                            Upload Accepted
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                            Upload Rejected
                                                        </span>
                                                    @endif
                                                @endif
                                            @else
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
                        @if(in_array('file', $milestone->template->submission_type) && in_array('publication', $milestone->template->submission_type))
                            <p class="mb-2">Please upload a comprehensive package containing both your working thesis chapter and your verified publication record. PDF format is mandatory.</p>
                        @elseif(in_array('publication', $milestone->template->submission_type))
                            <p class="mb-2">Please upload a verified copy of your peer-reviewed publication record. PDF format is required for archiving purposes.</p>
                        @else
                            <p class="mb-2">Ensure your submission meets the core program requirements. Acceptable formats include PDF and DOCX (max 10MB).</p>
                        @endif
                        <p>Upon submission, your designated committee will be notified automatically.</p>
                    </div>
                </div>
            </div>

            <!-- Contacts Section -->
            <div class="bg-white rounded-[2.5rem] border-t-4 border-emerald-500 shadow-xl shadow-slate-200/40 p-6">
                <h3 class="text-sm font-bold text-slate-900 tracking-tight mb-5 uppercase">Scholarly Oversight</h3>
                
                @if($milestone->thesis->internalExaminer)
                    <div class="mb-6">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Assigned Internal Examiner</p>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-brand-100 group hover:border-brand-200 hover:bg-white transition-colors cursor-pointer"
                            @click.prevent.stop="setTimeout(() => { showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($milestone->thesis->internalExaminer->user->name) }}; }, 50)">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white border border-brand-100 flex items-center justify-center text-brand-600 text-sm font-bold shadow-xl shadow-slate-200/40">
                                    {{ substr($milestone->thesis->internalExaminer->user->name, 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-slate-900 leading-tight">{{ $milestone->thesis->internalExaminer->user->name }}</p>
                                    <p class="text-[11px] font-medium text-slate-500 mt-0.5">{{ $milestone->thesis->internalExaminer->department ?? 'Institutional Department' }}</p>
                                </div>
                                <div class="p-2 bg-white border border-slate-100 rounded-lg text-slate-400 group-hover:text-brand-600 transition-colors shadow-xl shadow-slate-200/40">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="space-y-4">
                    @php
                        $canShowSupervisors = $milestone->template->show_supervisor_details;
                        if ($milestone->template->order == 2 && $milestone->status !== 'approved') {
                            $canShowSupervisors = false;
                        }
                    @endphp
                    @if($canShowSupervisors && isset($supervisors) && $supervisors->count() > 0)
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Assigned Committee</p>
                        @foreach($supervisors as $supervisor)
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 group hover:border-slate-200 hover:bg-white transition-colors cursor-pointer"
                                @if($milestone->template->has_chat) @click.prevent.stop="setTimeout(() => { showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($supervisor->user->name) }}; }, 50)" @endif>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-emerald-600 text-sm font-bold shadow-xl shadow-slate-200/40">
                                        {{ substr($supervisor->user->name, 0, 1) }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-slate-900 leading-tight">{{ $supervisor->user->name }}</p>
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
                                @if($milestone->template->has_chat) @click.prevent.stop="setTimeout(() => { showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($coordinator->user->name) }}; }, 50)" @endif>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-emerald-600 text-sm font-bold shadow-xl shadow-slate-200/40">
                                        {{ substr($coordinator->user->name, 0, 1) }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-slate-900 leading-tight">{{ $coordinator->user->name }}</p>
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