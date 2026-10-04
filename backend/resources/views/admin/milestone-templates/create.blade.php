@extends('layouts.admin')

@section('header')
    Milestone Protocol Architect
@endsection

@section('content')
<div class="space-y-8"
     x-data="{
        step: 1,
        totalSteps: 6,
        requiresSubmission: {{ old('requires_submission') ? 'true' : 'false' }},
        allowPlagiarism: {{ old('allow_plagiarism_report') ? 'true' : 'false' }},
        requiresApproval: {{ old('requires_approval', true) ? 'true' : 'false' }},
        allowDefence: {{ old('allow_defence_date') ? 'true' : 'false' }},
        next() { if (this.step < this.totalSteps) this.step++ },
        prev() { if (this.step > 1) this.step-- },
        progress() { return Math.round((this.step / this.totalSteps) * 100) }
     }">

    {{-- Page Header --}}
    <div class="relative overflow-hidden rounded-[2.5rem] p-10 lg:p-12 bg-white border border-green-100 shadow-xl shadow-green-500/5">
        <div class="absolute top-0 right-0 w-[40rem] h-[40rem] bg-green-50/50 blur-[100px] rounded-full -mr-32 -mt-32 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-green-50/30 blur-[80px] rounded-full -ml-32 -mb-32 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="flex-1 space-y-4">
                <div class="inline-flex items-center gap-2.5 px-4 py-2 bg-green-50 border border-green-200 rounded-full text-green-700 text-[10px] font-black uppercase tracking-wider shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Protocol Architect
                </div>
                <div>
                    <h1 class="text-3xl lg:text-5xl font-black text-slate-900 tracking-tight leading-none mb-3">
                        Create Milestone <span class="text-green-600">Template</span>
                    </h1>
                    <p class="text-base text-slate-500 font-medium leading-relaxed max-w-xl">
                        Design the complete workflow logic for this institutional milestone — from submission requirements to final sign-off protocols.
                    </p>
                </div>
                <a href="{{ route('admin.milestone-templates.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-green-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    Back to Templates
                </a>
            </div>
            <div class="shrink-0 hidden lg:flex flex-col items-center gap-3 text-center">
                <div class="w-20 h-20 rounded-3xl bg-green-50 border border-green-100 flex items-center justify-center shadow-sm">
                    <svg class="w-9 h-9 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <span class="text-[9px] font-black text-green-600 uppercase tracking-widest">Step <span x-text="step"></span> / <span x-text="totalSteps"></span></span>
            </div>
        </div>
    </div>

    {{-- Progress Stepper --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Progress</span>
            <span class="text-xs font-black text-green-600 uppercase tracking-widest" x-text="progress() + '% Complete'"></span>
        </div>
        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden mb-6">
            <div class="h-full bg-gradient-to-r from-green-500 to-green-600 rounded-full transition-all duration-700 ease-out"
                 :style="'width: ' + progress() + '%'"></div>
        </div>
        {{-- Step Indicators --}}
        <div class="flex items-center justify-between px-1">
            @foreach([
                ['icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'label' => 'Identity'],
                ['icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12', 'label' => 'Submission'],
                ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'label' => 'Plagiarism'],
                ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Sign-off'],
                ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'label' => 'Defence'],
                ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Panels'],
            ] as $idx => $s)
            <div class="flex flex-col items-center gap-1.5">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-300 border-2"
                     :class="step > {{ $idx + 1 }} ? 'bg-green-600 border-green-600 shadow-md shadow-green-500/25'
                            : step === {{ $idx + 1 }} ? 'bg-white border-green-500 ring-2 ring-green-200'
                            : 'bg-white border-slate-200'">
                    <svg class="w-3.5 h-3.5 transition-colors duration-300"
                         :class="step > {{ $idx + 1 }} ? 'text-white' : step === {{ $idx + 1 }} ? 'text-green-600' : 'text-slate-300'"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}"/>
                    </svg>
                </div>
                <span class="text-[9px] font-black uppercase tracking-widest transition-colors duration-300 hidden sm:block"
                      :class="step >= {{ $idx + 1 }} ? 'text-green-600' : 'text-slate-300'">{{ $s['label'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <form action="{{ route('admin.milestone-templates.store') }}" method="POST" id="milestone-form">
    @csrf

    {{-- STEP 1: BASIC IDENTITY --}}
    <div x-show="step === 1"
         x-transition:enter="transition ease-out duration-400"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 flex items-center gap-4 bg-slate-50/50">
                <div class="w-12 h-12 rounded-2xl bg-green-50 border border-green-100 flex items-center justify-center text-green-600 shadow-sm">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Basic Identity</h2>
                    <p class="text-xs font-medium text-slate-400 mt-0.5">Name, sequence order and program scope for this protocol.</p>
                </div>
            </div>
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Protocol Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               placeholder="e.g. Thesis Proposal Submission"
                               class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 text-sm font-medium text-slate-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Sequence Order <span class="text-rose-500">*</span></label>
                        <input type="number" name="order" value="{{ old('order') }}" min="1"
                               placeholder="e.g. 1, 2, 3..."
                               class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 text-sm font-medium text-slate-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Description</label>
                    <textarea name="description" rows="3" placeholder="Brief description of this milestone's purpose..."
                              class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 text-sm font-medium text-slate-900 placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all resize-none">{{ old('description') }}</textarea>
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Program Scope</label>
                    <div class="relative">
                        <select name="program_id" class="w-full appearance-none bg-white border border-slate-200 rounded-2xl px-5 py-4 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                            <option value="">🌐 Global Institutional Protocol (All Programs)</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 2: SUBMISSION & ARTIFACTS --}}
    <div x-show="step === 2"
         x-transition:enter="transition ease-out duration-400"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 flex items-center gap-4 bg-slate-50/50">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Submission & Artifacts</h2>
                    <p class="text-xs font-medium text-slate-400 mt-0.5">Define what documents students are required to submit.</p>
                </div>
            </div>
            <div class="p-8 space-y-6">
                {{-- Master Toggle --}}
                <label class="flex items-center justify-between p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300"
                       :class="requiresSubmission ? 'bg-blue-50 border-blue-200' : 'bg-white border-slate-100 hover:border-slate-200'">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all"
                             :class="requiresSubmission ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25' : 'bg-slate-100 text-slate-400'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4"/></svg>
                        </div>
                        <div>
                            <span class="text-sm font-black text-slate-900 block">Student Must Submit Documents</span>
                            <span class="text-xs text-slate-400" x-text="requiresSubmission ? 'Submission required — configure below.' : 'Toggle to enable submission requirements.'"></span>
                        </div>
                    </div>
                    <input type="checkbox" name="requires_submission" value="1" x-model="requiresSubmission" class="sr-only">
                    <div class="w-12 h-6 rounded-full transition-all duration-300 relative flex-shrink-0"
                         :class="requiresSubmission ? 'bg-blue-600' : 'bg-slate-200'">
                        <div class="absolute top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-all duration-300"
                             :class="requiresSubmission ? 'left-7' : 'left-1'"></div>
                    </div>
                </label>

                <div x-show="requiresSubmission" x-transition class="space-y-4">
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                        <span class="block text-[10px] font-black text-blue-600 uppercase tracking-[0.15em]">Required Artifact Types</span>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach([
                                ['ppt', 'PPT / Presentation', 'M8 14h8M8 10h8M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                                ['file', 'Manuscript', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                                ['publication', 'Publication', 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253']
                            ] as [$val,$lbl,$icon])
                            <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all bg-white hover:border-blue-300">
                                <input type="checkbox" name="submission_type[]" value="{{ $val }}" {{ $val === 'file' ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500/20">
                                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
                                <span class="text-sm font-bold text-slate-700">{{ $lbl }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="submission_requires_approval" value="1" {{ old('submission_requires_approval') ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-blue-600 border-slate-300">
                            <div>
                                <span class="text-sm font-black text-slate-900 block">Require Pre-Submission Approval (Unlock Gate)</span>
                                <span class="text-xs text-slate-400">An authorized role must unlock submissions before the student can upload.</span>
                            </div>
                        </label>
                        <div class="pl-7 space-y-2">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Roles authorized to unlock:</span>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                @foreach($roles as $role)
                                <label class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white border border-slate-200 hover:border-blue-300 cursor-pointer transition-all">
                                    <input type="checkbox" name="submission_approver_roles[]" value="{{ $role }}" {{ (is_array(old('submission_approver_roles')) && in_array($role, old('submission_approver_roles'))) ? 'checked' : '' }}
                                           class="w-3.5 h-3.5 rounded text-blue-600 border-slate-300">
                                    <span class="text-xs font-bold text-slate-600">{{ $role }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 3: PLAGIARISM PROTOCOL --}}
    <div x-show="step === 3"
         x-transition:enter="transition ease-out duration-400"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 flex items-center gap-4 bg-slate-50/50">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shadow-sm">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Plagiarism Verification</h2>
                    <p class="text-xs font-medium text-slate-400 mt-0.5">Configure the similarity certification step for this milestone.</p>
                </div>
            </div>
            <div class="p-8 space-y-6">
                <label class="flex items-center justify-between p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300"
                       :class="allowPlagiarism ? 'bg-amber-50 border-amber-200' : 'bg-white border-slate-100 hover:border-slate-200'">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all"
                             :class="allowPlagiarism ? 'bg-amber-500 text-white shadow-md shadow-amber-500/25' : 'bg-slate-100 text-slate-400'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4"/></svg>
                        </div>
                        <div>
                            <span class="text-sm font-black text-slate-900 block">Include Plagiarism Audit Step</span>
                            <span class="text-xs text-slate-400" x-text="allowPlagiarism ? 'Similarity certification is required for this milestone.' : 'Toggle to enable plagiarism verification.'"></span>
                        </div>
                    </div>
                    <input type="checkbox" name="allow_plagiarism_report" value="1" x-model="allowPlagiarism" class="sr-only">
                    <div class="w-12 h-6 rounded-full transition-all duration-300 relative flex-shrink-0"
                         :class="allowPlagiarism ? 'bg-amber-500' : 'bg-slate-200'">
                        <div class="absolute top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-all duration-300"
                             :class="allowPlagiarism ? 'left-7' : 'left-1'"></div>
                    </div>
                </label>

                <div x-show="allowPlagiarism" x-transition class="p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                    <span class="block text-[10px] font-black text-amber-600 uppercase tracking-[0.15em]">Primary Upload Authority</span>
                    <p class="text-xs text-slate-400">Which role is responsible for uploading and certifying the similarity report?</p>
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        @foreach(['Admin','Program Coordinator'] as $pRole)
                        <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all bg-white hover:border-amber-300">
                            <input type="radio" name="plagiarism_report_role" value="{{ $pRole }}" {{ $pRole === 'Admin' ? 'checked' : '' }}
                                   class="w-4 h-4 text-amber-600 border-slate-300 focus:ring-amber-500/20">
                            <span class="text-sm font-bold text-slate-700">{{ $pRole }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 4: INSTITUTIONAL SIGN-OFF --}}
    <div x-show="step === 4"
         x-transition:enter="transition ease-out duration-400"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 flex items-center gap-4 bg-slate-50/50">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Institutional Sign-off</h2>
                    <p class="text-xs font-medium text-slate-400 mt-0.5">Define who must authorize this milestone for completion.</p>
                </div>
            </div>
            <div class="p-8 space-y-6">
                <label class="flex items-center justify-between p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300"
                       :class="requiresApproval ? 'bg-emerald-50 border-emerald-200' : 'bg-white border-slate-100 hover:border-slate-200'">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all"
                             :class="requiresApproval ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/25' : 'bg-slate-100 text-slate-400'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4"/></svg>
                        </div>
                        <div>
                            <span class="text-sm font-black text-slate-900 block">Require Final Institutional Approval</span>
                            <span class="text-xs text-slate-400" x-text="requiresApproval ? 'Approval workflow is active.' : 'Toggle to require sign-off before completion.'"></span>
                        </div>
                    </div>
                    <input type="checkbox" name="requires_approval" value="1" x-model="requiresApproval" class="sr-only">
                    <div class="w-12 h-6 rounded-full transition-all duration-300 relative flex-shrink-0"
                         :class="requiresApproval ? 'bg-emerald-600' : 'bg-slate-200'">
                        <div class="absolute top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-all duration-300"
                             :class="requiresApproval ? 'left-7' : 'left-1'"></div>
                    </div>
                </label>

                <div x-show="requiresApproval" x-transition class="space-y-4">
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                        <span class="block text-[10px] font-black text-emerald-600 uppercase tracking-[0.15em]">Approval Threshold</span>
                        <p class="text-xs text-slate-400">How many approvals are needed to mark this milestone as complete?</p>
                        <div class="flex items-center gap-4 pt-1">
                            <input type="number" name="approval_threshold" value="{{ old('approval_threshold', 1) }}" min="1"
                                   class="w-24 bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm font-black text-slate-900 text-center focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                            <span class="text-sm text-slate-400 font-medium">approvals required</span>
                        </div>
                    </div>

                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                        <span class="block text-[10px] font-black text-emerald-600 uppercase tracking-[0.15em]">Authorized Approver Roles</span>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach($roles as $role)
                            <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white border border-slate-200 hover:border-emerald-300 cursor-pointer transition-all">
                                <input type="checkbox" name="required_approvers[]" value="{{ $role }}"
                                       {{ (is_array(old('required_approvers', ['Admin', 'Program Coordinator'])) && in_array($role, old('required_approvers', ['Admin', 'Program Coordinator']))) ? 'checked' : '' }}
                                       class="w-3.5 h-3.5 rounded text-emerald-600 border-slate-300">
                                <span class="text-xs font-bold text-slate-600">{{ $role }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 5: DEFENCE & EXAMINATION --}}
    <div x-show="step === 5"
         x-transition:enter="transition ease-out duration-400"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 flex items-center gap-4 bg-slate-50/50">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shadow-sm">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Defence & Examination</h2>
                    <p class="text-xs font-medium text-slate-400 mt-0.5">Enable and configure defence scheduling for this milestone.</p>
                </div>
            </div>
            <div class="p-8 space-y-6">
                <label class="flex items-center justify-between p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300"
                       :class="allowDefence ? 'bg-rose-50 border-rose-200' : 'bg-white border-slate-100 hover:border-slate-200'">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all"
                             :class="allowDefence ? 'bg-rose-600 text-white shadow-md shadow-rose-500/25' : 'bg-slate-100 text-slate-400'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4"/></svg>
                        </div>
                        <div>
                            <span class="text-sm font-black text-slate-900 block">Show Defence / Examination Details</span>
                            <span class="text-xs text-slate-400" x-text="allowDefence ? 'Defence scheduling is enabled.' : 'Toggle to enable examination scheduling.'"></span>
                        </div>
                    </div>
                    <input type="checkbox" name="allow_defence_date" value="1" x-model="allowDefence" class="sr-only">
                    <div class="w-12 h-6 rounded-full transition-all duration-300 relative flex-shrink-0"
                         :class="allowDefence ? 'bg-rose-600' : 'bg-slate-200'">
                        <div class="absolute top-1 w-4 h-4 rounded-full bg-white shadow-sm transition-all duration-300"
                             :class="allowDefence ? 'left-7' : 'left-1'"></div>
                    </div>
                </label>

                <div x-show="allowDefence" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                        <span class="block text-[10px] font-black text-rose-600 uppercase tracking-[0.15em]">Defence Category</span>
                        <div class="relative">
                            <select name="defence_type" class="w-full appearance-none bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                                <option value="proposal">📋 Proposal Defence</option>
                                <option value="internal">🏛️ Internal Defence</option>
                                <option value="external">🎓 External / Final Viva</option>
                            </select>
                            <svg class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                        <span class="block text-[10px] font-black text-rose-600 uppercase tracking-[0.15em]">Scheduling Authority</span>
                        <div class="relative">
                            <select name="defence_date_role" class="w-full appearance-none bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                                <option value="Admin">Administrator</option>
                                <option value="Program Coordinator">Program Coordinator</option>
                            </select>
                            <svg class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 6: DASHBOARD PANELS & FEATURES --}}
    <div x-show="step === 6"
         x-transition:enter="transition ease-out duration-400"
         x-transition:enter-start="opacity-0 translate-y-3"
         x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 flex items-center gap-4 bg-slate-50/50">
                <div class="w-12 h-12 rounded-2xl bg-green-50 border border-green-100 flex items-center justify-center text-green-600 shadow-sm">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Dashboard Panels & Features</h2>
                    <p class="text-xs font-medium text-slate-400 mt-0.5">Select which panels appear on the student's milestone dashboard.</p>
                </div>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach([
                        ['show_supervisor_assignment', 'Supervisor Assignment Panel', 'Show the panel to assign a supervisor to this thesis.', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                        ['show_internal_examiner_assignment', 'Internal Examiner Assignment', 'Allow admin to designate an internal examiner.', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                        ['show_external_examiner_assignment', 'External Examiner Assignment', 'Display the external examiner designation panel.', 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                        ['show_supervisor_details', 'Supervisor Profile Display', 'Show supervisor profiles to the student.', 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0M19.079 15.879A9 9 0 105.903 5.129'],
                        ['is_final_archival', 'Mark as Final Institutional Archive', 'This is the terminal milestone — enables full thesis archival fields.', 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
                        ['has_chat', 'Enable Direct Messaging (Comm-Link)', 'Enable the chat panel for milestone-level discussions.', 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                    ] as [$field, $label, $desc, $icon])
                    <label class="flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all group hover:border-green-300 hover:bg-green-50/30 bg-white border-slate-100">
                        <input type="checkbox" name="{{ $field }}" value="1" checked
                               class="w-4 h-4 mt-0.5 rounded text-green-600 border-slate-300 focus:ring-green-500/20 flex-shrink-0">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-green-50 border border-green-100 flex items-center justify-center text-green-600 flex-shrink-0 group-hover:bg-green-100 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
                            </div>
                            <div>
                                <span class="text-sm font-black text-slate-900 block">{{ $label }}</span>
                                <span class="text-[11px] text-slate-400 leading-relaxed">{{ $desc }}</span>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Navigation Controls --}}
    <div class="flex items-center justify-between py-2">
        <div class="flex items-center gap-3">
            <button type="button" @click="prev()"
                    x-show="step > 1"
                    class="flex items-center gap-2 px-6 py-3.5 rounded-2xl border-2 border-slate-200 text-sm font-bold text-slate-500 hover:bg-slate-50 hover:border-slate-300 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                Previous
            </button>
            <a x-show="step === 1" href="{{ route('admin.milestone-templates.index') }}"
               class="flex items-center gap-2 px-6 py-3.5 rounded-2xl border-2 border-slate-200 text-sm font-bold text-slate-400 hover:bg-slate-50 transition-all">
                Cancel
            </a>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" @click="next()"
                    x-show="step < totalSteps"
                    class="flex items-center gap-2 px-7 py-3.5 rounded-2xl bg-green-600 hover:bg-green-700 text-sm font-black text-white transition-all shadow-lg shadow-green-600/20 active:scale-95">
                Continue
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>

            <button type="submit"
                    x-show="step === totalSteps"
                    class="flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-green-600 hover:bg-green-700 text-sm font-black text-white transition-all shadow-xl shadow-green-600/20 active:scale-95">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                Finalize & Create Protocol
            </button>
        </div>
    </div>

    </form>
</div>
@endsection
