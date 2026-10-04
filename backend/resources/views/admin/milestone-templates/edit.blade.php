@extends('layouts.admin')

@section('header')
    Milestone Protocol Architect (Edit)
@endsection

@section('content')
<div class="space-y-8" x-data="{ 
    requiresSubmission: {{ old('requires_submission', $milestoneTemplate->requires_submission) ? 'true' : 'false' }},
    allowPlagiarism: {{ old('allow_plagiarism_report', $milestoneTemplate->allow_plagiarism_report) ? 'true' : 'false' }},
    requiresApproval: {{ old('requires_approval', $milestoneTemplate->requires_approval) ? 'true' : 'false' }},
    allowDefence: {{ old('allow_defence_date', $milestoneTemplate->allow_defence_date) ? 'true' : 'false' }}
}">

    {{-- Page Header --}}
    <div class="relative overflow-hidden rounded-[2.5rem] p-10 lg:p-12 bg-white border border-green-100 shadow-xl shadow-green-500/5">
        <div class="absolute top-0 right-0 w-[40rem] h-[40rem] bg-green-50/50 blur-[100px] rounded-full -mr-32 -mt-32 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-green-50/30 blur-[80px] rounded-full -ml-32 -mb-32 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            <div class="flex-1 space-y-4">
                <div class="inline-flex items-center gap-2.5 px-4 py-2 bg-green-50 border border-green-200 rounded-full text-green-700 text-[10px] font-black uppercase tracking-wider shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Modify Protocol
                </div>
                <div>
                    <h1 class="text-3xl lg:text-5xl font-black text-slate-900 tracking-tight leading-none mb-3">
                        Adjust <span class="text-green-600">{{ $milestoneTemplate->name }}</span>
                    </h1>
                    <p class="text-base text-slate-500 font-medium leading-relaxed max-w-xl">
                        Modify the core logic of this institutional milestone. Changes will affect future student workflows immediately.
                    </p>
                </div>
                <div class="flex items-center gap-4 flex-wrap">
                    <a href="{{ route('admin.milestone-templates.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-green-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        Back to Templates
                    </a>
                </div>
            </div>
            <div class="shrink-0 hidden lg:flex flex-col items-center gap-3 text-center">
                <div class="w-20 h-20 rounded-3xl bg-green-50 border border-green-100 flex items-center justify-center shadow-sm">
                    <svg class="w-9 h-9 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.milestone-templates.update', $milestoneTemplate) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        {{-- STEP 1: BASIC IDENTITY --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 flex items-center gap-4 bg-slate-50/50">
                <div class="w-12 h-12 rounded-2xl bg-green-50 border border-green-100 flex items-center justify-center text-green-600 shadow-sm">
                    <span class="text-sm font-black">1</span>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Basic Identity</h2>
                    <p class="text-xs font-medium text-slate-400 mt-0.5">Name, sequence order and program scope.</p>
                </div>
            </div>
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Protocol Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $milestoneTemplate->name) }}" required
                               class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Sequence Order <span class="text-rose-500">*</span></label>
                        <input type="number" name="order" value="{{ old('order', $milestoneTemplate->order) }}" min="1"
                               class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full bg-white border border-slate-200 rounded-2xl px-5 py-4 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all resize-none">{{ old('description', $milestoneTemplate->description) }}</textarea>
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.15em]">Program Scope</label>
                    <div class="relative">
                        <select name="program_id" class="w-full appearance-none bg-white border border-slate-200 rounded-2xl px-5 py-4 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all">
                            <option value="">🌐 Global Institutional Protocol (All Programs)</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ $milestoneTemplate->program_id == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 2: SUBMISSION & ARTIFACTS --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 flex items-center gap-4 bg-slate-50/50">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm">
                    <span class="text-sm font-black">2</span>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Submission & Artifacts</h2>
                    <p class="text-xs font-medium text-slate-400 mt-0.5">Documents required for submission.</p>
                </div>
            </div>
            <div class="p-8 space-y-6">
                <label class="flex items-center justify-between p-5 rounded-2xl border-2 cursor-pointer transition-all duration-300"
                       :class="requiresSubmission ? 'bg-blue-50 border-blue-200' : 'bg-white border-slate-100 hover:border-slate-200'">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all"
                             :class="requiresSubmission ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25' : 'bg-slate-100 text-slate-400'">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4"/></svg>
                        </div>
                        <div>
                            <span class="text-sm font-black text-slate-900 block">Student Must Submit Documents</span>
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
                        @php $subTypes = old('submission_type', $milestoneTemplate->submission_type ?? ['file']); @endphp
                        <span class="block text-[10px] font-black text-blue-600 uppercase tracking-[0.15em]">Required Artifact Types</span>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all bg-white hover:border-blue-300">
                                <input type="checkbox" name="submission_type[]" value="ppt" {{ in_array('ppt', $subTypes) ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600 border-slate-300">
                                <span class="text-sm font-bold text-slate-700">PPT / Presentation</span>
                            </label>
                            <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all bg-white hover:border-blue-300">
                                <input type="checkbox" name="submission_type[]" value="file" {{ in_array('file', $subTypes) ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600 border-slate-300">
                                <span class="text-sm font-bold text-slate-700">Manuscript</span>
                            </label>
                            <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all bg-white hover:border-blue-300">
                                <input type="checkbox" name="submission_type[]" value="publication" {{ in_array('publication', $subTypes) ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600 border-slate-300">
                                <span class="text-sm font-bold text-slate-700">Publication</span>
                            </label>
                        </div>
                    </div>

                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="submission_requires_approval" value="1" {{ old('submission_requires_approval', $milestoneTemplate->submission_requires_approval) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-blue-600 border-slate-300">
                            <div>
                                <span class="text-sm font-black text-slate-900 block">Require Pre-Submission Approval (Unlock Gate)</span>
                                <span class="text-xs text-slate-400">An authorized role must unlock submissions before the student can upload.</span>
                            </div>
                        </label>
                        <div class="pl-7 space-y-2">
                            @php $subApprovers = old('submission_approver_roles', $milestoneTemplate->submission_approver_roles ?? []); @endphp
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Roles authorized to unlock:</span>
                            <div class="grid grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach($roles as $role)
                                <label class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white border border-slate-200 hover:border-blue-300 cursor-pointer transition-all">
                                    <input type="checkbox" name="submission_approver_roles[]" value="{{ $role }}" {{ in_array($role, $subApprovers) ? 'checked' : '' }}
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

        {{-- STEP 3: PLAGIARISM PROTOCOL --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 flex items-center gap-4 bg-slate-50/50">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shadow-sm">
                    <span class="text-sm font-black">3</span>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Plagiarism Verification</h2>
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
                    @php $pRole = old('plagiarism_report_role', $milestoneTemplate->plagiarism_report_role ?? 'Admin'); @endphp
                    <span class="block text-[10px] font-black text-amber-600 uppercase tracking-[0.15em]">Primary Upload Authority</span>
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all bg-white hover:border-amber-300">
                            <input type="radio" name="plagiarism_report_role" value="Admin" {{ $pRole === 'Admin' ? 'checked' : '' }} class="w-4 h-4 text-amber-600 border-slate-300">
                            <span class="text-sm font-bold text-slate-700">Admin</span>
                        </label>
                        <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all bg-white hover:border-amber-300">
                            <input type="radio" name="plagiarism_report_role" value="Program Coordinator" {{ $pRole === 'Program Coordinator' ? 'checked' : '' }} class="w-4 h-4 text-amber-600 border-slate-300">
                            <span class="text-sm font-bold text-slate-700">Program Coordinator</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 4: INSTITUTIONAL SIGN-OFF --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 flex items-center gap-4 bg-slate-50/50">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm">
                    <span class="text-sm font-black">4</span>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Institutional Sign-off</h2>
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
                        <div class="flex items-center gap-4 pt-1">
                            <input type="number" name="approval_threshold" value="{{ old('approval_threshold', $milestoneTemplate->approval_threshold) }}" min="1"
                                   class="w-24 bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm font-black text-slate-900 text-center focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                            <span class="text-sm text-slate-400 font-medium">approvals required</span>
                        </div>
                    </div>

                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                        @php $approvers = old('required_approvers', $milestoneTemplate->required_approvers ?? []); @endphp
                        <span class="block text-[10px] font-black text-emerald-600 uppercase tracking-[0.15em]">Authorized Approver Roles</span>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach($roles as $role)
                            <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white border border-slate-200 hover:border-emerald-300 cursor-pointer transition-all">
                                <input type="checkbox" name="required_approvers[]" value="{{ $role }}" {{ in_array($role, $approvers) ? 'checked' : '' }}
                                       class="w-3.5 h-3.5 rounded text-emerald-600 border-slate-300">
                                <span class="text-xs font-bold text-slate-600">{{ $role }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 5: DEFENCE & EXAMINATION --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 flex items-center gap-4 bg-slate-50/50">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shadow-sm">
                    <span class="text-sm font-black">5</span>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Defence & Examination</h2>
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
                        <select name="defence_type" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm">
                            <option value="proposal" {{ $milestoneTemplate->defence_type == 'proposal' ? 'selected' : '' }}>Proposal Defence</option>
                            <option value="internal" {{ $milestoneTemplate->defence_type == 'internal' ? 'selected' : '' }}>Internal Defence</option>
                            <option value="external" {{ $milestoneTemplate->defence_type == 'external' ? 'selected' : '' }}>External / Final Viva</option>
                        </select>
                    </div>
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                        <span class="block text-[10px] font-black text-rose-600 uppercase tracking-[0.15em]">Scheduling Authority</span>
                        <select name="defence_date_role" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm">
                            <option value="Admin" {{ $milestoneTemplate->defence_date_role == 'Admin' ? 'selected' : '' }}>Administrator</option>
                            <option value="Program Coordinator" {{ $milestoneTemplate->defence_date_role == 'Program Coordinator' ? 'selected' : '' }}>Program Coordinator</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 6: DASHBOARD PANELS --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-50 flex items-center gap-4 bg-slate-50/50">
                <div class="w-12 h-12 rounded-2xl bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-500 shadow-sm">
                    <span class="text-sm font-black">6</span>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Dashboard Panels & Features</h2>
                </div>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach([
                        ['show_supervisor_assignment', 'Supervisor Assignment Panel'],
                        ['show_internal_examiner_assignment', 'Internal Examiner Assignment'],
                        ['show_external_examiner_assignment', 'External Examiner Assignment'],
                        ['show_supervisor_details', 'Supervisor Profile Display'],
                        ['is_final_archival', 'Mark as Final Institutional Archive'],
                        ['has_chat', 'Enable Direct Messaging'],
                    ] as [$field, $label])
                    <label class="flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all bg-white border-slate-100 hover:border-green-200">
                        <input type="checkbox" name="{{ $field }}" value="1" {{ $milestoneTemplate->$field ? 'checked' : '' }}
                               class="w-4 h-4 mt-0.5 rounded text-green-600 border-slate-300 focus:ring-green-500/20">
                        <span class="text-sm font-bold text-slate-800">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ACTIONS --}}
        <div class="mt-8 flex items-center justify-between pb-10">
            <button type="button" 
                    data-confirm="Are you sure you want to delete this milestone protocol? This action is irrecoverable and may affect associated student milestones."
                    data-confirm-title="Delete Milestone Protocol"
                    data-confirm-type="danger"
                    data-confirm-btn="Trash Protocol"
                    onclick="document.getElementById('del-form').submit();" 
                    class="text-sm font-black text-rose-500 hover:text-rose-600 px-4 py-2 hover:bg-rose-50 rounded-xl transition-colors">
                Trash Protocol
            </button>
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.milestone-templates.index') }}" class="px-6 py-3.5 rounded-2xl border-2 border-slate-200 text-sm font-bold text-slate-500 hover:bg-slate-50 transition-all">
                    Cancel
                </a>
                <button type="submit" class="px-8 py-3.5 rounded-2xl bg-green-600 hover:bg-green-700 text-sm font-black text-white transition-all shadow-lg shadow-green-600/20 active:scale-95">
                    Update Protocol Specification
                </button>
            </div>
        </div>
    </form>
    
    <form id="del-form" action="{{ route('admin.milestone-templates.destroy', $milestoneTemplate) }}" method="POST" class="hidden">
        @csrf 
        @method('DELETE')
    </form>
</div>
@endsection
