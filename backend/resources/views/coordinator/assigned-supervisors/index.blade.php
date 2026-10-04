@extends('layouts.coordinator')

@section('header', 'Assigned Supervisor')

@section('content')
@php
    // Prepare students array for Alpine.js modal interaction
    $studentsJson = $students->map(function($student) {
        $thesis = $student->thesis;
        $ms = $thesis && $thesis->milestones
            ? ($thesis->milestones->firstWhere(fn($m) => ($m->template?->slug === 'supervisors_assigned' || $m->template?->order == 2))
               ?? $thesis->milestones->first())
            : null;

        $submissions = $ms && $ms->relationLoaded('submissions') ? $ms->submissions : ($ms?->submissions ?? collect());
        $latestSub = $submissions instanceof \Illuminate\Support\Collection && $submissions->isNotEmpty()
            ? $submissions->sortByDesc('created_at')->first()
            : null;
        $hasUpload = !is_null($latestSub);
        $fileUrl = '';
        if ($latestSub && !empty($latestSub->file_url)) {
            $fileUrl = str_starts_with($latestSub->file_url, 'http')
                ? $latestSub->file_url
                : \Illuminate\Support\Facades\Storage::url($latestSub->file_url);
        }

        $assignments = $thesis && $thesis->relationLoaded('assignments') ? $thesis->assignments : ($thesis?->assignments ?? collect());
        $activeAssignments = $assignments instanceof \Illuminate\Support\Collection
            ? $assignments->where('status', 'active')->values()
            : collect();
        $leadAssignment = $activeAssignments->firstWhere('role', 'primary') ?? $activeAssignments->first();
        $secondaryAssignments = $activeAssignments->filter(fn($a) => $a->id !== $leadAssignment?->id)->values();

        $levelName = strtoupper($student->level->name ?? '');
        $isPhD = str_contains($levelName, 'PHD');
        $requiredCount = $isPhD ? 3 : 2;

        return [
            'id' => $student->id,
            'user_id' => $student->user_id,
            'name' => $student->user?->name ?? 'Student',
            'email' => $student->user?->email ?? '',
            'matric' => $student->student_id_number,
            'program_id' => $student->program_id,
            'program_name' => $student->program?->name ?? 'N/A',
            'level_name' => $student->level?->name ?? 'N/A',
            'is_phd' => $isPhD,
            'required_count' => $requiredCount,
            'thesis_id' => $thesis?->id,
            'thesis_title' => $thesis?->title ?? 'Untitled Thesis',
            'has_upload' => $hasUpload,
            'proposal_url' => $fileUrl,
            'proposal_date' => $latestSub && $latestSub->created_at ? $latestSub->created_at->format('M d, Y • h:i A') : '',
            'proposal_version' => $latestSub ? $latestSub->version : 1,
            'lead_id' => $leadAssignment?->supervisor_profile_id ?? '',
            'lead_name' => $leadAssignment?->supervisor?->user?->name ?? '',
            'second_id' => $secondaryAssignments->get(0)?->supervisor_profile_id ?? '',
            'second_name' => $secondaryAssignments->get(0)?->supervisor?->user?->name ?? '',
            'third_id' => $secondaryAssignments->get(1)?->supervisor_profile_id ?? '',
            'third_name' => $secondaryAssignments->get(1)?->supervisor?->user?->name ?? '',
            'assigned_count' => $activeAssignments->count(),
        ];
    })->values();
@endphp

