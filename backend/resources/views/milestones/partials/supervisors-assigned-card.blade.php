@php
    $student = $milestone->thesis?->student;
    $studentUser = $student?->user;
    $studentName = $studentUser?->name ?? 'Candidate';
    $program = $student?->program;
    $programName = $program?->name ?? 'Academic Program';
    $levelName = strtoupper($student?->level?->name ?? '');
    $isPhD = str_contains($levelName, 'PHD');
    $requiredSupervisorCount = $isPhD ? 3 : 2;

    $submissions = $milestone->submissions->sortByDesc('created_at');
    $latestProposal = $submissions->first();
    $hasUploaded = !is_null($latestProposal);
    $proposalUrl = $latestProposal ? (str_starts_with($latestProposal->file_url, 'http') ? $latestProposal->file_url : \Illuminate\Support\Facades\Storage::url($latestProposal->file_url)) : '';

    $assignments = $milestone->thesis?->assignments()->where('status', 'active')->with('supervisor.user')->get() ?? collect();
    $hasSupervisors = $assignments->count() > 0;

    // Available supervisors in student's program
    $availableSupervisors = \App\Models\SupervisorProfile::with('user')
        ->when($student?->program_id, function($q) use ($student) {
            $q->whereHas('programs', fn($p) => $p->where('programs.id', $student->program_id));
        })
        ->get();

    // Fallback if none mapped specifically
    if ($availableSupervisors->isEmpty()) {
        $availableSupervisors = \App\Models\SupervisorProfile::with('user')->get();
    }

    $allSupervisorsJson = $availableSupervisors->map(function($s) {
        return [
            'id' => $s->id,
            'name' => $s->user?->name ?? 'Supervisor',
            'rank' => $s->rank ?? 'Senior Lecturer',
            'is_professor' => strtoupper($s->rank ?? '') === 'PROFESSOR',
            'current_load' => (int) ($s->current_load ?? 0),
            'max_students' => (int) ($s->max_students ?? 5),
            'department' => $s->department ?? '',
        ];
    })->values();

    $assignedIds = $assignments->pluck('supervisor_profile_id')->all();
    $primaryAssignment = $assignments->firstWhere('role', 'primary') ?? $assignments->first();
    $leadSupervisorId = $primaryAssignment?->supervisor_profile_id ?? ($assignedIds[0] ?? '');
    
    $secondaryAssignments = $assignments->filter(fn($a) => $a->id !== $primaryAssignment?->id)->values();
    $secondSupervisorId = $secondaryAssignments->get(0)?->supervisor_profile_id ?? ($assignedIds[1] ?? '');
    $thirdSupervisorId = $secondaryAssignments->get(1)?->supervisor_profile_id ?? ($assignedIds[2] ?? '');
@endphp