<div x-data="{
    allSupervisors: {{ \Illuminate\Support\Js::from($supervisorsData) }},
    studentsList: {{ \Illuminate\Support\Js::from($studentsJson) }},
    showAssignModal: false,
    showPreviewModal: false,
    showMessageModal: false,
    activeStudent: null,
    previewUrl: '',
    previewTitle: '',
    messageRecipientName: '',
    messageRecipientId: '',
    messageRecipientUserId: '',
    modalForm: {
        lead_id: '',
        second_id: '',
        third_id: ''
    },
    randomizeAlert: '',
    formSubmitting: false,

    openAssignModal(studentId) {
        const student = this.studentsList.find(s => s.id === studentId);
        if (!student) return;
        this.activeStudent = student;
        this.modalForm.lead_id = student.lead_id || '';
        this.modalForm.second_id = student.second_id || '';
        this.modalForm.third_id = student.third_id || '';
        this.randomizeAlert = '';
        this.showAssignModal = true;
    },

    openPreview(url, title) {
        if (!url) return;
        this.previewUrl = url;
        this.previewTitle = title || 'Document Preview';
        this.showPreviewModal = true;
    },

    openMessageModal(student) {
        this.messageRecipientName = student.name;
        this.messageRecipientId = student.id;
        this.messageRecipientUserId = student.user_id;
        this.showMessageModal = true;
    },

    randomize() {
        if (!this.activeStudent) return;
        const student = this.activeStudent;

        // 1. Filter eligible supervisors for this student's program (or all if none program-specific)
        let eligible = this.allSupervisors.filter(s => 
            !s.program_ids || s.program_ids.length === 0 || s.program_ids.includes(student.program_id)
        );
        if (eligible.length === 0) {
            eligible = [...this.allSupervisors];
        }

        // 2. Main Supervisor must ALWAYS be a Professor
        const profs = eligible.filter(s => s.is_professor);
        if (profs.length === 0) {
            // Fall back to all professors across institution
            const allProfs = this.allSupervisors.filter(s => s.is_professor);
            if (allProfs.length === 0) {
                alert('No eligible Professors found in the supervisor roster. Institutional policy mandates a Professor as the Main Supervisor.');
                return;
            }
            profs.push(...allProfs);
        }

        // Sort professors by current student workload ascending (fewest students first)
        profs.sort((a, b) => a.current_load - b.current_load);
        const minProfLoad = profs[0].current_load;
        const lowestProfs = profs.filter(p => p.current_load === minProfLoad);
        const selectedLead = lowestProfs[Math.floor(Math.random() * lowestProfs.length)];

        this.modalForm.lead_id = selectedLead.id;

        // 3. Select secondary supervisor(s) from remaining pool sorted by lowest load
        let secondaryPool = eligible.filter(s => s.id !== selectedLead.id);
        if (secondaryPool.length === 0) {
            secondaryPool = this.allSupervisors.filter(s => s.id !== selectedLead.id);
        }

        secondaryPool.sort((a, b) => a.current_load - b.current_load);

        if (secondaryPool.length > 0) {
            const minSecLoad = secondaryPool[0].current_load;
            const lowestSec = secondaryPool.filter(s => s.current_load === minSecLoad);
            const selectedSecond = lowestSec[Math.floor(Math.random() * lowestSec.length)];
            this.modalForm.second_id = selectedSecond.id;

            // If PhD, select 3rd distinct supervisor
            if (student.is_phd) {
                let thirdPool = secondaryPool.filter(s => s.id !== selectedSecond.id);
                if (thirdPool.length > 0) {
                    const minThirdLoad = thirdPool[0].current_load;
                    const lowestThird = thirdPool.filter(s => s.current_load === minThirdLoad);
                    const selectedThird = lowestThird[Math.floor(Math.random() * lowestThird.length)];
                    this.modalForm.third_id = selectedThird.id;
                }
            }
        }

        this.randomizeAlert = 'Supervisors randomized successfully based on professor rank and current student workload! You can review or edit below.';
        setTimeout(() => { this.randomizeAlert = ''; }, 6000);
    },

    get isDuplicateSelected() {
        if (!this.activeStudent) return false;
        const ids = [this.modalForm.lead_id, this.modalForm.second_id];
        if (this.activeStudent.is_phd) {
            ids.push(this.modalForm.third_id);
        }
        const filtered = ids.filter(id => id && id.trim() !== '');
        return (new Set(filtered)).size !== filtered.length;
    }
}" class="space-y-8 pb-16">

    <!-- Sophisticated Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <div class="p-1.5 rounded-lg bg-acetel-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Supervisory Allocation • Milestone 2</span>
            </div>
            <h1 class="text-2xl md:text-4xl font-black text-slate-900 tracking-tight">Assigned Supervisor</h1>
            <p class="mt-2 text-sm font-medium text-slate-500">Manage and allocate academic supervisors for candidates currently on the Supervisors Assigned milestone.</p>
        </div>
    </div>

    <!-- Quick Analytics Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="p-6 bg-white rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Candidates on M2</p>
                <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($totalCount) }}</p>
                <p class="text-[11px] font-bold text-slate-400 mt-1">Supervisors Assigned</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </div>
        </div>

        <div class="p-6 bg-white rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Proposal Uploaded</p>
                <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($uploadedCount) }}</p>
                <p class="text-[11px] font-bold text-emerald-600 mt-1">Ready for Allocation</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>
        </div>

        <div class="p-6 bg-white rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Awaiting Proposal</p>
                <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($awaitingCount) }}</p>
                <p class="text-[11px] font-bold text-amber-600 mt-1">Pending Upload</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
        </div>

        <div class="p-6 bg-white rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black text-acetel-600 uppercase tracking-widest">Panel Allocated</p>
                <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($assignedCount) }}</p>
                <p class="text-[11px] font-bold text-acetel-600 mt-1">Active Committees</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-acetel-50 text-acetel-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="flex items-center gap-3 flex-wrap">
        <form method="GET" action="{{ route('coordinator.assigned-supervisors.index') }}" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <div class="relative w-full sm:w-80">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, matric, thesis..." class="w-full bg-white border border-slate-200 rounded-2xl pl-11 pr-4 py-2.5 placeholder-slate-400 text-sm focus:ring-2 focus:ring-acetel-500/20 focus:border-acetel-500 text-slate-900 font-medium shadow-sm transition-all outline-none">
            </div>

            @if(isset($programs) && count($programs) > 1)
                <div class="px-5 py-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-2 hover:border-acetel-300 transition-colors">
                    <select name="program_id" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold uppercase tracking-widest text-slate-500 focus:ring-0 cursor-pointer">
                        <option value="">All Programs</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" {{ request('program_id') == $prog->id ? 'selected' : '' }}>{{ $prog->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if(isset($cohorts) && count($cohorts) > 0)
                <div class="px-5 py-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-2 hover:border-acetel-300 transition-colors">
                    <select name="cohort_id" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold uppercase tracking-widest text-slate-500 focus:ring-0 cursor-pointer">
                        <option value="">All Cohorts</option>
                        @foreach($cohorts as $coh)
                            <option value="{{ $coh->id }}" {{ request('cohort_id') == $coh->id ? 'selected' : '' }}>{{ $coh->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="px-5 py-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-2 hover:border-acetel-300 transition-colors">
                <select name="batch" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold uppercase tracking-widest text-slate-500 focus:ring-0 cursor-pointer">
                    <option value="">All Batches</option>
                    <option value="1" {{ request('batch') == '1' ? 'selected' : '' }}>Batch 1</option>
                    <option value="2" {{ request('batch') == '2' ? 'selected' : '' }}>Batch 2</option>
                </select>
            </div>

            <div class="px-5 py-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-2 hover:border-acetel-300 transition-colors">
                <select name="proposal_status" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold uppercase tracking-widest text-slate-500 focus:ring-0 cursor-pointer">
                    <option value="">All Proposal Status</option>
                    <option value="uploaded" {{ request('proposal_status') == 'uploaded' ? 'selected' : '' }}>Uploaded (Ready)</option>
                    <option value="awaiting" {{ request('proposal_status') == 'awaiting' ? 'selected' : '' }}>Awaiting Proposal</option>
                </select>
            </div>

            <div class="px-5 py-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-2 hover:border-acetel-300 transition-colors">
                <select name="assignment_status" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold uppercase tracking-widest text-slate-500 focus:ring-0 cursor-pointer">
                    <option value="">All Assignment Status</option>
                    <option value="assigned" {{ request('assignment_status') == 'assigned' ? 'selected' : '' }}>Allocated</option>
                    <option value="pending" {{ request('assignment_status') == 'pending' ? 'selected' : '' }}>Pending Allocation</option>
                </select>
            </div>

            @if(request('search') || request('program_id') || request('cohort_id') || request('batch') || request('proposal_status') || request('assignment_status'))
                <a href="{{ route('coordinator.assigned-supervisors.index') }}" class="p-2.5 text-slate-400 hover:text-rose-500 bg-white border border-slate-200 rounded-2xl transition-all shadow-sm" title="Reset Filters">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                </a>
            @endif
            <button type="submit" class="hidden"></button>
        </form>
        <div class="px-5 py-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-3">
            <span class="text-xs font-bold text-slate-600">Total: {{ number_format($students->total()) }}</span>
        </div>
    </div>

    <!-- Main Candidates Table Container -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden min-h-[500px] flex flex-col">
        <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
            <div>
                <h3 class="text-xl font-black text-slate-900 tracking-tight">Candidates Awaiting / On Supervisors Assigned Milestone</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Click any candidate name or button to view proposal and allocate supervisors</p>
            </div>
        </div>

        <div class="flex-1">
            @if(isset($students) && $students->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Candidate</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Program & Level</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tentative Proposal</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Supervisory Committee</th>
                                <th class="px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($students as $student)
                                @php
                                    $thesis = $student->thesis;
                                    $ms = $thesis && $thesis->milestones
                                        ? ($thesis->milestones->firstWhere(fn($m) => ($m->template?->slug === 'supervisors_assigned' || $m->template?->order == 2))
                                           ?? $thesis->milestones->first())
                                        : null;

                                    $submissions = $ms && $ms->relationLoaded('submissions') ? $ms->submissions : ($ms?->submissions ?? collect());
                                    $latestSub = $submissions instanceof \Illuminate\Support\Collection && $submissions->isNotEmpty()
                                        ? $submissions->sortByDesc('created_at')->first()
                                        : null;
                                    $hasUpload = !is_null($latestSub);

                                    $assignments = $thesis && $thesis->relationLoaded('assignments') ? $thesis->assignments : ($thesis?->assignments ?? collect());
                                    $activeAssignments = $assignments instanceof \Illuminate\Support\Collection
                                        ? $assignments->where('status', 'active')->values()
                                        : collect();
                                    $hasSupervisors = $activeAssignments->count() > 0;
                                    $leadSup = $activeAssignments->firstWhere('role', 'primary') ?? $activeAssignments->first();
                                @endphp
                                <tr class="hover:bg-slate-50/40 transition-colors group">
                                    {{-- Candidate Name & Matric --}}
                                    <td class="px-10 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-11 h-11 rounded-2xl bg-indigo-50 border border-indigo-100/60 flex items-center justify-center text-indigo-600 font-black text-sm shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                                {{ substr($student->user?->name ?? 'Student', 0, 1) }}
                                            </div>
                                            <div>
                                                <button type="button" 
                                                        @click="openAssignModal('{{ $student->id }}')" 
                                                        class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors text-left hover:underline focus:outline-none">
                                                    {{ $student->user?->name ?? 'Student' }}
                                                </button>
                                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $student->student_id_number }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Program & Level --}}
                                    <td class="px-6 py-6 font-medium">
                                        <div class="space-y-1.5">
                                            <p class="text-xs font-bold text-slate-700 leading-none">{{ $student->program?->name ?? '--' }}</p>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md @if(str_contains(strtolower($student->level->name ?? ''), 'phd')) bg-indigo-50 text-indigo-700 @else bg-amber-50 text-amber-700 @endif text-[8px] font-black uppercase tracking-tighter border border-current/10">
                                                    {{ $student->level?->name ?? '--' }}
                                                </span>
                                                @if($student->cohort)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[8px] font-bold uppercase tracking-tighter border border-slate-200">
                                                        {{ $student->cohort->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Tentative Proposal Status --}}
                                    <td class="px-6 py-6">
                                        @if($hasUpload)
                                            <div class="flex flex-col items-start gap-1">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[9px] font-black uppercase tracking-wider border border-emerald-100">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                    Uploaded (Ready)
                                                </span>
                                                <span class="text-[9px] font-medium text-slate-400">
                                                    {{ $latestSub && $latestSub->created_at ? $latestSub->created_at->format('M d, Y') : '--' }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="flex flex-col items-start gap-1">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-[9px] font-black uppercase tracking-wider border border-amber-100">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    Awaiting Proposal
                                                </span>
                                                <span class="text-[9px] font-medium text-slate-400">
                                                    Not submitted yet
                                                </span>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Supervisory Committee --}}
                                    <td class="px-6 py-6">
                                        @if($hasSupervisors)
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    <span class="text-xs font-bold text-slate-800">{{ $leadSup?->supervisor?->user?->name ?? 'Lead Supervisor' }}</span>
                                                    <span class="text-[8px] font-black text-indigo-600 uppercase bg-indigo-50 px-1.5 py-0.2 rounded border border-indigo-100">Lead (Prof)</span>
                                                </div>
                                                @foreach($activeAssignments->filter(fn($a) => $a->id !== $leadSup?->id) as $subSup)
                                                    <div class="flex items-center gap-1.5 text-xs text-slate-500 pl-3">
                                                        <span>• {{ $subSup->supervisor?->user?->name ?? 'Co-Supervisor' }}</span>
                                                        <span class="text-[8px] font-bold text-slate-400 uppercase">(Co-Sup)</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 text-[9px] font-bold uppercase tracking-wider">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Pending Allocation
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Action Button --}}
                                    <td class="px-10 py-6 text-right">
                                        <button type="button" 
                                                @click="openAssignModal('{{ $student->id }}')" 
                                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white text-xs font-bold shadow-md hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-200">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                            <span>{{ $hasSupervisors ? 'Manage Committee' : 'Assign Supervisors' }}</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-[420px] text-center px-10">
                    <div class="w-20 h-20 rounded-[2rem] bg-indigo-50 border border-indigo-100 flex items-center justify-center mb-6 shadow-inner text-indigo-500">
                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h4 class="text-xl font-black text-slate-900 tracking-tight">No Candidates Currently on Milestone 2</h4>
                    <p class="text-sm text-slate-500 mt-2 max-w-sm mx-auto">There are currently no students in your coordinated programs awaiting supervisor assignment.</p>
                </div>
            @endif
        </div>

        @if($students->hasPages())
            <div class="px-10 py-6 bg-slate-50/50 border-t border-slate-50">
                {{ $students->links() }}
            </div>
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- SUPERVISOR ALLOCATION MODAL                                               --}}
    {{-- ========================================================================= --}}
    <div x-show="showAssignModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop --}}
            <div x-show="showAssignModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                 @click="showAssignModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div x-show="showAssignModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-100 relative z-10">
                
                <template x-if="activeStudent">
                    <form :action="`/coordinator/assigned-supervisors/${activeStudent.id}/assign`" 
                          method="POST" 
                          @submit="formSubmitting = true">
                        @csrf

                        {{-- Modal Header --}}
                        <div class="px-8 pt-8 pb-6 border-b border-slate-100 flex items-start justify-between bg-slate-50/50">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-500 text-white flex items-center justify-center font-black text-lg shadow-md shadow-indigo-500/20">
                                    <span x-text="activeStudent.name.charAt(0)"></span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-xl font-black text-slate-900 tracking-tight" x-text="activeStudent.name"></h3>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider" 
                                              :class="activeStudent.is_phd ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                                              x-text="activeStudent.level_name"></span>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-500 mt-0.5">
                                        <span x-text="activeStudent.matric"></span> • <span x-text="activeStudent.program_name"></span>
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="showAssignModal = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="px-8 py-6 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">

                            {{-- ========================================================= --}}
                            {{-- 1. TENTATIVE PROPOSAL SUBMISSION STATUS                   --}}
                            {{-- ========================================================= --}}
                            <div class="rounded-2xl p-5 border transition-all" 
                                 :class="activeStudent.has_upload ? 'bg-emerald-50/60 border-emerald-200' : 'bg-amber-50/60 border-amber-200'">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-start gap-3.5">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm"
                                             :class="activeStudent.has_upload ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white'">
                                            <template x-if="activeStudent.has_upload">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            </template>
                                            <template x-if="!activeStudent.has_upload">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                            </template>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-sm font-black text-slate-900" 
                                                    x-text="activeStudent.has_upload ? 'Tentative Research Proposal Submitted' : 'Awaiting Student Tentative Proposal'"></h4>
                                                <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider"
                                                      :class="activeStudent.has_upload ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                                      x-text="activeStudent.has_upload ? 'Uploaded' : 'Pending'"></span>
                                            </div>
                                            <p class="text-xs text-slate-600 mt-1" 
                                               x-text="activeStudent.has_upload ? `Submitted on ${activeStudent.proposal_date} (v0${activeStudent.proposal_version}). Review this document to evaluate the candidate's research direction.` : 'The student has not uploaded their tentative research proposal yet. You may still proceed with supervisor allocation.'"></p>
                                        </div>
                                    </div>

                                    {{-- Actions for Proposal --}}
                                    <div class="shrink-0 flex items-center gap-2">
                                        <template x-if="activeStudent.has_upload">
                                            <button type="button" 
                                                    @click="openPreview(activeStudent.proposal_url, `Tentative Proposal - ${activeStudent.name}`)" 
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-emerald-300 text-emerald-800 hover:bg-emerald-50 text-xs font-bold shadow-sm transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                <span>Preview</span>
                                            </button>
                                        </template>

                                        <template x-if="!activeStudent.has_upload">
                                            <button type="button" 
                                                    @click="openMessageModal(activeStudent)" 
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-amber-300 text-amber-800 hover:bg-amber-50 text-xs font-bold shadow-sm transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                                                <span>Message</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- ========================================================= --}}
                            {{-- 2. RANDOMIZE SUPERVISORS BUTTON & ALERTS                  --}}
                            {{-- ========================================================= --}}
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                                <div>
                                    <h4 class="text-sm font-black text-slate-900 tracking-tight">Supervisory Committee</h4>
                                    <p class="text-xs text-slate-500 font-medium">
                                        Select <span class="font-bold text-slate-800" x-text="activeStudent.required_count"></span> supervisors. 
                                        Main Supervisor must be a <strong class="text-indigo-600">Professor</strong>.
                                    </p>
                                </div>

                                {{-- Randomize Button --}}
                                <button type="button" 
                                        @click="randomize()" 
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-600 hover:text-white hover:border-transparent text-xs font-black shadow-sm transition-all duration-200 group">
                                    <svg class="w-4 h-4 text-indigo-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                    <span>Randomize Supervisors</span>
                                </button>
                            </div>

                            {{-- Alert message if randomized --}}
                            <div x-show="randomizeAlert" 
                                 x-transition 
                                 class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-3">
                                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                <span x-text="randomizeAlert"></span>
                            </div>

                            {{-- Duplicate Selection Warning --}}
                            <div x-show="isDuplicateSelected" 
                                 x-cloak
                                 class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-3">
                                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                <span>A supervisor cannot be assigned more than once to the same committee. Please choose distinct supervisors.</span>
                            </div>

                            {{-- ========================================================= --}}
                            {{-- 3. SUPERVISOR SELECT DROPDOWNS                            --}}
                            {{-- ========================================================= --}}
                            <div class="space-y-4">
                                {{-- Main Supervisor (Must be a Professor) --}}
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2 flex items-center justify-between">
                                        <span>1. Main Supervisor <span class="text-rose-500">*</span></span>
                                        <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">
                                            Institutional Mandate: Must be a Professor
                                        </span>
                                    </label>
                                    <select name="supervisors[]" 
                                            x-model="modalForm.lead_id" 
                                            required 
                                            class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                                        <option value="">-- Select Main Supervisor (Professor) --</option>
                                        {{-- Professors group --}}
                                        <optgroup label="Available Professors (Mandated Rank)">
                                            <template x-for="sup in allSupervisors.filter(s => s.is_professor)" :key="'prof_' + sup.id">
                                                <option :value="sup.id" 
                                                        :selected="modalForm.lead_id === sup.id"
                                                        x-text="`👑 ${sup.name} (${sup.department} • Active Load: ${sup.current_load}/${sup.max_students})`">
                                                </option>
                                            </template>
                                        </optgroup>
                                        {{-- Non-professors disabled note --}}
                                        <optgroup label="Other Supervisors (Non-Professors ineligible for Lead)">
                                            <template x-for="sup in allSupervisors.filter(s => !s.is_professor)" :key="'nonprof_' + sup.id">
                                                <option :value="sup.id" disabled x-text="`${sup.name} (${sup.rank} - Not Professor)`"></option>
                                            </template>
                                        </optgroup>
                                    </select>
                                </div>

                                {{-- Co-Supervisor 1 --}}
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2 flex items-center justify-between">
                                        <span>2. Co-Supervisor <span class="text-rose-500">*</span></span>
                                        <span class="text-[10px] font-bold text-slate-400">Secondary Role</span>
                                    </label>
                                    <select name="supervisors[]" 
                                            x-model="modalForm.second_id" 
                                            required 
                                            class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                                        <option value="">-- Select Co-Supervisor --</option>
                                        <template x-for="sup in allSupervisors" :key="'sec_' + sup.id">
                                            <option :value="sup.id" 
                                                    :selected="modalForm.second_id === sup.id"
                                                    :disabled="sup.id === modalForm.lead_id || (activeStudent.is_phd && sup.id === modalForm.third_id)"
                                                    x-text="`${sup.name} (${sup.rank} • Active Load: ${sup.current_load}/${sup.max_students})`">
                                            </option>
                                        </template>
                                    </select>
                                </div>

                                {{-- Co-Supervisor 2 (PhD Only) --}}
                                <template x-if="activeStudent.is_phd">
                                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2 flex items-center justify-between">
                                            <span>3. Co-Supervisor 2 <span class="text-rose-500">*</span></span>
                                            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">PhD Requirement</span>
                                        </label>
                                        <select name="supervisors[]" 
                                                x-model="modalForm.third_id" 
                                                :required="activeStudent.is_phd"
                                                class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                                            <option value="">-- Select Third Committee Member --</option>
                                            <template x-for="sup in allSupervisors" :key="'third_' + sup.id">
                                                <option :value="sup.id" 
                                                        :selected="modalForm.third_id === sup.id"
                                                        :disabled="sup.id === modalForm.lead_id || sup.id === modalForm.second_id"
                                                        x-text="`${sup.name} (${sup.rank} • Active Load: ${sup.current_load}/${sup.max_students})`">
                                                </option>
                                            </template>
                                        </select>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="px-8 py-5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                            <button type="button" 
                                    @click="showAssignModal = false" 
                                    class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-100 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    :disabled="formSubmitting || isDuplicateSelected || !modalForm.lead_id || !modalForm.second_id || (activeStudent.is_phd && !modalForm.third_id)" 
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white text-xs font-black shadow-md hover:shadow-lg hover:shadow-indigo-500/20 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <template x-if="formSubmitting">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                </template>
                                <span>Save Supervisor Committee</span>
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- PROPOSAL PDF PREVIEW MODAL                                                --}}
    {{-- ========================================================================= --}}
    <div x-show="showPreviewModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="preview-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showPreviewModal" 
                 class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity" 
                 @click="showPreviewModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="showPreviewModal" 
                 class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full border border-slate-100 relative z-10">
                <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900" x-text="previewTitle"></h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Document Preview</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="previewUrl" target="_blank" download class="p-2 text-slate-400 hover:text-indigo-600 rounded-xl hover:bg-slate-100 transition-colors" title="Open in new tab">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                        <button type="button" @click="showPreviewModal = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>
                <div class="h-[75vh] w-full bg-slate-100 p-2">
                    <iframe :src="previewUrl" class="w-full h-full rounded-2xl border-none"></iframe>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SEND DIRECT INBOX MESSAGE MODAL                                           --}}
    {{-- ========================================================================= --}}
    <div x-show="showMessageModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showMessageModal" 
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
                 @click="showMessageModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="showMessageModal" 
                 class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 relative z-10">
                <form action="{{ route('inbox.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="to[]" :value="messageRecipientUserId">

                    <div class="px-8 pt-8 pb-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900">Message Candidate</h3>
                                <p class="text-xs font-semibold text-slate-500" x-text="`To: ${messageRecipientName}`"></p>
                            </div>
                        </div>
                        <button type="button" @click="showMessageModal = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="p-8 space-y-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Subject</label>
                            <input type="text" name="subject" value="Action Required: Upload Tentative Research Proposal" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Message Content</label>
                            <textarea name="body" rows="4" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">Dear student, please upload your tentative research proposal on the portal as soon as possible so that your supervisory committee can be finalized and allocated. Thank you.</textarea>
                        </div>
                    </div>

                    <div class="px-8 py-5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <button type="button" @click="showMessageModal = false" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-100 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-amber-600 text-white text-xs font-black shadow-md hover:shadow-lg transition-all">
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