<div id="supervisors-assigned-card-{{ $milestone->id }}" 
     x-data="{
        showAssignModal: false,
        showStudentMessageModal: false,
        messageRecipient: '{{ addslashes($studentName) }}',
        uploading: false,
        fileError: '',
        selectedLead: '{{ $leadSupervisorId }}',
        selectedSecond: '{{ $secondSupervisorId }}',
        selectedThird: '{{ $thirdSupervisorId }}',
        supervisorsList: {{ \Illuminate\Support\Js::from($allSupervisorsJson) }},
        isPhD: {{ $isPhD ? 'true' : 'false' }},
        randomizeAlert: '',
        randomize() {
            const profs = this.supervisorsList.filter(s => s.is_professor && s.current_load < s.max_students);
            const allEligible = this.supervisorsList.filter(s => s.current_load < s.max_students);
            
            const availableProfs = profs.length > 0 ? profs : this.supervisorsList.filter(s => s.is_professor);
            if (availableProfs.length === 0) {
                alert('No Professors found for this program. Institutional guidelines require a Professor as Lead Supervisor.');
                return;
            }
            
            // Pick a random Professor for Lead
            const randomProf = availableProfs[Math.floor(Math.random() * availableProfs.length)];
            this.selectedLead = randomProf.id;
            
            // Pick secondary supervisors (different from lead)
            const remaining = allEligible.filter(s => s.id !== this.selectedLead);
            const pool = remaining.length >= (this.isPhD ? 2 : 1) ? remaining : this.supervisorsList.filter(s => s.id !== this.selectedLead);
            
            if (pool.length > 0) {
                const shuffled = [...pool].sort(() => 0.5 - Math.random());
                this.selectedSecond = shuffled[0]?.id || '';
                if (this.isPhD && shuffled.length > 1) {
                    this.selectedThird = shuffled[1]?.id || '';
                }
            }
            
            this.randomizeAlert = 'Supervisors randomized successfully based on academic rank and workload!';
            setTimeout(() => { this.randomizeAlert = ''; }, 4500);
        }
     }" 
     class="space-y-6">

    {{-- ========================================================================= --}}
    {{-- 1. STUDENT SIDE VIEW                                                      --}}
    {{-- ========================================================================= --}}
    @if(auth()->user()->hasRole('Student'))
        @if(!$hasUploaded)
            {{-- State 1A: Student has NOT uploaded proposal yet --}}
            <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 p-8 sm:p-10 relative overflow-hidden animate-in-up">
                <div class="flex items-start gap-5 mb-8">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 shadow-sm">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-black uppercase tracking-wider mb-2 border border-indigo-100">
                            <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
                            <span>Upload Required</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                            Upload Tentative Research Proposal
                        </h2>
                        <p class="text-sm sm:text-base font-semibold text-slate-600 mt-2 leading-relaxed max-w-3xl">
                            Upload your tentative research proposal, it will be used to select your supervisors.
                        </p>
                    </div>
                </div>

                {{-- Upload Dropzone Form --}}
                <form @submit.prevent="
                    const fileInput = $event.target.querySelector('input[type=file]');
                    if (!fileInput || !fileInput.files[0]) {
                        (window.toast ? window.toast.error('Please select your proposal document (PDF)') : alert('Please select your proposal document (PDF)'));
                        return;
                    }
                    if (fileInput.files[0].size > 30 * 1024 * 1024) {
                        fileError = 'The selected file exceeds the 30MB limit.';
                        (window.toast ? window.toast.error(fileError) : alert(fileError));
                        return;
                    }
                    uploading = true;
                    fetch('{{ route('milestones.store', $milestone) }}', {
                        method: 'POST',
                        body: new FormData($event.target),
                        headers: { 'Accept': 'text/html' }
                    }).then(res => {
                        window.location.reload();
                    }).catch(err => {
                        console.error(err);
                        window.location.reload();
                    }).finally(() => {
                        uploading = false;
                    });
                " class="space-y-6">
                    @csrf
                    
                    <div class="relative w-full">
                        <input type="file" 
                               name="file" 
                               accept=".pdf" 
                               required 
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                               @change="
                                   const f = $event.target.files[0];
                                   document.getElementById('proposal-file-name-{{ $milestone->id }}').textContent = f ? f.name : 'Click or drop to select your proposal PDF';
                                   if (f && f.size > 30 * 1024 * 1024) {
                                       fileError = 'The file exceeds 30MB.';
                                       $event.target.value = '';
                                   } else {
                                       fileError = '';
                                   }
                               "/>
                        <div class="w-full flex flex-col items-center justify-center gap-4 px-6 py-12 bg-slate-50 border-2 border-slate-200 border-dashed rounded-3xl hover:border-indigo-400 hover:bg-indigo-50/40 transition-all cursor-pointer group">
                            <div class="w-14 h-14 rounded-2xl bg-white text-indigo-600 flex items-center justify-center shadow-lg shadow-slate-200/50 group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <div class="text-center">
                                <span id="proposal-file-name-{{ $milestone->id }}" class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">
                                    Click or drop to select your tentative research proposal (PDF Only)
                                </span>
                                <p class="text-xs font-medium text-slate-400 mt-1">PDF document up to 30MB</p>
                            </div>
                        </div>
                    </div>
                    
                    <span x-show="fileError" x-text="fileError" class="text-xs font-bold text-rose-600 block" style="display: none;"></span>

                    <div class="flex items-center justify-between pt-2">
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Used by Program Coordinator to assign best-fit supervisors</span>
                        </div>
                        <button type="submit" 
                                :disabled="uploading" 
                                class="inline-flex items-center gap-2.5 px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-lg shadow-indigo-600/30 active:scale-95 disabled:opacity-50 cursor-pointer">
                            <svg x-show="!uploading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <svg x-show="uploading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" style="display: none;">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="uploading ? 'Uploading Proposal...' : 'Submit Tentative Proposal'"></span>
                        </button>
                    </div>
                </form>
            </div>
        @else
            {{-- State 1B: Student HAS uploaded proposal --}}
            <div class="space-y-6">
                {{-- Reassuring Received Banner --}}
                <div class="bg-gradient-to-br from-emerald-50 via-teal-50/50 to-white rounded-[2.5rem] border border-emerald-200 shadow-xl shadow-slate-200/40 p-8 sm:p-10 relative overflow-hidden animate-in-up">
                    <div class="absolute -right-12 -top-12 w-48 h-48 bg-emerald-100/60 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="relative flex items-start gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-lg shadow-emerald-500/25">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="space-y-2 max-w-3xl">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider border border-emerald-200">
                                <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                                <span>Submission Received</span>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-snug">
                                Your submission is received and your program coordinator is currently reviewing it to get your supervisors more fitting to your research.
                            </h2>
                            <p class="text-xs sm:text-sm font-medium text-slate-600 leading-relaxed pt-1">
                                Your Program Coordinator will evaluate your proposed research area and appoint a qualified supervisory panel ({{ $isPhD ? '3 supervisors including a Lead Professor' : '2 supervisors including a Lead Professor' }}). You will be notified automatically once assigned.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Uploaded Proposal Document Card --}}
                <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <div class="w-1.5 h-4 bg-emerald-500 rounded-full"></div>
                            Uploaded Tentative Proposal
                        </h3>
                        <span class="text-xs font-bold text-slate-400">
                            Version 0{{ $latestProposal->version }} • {{ $latestProposal->created_at->format('M d, Y') }}
                        </span>
                    </div>

                    <div class="p-5 bg-slate-50 border border-slate-100 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-slate-200 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center shrink-0 shadow-sm font-black text-xs">
                                PDF
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">
                                    {{ $latestProposal->file_meta['original_name'] ?? 'Tentative_Research_Proposal.pdf' }}
                                </h4>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Submitted {{ $latestProposal->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click.prevent="$dispatch('open-document-preview', { 
                                        url: '{{ $proposalUrl }}', 
                                        title: 'Tentative Proposal - {{ addslashes($studentName) }}', 
                                        type: 'pdf' 
                                    })"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold uppercase tracking-wider transition-colors border border-indigo-200 cursor-pointer">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>Preview Document</span>
                            </button>
                            <a href="{{ $proposalUrl }}" download class="p-2 bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 rounded-xl transition-colors" title="Download">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Supervisors Allocation Status (Student Side) --}}
                <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 p-6 sm:p-8">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                        Supervisory Committee Allocation
                    </h3>
                    @if($hasSupervisors)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($assignments as $assignment)
                                @php $sup = $assignment->supervisor; @endphp
                                <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-indigo-600 font-black text-sm shadow-sm">
                                        {{ substr($sup->user?->name ?? 'S', 0, 1) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider {{ $assignment->role === 'primary' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-200 text-slate-700' }}">
                                                {{ $assignment->role === 'primary' ? 'Lead Supervisor' : 'Co-Supervisor' }}
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-400">{{ $sup->rank }}</span>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-900 truncate mt-0.5">{{ $sup->user?->name }}</h4>
                                        <p class="text-xs text-slate-500 truncate">{{ $sup->department ?? $programName }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 bg-slate-50 border border-slate-200 border-dashed rounded-2xl flex items-center gap-4 text-slate-600">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center shrink-0 text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="text-xs font-semibold">
                                <span class="font-bold text-slate-800">Supervisors Not Yet Assigned:</span> Your Program Coordinator is currently matching your research proposal with prospective faculty members.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    {{-- ========================================================================= --}}
    {{-- 2. PROGRAM COORDINATOR SIDE VIEW                                          --}}
    {{-- ========================================================================= --}}
    @elseif(auth()->user()->hasRole('Program Coordinator'))
        @if(!$hasUploaded)
            {{-- State 2A: Student has NOT uploaded proposal yet --}}
            <div class="bg-amber-50/70 border border-amber-200 rounded-[2.5rem] p-8 sm:p-10 shadow-xl shadow-slate-200/40 relative overflow-hidden animate-in-up">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-start gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-lg shadow-amber-500/25">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-black uppercase tracking-wider mb-2 border border-amber-200">
                                <span>Awaiting Candidate Submission</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                                Student yet to upload tentative proposal
                            </h2>
                            <p class="text-sm font-medium text-slate-700 mt-1.5 leading-relaxed max-w-2xl">
                                Candidate <strong class="text-slate-900">{{ $studentName }}</strong> has not uploaded their tentative research proposal yet. You can send a direct reminder to their inbox to prompt submission.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 shrink-0">
                        <button type="button" 
                                @click.prevent.stop="showStudentMessageModal = true; messageRecipient = '{{ addslashes($studentName) }}'"
                                class="inline-flex items-center gap-2 px-6 py-3.5 bg-amber-600 hover:bg-amber-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-lg shadow-amber-600/30 active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <span>Send Message to Student</span>
                        </button>

                        <button type="button" 
                                @click="showAssignModal = true"
                                class="inline-flex items-center gap-2 px-5 py-3.5 bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 rounded-2xl text-xs font-bold uppercase tracking-wider transition-colors shadow-sm cursor-pointer">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            <span>Assign Supervisors</span>
                        </button>
                    </div>
                </div>
            </div>
        @else
            {{-- State 2B: Student HAS uploaded proposal --}}
            <div class="space-y-6 animate-in-up">
                {{-- Document Overview & Click-to-Preview Card --}}
                <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 p-8 sm:p-10">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-100">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider mb-2 border border-emerald-200">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                <span>Proposal Available for Review</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                                Candidate Tentative Research Proposal
                            </h2>
                            <p class="text-sm font-medium text-slate-500 mt-1">
                                Click on the document below to preview the upload and match supervisors to this candidate's research focus.
                            </p>
                        </div>

                        {{-- Assign Supervisors Trigger Button --}}
                        <div class="shrink-0">
                            <button type="button" 
                                    @click="showAssignModal = true"
                                    class="inline-flex items-center gap-2 px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-lg shadow-indigo-600/30 active:scale-95 cursor-pointer">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                <span>{{ $hasSupervisors ? 'Modify / Reassign Supervisors' : 'Assign Supervisors' }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- Clickable Document Card --}}
                    <div class="mt-6">
                        <div @click.prevent="$dispatch('open-document-preview', { 
                                 url: '{{ $proposalUrl }}', 
                                 title: 'Tentative Proposal - {{ addslashes($studentName) }}', 
                                 type: 'pdf' 
                             })"
                             class="group p-6 bg-slate-50 hover:bg-indigo-50/50 border-2 border-slate-200 hover:border-indigo-300 rounded-3xl transition-all cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-white text-rose-600 border border-slate-200 flex items-center justify-center font-black text-sm shadow-md group-hover:scale-105 transition-transform shrink-0">
                                    PDF
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
                                            {{ $latestProposal->file_meta['original_name'] ?? 'Tentative_Research_Proposal.pdf' }}
                                        </h4>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-indigo-100 text-indigo-800">
                                            Click to Preview
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Uploaded by {{ $studentName }} on {{ $latestProposal->created_at->format('M d, Y • h:i A') }} • Version 0{{ $latestProposal->version }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold uppercase tracking-wider group-hover:bg-indigo-700 transition-colors shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>Preview Document</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Current Supervisory Committee Allocation --}}
                    <div class="mt-8 pt-6 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                                Allocated Supervisors
                            </h3>
                            <span class="text-xs font-bold {{ $hasSupervisors ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $hasSupervisors ? $assignments->count() . ' of ' . $requiredSupervisorCount . ' Assigned' : 'Awaiting Assignment' }}
                            </span>
                        </div>

                        @if($hasSupervisors)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($assignments as $assignment)
                                    @php $sup = $assignment->supervisor; @endphp
                                    <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-indigo-600 font-black text-sm shadow-sm">
                                            {{ substr($sup->user?->name ?? 'S', 0, 1) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider {{ $assignment->role === 'primary' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-200 text-slate-700' }}">
                                                    {{ $assignment->role === 'primary' ? 'Lead' : 'Co-Supervisor' }}
                                                </span>
                                                <span class="text-[10px] font-bold text-slate-400">{{ $sup->rank }}</span>
                                            </div>
                                            <h4 class="text-sm font-bold text-slate-900 truncate mt-0.5">{{ $sup->user?->name }}</h4>
                                            <p class="text-xs text-slate-500 truncate">{{ $sup->department ?? $programName }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-6 bg-amber-50/60 border border-amber-200 rounded-2xl flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span class="text-xs font-semibold text-amber-900">
                                        Supervisors have not yet been assigned to this candidate. Click <strong>Assign Supervisors</strong> to select or randomize.
                                    </span>
                                </div>
                                <button type="button" @click="showAssignModal = true" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider shrink-0 transition-colors shadow-sm">
                                    Assign Now
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

    {{-- ========================================================================= --}}
    {{-- 3. ADMIN SIDE VIEW                                                        --}}
    {{-- ========================================================================= --}}
    @elseif(auth()->user()->hasRole('Admin'))
        <div class="space-y-6 animate-in-up">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Panel 1: Uploaded Proposal by Student --}}
                <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 p-8 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                <div class="w-1.5 h-4 bg-indigo-500 rounded-full"></div>
                                Candidate Proposal Document
                            </h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $hasUploaded ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $hasUploaded ? 'Uploaded' : 'Pending' }}
                            </span>
                        </div>

                        @if($hasUploaded)
                            <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center font-black text-xs text-rose-600 shadow-sm shrink-0">
                                    PDF
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold text-slate-900 truncate">
                                        {{ $latestProposal->file_meta['original_name'] ?? 'Tentative_Research_Proposal.pdf' }}
                                    </h4>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Version 0{{ $latestProposal->version }} • {{ $latestProposal->created_at->format('M d, Y • h:i A') }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="p-6 bg-amber-50/60 border border-amber-200 rounded-2xl text-center mb-4">
                                <svg class="w-8 h-8 text-amber-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <p class="text-xs font-bold text-amber-900">Student is yet to upload tentative proposal</p>
                                <p class="text-[11px] text-amber-700 mt-1">Awaiting candidate proposal submission.</p>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                        @if($hasUploaded)
                            <button type="button" 
                                    @click.prevent="$dispatch('open-document-preview', { 
                                        url: '{{ $proposalUrl }}', 
                                        title: 'Tentative Proposal - {{ addslashes($studentName) }}', 
                                        type: 'pdf' 
                                    })"
                                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-md active:scale-95 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Preview Uploaded Proposal</span>
                            </button>
                        @else
                            <button type="button" 
                                    @click.prevent.stop="showStudentMessageModal = true; messageRecipient = '{{ addslashes($studentName) }}'"
                                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-xs font-bold uppercase tracking-wider transition-colors shadow-sm cursor-pointer">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <span>Message Student</span>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Panel 2: Supervisors Allocated by Programme Coordinator --}}
                <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 p-8 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                <div class="w-1.5 h-4 bg-emerald-500 rounded-full"></div>
                                Coordinator Supervisor Allocation
                            </h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $hasSupervisors ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $hasSupervisors ? 'Allocated (' . $assignments->count() . ')' : 'Pending Allocation' }}
                            </span>
                        </div>

                        @if($hasSupervisors)
                            <div class="space-y-3 mb-4">
                                @foreach($assignments as $assignment)
                                    @php $sup = $assignment->supervisor; @endphp
                                    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-indigo-600 font-bold text-xs shadow-sm shrink-0">
                                                {{ substr($sup->user?->name ?? 'S', 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-slate-900 truncate">{{ $sup->user?->name }}</span>
                                                    <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded {{ $assignment->role === 'primary' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-200 text-slate-700' }}">
                                                        {{ $assignment->role === 'primary' ? 'Lead' : 'Co-Sup' }}
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-slate-500 truncate">{{ $sup->rank }} • {{ $sup->department ?? $programName }}</p>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-400 shrink-0">
                                            Load: {{ $sup->current_load }}/{{ $sup->max_students }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-6 bg-slate-50 border border-slate-200 rounded-2xl text-center mb-4">
                                <svg class="w-8 h-8 text-slate-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <p class="text-xs font-bold text-slate-700">Awaiting supervisor allocation by Program Coordinator</p>
                                <p class="text-[11px] text-slate-500 mt-1">Required: {{ $requiredSupervisorCount }} supervisors (Lead must be Professor)</p>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                        <button type="button" 
                                @click="showAssignModal = true"
                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all shadow-md active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            <span>{{ $hasSupervisors ? 'Manage / Override Supervisors' : 'Assign Supervisors' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    {{-- ========================================================================= --}}
    {{-- 4. GENERAL / OTHER ROLES FALLBACK                                         --}}
    {{-- ========================================================================= --}}
    @else
        <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 p-8 space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Supervisors Assigned Overview</h3>
                    <p class="text-xs text-slate-500">Candidate: {{ $studentName }} • Program: {{ $programName }}</p>
                </div>
                @if($hasUploaded)
                    <button type="button" 
                            @click.prevent="$dispatch('open-document-preview', { 
                                url: '{{ $proposalUrl }}', 
                                title: 'Tentative Proposal - {{ addslashes($studentName) }}', 
                                type: 'pdf' 
                            })"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold uppercase tracking-wider transition-colors border border-indigo-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Preview Proposal</span>
                    </button>
                @endif
            </div>

            @if($hasSupervisors)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($assignments as $assignment)
                        @php $sup = $assignment->supervisor; @endphp
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-indigo-600 font-bold text-xs shadow-sm">
                                {{ substr($sup->user?->name ?? 'S', 0, 1) }}
                            </div>
                            <div>
                                <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded {{ $assignment->role === 'primary' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $assignment->role === 'primary' ? 'Lead' : 'Co-Supervisor' }}
                                </span>
                                <h4 class="text-sm font-bold text-slate-900">{{ $sup->user?->name }}</h4>
                                <p class="text-xs text-slate-500">{{ $sup->rank }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs font-semibold text-slate-500">Supervisors have not yet been assigned by the Program Coordinator.</p>
            @endif
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL: ASSIGN SUPERVISORS (COORDINATOR / ADMIN)                            --}}
    {{-- ========================================================================= --}}
    @if(auth()->user()->hasAnyRole(['Program Coordinator', 'Admin']))
        <template x-teleport="body">
            <div x-show="showAssignModal" 
                 class="fixed z-50 inset-0 overflow-y-auto" 
                 style="display: none;" 
                 x-cloak>
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showAssignModal" 
                         @click="showAssignModal = false"
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0" 
                         x-transition:enter-end="opacity-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100" 
                         x-transition:leave-end="opacity-0" 
                         class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm cursor-pointer" 
                         aria-hidden="true"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showAssignModal" 
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-100 relative z-10">
                        
                        <form action="{{ route('theses.assign_supervisor', $milestone->thesis_project_id) }}" 
                              method="POST"
                              @submit="
                                  if (!selectedLead || !selectedSecond || (isPhD && !selectedThird)) {
                                      $event.preventDefault();
                                      alert('Please select all required supervisors.');
                                      return;
                                  }
                                  const ids = [selectedLead, selectedSecond];
                                  if (isPhD) ids.push(selectedThird);
                                  const unique = new Set(ids);
                                  if (unique.size !== ids.length) {
                                      $event.preventDefault();
                                      alert('Each supervisor in the panel must be unique. You cannot assign the same supervisor multiple times.');
                                      return;
                                  }
                              ">
                            @csrf
                            
                            {{-- Hidden inputs with selected supervisor IDs --}}
                            <input type="hidden" name="supervisors[]" :value="selectedLead">
                            <input type="hidden" name="supervisors[]" :value="selectedSecond">
                            <template x-if="isPhD">
                                <input type="hidden" name="supervisors[]" :value="selectedThird">
                            </template>

                            {{-- Modal Header --}}
                            <div class="bg-gradient-to-r from-indigo-900 via-slate-900 to-indigo-950 p-6 sm:p-8 text-white relative">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/10 text-indigo-300 border border-white/10 mb-2">
                                            Institutional Panel Assignment
                                        </span>
                                        <h3 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                                            Assign Supervisory Committee
                                        </h3>
                                        <p class="text-xs text-slate-300 mt-1">
                                            Candidate: <strong class="text-white">{{ $studentName }}</strong> &bull; Program: <strong class="text-white">{{ $programName }}</strong> ({{ $levelName ?: 'Postgraduate' }})
                                        </p>
                                    </div>
                                    <button type="button" @click="showAssignModal = false" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-white/10 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="p-6 sm:p-8 space-y-6">
                                {{-- Randomize Alert Banner --}}
                                <div x-show="randomizeAlert" 
                                     x-transition 
                                     class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold flex items-center gap-3">
                                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span x-text="randomizeAlert"></span>
                                </div>

                                {{-- Institutional Rules Callout with Randomize Button --}}
                                <div class="p-5 bg-indigo-50/70 border border-indigo-100 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                            <span class="text-xs font-black uppercase tracking-wider text-indigo-950">
                                                {{ $isPhD ? 'PhD Protocol: 3 Members' : 'MSc Protocol: 2 Members' }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-indigo-800 leading-relaxed">
                                            Lead Supervisor (Slot 1) must hold the rank of <strong>Professor</strong>.
                                        </p>
                                    </div>

                                    {{-- The RANDOMIZE Button --}}
                                    <button type="button" 
                                            @click="randomize()"
                                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md hover:shadow-lg transition-all active:scale-95 shrink-0 cursor-pointer">
                                        <svg class="w-4 h-4 text-purple-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                        </svg>
                                        <span>Randomize</span>
                                    </button>
                                </div>

                                {{-- Selection Dropdowns --}}
                                <div class="space-y-4">
                                    {{-- Slot 1: Lead Supervisor (Professor) --}}
                                    <div>
                                        <label class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                            <span>1. Lead Supervisor (Rank: Professor Required)</span>
                                            <span class="text-[10px] text-indigo-600 font-bold">Primary Chair</span>
                                        </label>
                                        <select x-model="selectedLead" 
                                                required 
                                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                                            <option value="">-- Select Professor for Lead Supervisor --</option>
                                            <template x-for="sup in supervisorsList" :key="sup.id">
                                                <option :value="sup.id" 
                                                        :disabled="!sup.is_professor && sup.id !== selectedLead"
                                                        x-text="(sup.is_professor ? '⭐ Professor ' : sup.rank + ' ') + sup.name + ' • Load: ' + sup.current_load + '/' + sup.max_students + (!sup.is_professor ? ' (Not a Professor)' : '')">
                                                </option>
                                            </template>
                                        </select>
                                    </div>

                                    {{-- Slot 2: Co-Supervisor 1 --}}
                                    <div>
                                        <label class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                            <span>2. Co-Supervisor</span>
                                            <span class="text-[10px] text-slate-400 font-bold">Secondary</span>
                                        </label>
                                        <select x-model="selectedSecond" 
                                                required 
                                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                                            <option value="">-- Select Co-Supervisor --</option>
                                            <template x-for="sup in supervisorsList" :key="sup.id">
                                                <option :value="sup.id" 
                                                        :disabled="sup.id === selectedLead || (isPhD && sup.id === selectedThird)"
                                                        x-text="sup.rank + ' ' + sup.name + ' • Load: ' + sup.current_load + '/' + sup.max_students">
                                                </option>
                                            </template>
                                        </select>
                                    </div>

                                    {{-- Slot 3: Co-Supervisor 2 (PhD Only) --}}
                                    <template x-if="isPhD">
                                        <div>
                                            <label class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                                <span>3. Second Co-Supervisor (PhD Requirement)</span>
                                                <span class="text-[10px] text-indigo-600 font-bold">Tertiary</span>
                                            </label>
                                            <select x-model="selectedThird" 
                                                    required 
                                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                                                <option value="">-- Select Second Co-Supervisor --</option>
                                                <template x-for="sup in supervisorsList" :key="sup.id">
                                                    <option :value="sup.id" 
                                                            :disabled="sup.id === selectedLead || sup.id === selectedSecond"
                                                            x-text="sup.rank + ' ' + sup.name + ' • Load: ' + sup.current_load + '/' + sup.max_students">
                                                    </option>
                                                </template>
                                            </select>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Modal Footer --}}
                            <div class="bg-slate-50 px-6 py-4 sm:px-8 border-t border-slate-100 flex items-center justify-between">
                                <p class="text-xs text-slate-500">
                                    Assignments will notify the student and committee members immediately.
                                </p>
                                <div class="flex items-center gap-3">
                                    <button type="button" 
                                            @click="showAssignModal = false" 
                                            class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-600 hover:bg-slate-100 transition-colors shadow-sm">
                                        Cancel
                                    </button>
                                    <button type="submit" 
                                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-lg shadow-indigo-600/30 active:scale-95 cursor-pointer">
                                        Authorize Assignment
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL: SEND MESSAGE TO CANDIDATE (COORDINATOR / ADMIN)                    --}}
    {{-- ========================================================================= --}}
    <template x-teleport="body">
        <div x-show="showStudentMessageModal" 
             class="fixed z-50 inset-0 overflow-y-auto" 
             style="display: none;" 
             x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showStudentMessageModal" 
                     @click="showStudentMessageModal = false"
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm cursor-pointer" 
                     aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showStudentMessageModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-100 relative z-10">
                     
                    <form action="{{ route('messages.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="thesis_project_id" value="{{ $milestone->thesis_project_id }}">
                        <input type="hidden" name="student_milestone_id" value="{{ $milestone->id }}">
                        
                        <div class="bg-white px-6 pt-8 pb-6 sm:p-8">
                            <div class="sm:flex sm:items-start gap-5">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-amber-50 sm:mx-0 shadow-md border border-amber-100 text-amber-600">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <div class="mt-4 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-xl font-bold text-slate-900 tracking-tight">
                                        Send Message to Candidate
                                    </h3>
                                    <p class="mt-1 text-xs font-medium text-slate-500 mb-4">
                                        Direct inbox communication to <strong class="text-slate-800">{{ $studentName }}</strong> regarding proposal submission.
                                    </p>
                                    
                                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Message Content</label>
                                    <textarea name="content" rows="4" class="w-full focus:ring-amber-500 focus:border-amber-500 text-sm border-slate-200 rounded-xl p-4 bg-slate-50 transition-colors resize-none" placeholder="Dear {{ $studentName }}, please upload your tentative research proposal so we can proceed with assigning your supervisors..." required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 sm:px-8 border-t border-slate-100 flex items-center justify-end gap-3">
                            <button type="button" @click="showStudentMessageModal = false" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-600 hover:bg-slate-100 transition-colors shadow-sm">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2.5 bg-amber-600 rounded-xl text-xs font-black uppercase tracking-wider text-white hover:bg-amber-700 transition-all shadow-md flex items-center gap-2 cursor-pointer">
                                <span>Send Message</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
