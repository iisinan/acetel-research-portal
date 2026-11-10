@extends('layouts.admin')

@section('content')
<div class="space-y-10 pb-10">
    @php
        $exportExcelBase = (isset($isCoordinator) && $isCoordinator)
            ? route('coordinator.milestone-templates.export-ongoing-excel')
            : route('admin.milestone-templates.export-ongoing-excel');

        $exportPdfBase = (isset($isCoordinator) && $isCoordinator)
            ? route('coordinator.milestone-templates.export-ongoing-pdf')
            : route('admin.milestone-templates.export-ongoing-pdf');
    @endphp

    <!-- Sophisticated Header with Download Ongoing Records Chooser -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6"
        x-data="{ 
            exportModalOpen: false, 
            exportDropdownOpen: false,
            selectedCohort: '',
            selectedProgram: '',
            selectedMilestone: '',
            getExcelUrl() {
                let params = new URLSearchParams();
                if (this.selectedCohort) params.append('cohort_id', this.selectedCohort);
                if (this.selectedProgram) params.append('program_id', this.selectedProgram);
                if (this.selectedMilestone) params.append('template_id', this.selectedMilestone);
                let qs = params.toString();
                return '{{ $exportExcelBase }}' + (qs ? '?' + qs : '');
            },
            getPdfUrl() {
                let params = new URLSearchParams();
                if (this.selectedCohort) params.append('cohort_id', this.selectedCohort);
                if (this.selectedProgram) params.append('program_id', this.selectedProgram);
                if (this.selectedMilestone) params.append('template_id', this.selectedMilestone);
                let qs = params.toString();
                return '{{ $exportPdfBase }}' + (qs ? '?' + qs : '');
            }
        }">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <div class="p-1.5 rounded-lg bg-acetel-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Milestone Templates</span>
            </div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Milestones</h1>
            <p class="mt-2 text-sm font-medium text-slate-500">View the static milestones and approval requirements for student progress.</p>
        </div>

        <!-- Download Ongoing Records Action Button -->
        <div class="relative flex items-center">
            <div class="inline-flex items-center rounded-2xl shadow-sm border border-slate-200 bg-white hover:border-slate-300 transition-all overflow-visible relative">
                <button type="button" @click="exportModalOpen = true"
                    class="inline-flex items-center gap-2.5 px-4 sm:px-5 py-2.5 sm:py-3 text-xs font-black uppercase tracking-wider text-slate-800 hover:text-emerald-700 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Download Ongoing Records</span>
                </button>
                <button type="button" @click="exportDropdownOpen = !exportDropdownOpen"
                    class="px-2.5 py-2.5 sm:py-3 border-l border-slate-200 text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors"
                    title="Quick Download Options">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Quick Download Dropdown -->
                <div x-show="exportDropdownOpen" @click.outside="exportDropdownOpen = false" x-cloak x-transition
                    class="absolute right-0 top-full mt-2 w-72 bg-white border border-slate-100 rounded-2xl shadow-2xl shadow-slate-300/50 z-50 p-2 text-left">
                    <div class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 mb-1">
                        Instant Download (All Ongoing)
                    </div>
                    <a :href="getExcelUrl()" @click="exportDropdownOpen = false"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 text-xs font-bold transition-colors group">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-extrabold text-slate-800">Download Excel (.csv)</div>
                            <div class="text-[10px] font-normal text-slate-400">Complete raw table with all columns</div>
                        </div>
                    </a>
                    <a :href="getPdfUrl()" @click="exportDropdownOpen = false"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-rose-50 text-slate-700 hover:text-rose-800 text-xs font-bold transition-colors group">
                        <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-extrabold text-slate-800">Download PDF (.pdf)</div>
                            <div class="text-[10px] font-normal text-slate-400">Formatted report by milestone stage</div>
                        </div>
                    </a>
                    <div class="border-t border-slate-100 my-1"></div>
                    <button type="button" @click="exportDropdownOpen = false; exportModalOpen = true"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-left text-[11px] font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <span>Filter & Export Options</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- Modal Chooser: Excel or PDF with Optional Filters -->
            <template x-teleport="body">
                <div x-show="exportModalOpen" x-cloak x-transition.opacity 
                    class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                    @keydown.escape.window="exportModalOpen = false">
                    <div @click.outside="exportModalOpen = false" 
                        class="bg-white rounded-[2rem] shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col border border-slate-100">
                        
                        {{-- Modal Header --}}
                        <div class="p-6 pb-4 border-b border-slate-100 flex items-start justify-between">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Export Student Records</span>
                                    <h3 class="text-xl font-black text-slate-900 leading-tight mt-0.5">Download Ongoing Milestones</h3>
                                    <p class="text-xs text-slate-500 mt-1">Select your preferred export format to download records of all candidates currently active on milestones.</p>
                                </div>
                            </div>
                            <button type="button" @click="exportModalOpen = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Filters Section (Optional) --}}
                        <div class="p-6 bg-slate-50/70 border-b border-slate-100">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                <span>Filter Records (Optional — Default is All)</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-{{ (!isset($isCoordinator) || !$isCoordinator) ? '3' : '2' }} gap-3">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-600 uppercase tracking-wider mb-1">Cohort</label>
                                    <select x-model="selectedCohort" class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:ring-emerald-500 focus:border-emerald-500">
                                        <option value="">All Cohorts</option>
                                        @foreach($cohorts as $cohort)
                                            <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if(!isset($isCoordinator) || !$isCoordinator)
                                <div>
                                    <label class="block text-[10px] font-black text-slate-600 uppercase tracking-wider mb-1">Program</label>
                                    <select x-model="selectedProgram" class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:ring-emerald-500 focus:border-emerald-500">
                                        <option value="">All Programs</option>
                                        @foreach($programs ?? [] as $prog)
                                            <option value="{{ $prog->id }}">{{ $prog->code }} - {{ $prog->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                <div>
                                    <label class="block text-[10px] font-black text-slate-600 uppercase tracking-wider mb-1">Milestone Stage</label>
                                    <select x-model="selectedMilestone" class="w-full text-xs font-semibold bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-800 focus:ring-emerald-500 focus:border-emerald-500">
                                        <option value="">All Ongoing Stages</option>
                                        @foreach($templates as $tpl)
                                            <option value="{{ $tpl->id }}">M{{ $tpl->order }}: {{ $tpl->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Format Chooser Cards --}}
                        <div class="p-6 space-y-4">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                Choose Export Format
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Card 1: Excel --}}
                                <div class="p-5 rounded-2xl border-2 border-emerald-200 bg-emerald-50/30 hover:bg-emerald-50/60 transition-all flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="p-2 rounded-xl bg-emerald-100 text-emerald-800">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Excel / CSV
                                            </span>
                                        </div>
                                        <h4 class="text-base font-black text-slate-900 mb-1">Excel Spreadsheet</h4>
                                        <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                                            Export full tabular records containing matric numbers, names, emails, thesis titles, ongoing milestones, supervisors, defence schedules, and status.
                                        </p>
                                    </div>
                                    <a :href="getExcelUrl()" @click="exportModalOpen = false"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-sm shadow-emerald-200 transition-all">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <span>Download as Excel</span>
                                    </a>
                                </div>

                                {{-- Card 2: PDF --}}
                                <div class="p-5 rounded-2xl border-2 border-rose-200 bg-rose-50/30 hover:bg-rose-50/60 transition-all flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="p-2 rounded-xl bg-rose-100 text-rose-800">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">
                                                Official PDF
                                            </span>
                                        </div>
                                        <h4 class="text-base font-black text-slate-900 mb-1">PDF Master Report</h4>
                                        <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                                            Formatted landscape PDF with institutional ACETEL headers, summary breakdown statistics, and candidates grouped neatly by each ongoing milestone.
                                        </p>
                                    </div>
                                    <a :href="getPdfUrl()" @click="exportModalOpen = false"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-sm shadow-rose-200 transition-all">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <span>Download as PDF</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                            <button type="button" @click="exportModalOpen = false"
                                class="px-5 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>


    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="w-20 px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 text-center">Order</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Milestone Name</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Program</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Requirements</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 text-right">Schedule</th>
                    </tr>
                </thead>
                
                    @forelse($templates as $template)
                    <tbody x-data="{ 
                        expanded: false, 
                        showStudents: false, 
                        selectAll: false, 
                        selected: [], 
                        search: '', 
                        cohortFilter: '', 
                        trackFilter: 'all', 
                        degreeFilter: 'all',
                        rescheduleDayOpen: false, 
                        slotModalOpen: false, 
                        slotStudentId: null, 
                        slotStudentName: '', 
                        slotAction: 'schedule', 
                        slotDate: '', 
                        slotTime: '', 
                        slotLink: '', 
                        slotTrack: '',
                        slotFormUrl: '',
                        messageModalOpen: false,
                        messageTarget: 'selected',
                        singleStudentId: null,
                        singleStudentName: '',
                        singleStudentMatric: '',
                        messageSubject: {{ json_encode('Notice regarding ' . $template->name . ' Milestone') }},
                        messageBody: '',
                        messageChannels: ['inbox', 'email'],
                        messagePriority: 'normal',
                        isSubmittingMessage: false,
                        openMessageModal(target = 'selected') {
                            this.messageTarget = target;
                            if (target === 'selected' && this.selected.length === 0) {
                                this.messageTarget = 'all';
                            }
                            this.singleStudentId = null;
                            this.singleStudentName = '';
                            this.singleStudentMatric = '';
                            this.isSubmittingMessage = false;
                            this.messageModalOpen = true;
                        },
                        openSingleMessageModal(id, name, matric) {
                            this.messageTarget = 'single';
                            this.singleStudentId = id;
                            this.singleStudentName = name;
                            this.singleStudentMatric = matric;
                            this.isSubmittingMessage = false;
                            this.messageModalOpen = true;
                        },
                        assignModalOpen: false,
                        assignStudentName: '',
                        assignFormUrl: '',
                        assignProfileIds: [],
                        assignInternalExaminerProfileId: '',
                        openAssignModal(name, formUrl, profileIds, internalExaminerProfileId = '') {
                            this.assignStudentName = name;
                            this.assignFormUrl = formUrl;
                            this.assignProfileIds = (profileIds || []).map(Number);
                            this.assignInternalExaminerProfileId = internalExaminerProfileId ? String(internalExaminerProfileId) : '';
                            this.assignModalOpen = true;
                        }
                    }" class="divide-y divide-slate-50 border-t border-slate-50">
                    <tr class="hover:bg-slate-50/30 transition-colors group cursor-pointer" data-id="{{ $template->id }}" @click="if(!$event.target.closest('button') && !$event.target.closest('a') && !$event.target.closest('input') && !$event.target.closest('form')) expanded = !expanded">
                        <td class="px-10 py-7 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <span class="text-[10px] font-black text-slate-400 tabular-nums uppercase">{{ $template->order }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            <div class="max-w-xs">
                                <p class="text-base font-black text-slate-900 leading-tight group-hover:text-acetel-600 transition-colors">{{ $template->name }}</p>
                                <p class="text-[10px] font-medium text-slate-400 mt-1 line-clamp-1">{{ $template->description }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            @if($template->program_id)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest bg-acetel-50 text-acetel-600 border border-acetel-100">
                                    {{ $template->program->code }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest bg-slate-900 text-white border border-transparent">
                                    All Programs
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-7">
                            <div class="flex flex-wrap gap-2">
                                @if($template->requires_submission)
                                    <span class="px-2 py-1 rounded-lg bg-indigo-50 text-[9px] font-black text-indigo-600 uppercase tracking-widest border border-indigo-100">Submission</span>
                                @endif
                                @if($template->requires_approval)
                                    <span class="px-2 py-1 rounded-lg bg-emerald-50 text-[9px] font-black text-emerald-600 uppercase tracking-widest border border-emerald-100">Approval</span>
                                @endif
                                @if($template->allow_defence_date)
                                     <span class="px-2 py-1 rounded-lg bg-rose-50 text-[9px] font-black text-rose-600 uppercase tracking-widest border border-rose-100">Defence</span>
                                @endif
                                @if($template->is_final_archival)
                                     <span class="px-2 py-1 rounded-lg bg-amber-50 text-[9px] font-black text-amber-600 uppercase tracking-widest border border-amber-100">Final Archival</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-7 text-right">
                            @if($template->allow_defence_date)
                                <div x-data="{ 
                                    showDatePicker: false, 
                                    localDate: '{{ $template->global_defence_date ? $template->global_defence_date->format('Y-m-d') : '' }}',
                                    isDateExpired: {{ ($template->global_defence_date && \Carbon\Carbon::parse($template->global_defence_date)->isPast()) ? 'true' : 'false' }}
                                }" class="relative inline-block text-left">
                                    
                                    @if($template->global_defence_date && !\Carbon\Carbon::parse($template->global_defence_date)->isPast())
                                        <button @click.stop="showDatePicker = !showDatePicker" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            {{ $template->global_defence_date->format('M d, Y') }}
                                        </button>
                                    @else
                                        <button @click.stop="showDatePicker = !showDatePicker" class="inline-flex items-center gap-2 px-4 py-2 {{ ($template->global_defence_date && \Carbon\Carbon::parse($template->global_defence_date)->isPast()) ? 'bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 shadow-sm shadow-red-100' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 shadow-sm' }} rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            {{ ($template->global_defence_date && \Carbon\Carbon::parse($template->global_defence_date)->isPast()) ? 'Expired / Set New Date' : 'Set Date' }}
                                        </button>
                                    @endif

                                    <!-- Dropdown Date Picker -->
                                    <div x-show="showDatePicker" @click.outside="showDatePicker = false" @click.stop class="absolute right-0 mt-2 p-4 bg-white border border-slate-100 rounded-2xl shadow-xl shadow-slate-200/50 z-50 w-64 max-w-[85vw]" style="display: none;">
                                        <form action="{{ route('admin.milestone-templates.set-date', $template->id) }}" method="POST" class="flex flex-col gap-3">
                                            @csrf
                                            <input type="date" name="global_defence_date" x-model="localDate" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500" required>
                                            <button type="submit" class="w-full px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Save Schedule</button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr x-cloak x-show="expanded" x-transition class="bg-slate-50/30">
                        <td colspan="5" class="p-6">
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 cursor-default" @click.stop>
                                
                                @php
                                    $scheduledMilestones = $template->studentMilestones
                                        ->filter(fn($m) => !empty($m->defence_date) && $m->status !== 'approved')
                                        ->sortBy(fn($m) => \Carbon\Carbon::parse($m->defence_date)->format('Y-m-d') . ' ' . ($m->defence_time ?? ''))
                                        ->values();
                                    $scheduledCount = $scheduledMilestones->count();
                                    $existingLink = $scheduledMilestones->first(fn($m) => !empty($m->meeting_link))?->meeting_link;
                                    $existingTimeRaw = $scheduledMilestones->first(fn($m) => !empty($m->defence_time))?->defence_time;
                                    $existingTime = $existingTimeRaw ? \Carbon\Carbon::parse($existingTimeRaw)->format('H:i') : null;
                                    $lastScheduledDate = $scheduledCount > 0 ? \Carbon\Carbon::parse($scheduledMilestones->last()->defence_date)->format('Y-m-d') : null;

                                    $schedCyberCount = $scheduledMilestones->where('discipline_group', 'cybersecurity')->count();
                                    $schedAiCount = $scheduledMilestones->where('discipline_group', 'artificial_intelligence')->count();
                                    $schedMisCount = $scheduledMilestones->where('discipline_group', 'management_information_systems')->count();
                                    $trackZoomLinks = $template->metadata['track_zoom_links'] ?? [];

                                    if (empty($trackZoomLinks['cybersecurity'])) {
                                        $trackZoomLinks['cybersecurity'] = $scheduledMilestones->first(fn($m) => $m->discipline_group === 'cybersecurity' && !empty($m->meeting_link))?->meeting_link;
                                    }
                                    if (empty($trackZoomLinks['artificial_intelligence'])) {
                                        $trackZoomLinks['artificial_intelligence'] = $scheduledMilestones->first(fn($m) => $m->discipline_group === 'artificial_intelligence' && !empty($m->meeting_link))?->meeting_link;
                                    }
                                    if (empty($trackZoomLinks['management_information_systems'])) {
                                        $trackZoomLinks['management_information_systems'] = $scheduledMilestones->first(fn($m) => $m->discipline_group === 'management_information_systems' && !empty($m->meeting_link))?->meeting_link;
                                    }

                                    $isSeminar = $template->slug === 'seminar_as_a_course';
                                    if ($isSeminar) {
                                        $cyberMilestones = $template->studentMilestones->filter(fn($m) => $m->discipline_group === 'cybersecurity');
                                        $aiMilestones = $template->studentMilestones->filter(fn($m) => $m->discipline_group === 'artificial_intelligence');
                                        $misMilestones = $template->studentMilestones->filter(fn($m) => $m->discipline_group === 'management_information_systems');

                                        $cyberScheduled = $cyberMilestones->filter(fn($m) => !empty($m->defence_date))->count();
                                        $aiScheduled = $aiMilestones->filter(fn($m) => !empty($m->defence_date))->count();
                                        $misScheduled = $misMilestones->filter(fn($m) => !empty($m->defence_date))->count();

                                        $groupMeta = $template->metadata['group_examiners'] ?? [];
                                        $cyberUserIds = $groupMeta['cybersecurity']['user_ids'] ?? [];
                                        $aiUserIds = $groupMeta['artificial_intelligence']['user_ids'] ?? [];
                                        $misUserIds = $groupMeta['management_information_systems']['user_ids'] ?? [];

                                        $cyberExaminers = !empty($cyberUserIds) ? collect($cyberUserIds)->map(fn($id) => $examinerUsersMap[$id] ?? null)->filter() : collect();
                                        $aiExaminers = !empty($aiUserIds) ? collect($aiUserIds)->map(fn($id) => $examinerUsersMap[$id] ?? null)->filter() : collect();
                                        $misExaminers = !empty($misUserIds) ? collect($misUserIds)->map(fn($id) => $examinerUsersMap[$id] ?? null)->filter() : collect();

                                        $cyberLink = $cyberMilestones->first(fn($m) => !empty($m->meeting_link))?->meeting_link ?? '';
                                        $aiLink = $aiMilestones->first(fn($m) => !empty($m->meeting_link))?->meeting_link ?? '';
                                        $misLink = $misMilestones->first(fn($m) => !empty($m->meeting_link))?->meeting_link ?? '';

                                        $cyberProfileIds = $groupMeta['cybersecurity']['profile_ids'] ?? [];
                                        $aiProfileIds = $groupMeta['artificial_intelligence']['profile_ids'] ?? [];
                                        $misProfileIds = $groupMeta['management_information_systems']['profile_ids'] ?? [];
                                    }

                                    $allMilestones = $template->studentMilestones;
                                    $phdMilestones = $allMilestones->filter(function($m) {
                                        $raw = $m->thesis?->student?->level?->name 
                                            ?? $m->thesis?->student?->program?->degree_type 
                                            ?? ($m->thesis?->student?->program?->name ?? '');
                                        return str_contains(strtolower($raw), 'phd') || str_contains(strtolower($raw), 'doctor');
                                    });
                                    $mscMilestones = $allMilestones->reject(function($m) {
                                        $raw = $m->thesis?->student?->level?->name 
                                            ?? $m->thesis?->student?->program?->degree_type 
                                            ?? ($m->thesis?->student?->program?->name ?? '');
                                        return str_contains(strtolower($raw), 'phd') || str_contains(strtolower($raw), 'doctor');
                                    });
                                    $phdCount = $phdMilestones->count();
                                    $mscCount = $mscMilestones->count();
                                    $showDegreeFilter = ($phdCount > 0 && $mscCount > 0) || $template->slug === 'internal_defence';
                                @endphp

                                @if($isSeminar)
                                    <div class="mb-6 bg-slate-50/80 p-3.5 rounded-2xl border border-slate-200/80">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 pb-2.5 mb-2.5 border-b border-slate-200/60">
                                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                                Seminar Discipline Tracks (Parallel Presentation & Dedicated Examiners)
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-400">Click a track to filter students, assign dedicated examiners, or schedule</span>
                                        </div>
                                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5">
                                            <button type="button" @click="trackFilter = 'all'; selected = []"
                                                :class="trackFilter === 'all' ? 'bg-slate-900 text-white shadow-md font-black ring-2 ring-slate-900/20' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-200/80 font-bold'"
                                                class="flex flex-col p-3 rounded-xl text-left transition-all cursor-pointer">
                                                <div class="flex items-center justify-between mb-0.5">
                                                    <span class="text-xs">All Tracks</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="trackFilter === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700'">
                                                        {{ $template->studentMilestones->count() }}
                                                    </span>
                                                </div>
                                                <span class="text-[10px] opacity-75 font-normal">All candidates combined</span>
                                            </button>

                                            <button type="button" @click="trackFilter = 'cybersecurity'; selected = []"
                                                :class="trackFilter === 'cybersecurity' ? 'bg-cyan-700 text-white shadow-md font-black ring-2 ring-cyan-500/30' : 'bg-white hover:bg-cyan-50/50 text-slate-700 border border-slate-200/80 font-bold'"
                                                class="flex flex-col p-3 rounded-xl text-left transition-all cursor-pointer">
                                                <div class="flex items-center justify-between mb-0.5">
                                                    <span class="text-xs flex items-center gap-1">🛡️ Cybersecurity</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="trackFilter === 'cybersecurity' ? 'bg-white/20 text-white' : 'bg-cyan-50 text-cyan-800'">
                                                        {{ $cyberMilestones->count() }}
                                                    </span>
                                                </div>
                                                <span class="text-[10px] opacity-75 font-normal">{{ $cyberScheduled }} scheduled</span>
                                            </button>

                                            <button type="button" @click="trackFilter = 'artificial_intelligence'; selected = []"
                                                :class="trackFilter === 'artificial_intelligence' ? 'bg-violet-700 text-white shadow-md font-black ring-2 ring-violet-500/30' : 'bg-white hover:bg-violet-50/50 text-slate-700 border border-slate-200/80 font-bold'"
                                                class="flex flex-col p-3 rounded-xl text-left transition-all cursor-pointer">
                                                <div class="flex items-center justify-between mb-0.5">
                                                    <span class="text-xs flex items-center gap-1">🤖 AI</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="trackFilter === 'artificial_intelligence' ? 'bg-white/20 text-white' : 'bg-violet-50 text-violet-800'">
                                                        {{ $aiMilestones->count() }}
                                                    </span>
                                                </div>
                                                <span class="text-[10px] opacity-75 font-normal">{{ $aiScheduled }} scheduled</span>
                                            </button>

                                            <button type="button" @click="trackFilter = 'management_information_systems'; selected = []"
                                                :class="trackFilter === 'management_information_systems' ? 'bg-amber-700 text-white shadow-md font-black ring-2 ring-amber-500/30' : 'bg-white hover:bg-amber-50/50 text-slate-700 border border-slate-200/80 font-bold'"
                                                class="flex flex-col p-3 rounded-xl text-left transition-all cursor-pointer">
                                                <div class="flex items-center justify-between mb-0.5">
                                                    <span class="text-xs flex items-center gap-1">📊 MIS</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="trackFilter === 'management_information_systems' ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-800'">
                                                        {{ $misMilestones->count() }}
                                                    </span>
                                                </div>
                                                <span class="text-[10px] opacity-75 font-normal">{{ $misScheduled }} scheduled</span>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                                
                                {{-- Summary Cards --}}
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 sm:gap-4 mb-6">
                                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Students</p>
                                        <p class="text-2xl font-black text-slate-900">{{ $template->studentMilestones->count() }}</p>
                                    </div>
                                    <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100">
                                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">Scheduled</p>
                                        <p class="text-2xl font-black text-emerald-700">{{ $template->studentMilestones->filter(fn($m) => !empty($m->defence_date))->count() }}</p>
                                    </div>
                                    <div class="bg-amber-50 rounded-xl p-4 border border-amber-100">
                                        <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-1">Not Scheduled</p>
                                        <p class="text-2xl font-black text-amber-700">{{ $template->studentMilestones->filter(fn($m) => empty($m->defence_date))->count() }}</p>
                                    </div>
                                    @if(in_array('Supervisor', $template->required_approvers ?? []))
                                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                                        <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-1">Approved by Supervisor</p>
                                        <p class="text-2xl font-black text-blue-700">{{ $template->studentMilestones->filter(fn($m) => $m->is_supervisor_approved || ($m->submissions->last() && $m->submissions->last()->feedback && $m->submissions->last()->feedback->decision === 'approved'))->count() }}</p>
                                    </div>
                                    <div class="bg-rose-50 rounded-xl p-4 border border-rose-100">
                                        <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest mb-1">Pending Supervisor</p>
                                        <p class="text-2xl font-black text-rose-700">{{ $template->studentMilestones->filter(fn($m) => !$m->is_supervisor_approved && !($m->submissions->last() && $m->submissions->last()->feedback && $m->submissions->last()->feedback->decision === 'approved'))->count() }}</p>
                                    </div>
                                    @endif
                                </div>

                                {{-- Actions Row --}}
                                <div class="flex flex-wrap items-center gap-3 mb-6">
                                    <a href="{{ isset($isCoordinator) && $isCoordinator ? route('coordinator.milestone-templates.export-students', $template->id) : route('admin.milestone-templates.export-students', $template->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Export CSV
                                    </a>
                                    @if($template->studentMilestones->count() > 0)
                                    <button type="button" 
                                        @click="openMessageModal('all')"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-50 hover:bg-brand-100 text-brand-700 border border-brand-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-2xs cursor-pointer"
                                        title="Send an inbox message or email to students on this milestone">
                                        <svg class="w-3.5 h-3.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Message Students</span>
                                    </button>
                                    @endif
                                    @if($template->slug !== 'supervisors_assigned')
                                    <a href="{{ isset($isCoordinator) && $isCoordinator ? route('coordinator.milestone-templates.export-scheduled-scores', $template->id) : route('admin.milestone-templates.export-scheduled-scores', $template->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                        SCHEDULED SCORES
                                    </a>
                                    @endif
                                    @if($template->slug === 'seminar_as_a_course' && (!isset($isCoordinator) || !$isCoordinator))
                                    <a href="{{ route('admin.milestone-templates.export-examiner-attendance', $template->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Examiner Attendance
                                    </a>
                                    @endif

                                    @if($template->slug !== 'supervisors_assigned')
                                        @php
                                            $basePdfRoute = isset($isCoordinator) && $isCoordinator 
                                                ? route('coordinator.milestone-templates.export-scheduled-pdf', $template->id) 
                                                : route('admin.milestone-templates.export-scheduled-pdf', $template->id);
                                        @endphp
                                        @if($template->slug === 'seminar_as_a_course')
                                            <div x-data="{ pdfMenu: false }" class="relative inline-block" @click.outside="pdfMenu = false">
                                                <div class="inline-flex rounded-xl shadow-xs">
                                                    <a href="{{ $basePdfRoute }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-l-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        </svg>
                                                        <span>SCHEDULED PDF</span>
                                                    </a>
                                                    <button type="button" @click="pdfMenu = !pdfMenu" class="px-2 py-2.5 bg-rose-100 hover:bg-rose-200 text-rose-800 border-y border-r border-rose-200 rounded-r-xl transition-all" title="Export by Group">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                                    </button>
                                                </div>
                                                <div x-show="pdfMenu" x-cloak class="absolute left-0 mt-1 w-56 bg-white border border-slate-200 rounded-2xl shadow-xl py-1.5 z-50 text-xs">
                                                    <div class="px-3 py-1.5 text-[9px] font-black uppercase tracking-wider text-slate-400">Download Schedule PDF</div>
                                                    <a href="{{ $basePdfRoute }}" class="flex items-center justify-between px-3 py-2 hover:bg-rose-50 text-slate-700 hover:text-rose-700 font-bold">
                                                        <span>📄 Master PDF (All Groups)</span>
                                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-mono">{{ $scheduledCount }}</span>
                                                    </a>
                                                    <a href="{{ $basePdfRoute }}?discipline_group=cybersecurity" class="flex items-center justify-between px-3 py-2 hover:bg-cyan-50 text-slate-700 hover:text-cyan-800 font-bold">
                                                        <span>🛡️ Cybersecurity PDF</span>
                                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-cyan-50 text-cyan-800 font-mono">{{ $schedCyberCount }}</span>
                                                    </a>
                                                    <a href="{{ $basePdfRoute }}?discipline_group=artificial_intelligence" class="flex items-center justify-between px-3 py-2 hover:bg-violet-50 text-slate-700 hover:text-violet-800 font-bold">
                                                        <span>🤖 AI PDF</span>
                                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-violet-50 text-violet-800 font-mono">{{ $schedAiCount }}</span>
                                                    </a>
                                                    <a href="{{ $basePdfRoute }}?discipline_group=management_information_systems" class="flex items-center justify-between px-3 py-2 hover:bg-amber-50 text-slate-700 hover:text-amber-800 font-bold">
                                                        <span>📊 MIS PDF</span>
                                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 font-mono">{{ $schedMisCount }}</span>
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <a href="{{ $basePdfRoute }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                                SCHEDULED PDF
                                            </a>
                                        @endif
                                    @endif

                                    @if($template->studentMilestones->count() > 0)
                                        <button @click="showStudents = !showStudents; if(showStudents) $nextTick(() => $refs.searchInput?.focus())" 
                                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                                            :class="showStudents ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            <span x-text="showStudents ? 'Hide Students' : 'View Student Details'"></span>
                                        </button>
                                    @endif


                                    @if($scheduledCount > 0)
                                        @if(!isset($isCoordinator) || !$isCoordinator)
                                            <button type="button" @click="showStudents = true; $nextTick(() => $refs.scheduleForm?.scrollIntoView({ behavior: 'smooth', block: 'center' }))"
                                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                                <span>Add Students</span>
                                            </button>

                                            <div x-data="{ 
                                                editOpen: false,
                                                editTrackFilter: 'all',
                                                editSearch: '',
                                                batchLink: '',
                                                batchOnlyEmpty: false,
                                                batchDate: '',
                                                batchTime: '',
                                                toastMessage: '',
                                                showToast: false,
                                                swapSourceId: null,
                                                swapSourceName: '',
                                                swapSourceDate: '',
                                                swapSourceTime: '',
                                                swapSourceTrack: '',

                                                notify(msg) {
                                                    this.toastMessage = msg;
                                                    this.showToast = true;
                                                    setTimeout(() => { this.showToast = false; }, 4000);
                                                },

                                                startSwap(id, name, date, time, track) {
                                                    this.swapSourceId = id;
                                                    this.swapSourceName = name;
                                                    this.swapSourceDate = date;
                                                    this.swapSourceTime = time;
                                                    this.swapSourceTrack = track;
                                                    this.notify('Selected ' + name + ' (' + date + ' ' + time + ') — Click [⇄ Swap Here] on any candidate below.');
                                                },

                                                cancelSwap() {
                                                    this.swapSourceId = null;
                                                    this.swapSourceName = '';
                                                    this.swapSourceDate = '';
                                                    this.swapSourceTime = '';
                                                    this.swapSourceTrack = '';
                                                },

                                                executeSwap(targetId, targetName) {
                                                    if (!this.swapSourceId || this.swapSourceId == targetId) return;

                                                    const sourceRow = document.getElementById('sched-row-' + this.swapSourceId);
                                                    const targetRow = document.getElementById('sched-row-' + targetId);

                                                    if (!sourceRow || !targetRow) return;

                                                    const sourceDateInput = sourceRow.querySelector('input[data-field=date]');
                                                    const sourceTimeInput = sourceRow.querySelector('input[data-field=time]');
                                                    const targetDateInput = targetRow.querySelector('input[data-field=date]');
                                                    const targetTimeInput = targetRow.querySelector('input[data-field=time]');

                                                    if (sourceDateInput && targetDateInput && sourceTimeInput && targetTimeInput) {
                                                        const tempDate = sourceDateInput.value;
                                                        const tempTime = sourceTimeInput.value;

                                                        sourceDateInput.value = targetDateInput.value;
                                                        sourceTimeInput.value = targetTimeInput.value;

                                                        targetDateInput.value = tempDate;
                                                        targetTimeInput.value = tempTime;

                                                        sourceRow.classList.add('bg-emerald-50', 'ring-2', 'ring-emerald-400');
                                                        targetRow.classList.add('bg-emerald-50', 'ring-2', 'ring-emerald-400');
                                                        setTimeout(() => {
                                                            sourceRow.classList.remove('bg-emerald-50', 'ring-2', 'ring-emerald-400');
                                                            targetRow.classList.remove('bg-emerald-50', 'ring-2', 'ring-emerald-400');
                                                        }, 2500);

                                                        this.notify('Swapped slots between ' + this.swapSourceName + ' and ' + targetName + '!');
                                                    }

                                                    this.cancelSwap();
                                                },

                                                applyBatchMeetingLink() {
                                                    if (!this.batchLink) {
                                                        alert('Please enter or select a Zoom meeting link to apply.');
                                                        return;
                                                    }
                                                    let count = 0;
                                                    const rows = document.querySelectorAll('tr[data-sched-id]');
                                                    rows.forEach(row => {
                                                        const track = row.getAttribute('data-sched-track');
                                                        const name = row.getAttribute('data-sched-name') || '';
                                                        const matric = row.getAttribute('data-sched-matric') || '';
                                                        const matchesTrack = (this.editTrackFilter === 'all' || this.editTrackFilter === track);
                                                        const matchesSearch = (!this.editSearch || name.toLowerCase().includes(this.editSearch.toLowerCase()) || matric.toLowerCase().includes(this.editSearch.toLowerCase()));

                                                        if (matchesTrack && matchesSearch) {
                                                            const linkInput = row.querySelector('input[data-field=link]');
                                                            if (linkInput) {
                                                                if (!this.batchOnlyEmpty || !linkInput.value.trim()) {
                                                                    linkInput.value = this.batchLink;
                                                                    count++;
                                                                }
                                                            }
                                                        }
                                                    });
                                                    const groupLabel = this.editTrackFilter === 'cybersecurity' ? 'Cybersecurity' : (this.editTrackFilter === 'artificial_intelligence' ? 'AI' : (this.editTrackFilter === 'management_information_systems' ? 'MIS' : 'all groups'));
                                                    this.notify('Updated Zoom link for ' + count + ' student(s) in ' + groupLabel + '.');
                                                },

                                                applyBatchDate() {
                                                    if (!this.batchDate) {
                                                        alert('Please select a date to apply.');
                                                        return;
                                                    }
                                                    let count = 0;
                                                    const rows = document.querySelectorAll('tr[data-sched-id]');
                                                    rows.forEach(row => {
                                                        const track = row.getAttribute('data-sched-track');
                                                        const name = row.getAttribute('data-sched-name') || '';
                                                        const matric = row.getAttribute('data-sched-matric') || '';
                                                        const matchesTrack = (this.editTrackFilter === 'all' || this.editTrackFilter === track);
                                                        const matchesSearch = (!this.editSearch || name.toLowerCase().includes(this.editSearch.toLowerCase()) || matric.toLowerCase().includes(this.editSearch.toLowerCase()));

                                                        if (matchesTrack && matchesSearch) {
                                                            const dateInput = row.querySelector('input[data-field=date]');
                                                            if (dateInput) {
                                                                dateInput.value = this.batchDate;
                                                                count++;
                                                            }
                                                        }
                                                    });
                                                    this.notify('Applied date (' + this.batchDate + ') to ' + count + ' student(s).');
                                                },

                                                applyBatchTime(staggerMinutes = 0) {
                                                    if (!this.batchTime) {
                                                        alert('Please select a start time.');
                                                        return;
                                                    }
                                                    let count = 0;
                                                    const rows = document.querySelectorAll('tr[data-sched-id]');
                                                    let [startH, startM] = this.batchTime.split(':').map(Number);

                                                    rows.forEach(row => {
                                                        const track = row.getAttribute('data-sched-track');
                                                        const name = row.getAttribute('data-sched-name') || '';
                                                        const matric = row.getAttribute('data-sched-matric') || '';
                                                        const matchesTrack = (this.editTrackFilter === 'all' || this.editTrackFilter === track);
                                                        const matchesSearch = (!this.editSearch || name.toLowerCase().includes(this.editSearch.toLowerCase()) || matric.toLowerCase().includes(this.editSearch.toLowerCase()));

                                                        if (matchesTrack && matchesSearch) {
                                                            const timeInput = row.querySelector('input[data-field=time]');
                                                            if (timeInput) {
                                                                if (staggerMinutes > 0) {
                                                                    let totalM = (startH * 60 + startM) + (count * staggerMinutes);
                                                                    let h = Math.floor(totalM / 60) % 24;
                                                                    let m = totalM % 60;
                                                                    timeInput.value = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
                                                                } else {
                                                                    timeInput.value = this.batchTime;
                                                                }
                                                                count++;
                                                            }
                                                        }
                                                    });
                                                    this.notify(staggerMinutes > 0 
                                                        ? 'Staggered times (+30m) starting from ' + this.batchTime + ' across ' + count + ' student(s).' 
                                                        : 'Applied time (' + this.batchTime + ') to ' + count + ' student(s).');
                                                }
                                            }" class="inline">
                                                <button type="button" @click="editOpen = true"
                                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    <span>Edit Schedule</span>
                                                </button>

                                                <template x-teleport="body">
                                                    <div x-show="editOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6 bg-slate-900/60 backdrop-blur-sm" @keydown.escape.window="if(!swapSourceId) editOpen = false">
                                                        <div @click.outside="if(!swapSourceId) editOpen = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-7xl max-h-[92vh] flex flex-col overflow-hidden border border-slate-100">
                                                            <form action="{{ route('admin.milestone-templates.update-schedule', $template->id) }}" method="POST" class="flex flex-col min-h-0 flex-1">
                                                                @csrf

                                                                {{-- Modal Header --}}
                                                                <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between gap-4 bg-white shrink-0">
                                                                    <div>
                                                                        <div class="flex items-center gap-2 mb-1">
                                                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200">
                                                                                Schedule Manager
                                                                            </span>
                                                                            <span class="text-xs font-bold text-slate-400">• {{ $scheduledCount }} Scheduled Candidates</span>
                                                                        </div>
                                                                        <h3 class="text-lg font-black text-slate-900">Edit {{ $template->presentation_title }} Schedule</h3>
                                                                        <p class="text-xs text-slate-500 mt-0.5">Filter by discipline group, assign Zoom links to all in a group, shift dates, or swap slots between candidates.</p>
                                                                    </div>
                                                                    <div class="flex items-center gap-2">
                                                                        @if($template->slug === 'seminar_as_a_course')
                                                                            <a :href="'{{ $basePdfRoute }}' + (editTrackFilter !== 'all' ? '?discipline_group=' + editTrackFilter : '')" target="_blank"
                                                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition shadow-xs"
                                                                               title="Download printable PDF timetable">
                                                                               <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                                                               <span x-text="editTrackFilter === 'all' ? 'PDF (All Groups)' : ('PDF (' + (editTrackFilter === 'cybersecurity' ? 'Cyber' : (editTrackFilter === 'artificial_intelligence' ? 'AI' : 'MIS')) + ')')"></span>
                                                                            </a>
                                                                        @endif
                                                                        <button type="button" @click="editOpen = false; cancelSwap();" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors">
                                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                        </button>
                                                                    </div>
                                                                </div>

                                                                {{-- Toast notification banner --}}
                                                                <div x-show="showToast" x-cloak x-transition.opacity class="px-6 py-2.5 bg-emerald-600 text-white text-xs font-bold flex items-center justify-between shrink-0 shadow-inner">
                                                                    <div class="flex items-center gap-2">
                                                                        <svg class="w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                        <span x-text="toastMessage"></span>
                                                                    </div>
                                                                    <button type="button" @click="showToast = false" class="text-white/80 hover:text-white font-black text-sm">✕</button>
                                                                </div>

                                                                {{-- Active Swap Indicator Banner --}}
                                                                <div x-show="swapSourceId" x-cloak class="px-6 py-3 bg-amber-500 text-white flex items-center justify-between gap-4 shadow-md sticky top-0 z-20 shrink-0">
                                                                    <div class="flex items-center gap-2 text-xs font-bold">
                                                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                                                        <span>
                                                                            Swapping slot: <strong x-text="swapSourceName" class="underline"></strong> (<span x-text="swapSourceDate"></span> at <span x-text="swapSourceTime"></span>) — Click <strong class="bg-white/20 px-1.5 py-0.5 rounded">⇄ Swap Here</strong> on any candidate below to exchange their timetable slots.
                                                                        </span>
                                                                    </div>
                                                                    <button type="button" @click="cancelSwap()" class="px-3 py-1 bg-white/20 hover:bg-white/30 rounded-lg text-[10px] font-black uppercase tracking-wider transition-colors shrink-0">
                                                                        Cancel Swap
                                                                    </button>
                                                                </div>

                                                                {{-- Navigation Tabs & Filter Bar --}}
                                                                <div class="px-6 py-3 bg-slate-50 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-3 shrink-0">
                                                                    {{-- Track Filter Tabs --}}
                                                                    <div class="flex flex-wrap items-center gap-1.5">
                                                                        <button type="button" @click="editTrackFilter = 'all'"
                                                                            :class="editTrackFilter === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-200/80'"
                                                                            class="px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-1.5">
                                                                            <span>All Groups</span>
                                                                            <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="editTrackFilter === 'all' ? 'bg-white/20' : 'bg-slate-100 text-slate-600'">{{ $scheduledCount }}</span>
                                                                        </button>

                                                                        @if($template->slug === 'seminar_as_a_course')
                                                                            <button type="button" @click="editTrackFilter = 'cybersecurity'"
                                                                                :class="editTrackFilter === 'cybersecurity' ? 'bg-cyan-700 text-white shadow-xs' : 'bg-white hover:bg-cyan-50/50 text-slate-700 border border-slate-200/80'"
                                                                                class="px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-1.5">
                                                                                <span>🛡️ Cybersecurity</span>
                                                                                <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="editTrackFilter === 'cybersecurity' ? 'bg-white/20' : 'bg-cyan-50 text-cyan-800'">{{ $schedCyberCount }}</span>
                                                                            </button>

                                                                            <button type="button" @click="editTrackFilter = 'artificial_intelligence'"
                                                                                :class="editTrackFilter === 'artificial_intelligence' ? 'bg-violet-700 text-white shadow-xs' : 'bg-white hover:bg-violet-50/50 text-slate-700 border border-slate-200/80'"
                                                                                class="px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-1.5">
                                                                                <span>🤖 Artificial Intelligence</span>
                                                                                <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="editTrackFilter === 'artificial_intelligence' ? 'bg-white/20' : 'bg-violet-50 text-violet-800'">{{ $schedAiCount }}</span>
                                                                            </button>

                                                                            <button type="button" @click="editTrackFilter = 'management_information_systems'"
                                                                                :class="editTrackFilter === 'management_information_systems' ? 'bg-amber-700 text-white shadow-xs' : 'bg-white hover:bg-amber-50/50 text-slate-700 border border-slate-200/80'"
                                                                                class="px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-1.5">
                                                                                <span>📊 MIS</span>
                                                                                <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="editTrackFilter === 'management_information_systems' ? 'bg-white/20' : 'bg-amber-50 text-amber-800'">{{ $schedMisCount }}</span>
                                                                            </button>
                                                                        @endif
                                                                    </div>

                                                                    {{-- Search Input --}}
                                                                    <div class="relative min-w-[240px]">
                                                                        <input type="text" x-model="editSearch" placeholder="Filter by name or matric..." class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                                    </div>
                                                                </div>

                                                                {{-- Batch Actions Bar (Applied to visible / filtered students) --}}
                                                                <div class="px-6 py-3.5 bg-white border-b border-slate-200/70 space-y-3 shrink-0">
                                                                    <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 text-xs">
                                                                        {{-- Zoom Link Batch Apply --}}
                                                                        <div class="flex-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                                                            <div class="relative flex-1">
                                                                                <input type="url" x-model="batchLink" placeholder="Paste Zoom link to apply to group..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                                                            </div>

                                                                            {{-- Quick Prefill Chips --}}
                                                                            @if($template->slug === 'seminar_as_a_course' && !empty($trackZoomLinks))
                                                                                <div class="flex items-center gap-1 shrink-0">
                                                                                    @if(!empty($trackZoomLinks['cybersecurity']))
                                                                                        <button type="button" @click="batchLink = '{{ $trackZoomLinks['cybersecurity'] }}'; editTrackFilter = 'cybersecurity'" class="px-2 py-1 rounded-lg bg-cyan-50 hover:bg-cyan-100 text-cyan-800 border border-cyan-200 text-[10px] font-black uppercase tracking-wider">
                                                                                            🛡️ Cyber
                                                                                        </button>
                                                                                    @endif
                                                                                    @if(!empty($trackZoomLinks['artificial_intelligence']))
                                                                                        <button type="button" @click="batchLink = '{{ $trackZoomLinks['artificial_intelligence'] }}'; editTrackFilter = 'artificial_intelligence'" class="px-2 py-1 rounded-lg bg-violet-50 hover:bg-violet-100 text-violet-800 border border-violet-200 text-[10px] font-black uppercase tracking-wider">
                                                                                            🤖 AI
                                                                                        </button>
                                                                                    @endif
                                                                                    @if(!empty($trackZoomLinks['management_information_systems']))
                                                                                        <button type="button" @click="batchLink = '{{ $trackZoomLinks['management_information_systems'] }}'; editTrackFilter = 'management_information_systems'" class="px-2 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-[10px] font-black uppercase tracking-wider">
                                                                                            📊 MIS
                                                                                        </button>
                                                                                    @endif
                                                                                </div>
                                                                            @endif

                                                                            <label class="flex items-center gap-1.5 text-[10px] text-slate-600 font-bold cursor-pointer select-none shrink-0 px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 transition">
                                                                                <input type="checkbox" x-model="batchOnlyEmpty" class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                                                                <span>Empty only</span>
                                                                            </label>

                                                                            <button type="button" @click="applyBatchMeetingLink()" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[10px] font-black uppercase tracking-wider shadow-sm transition-all shrink-0 flex items-center justify-center gap-1.5">
                                                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                                                <span x-text="editTrackFilter === 'all' ? 'Apply Zoom to All ({{ $scheduledCount }})' : ('Apply Zoom to ' + (editTrackFilter === 'cybersecurity' ? 'Cyber ({{ $schedCyberCount }})' : (editTrackFilter === 'artificial_intelligence' ? 'AI ({{ $schedAiCount }})' : 'MIS ({{ $schedMisCount }})')))"></span>
                                                                            </button>
                                                                        </div>

                                                                        {{-- Date & Time Batch Apply --}}
                                                                        <div class="flex items-center gap-2 shrink-0 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                                                                            <input type="date" x-model="batchDate" class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white">
                                                                            <button type="button" @click="applyBatchDate()" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-wider shadow-sm">
                                                                                Set Date
                                                                            </button>

                                                                            <input type="time" x-model="batchTime" class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white ml-2">
                                                                            <button type="button" @click="applyBatchTime(0)" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-wider shadow-sm" title="Set this exact time for all">
                                                                                Set Time
                                                                            </button>
                                                                            <button type="button" @click="applyBatchTime(30)" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-[10px] font-black uppercase tracking-wider shadow-sm" title="Stagger starting times in 30-minute intervals">
                                                                                ⚡ Stagger (+30m)
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                {{-- Per-student rows Table --}}
                                                                <div class="overflow-y-auto flex-1 min-h-0">
                                                                    <table class="w-full text-left text-xs">
                                                                        <thead class="bg-slate-50 sticky top-0 z-10 border-b border-slate-200/80 text-[10px] font-black uppercase tracking-wider text-slate-500">
                                                                            <tr>
                                                                                <th class="px-6 py-3.5">Candidate & Track</th>
                                                                                <th class="px-4 py-3.5">Presentation Date</th>
                                                                                <th class="px-4 py-3.5">Slot Time</th>
                                                                                <th class="px-4 py-3.5">Meeting Link (Zoom)</th>
                                                                                <th class="px-6 py-3.5 text-center">Actions / Swap</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody class="divide-y divide-slate-100">
                                                                            @foreach($scheduledMilestones as $esm)
                                                                                @php
                                                                                    $candidateName = $esm->thesis?->student?->user?->name ?? 'Candidate';
                                                                                    $candidateMatric = $esm->thesis?->student?->student_id_number ?? ($esm->thesis?->student?->matric_number ?? 'N/A');
                                                                                    $track = $esm->discipline_group ?? 'cybersecurity';
                                                                                    $dateVal = $esm->defence_date ? \Carbon\Carbon::parse($esm->defence_date)->format('Y-m-d') : '';
                                                                                    $timeVal = $esm->defence_time ? \Carbon\Carbon::parse($esm->defence_time)->format('H:i') : '';
                                                                                @endphp
                                                                                <tr id="sched-row-{{ $esm->id }}"
                                                                                    data-sched-id="{{ $esm->id }}"
                                                                                    data-sched-track="{{ $track }}"
                                                                                    data-sched-name="{{ strtolower($candidateName) }}"
                                                                                    data-sched-matric="{{ strtolower($candidateMatric) }}"
                                                                                    x-show="(editTrackFilter === 'all' || editTrackFilter === '{{ $track }}') && (!editSearch || '{{ strtolower(addslashes($candidateName)) }}'.includes(editSearch.toLowerCase()) || '{{ strtolower(addslashes($candidateMatric)) }}'.includes(editSearch.toLowerCase()))"
                                                                                    x-data="{ rm: false }"
                                                                                    :class="{ 'bg-rose-50/70 opacity-60': rm, 'ring-2 ring-amber-400 bg-amber-50/50': swapSourceId == '{{ $esm->id }}' }"
                                                                                    class="transition-colors hover:bg-slate-50/60">
                                                                                    <td class="px-6 py-3">
                                                                                        <div class="flex items-center gap-2">
                                                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider shrink-0
                                                                                                {{ $track === 'cybersecurity' ? 'bg-cyan-50 text-cyan-800 border border-cyan-200' : ($track === 'artificial_intelligence' ? 'bg-violet-50 text-violet-800 border border-violet-200' : 'bg-amber-50 text-amber-800 border border-amber-200') }}">
                                                                                                @if($track === 'cybersecurity') 🛡️ Cyber
                                                                                                @elseif($track === 'artificial_intelligence') 🤖 AI
                                                                                                @else 📊 MIS
                                                                                                @endif
                                                                                            </span>
                                                                                            <div>
                                                                                                <div class="font-bold text-slate-900 leading-tight">{{ $candidateName }}</div>
                                                                                                <div class="text-[10px] text-slate-500 font-semibold">{{ $candidateMatric }}</div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </td>
                                                                                    <td class="px-4 py-3">
                                                                                        <input type="date" data-field="date" name="entries[{{ $esm->id }}][defence_date]" value="{{ $dateVal }}" :disabled="rm"
                                                                                            class="w-36 px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                                                    </td>
                                                                                    <td class="px-4 py-3">
                                                                                        <input type="time" data-field="time" name="entries[{{ $esm->id }}][defence_time]" value="{{ $timeVal }}" :disabled="rm"
                                                                                            class="w-28 px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                                                    </td>
                                                                                    <td class="px-4 py-3">
                                                                                        <div class="flex items-center gap-1.5">
                                                                                            <input type="url" data-field="link" name="entries[{{ $esm->id }}][meeting_link]" value="{{ $esm->meeting_link }}" placeholder="https://zoom.us/j/..." :disabled="rm"
                                                                                                class="w-full min-w-[220px] px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-medium focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                                                                            @if(!empty($trackZoomLinks[$track]))
                                                                                                <button type="button" @click="$el.previousElementSibling.value = '{{ $trackZoomLinks[$track] }}'" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Fill with track default Zoom link">
                                                                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                                                                                </button>
                                                                                            @endif
                                                                                        </div>
                                                                                    </td>
                                                                                    <td class="px-6 py-3 text-center">
                                                                                        <div class="flex items-center justify-center gap-1.5">
                                                                                            {{-- Swap Actions --}}
                                                                                            <template x-if="swapSourceId == '{{ $esm->id }}'">
                                                                                                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 rounded-lg font-black text-[10px] uppercase border border-amber-300">
                                                                                                    Selected
                                                                                                </span>
                                                                                            </template>
                                                                                            <template x-if="swapSourceId && swapSourceId != '{{ $esm->id }}'">
                                                                                                <button type="button" 
                                                                                                    @click="executeSwap('{{ $esm->id }}', '{{ addslashes($candidateName) }}')"
                                                                                                    class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-black text-[10px] uppercase tracking-wider transition shadow-sm animate-pulse-subtle">
                                                                                                    ⇄ Swap Here
                                                                                                </button>
                                                                                            </template>
                                                                                            <template x-if="!swapSourceId">
                                                                                                <button type="button" 
                                                                                                    @click="startSwap('{{ $esm->id }}', '{{ addslashes($candidateName) }}', $el.closest('tr').querySelector('input[data-field=date]').value, $el.closest('tr').querySelector('input[data-field=time]').value, '{{ $track }}')"
                                                                                                    class="px-2.5 py-1 bg-slate-100 hover:bg-amber-100 text-slate-700 hover:text-amber-800 rounded-lg border border-slate-200 hover:border-amber-300 text-[10px] font-black uppercase tracking-wider transition"
                                                                                                    title="Swap slot with another student">
                                                                                                    ⇄ Swap
                                                                                                </button>
                                                                                            </template>

                                                                                            {{-- Remove Action --}}
                                                                                            <input type="hidden" name="entries[{{ $esm->id }}][remove]" :value="rm ? 1 : 0">
                                                                                            <button type="button" @click="rm = !rm"
                                                                                                :class="rm ? 'bg-rose-600 text-white border-rose-600' : 'bg-white text-rose-600 border-rose-200 hover:bg-rose-50'"
                                                                                                class="px-2.5 py-1 rounded-lg border text-[10px] font-black uppercase tracking-wider transition"
                                                                                                x-text="rm ? 'Undo' : 'Remove'"></button>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                {{-- Footer --}}
                                                                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between gap-3 bg-white shrink-0">
                                                                    <div class="text-[11px] text-slate-500 flex items-center gap-3">
                                                                        <span>Total Scheduled: <strong>{{ $scheduledCount }}</strong> candidates</span>
                                                                        <span class="text-slate-300">•</span>
                                                                        <span class="text-slate-400">Slots update automatically when saved; candidates receive an instant timetable notification.</span>
                                                                    </div>
                                                                    <div class="flex gap-2 shrink-0">
                                                                        <button type="button" @click="editOpen = false; cancelSwap();" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">Close</button>
                                                                        <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider text-white bg-amber-600 hover:bg-amber-700 shadow-md shadow-amber-600/25 transition-all">Save Changes</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>

                                            {{-- Reschedule Day Button & Modal --}}
                                            <div class="inline">
                                                <button type="button" @click="rescheduleDayOpen = true"
                                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-800 border border-indigo-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <span>Reschedule Day</span>
                                                </button>

                                                <template x-teleport="body">
                                                    <div x-show="rescheduleDayOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @keydown.escape.window="rescheduleDayOpen = false">
                                                        <div @click.outside="rescheduleDayOpen = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100">
                                                            <form action="{{ route('admin.milestone-templates.reschedule-day', $template->id) }}" method="POST">
                                                                @csrf
                                                                {{-- Header --}}
                                                                <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between gap-4 bg-slate-50/50">
                                                                    <div class="flex items-center gap-3">
                                                                        <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0">
                                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                            </svg>
                                                                        </div>
                                                                        <div>
                                                                            <h3 class="text-base font-black text-slate-900">Reschedule Presentation Day</h3>
                                                                            <p class="text-xs text-slate-500 mt-0.5">Shift all presenters scheduled on a specific date to a new date.</p>
                                                                        </div>
                                                                    </div>
                                                                    <button type="button" @click="rescheduleDayOpen = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                    </button>
                                                                </div>

                                                                {{-- Form Body --}}
                                                                <div class="p-6 space-y-4 text-xs text-left">
                                                                    @if($template->slug === 'seminar_as_a_course')
                                                                        <div>
                                                                            <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                                                                Discipline Track to Reschedule
                                                                            </label>
                                                                            <select name="discipline_group" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                                                                <option value="">All Tracks (Global shift)</option>
                                                                                <option value="cybersecurity" :selected="trackFilter === 'cybersecurity'">🛡️ Cybersecurity Track Only</option>
                                                                                <option value="artificial_intelligence" :selected="trackFilter === 'artificial_intelligence'">🤖 AI Track Only</option>
                                                                                <option value="management_information_systems" :selected="trackFilter === 'management_information_systems'">📊 MIS Track Only</option>
                                                                            </select>
                                                                            <p class="text-[10px] text-slate-400 mt-1">Select a specific track to shift only that group and protect other tracks' schedules and Zoom links.</p>
                                                                        </div>
                                                                    @endif

                                                                    @php
                                                                        $dayGroups = $scheduledMilestones->groupBy(fn($m) => \Carbon\Carbon::parse($m->defence_date)->format('Y-m-d'));
                                                                    @endphp

                                                                    <div>
                                                                        <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                                                            Current Scheduled Day to Shift <span class="text-rose-500">*</span>
                                                                        </label>
                                                                        <select name="from_date" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                                                            <option value="all">-- All Scheduled Dates in this Group / Session --</option>
                                                                            @foreach($dayGroups as $dateKey => $group)
                                                                                <option value="{{ $dateKey }}">
                                                                                    {{ \Carbon\Carbon::parse($dateKey)->format('l, F j, Y') }} ({{ $group->count() }} presenter{{ $group->count() > 1 ? 's' : '' }})
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        <p class="text-[10px] text-slate-400 mt-1">Select a specific date or move all scheduled presentations in the selected track together.</p>
                                                                    </div>

                                                                    <div>
                                                                        <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                                                            New Target Date <span class="text-rose-500">*</span>
                                                                        </label>
                                                                        <input type="date" name="to_date" required min="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                                                        <p class="text-[10px] text-slate-400 mt-1">Pick the new date when these presentations should take place.</p>
                                                                    </div>

                                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                                                                        <div>
                                                                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1.5">
                                                                                New Start Time (Optional)
                                                                            </label>
                                                                            <input type="time" name="start_time" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                                                            <p class="text-[9px] text-slate-400 mt-0.5">Leave blank to retain current individual times.</p>
                                                                        </div>
                                                                        <div>
                                                                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1.5">
                                                                                Meeting Link (Optional)
                                                                            </label>
                                                                            <input type="url" name="meeting_link" placeholder="https://zoom.us/j/..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                                                            <p class="text-[9px] text-slate-400 mt-0.5">Leave blank to retain current links.</p>
                                                                        </div>
                                                                    </div>

                                                                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-800 text-[11px] leading-relaxed flex items-start gap-2">
                                                                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                        </svg>
                                                                        <span>
                                                                            Presenters on this day will have their defence schedules updated and receive a notification with the new date details.
                                                                        </span>
                                                                    </div>
                                                                </div>

                                                                {{-- Footer --}}
                                                                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50/50">
                                                                    <button type="button" @click="rescheduleDayOpen = false" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 transition-colors">Cancel</button>
                                                                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition-all flex items-center gap-2">
                                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                        </svg>
                                                                        <span>Shift Presentations</span>
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>

                                            <form action="{{ route('admin.milestone-templates.end-schedule', $template->id) }}" method="POST" class="inline">
                                                @csrf
                                                @if($template->slug === 'seminar_as_a_course')
                                                    <input type="hidden" name="discipline_group" :value="trackFilter !== 'all' ? trackFilter : ''">
                                                @endif
                                                <button type="submit" data-confirm="Are you sure you want to end the presentation session? Eligible candidates who have uploaded their presentation slides and received a passing evaluation from the panel will be approved and advanced to the next milestone."
                                                    data-confirm-title="End Presentation Session"
                                                    data-confirm-type="success"
                                                    data-confirm-btn="End Session"
                                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span x-text="trackFilter === 'cybersecurity' ? 'End Cyber Session' : (trackFilter === 'artificial_intelligence' ? 'End AI Session' : (trackFilter === 'management_information_systems' ? 'End MIS Session' : 'End Presentation Session'))">End Presentation Session</span>
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.milestone-templates.cancel-schedule', $template->id) }}" method="POST" class="inline">
                                                @csrf
                                                @if($template->slug === 'seminar_as_a_course')
                                                    <input type="hidden" name="discipline_group" :value="trackFilter !== 'all' ? trackFilter : ''">
                                                @endif
                                                <button type="submit" 
                                                    :data-confirm="trackFilter === 'cybersecurity' ? 'Are you sure you want to cancel the Cybersecurity presentation schedule? This will clear presentation dates, times, and Zoom links for Cybersecurity candidates without affecting other tracks.' : (trackFilter === 'artificial_intelligence' ? 'Are you sure you want to cancel the AI presentation schedule? This will clear presentation dates, times, and Zoom links for AI candidates without affecting other tracks.' : (trackFilter === 'management_information_systems' ? 'Are you sure you want to cancel the MIS presentation schedule? This will clear presentation dates, times, and Zoom links for MIS candidates without affecting other tracks.' : 'Are you sure you want to cancel the presentation schedule for {{ addslashes($template->name) }}? This will clear all presentation dates, times, and Zoom links for {{ $scheduledCount }} scheduled student(s), and remove the live presentation tab.'))"
                                                    :data-confirm-title="trackFilter !== 'all' ? 'Cancel ' + (trackFilter === 'cybersecurity' ? 'Cyber' : (trackFilter === 'artificial_intelligence' ? 'AI' : 'MIS')) + ' Schedule' : 'Cancel Presentation Schedule'"
                                                    data-confirm-type="danger"
                                                    data-confirm-btn="Cancel Schedule"
                                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    <span x-text="trackFilter === 'cybersecurity' ? 'Cancel Cyber Schedule' : (trackFilter === 'artificial_intelligence' ? 'Cancel AI Schedule' : (trackFilter === 'management_information_systems' ? 'Cancel MIS Schedule' : 'Cancel Presentation Schedule'))">Cancel Presentation Schedule</span>
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('presentations.show', $template->id) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            <span>View Presentation Page</span>
                                        </a>
                                    @elseif($template->allow_defence_date || in_array($template->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2', 'internal_defence']))
                                        @if(!isset($isCoordinator) || !$isCoordinator)
                                            <button type="button" disabled title="No presentations currently scheduled for this milestone"
                                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-50 text-slate-400 border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest cursor-not-allowed opacity-60">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>End Presentation Session</span>
                                            </button>
                                        @endif
                                    @endif
                                </div>

                                {{-- Global / Track Examiner Assignment (Admin & Coordinator) --}}
                                @if(in_array($template->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2', 'internal_defence']))
                                    @if($template->slug === 'seminar_as_a_course')
                                        @php
                                            $trackCards = [
                                                'cybersecurity' => [
                                                    'name' => 'Cybersecurity Track',
                                                    'icon' => '🛡️',
                                                    'badge_bg' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                                                    'btn_bg' => 'bg-cyan-600 hover:bg-cyan-700',
                                                    'examiners' => $cyberExaminers,
                                                    'profile_ids' => $cyberProfileIds,
                                                    'count' => $cyberMilestones->count(),
                                                    'scheduled' => $cyberScheduled,
                                                ],
                                                'artificial_intelligence' => [
                                                    'name' => 'Artificial Intelligence Track',
                                                    'icon' => '🤖',
                                                    'badge_bg' => 'bg-violet-100 text-violet-800 border-violet-200',
                                                    'btn_bg' => 'bg-violet-600 hover:bg-violet-700',
                                                    'examiners' => $aiExaminers,
                                                    'profile_ids' => $aiProfileIds,
                                                    'count' => $aiMilestones->count(),
                                                    'scheduled' => $aiScheduled,
                                                ],
                                                'management_information_systems' => [
                                                    'name' => 'Management Info Systems Track',
                                                    'icon' => '📊',
                                                    'badge_bg' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                    'btn_bg' => 'bg-amber-600 hover:bg-amber-700',
                                                    'examiners' => $misExaminers,
                                                    'profile_ids' => $misProfileIds,
                                                    'count' => $misMilestones->count(),
                                                    'scheduled' => $misScheduled,
                                                ],
                                            ];
                                        @endphp
                                        <div class="mb-6 space-y-3">
                                            <div class="flex items-center justify-between pb-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                                                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-800">Track-Specific Examiner Assignments</h4>
                                                </div>
                                                <span class="text-[11px] text-slate-400 font-medium">Each discipline group is examined independently by its dedicated panel</span>
                                            </div>

                                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                                @foreach($trackCards as $tKey => $tConfig)
                                                    @php
                                                        $tSelJs = collect($tConfig['profile_ids'])->map(fn($id) => "'{$id}'")->implode(', ');
                                                    @endphp
                                                    <div x-show="trackFilter === 'all' || trackFilter === '{{ $tKey }}'"
                                                         class="bg-slate-50/70 border border-slate-200/80 rounded-2xl p-4 flex flex-col justify-between space-y-3">
                                                        <div>
                                                            <div class="flex items-center justify-between pb-2 border-b border-slate-200/60">
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-base">{{ $tConfig['icon'] }}</span>
                                                                    <div>
                                                                        <h5 class="text-xs font-black text-slate-900 leading-tight">{{ $tConfig['name'] }}</h5>
                                                                        <p class="text-[10px] text-slate-400 font-medium">{{ $tConfig['count'] }} candidates ({{ $tConfig['scheduled'] }} scheduled)</p>
                                                                    </div>
                                                                </div>
                                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black border {{ $tConfig['badge_bg'] }}">
                                                                    {{ $tConfig['examiners']->count() }} assigned
                                                                </span>
                                                            </div>

                                                            {{-- Currently Assigned List --}}
                                                            <div class="mt-2.5">
                                                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5 flex items-center justify-between">
                                                                    <span>Active Examiners</span>
                                                                    @if($tConfig['examiners']->isNotEmpty())
                                                                        <form action="{{ (auth()->user()->hasRole('Program Coordinator') && !auth()->user()->hasRole('Admin')) ? route('coordinator.milestone-templates.assign-examiner-global', $template->id) : route('admin.milestone-templates.assign-examiner-global', $template->id) }}" method="POST" class="inline">
                                                                            @csrf
                                                                            <input type="hidden" name="discipline_group" value="{{ $tKey }}">
                                                                            <button type="submit" 
                                                                                data-confirm="Remove all examiners assigned to {{ $tConfig['name'] }}?"
                                                                                data-confirm-title="Remove Track Examiners"
                                                                                class="text-[9px] font-bold text-rose-600 hover:text-rose-800 hover:underline cursor-pointer">
                                                                                Clear All
                                                                            </button>
                                                                        </form>
                                                                    @endif
                                                                </div>
                                                                @if($tConfig['examiners']->isNotEmpty())
                                                                    <div class="flex flex-wrap gap-1.5 mb-2">
                                                                        @foreach($tConfig['examiners'] as $ex)
                                                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-white text-slate-800 border border-slate-200 shadow-2xs">
                                                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                                                <span class="truncate max-w-[140px]">{{ $ex->name }}</span>
                                                                            </span>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <p class="text-[11px] text-slate-400 italic mb-2">No examiners assigned yet.</p>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        {{-- Multiselect Form --}}
                                                        <form action="{{ (auth()->user()->hasRole('Program Coordinator') && !auth()->user()->hasRole('Admin')) ? route('coordinator.milestone-templates.assign-examiner-global', $template->id) : route('admin.milestone-templates.assign-examiner-global', $template->id) }}" method="POST"
                                                            x-data="{
                                                                search: '',
                                                                options: [
                                                                    @foreach($supervisors as $sup)
                                                                        { id: '{{ $sup->id }}', name: '{{ addslashes($sup->user->name) }}', program: '{{ addslashes($sup->programs->first()->code ?? 'N/A') }}' }{{ !$loop->last ? ',' : '' }}
                                                                    @endforeach
                                                                ],
                                                                selected: [{{ $tSelJs }}],
                                                                open: false,
                                                                get filteredOptions() {
                                                                    if (this.search === '') return this.options;
                                                                    let s = this.search.toLowerCase();
                                                                    return this.options.filter(o => o.name.toLowerCase().includes(s) || o.program.toLowerCase().includes(s));
                                                                },
                                                                get selectedNames() {
                                                                    if (this.selected.length === 0) return 'Select Examiners...';
                                                                    if (this.selected.length === 1) return this.options.find(o => o.id == this.selected[0])?.name || '';
                                                                    return this.selected.length + ' selected';
                                                                }
                                                            }"
                                                            @submit="if(selected.length === 0) { if(!confirm('No examiners selected. Remove all examiners for {{ $tConfig['name'] }}?')) { $event.preventDefault(); } }">
                                                            @csrf
                                                            <input type="hidden" name="discipline_group" value="{{ $tKey }}">
                                                            <template x-for="id in selected" :key="id">
                                                                <input type="hidden" name="supervisor_profile_ids[]" :value="id">
                                                            </template>

                                                            <div class="flex items-center gap-1.5">
                                                                <div class="relative flex-1">
                                                                    <div @click="open = !open" class="w-full py-1.5 pl-2.5 pr-6 text-xs border border-slate-200 bg-white rounded-lg cursor-pointer flex items-center justify-between">
                                                                        <span class="truncate text-slate-700 font-medium" x-text="selectedNames"></span>
                                                                        <svg class="w-3 h-3 text-slate-400 absolute right-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                                    </div>

                                                                    <div x-show="open" @click.outside="open = false" x-transition class="absolute bottom-full left-0 mb-1 w-64 bg-white border border-slate-200 rounded-xl shadow-xl overflow-y-auto z-50 text-left" style="display: none;">
                                                                        <div class="p-2 sticky top-0 bg-slate-50 border-b border-slate-100 z-10 flex flex-col gap-1.5">
                                                                            <input type="text" x-model="search" placeholder="Search supervisor..." class="w-full text-xs p-1.5 border border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" @click.stop>
                                                                            <div class="flex justify-between gap-1.5">
                                                                                <button type="button" @click.stop="selected = [...new Set([...selected, ...filteredOptions.map(o => o.id)])]" class="text-[9px] font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-wider px-2 py-0.5 bg-indigo-50 rounded flex-1">All</button>
                                                                                <button type="button" @click.stop="selected = []" class="text-[9px] font-bold text-slate-500 hover:text-slate-700 uppercase tracking-wider px-2 py-0.5 bg-slate-100 rounded flex-1">Clear</button>
                                                                            </div>
                                                                        </div>
                                                                        <div class="py-1 max-h-40 overflow-y-auto">
                                                                            <template x-for="option in filteredOptions" :key="option.id">
                                                                                <label class="flex items-center px-3 py-1.5 hover:bg-slate-50 cursor-pointer text-xs">
                                                                                    <input type="checkbox" :value="option.id" x-model="selected" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 mr-2 w-3.5 h-3.5">
                                                                                    <span class="truncate text-slate-700" x-text="option.name"></span>
                                                                                </label>
                                                                            </template>
                                                                        </div>
                                                                        <div class="p-2 border-t border-slate-100 bg-slate-50">
                                                                            <button type="button" @click.stop="open = false" class="w-full py-1 bg-slate-900 hover:bg-slate-800 text-white text-[10px] font-bold uppercase tracking-wider rounded-lg">Done</button>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <button type="submit" class="text-white {{ $tConfig['btn_bg'] }} px-3 py-1.5 rounded-lg text-xs font-bold transition-colors shadow-2xs shrink-0 cursor-pointer">
                                                                    Assign
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $globalEventType = $template->defence_type ?? match($template->slug) {
                                                'seminar_as_a_course' => 'seminar',
                                                'proposal_defence' => 'proposal',
                                                'progress_report_1' => 'progress_report_1',
                                                'progress_report_2' => 'progress_report_2',
                                                'internal_defence' => 'internal',
                                                default => 'first_seminar',
                                            };

                                            // Resolve current examiners: metadata first, then search events
                                            $metaUserIds = $template->metadata['examiner_user_ids'] ?? [];
                                            if (!empty($metaUserIds)) {
                                                $currentExaminers = collect($metaUserIds)->map(fn($id) => $examinerUsersMap[$id] ?? null)->filter();
                                            } else {
                                                $firstEventWithEx = null;
                                                foreach ($template->studentMilestones as $mItem) {
                                                    $ev = $mItem->thesis?->defenceEvents?->firstWhere('type', $globalEventType);
                                                    if ($ev && $ev->panelMembers->where('role', 'examiner')->count() > 0) {
                                                        $firstEventWithEx = $ev;
                                                        break;
                                                    }
                                                }
                                                $currentExaminers = $firstEventWithEx 
                                                    ? $firstEventWithEx->panelMembers->where('role', 'examiner')->map(fn($pm) => $pm->user)->filter() 
                                                    : collect();
                                            }

                                            $examinerBoxTitle = match($template->slug) {
                                                'proposal_defence' => 'Proposal Defence Examiner(s)',
                                                'progress_report_1' => 'Progress Report 1 Examiner(s)',
                                                'progress_report_2' => 'Progress Report 2 Examiner(s)',
                                                'internal_defence' => 'Internal Defence Examiner(s)',
                                                default => 'Seminar Examiner(s)',
                                            };

                                            $selectedProfileIds = $template->metadata['examiner_profile_ids'] ?? [];
                                            if (empty($selectedProfileIds) && $currentExaminers->count() > 0) {
                                                $currentExaminerUserIds = $currentExaminers->pluck('id')->all();
                                                $selectedProfileIds = $supervisors->whereIn('user_id', $currentExaminerUserIds)->pluck('id')->values()->all();
                                            }
                                            $selectedJsArray = collect($selectedProfileIds)->map(fn($id) => "'{$id}'")->implode(', ');
                                        @endphp
                                        <div class="mb-6 bg-indigo-50/50 border border-indigo-100 rounded-xl p-4">
                                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-black text-indigo-900 uppercase tracking-wider">{{ $examinerBoxTitle }}</p>
                                                        <div class="flex flex-wrap items-center gap-2 mt-0.5">
                                                            <p class="text-[10px] text-indigo-600">
                                                                @if($currentExaminers->count() > 0)
                                                                    Currently: <strong>{{ $currentExaminers->map(fn($e) => $e->name)->implode(', ') }}</strong>
                                                                @else
                                                                    No examiner assigned yet.
                                                                @endif
                                                            </p>
                                                            @if($currentExaminers->count() > 0)
                                                                <form action="{{ (auth()->user()->hasRole('Program Coordinator') && !auth()->user()->hasRole('Admin')) ? route('coordinator.milestone-templates.assign-examiner-global', $template->id) : route('admin.milestone-templates.assign-examiner-global', $template->id) }}" method="POST" class="inline">
                                                                    @csrf
                                                                    <button type="submit" 
                                                                        data-confirm="Are you sure you want to remove all currently assigned examiners from this presentation milestone?"
                                                                        data-confirm-title="Remove All Examiners"
                                                                        data-confirm-type="danger"
                                                                        data-confirm-btn="Yes, Remove All"
                                                                        class="text-[10px] font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-2 py-0.5 rounded transition-colors inline-flex items-center gap-1 cursor-pointer">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                                        Remove All
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <form action="{{ (auth()->user()->hasRole('Program Coordinator') && !auth()->user()->hasRole('Admin')) ? route('coordinator.milestone-templates.assign-examiner-global', $template->id) : route('admin.milestone-templates.assign-examiner-global', $template->id) }}" method="POST" class="flex items-center gap-2 sm:ml-auto overflow-visible relative" 
                                                    x-data="{
                                                        search: '',
                                                        options: [
                                                            @foreach($supervisors as $sup)
                                                                { id: '{{ $sup->id }}', name: '{{ addslashes($sup->user->name) }}', program: '{{ addslashes($sup->programs->first()->code ?? 'N/A') }}' }{{ !$loop->last ? ',' : '' }}
                                                            @endforeach
                                                        ],
                                                        selected: [{{ $selectedJsArray }}],
                                                        open: false,
                                                        get filteredOptions() {
                                                            if (this.search === '') return this.options;
                                                            let s = this.search.toLowerCase();
                                                            return this.options.filter(o => o.name.toLowerCase().includes(s) || o.program.toLowerCase().includes(s));
                                                        },
                                                        get selectedNames() {
                                                            if (this.selected.length === 0) return 'Select Examiner(s)';
                                                            if (this.selected.length === 1) return this.options.find(o => o.id == this.selected[0])?.name || '';
                                                            return this.selected.length + ' selected';
                                                        }
                                                    }"
                                                    @submit="if(selected.length === 0) { if(!confirm('No examiners selected. Do you want to remove all examiners from this milestone?')) { $event.preventDefault(); } }">
                                                    @csrf
                                                    <template x-for="id in selected" :key="id">
                                                        <input type="hidden" name="supervisor_profile_ids[]" :value="id">
                                                    </template>
                                                    
                                                    <!-- Alpine component for multiselect -->
                                                    <div class="relative w-48 z-50">
                                                        <div @click="open = !open" class="block w-full py-1.5 pl-3 pr-8 text-xs border border-indigo-200 bg-white rounded-lg cursor-pointer flex items-center justify-between">
                                                            <span class="truncate text-slate-700" x-text="selectedNames"></span>
                                                            <svg class="w-3 h-3 text-slate-400 absolute right-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                        </div>

                                                        <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 w-64 mt-1 bg-white border border-indigo-100 rounded-lg shadow-xl overflow-y-auto z-50" style="display: none;">
                                                            <div class="p-2 sticky top-0 bg-slate-50 border-b border-slate-100 z-10 flex flex-col gap-2">
                                                                <input type="text" x-model="search" placeholder="Search name or programme..." class="w-full text-xs p-1.5 border border-slate-200 rounded-md focus:ring-indigo-500 focus:border-indigo-500" @click.stop>
                                                                <div class="flex justify-between gap-2">
                                                                    <button type="button" @click.stop="selected = [...new Set([...selected, ...filteredOptions.map(o => o.id)])]" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-wider px-2 py-1 bg-indigo-50 hover:bg-indigo-100 rounded flex-1">Select All</button>
                                                                    <button type="button" @click.stop="selected = []" class="text-[10px] font-bold text-slate-500 hover:text-slate-700 uppercase tracking-wider px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded flex-1">Clear</button>
                                                                </div>
                                                            </div>
                                                            <div class="py-1 max-h-40 overflow-y-auto">
                                                                <template x-for="option in filteredOptions" :key="option.id">
                                                                    <label class="flex items-center px-3 py-2 hover:bg-indigo-50 cursor-pointer">
                                                                        <input type="checkbox" :value="option.id" x-model="selected" class="rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 mr-2 w-3.5 h-3.5">
                                                                        <span class="text-xs text-slate-700 flex flex-col"><span x-text="option.name"></span><span class="text-[9px] text-slate-400 font-bold tracking-wider" x-text="option.program"></span></span>
                                                                    </label>
                                                                </template>
                                                            </div>
                                                            <div class="p-2 border-t border-slate-100 bg-slate-50 space-y-2">
                                                                <textarea name="custom_message" placeholder="Custom Message (Optional)..." rows="2" class="w-full text-xs p-1 border border-slate-200 rounded-md focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                                                <div class="flex items-center gap-2">
                                                                    <button type="button" @click.stop="selected = []; $el.closest('form').submit();" class="py-1.5 px-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[10px] font-bold uppercase tracking-wider rounded">Remove All</button>
                                                                    <button type="button" @click.stop="open = false" class="flex-1 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold uppercase tracking-wider rounded">Done</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <button type="submit" class="text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-1.5 rounded-lg text-xs font-bold transition-colors shadow-sm shrink-0">
                                                        Assign to All
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                {{-- Schedule Form (Admin only) --}}
                                @if($template->studentMilestones->count() > 0 && (!isset($isCoordinator) || !$isCoordinator))
                                <div x-ref="scheduleForm" x-show="showStudents" x-cloak class="mb-6 bg-gradient-to-br from-slate-50 via-emerald-50/20 to-white p-5 rounded-2xl border border-emerald-100 shadow-sm">
                                    <form action="{{ route('admin.milestone-templates.schedule') }}" method="POST"
                                        x-data="{
                                            activeDays: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                                            dayOptions: [
                                                { key: 'monday', label: 'Mon', full: 'Monday' },
                                                { key: 'tuesday', label: 'Tue', full: 'Tuesday' },
                                                { key: 'wednesday', label: 'Wed', full: 'Wednesday' },
                                                { key: 'thursday', label: 'Thu', full: 'Thursday' },
                                                { key: 'friday', label: 'Fri', full: 'Friday' },
                                                { key: 'saturday', label: 'Sat', full: 'Saturday' },
                                                { key: 'sunday', label: 'Sun', full: 'Sunday' }
                                            ],
                                            toggleDay(dayKey) {
                                                if (this.activeDays.includes(dayKey)) {
                                                    if (this.activeDays.length === 1) {
                                                        alert('At least one day of the week must remain active for scheduling.');
                                                        return;
                                                    }
                                                    this.activeDays = this.activeDays.filter(d => d !== dayKey);
                                                } else {
                                                    this.activeDays.push(dayKey);
                                                }
                                            },
                                            setDaysPreset(preset) {
                                                if (preset === 'weekdays') {
                                                    this.activeDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                                                } else if (preset === 'all') {
                                                    this.activeDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                                } else if (preset === 'mon_thu') {
                                                    this.activeDays = ['monday', 'tuesday', 'wednesday', 'thursday'];
                                                }
                                            },
                                            excludedDates: [],
                                            newExcludedDate: '',
                                            addExcludedDate() {
                                                if (!this.newExcludedDate) return;
                                                if (!this.excludedDates.includes(this.newExcludedDate)) {
                                                    this.excludedDates.push(this.newExcludedDate);
                                                    this.excludedDates.sort();
                                                }
                                                this.newExcludedDate = '';
                                            },
                                            removeExcludedDate(dateStr) {
                                                this.excludedDates = this.excludedDates.filter(d => d !== dateStr);
                                            },
                                            formatDateChip(dateStr) {
                                                try {
                                                    const parts = dateStr.split('-');
                                                    if (parts.length === 3) {
                                                        const year = parseInt(parts[0], 10);
                                                        const month = parseInt(parts[1], 10) - 1;
                                                        const day = parseInt(parts[2], 10);
                                                        const d = new Date(year, month, day);
                                                        return d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                                                    }
                                                } catch (e) {}
                                                return dateStr;
                                            }
                                        }">
                                        @csrf
                                        <template x-for="id in selected">
                                            <input type="hidden" name="milestone_ids[]" :value="id">
                                        </template>
                                        <template x-for="day in activeDays" :key="'active-day-' + day">
                                            <input type="hidden" name="active_days[]" :value="day">
                                        </template>
                                        <template x-for="exDate in excludedDates" :key="'ex-date-' + exDate">
                                            <input type="hidden" name="excluded_dates[]" :value="exDate">
                                        </template>
                                        <input type="hidden" name="degree_type" :value="degreeFilter">

                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-emerald-600/10 text-emerald-700 flex items-center justify-center font-bold">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h4 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
                                                        <span>{{ $scheduledCount > 0 ? 'Add Students to Schedule' : 'Generate Presentation Schedule' }}</span>
                                                        @if($isSeminar)
                                                            <span x-show="trackFilter === 'all'" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-slate-900 text-white">All Tracks</span>
                                                            <span x-show="trackFilter === 'cybersecurity'" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-cyan-100 text-cyan-800 border border-cyan-200">🛡️ Cybersecurity Track</span>
                                                            <span x-show="trackFilter === 'artificial_intelligence'" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-violet-100 text-violet-800 border border-violet-200">🤖 AI Track</span>
                                                            <span x-show="trackFilter === 'management_information_systems'" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">📊 MIS Track</span>
                                                        @elseif($showDegreeFilter)
                                                            <span x-show="degreeFilter === 'all'" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-slate-900 text-white">All Degree Levels</span>
                                                            <span x-show="degreeFilter === 'PhD'" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-800 border border-purple-200">🎓 PhD Candidates</span>
                                                            <span x-show="degreeFilter === 'MSc'" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">📘 MSc Candidates</span>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">Automated</span>
                                                        @endif
                                                    </h4>
                                                    <p class="text-xs text-slate-500 font-medium">
                                                        @if($isSeminar)
                                                            <span>Batch assign presentation slots and Zoom room to selected students. Only candidates in the currently selected track will be scheduled with their dedicated panel.</span>
                                                        @elseif($showDegreeFilter)
                                                            <span>Batch assign presentation dates, time slots, and Zoom link. You can generate for <strong>PhD</strong>, <strong>MSc</strong>, or <strong>All</strong> candidates together.</span>
                                                        @else
                                                            <span>{{ $scheduledCount > 0 ? 'Select unscheduled students below and add them to the existing schedule. Fields are pre-filled from the current schedule.' : 'Batch assign defence dates, presentation time, and Zoom meeting link to selected students.' }}</span>
                                                        @endif
                                                    </p>

                                                    @if($showDegreeFilter && !$isSeminar)
                                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Target Degree:</span>
                                                        <div class="inline-flex rounded-xl bg-slate-100 p-0.5 border border-slate-200">
                                                            <button type="button" @click="degreeFilter = 'all'"
                                                                :class="degreeFilter === 'all' ? 'bg-white text-slate-800 shadow-sm font-black' : 'text-slate-600 hover:text-slate-900 font-bold'"
                                                                class="px-2.5 py-1 text-xs rounded-lg transition-all flex items-center gap-1 cursor-pointer">
                                                                All ({{ $template->studentMilestones->count() }})
                                                            </button>
                                                            <button type="button" @click="degreeFilter = 'PhD'"
                                                                :class="degreeFilter === 'PhD' ? 'bg-purple-600 text-white shadow-sm font-black' : 'text-slate-600 hover:text-purple-700 font-bold'"
                                                                class="px-2.5 py-1 text-xs rounded-lg transition-all flex items-center gap-1 cursor-pointer">
                                                                🎓 PhD Only ({{ $phdCount }})
                                                            </button>
                                                            <button type="button" @click="degreeFilter = 'MSc'"
                                                                :class="degreeFilter === 'MSc' ? 'bg-blue-600 text-white shadow-sm font-black' : 'text-slate-600 hover:text-blue-700 font-bold'"
                                                                class="px-2.5 py-1 text-xs rounded-lg transition-all flex items-center gap-1 cursor-pointer">
                                                                📘 MSc Only ({{ $mscCount }})
                                                            </button>
                                                        </div>
                                                        <button type="button" 
                                                            @click="let visibleRows = Array.from($root.querySelectorAll('tr[data-milestone-id]')).filter(row => row.style.display !== 'none'); selected = visibleRows.map(row => row.getAttribute('data-milestone-id')); selectAll = true;"
                                                            class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded-lg border border-emerald-200 transition flex items-center gap-1 cursor-pointer shadow-2xs">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                            <span>Select visible <span x-text="degreeFilter === 'all' ? 'All' : degreeFilter"></span> (<span x-text="degreeFilter === 'PhD' ? '{{ $phdCount }}' : (degreeFilter === 'MSc' ? '{{ $mscCount }}' : '{{ $template->studentMilestones->count() }}')"></span>)</span>
                                                        </button>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                 <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-colors"
                                                     :class="selected.length > 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200'">
                                                     <span class="w-2 h-2 rounded-full" :class="selected.length > 0 ? 'bg-emerald-600 animate-pulse' : 'bg-slate-400'"></span>
                                                     <span x-text="selected.length + ' student' + (selected.length === 1 ? '' : 's') + ' selected'"></span>
                                                 </span>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    <span>Start Date</span>
                                                    <span class="text-red-500">*</span>
                                                </label>
                                                <input type="date" name="start_date" required value="{{ $lastScheduledDate ?? '' }}"
                                                    class="w-full px-3 py-2 bg-white rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm transition">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span>Start Time</span>
                                                </label>
                                                <input type="time" name="start_time" value="{{ $existingTime ?? '09:00' }}" 
                                                    class="w-full px-3 py-2 bg-white rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm transition">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                    <span>Students / Day</span>
                                                    <span class="text-red-500">*</span>
                                                </label>
                                                <input type="number" name="students_per_day" value="5" min="1" required 
                                                    class="w-full px-3 py-2 bg-white rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm transition">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                        <span>Zoom / Meeting Link</span>
                                                    </span>
                                                    @if($isSeminar)
                                                        <span class="text-[10px] font-bold text-indigo-600">Track link</span>
                                                    @endif
                                                </label>
                                                <input type="url" name="meeting_link" value="{{ $existingLink ?? '' }}" placeholder="https://zoom.us/j/..." 
                                                    class="w-full px-3 py-2 bg-white rounded-xl border border-slate-200 text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm transition">
                                                @if($isSeminar)
                                                    <div class="mt-1.5 flex flex-wrap gap-1 text-[10px]">
                                                        @if($cyberLink)
                                                            <button type="button" @click="$el.closest('form').querySelector('input[name=meeting_link]').value = '{{ addslashes($cyberLink) }}'" class="px-2 py-0.5 rounded bg-cyan-50 text-cyan-700 hover:bg-cyan-100 border border-cyan-200 font-semibold cursor-pointer">Use Cyber Zoom</button>
                                                        @endif
                                                        @if($aiLink)
                                                            <button type="button" @click="$el.closest('form').querySelector('input[name=meeting_link]').value = '{{ addslashes($aiLink) }}'" class="px-2 py-0.5 rounded bg-violet-50 text-violet-700 hover:bg-violet-100 border border-violet-200 font-semibold cursor-pointer">Use AI Zoom</button>
                                                        @endif
                                                        @if($misLink)
                                                            <button type="button" @click="$el.closest('form').querySelector('input[name=meeting_link]').value = '{{ addslashes($misLink) }}'" class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 font-semibold cursor-pointer">Use MIS Zoom</button>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Advanced Schedule Machine Settings: Active Days & Excluded Dates --}}
                                        <div class="mt-4 pt-4 border-t border-slate-200/80 grid grid-cols-1 lg:grid-cols-2 gap-4">
                                            {{-- 1. Active Days of Week --}}
                                            <div class="bg-white/90 rounded-xl p-3.5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                                                <div>
                                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                                        <label class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                            </svg>
                                                            <span>Schedule Days of Week</span>
                                                        </label>
                                                        <div class="flex items-center gap-1 text-[10px]">
                                                            <span class="text-slate-400">Presets:</span>
                                                            <button type="button" @click="setDaysPreset('weekdays')" class="px-1.5 py-0.5 rounded font-semibold text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 transition cursor-pointer">Weekdays</button>
                                                            <span class="text-slate-300">•</span>
                                                            <button type="button" @click="setDaysPreset('all')" class="px-1.5 py-0.5 rounded font-semibold text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 transition cursor-pointer">All 7 Days</button>
                                                            <span class="text-slate-300">•</span>
                                                            <button type="button" @click="setDaysPreset('mon_thu')" class="px-1.5 py-0.5 rounded font-semibold text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 transition cursor-pointer">Mon–Thu</button>
                                                        </div>
                                                    </div>
                                                    <p class="text-[11px] text-slate-500 mb-2.5 leading-snug">
                                                        Click any day to include or remove it from the schedule (e.g. uncheck Fridays or add Sundays).
                                                    </p>
                                                </div>

                                                <div class="grid grid-cols-7 gap-1.5">
                                                    <template x-for="day in dayOptions" :key="day.key">
                                                        <button type="button" 
                                                            @click="toggleDay(day.key)"
                                                            :title="activeDays.includes(day.key) ? (day.full + ' is scheduled. Click to remove.') : (day.full + ' is skipped. Click to add.')"
                                                            class="py-2 px-1 rounded-xl text-xs font-bold flex flex-col items-center justify-center gap-0.5 transition-all border cursor-pointer"
                                                            :class="activeDays.includes(day.key) 
                                                                ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs hover:bg-emerald-700' 
                                                                : 'bg-slate-50 text-slate-400 border-slate-200 hover:border-slate-300 hover:text-slate-600 hover:bg-slate-100'">
                                                            <span x-text="day.label" class="tracking-tight"></span>
                                                            <span class="text-[9px] font-bold leading-none" :class="activeDays.includes(day.key) ? 'text-emerald-100' : 'text-slate-400'">
                                                                <span x-show="activeDays.includes(day.key)">✓</span>
                                                                <span x-show="!activeDays.includes(day.key)">✕</span>
                                                            </span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>

                                            {{-- 2. Exclude Specific Dates (e.g. 4th or 5th of October) --}}
                                            <div class="bg-white/90 rounded-xl p-3.5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                                                <div>
                                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                                        <label class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                            </svg>
                                                            <span>Exclude Specific Dates</span>
                                                        </label>
                                                        <template x-if="excludedDates.length > 0">
                                                            <button type="button" @click="excludedDates = []" class="text-[10px] font-semibold text-rose-600 hover:text-rose-800 hover:underline cursor-pointer">
                                                                Clear all (<span x-text="excludedDates.length"></span>)
                                                            </button>
                                                        </template>
                                                    </div>
                                                    <p class="text-[11px] text-slate-500 mb-2 leading-snug">
                                                        Select specific dates to remove from the schedule (e.g. 4th or 5th of October, holidays).
                                                    </p>

                                                    <div class="flex items-center gap-2 mb-2">
                                                        <input type="date" x-model="newExcludedDate" @change="addExcludedDate()" @keydown.enter.prevent="addExcludedDate()"
                                                            class="flex-1 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition">
                                                        <button type="button" @click="addExcludedDate()"
                                                            :disabled="!newExcludedDate"
                                                            class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-bold rounded-lg shadow-xs transition flex items-center gap-1 shrink-0 cursor-pointer">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                            <span>Exclude Date</span>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="min-h-[36px] max-h-24 overflow-y-auto">
                                                    <template x-if="excludedDates.length === 0">
                                                        <div class="flex items-center gap-1.5 text-[11px] text-slate-400 italic py-1">
                                                            <svg class="w-3.5 h-3.5 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            <span>No specific dates excluded. Use the picker above to add dates to skip.</span>
                                                        </div>
                                                    </template>
                                                    <template x-if="excludedDates.length > 0">
                                                        <div class="flex flex-wrap gap-1.5">
                                                            <template x-for="d in excludedDates" :key="d">
                                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/80 shadow-2xs">
                                                                    <svg class="w-3 h-3 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                                    </svg>
                                                                    <span x-text="formatDateChip(d)"></span>
                                                                    <button type="button" @click="removeExcludedDate(d)" class="text-rose-400 hover:text-rose-700 ml-0.5 rounded-full p-0.5 hover:bg-rose-100 transition cursor-pointer" title="Remove exclusion">
                                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                    </button>
                                                                </span>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 pt-3.5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                                            <div class="text-xs text-slate-500 font-medium flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center gap-1 text-slate-700 font-bold">
                                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Active Days:
                                                </span>
                                                <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100" 
                                                    x-text="dayOptions.filter(d => activeDays.includes(d.key)).map(d => d.label).join(', ')"></span>
                                                <template x-if="excludedDates.length > 0">
                                                    <span class="inline-flex items-center gap-1 text-rose-700 font-bold bg-rose-50 px-2 py-0.5 rounded border border-rose-100">
                                                        <span x-text="excludedDates.length"></span> date(s) skipped
                                                    </span>
                                                </template>
                                                <span class="text-slate-400 text-[11px]">• Only supervisor-approved candidates will be scheduled</span>
                                            </div>
                                            <button type="submit" 
                                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-emerald-600/20 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none cursor-pointer" 
                                                :disabled="selected.length === 0 || activeDays.length === 0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span>Generate Schedule</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                @endif

                                {{-- Student List (hidden until "View Student Details" is clicked) --}}
                                <div x-show="showStudents" x-cloak x-transition>
                                    @if($template->studentMilestones->count() > 0)
                                    {{-- Search and Filter & Actions Toolbar --}}
                                    <div class="mb-4 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                                        <div class="flex flex-col sm:flex-row gap-3 flex-1">
                                            <div class="relative flex-1">
                                                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                                <input x-ref="searchInput" x-model="search" type="text" placeholder="Search by name or matric number..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                            </div>
                                            <div class="w-full sm:w-64">
                                                <select x-model="cohortFilter" class="w-full py-2.5 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                                    <option value="">All Cohorts (Sets/Batches)</option>
                                                    @foreach($cohorts as $cohort)
                                                        <option value="{{ $cohort->id }}">{{ $cohort->name }} ({{ $cohort->intake_year }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if($showDegreeFilter && !$isSeminar)
                                            <div class="w-full sm:w-56">
                                                <select x-model="degreeFilter" class="w-full py-2.5 px-4 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                                    <option value="all">All Degree Levels</option>
                                                    <option value="PhD">🎓 PhD Candidates ({{ $phdCount }})</option>
                                                    <option value="MSc">📘 MSc Candidates ({{ $mscCount }})</option>
                                                </select>
                                            </div>
                                            @endif
                                        </div>

                                        {{-- Message & Student Actions --}}
                                        <div class="flex items-center flex-wrap gap-2">
                                            {{-- Message Selected Candidates --}}
                                            <button type="button" 
                                                @click="openMessageModal('selected')"
                                                x-show="selected.length > 0"
                                                x-cloak
                                                class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-sm transition active:scale-95 whitespace-nowrap cursor-pointer">
                                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                                <span x-text="'Message Selected (' + selected.length + ')'"></span>
                                            </button>

                                            {{-- Message All Milestone Candidates --}}
                                            <button type="button" 
                                                @click="openMessageModal('all')"
                                                class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition active:scale-95 whitespace-nowrap cursor-pointer border border-slate-200"
                                                title="Send an inbox message or email to all students on this milestone">
                                                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                                <span>Message All ({{ $template->studentMilestones->count() }})</span>
                                            </button>

                                            @if(!isset($isCoordinator) || !$isCoordinator)
                                            <div x-show="selected.length > 0" x-cloak class="flex items-center">
                                                <form action="{{ route('admin.students.bulk-destroy') }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <template x-for="id in selected">
                                                        <input type="hidden" name="milestone_ids[]" :value="id">
                                                    </template>
                                                    <button type="submit" 
                                                        data-confirm="Are you sure you want to permanently delete the selected student record(s) from the system? All their submissions, grades, milestones, and user accounts will be permanently removed. This action cannot be undone."
                                                        data-confirm-title="Delete Selected Students"
                                                        data-confirm-type="danger"
                                                        data-confirm-btn="Delete Selected Students"
                                                        class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-sm transition active:scale-95 whitespace-nowrap cursor-pointer">
                                                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        <span x-text="'Delete Selected (' + selected.length + ')'"></span>
                                                    </button>
                                                </form>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="overflow-hidden border border-slate-100 rounded-xl">
                                        <div class="max-h-[400px] overflow-y-auto">
                                            <table class="w-full text-left text-sm">
                                                <thead class="bg-slate-50 border-b border-slate-100 sticky top-0 z-10">
                                                    <tr>
                                                        @if(!isset($isCoordinator) || !$isCoordinator)
                                                        <th class="px-4 py-3 w-10">
                                                            <input type="checkbox" x-model="selectAll" 
                                                                @change="if(selectAll) { 
                                                                    let visibleRows = Array.from($root.querySelectorAll('tr[data-milestone-id]')).filter(row => row.style.display !== 'none'); 
                                                                    selected = visibleRows.map(row => row.getAttribute('data-milestone-id')); 
                                                                } else { selected = []; }" 
                                                                class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                                                        </th>
                                                        @endif
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Student</th>
                                                        @if($template->slug === 'seminar_as_a_course')
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Discipline Track</th>
                                                        @endif
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Status</th>
                                                        <th class="px-4 py-3 font-semibold text-slate-700">{{ $template->allow_defence_date ? 'Schedule & Meeting' : ($template->slug === 'supervisors_assigned' ? 'Assigned Supervisors' : 'Details') }}</th>
                                                        @if(in_array($template->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2', 'internal_defence']))
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Examiner(s)</th>
                                                        @if($template->slug === 'seminar_as_a_course')
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Presentation (PDF)</th>
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Score</th>
                                                        @else
                                                        <th class="px-4 py-3 font-semibold text-slate-700">Grade / Result</th>
                                                        @endif
                                                        @endif
                                                        <th class="px-4 py-3 w-16 text-right font-semibold text-slate-700"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100">
                                                    @foreach($template->studentMilestones as $sm)
                                                        @php
                                                            $latestSub = $sm->submissions->sortByDesc('created_at')->first();
                                                            $hasAcceptedUpload = $latestSub && $latestSub->feedback && $latestSub->feedback->decision === 'approved';
                                                            $hasRejectedUpload = $latestSub && $latestSub->feedback && $latestSub->feedback->decision === 'revision_required';
                                                            $hasUpload = $latestSub !== null;

                                                            if ($sm->status === 'approved') {
                                                                $detailedStatus = 'Approved';
                                                                $statusColor = 'bg-emerald-100 text-emerald-800 border border-emerald-200';
                                                            } elseif ($sm->status === 'revision_required' || $hasRejectedUpload) {
                                                                $detailedStatus = 'Revision Required';
                                                                $statusColor = 'bg-rose-100 text-rose-800 border border-rose-200';
                                                            } elseif ($sm->is_supervisor_approved) {
                                                                $detailedStatus = 'Approved by Supervisor';
                                                                $statusColor = 'bg-emerald-100 text-emerald-800 border border-emerald-200';
                                                            } elseif ($sm->status === 'submitted' || $hasUpload) {
                                                                $detailedStatus = 'Doc Uploaded (Pending Review)';
                                                                $statusColor = 'bg-blue-100 text-blue-700 border border-blue-200';
                                                            } elseif ($sm->status === 'partially_approved') {
                                                                $detailedStatus = 'Partially Cleared';
                                                                $statusColor = 'bg-indigo-100 text-indigo-700';
                                                            } else {
                                                                $detailedStatus = 'Awaiting Submission';
                                                                $statusColor = 'bg-slate-100 text-slate-600';
                                                            }
                                                            
                                                            $rowEventType = $template->defence_type ?? match($template->slug) {
                                                                'seminar_as_a_course' => 'seminar',
                                                                'proposal_defence' => 'proposal',
                                                                'progress_report_1' => 'progress_report_1',
                                                                'progress_report_2' => 'progress_report_2',
                                                                'internal_defence' => 'internal',
                                                                default => 'first_seminar',
                                                            };
                                                            $event = $sm->thesis ? current($sm->thesis->defenceEvents->where('type', $rowEventType)->all()) : null;
                                                            $rowExaminers = $event ? $event->panelMembers->where('role', 'examiner') : collect();
                                                            if ($rowExaminers->isEmpty() && $template->slug === 'seminar_as_a_course') {
                                                                $trackEx = match($sm->discipline_group) {
                                                                    'cybersecurity' => $cyberExaminers,
                                                                    'artificial_intelligence' => $aiExaminers,
                                                                    'management_information_systems' => $misExaminers,
                                                                    default => collect(),
                                                                };
                                                                if ($trackEx->isNotEmpty()) {
                                                                    $rowExaminers = $trackEx->map(fn($u) => (object)[
                                                                        'user' => $u,
                                                                        'user_id' => $u->id,
                                                                        'is_present' => false,
                                                                    ]);
                                                                }
                                                            } elseif ($rowExaminers->isEmpty() && isset($currentExaminers) && $currentExaminers->isNotEmpty()) {
                                                                $rowExaminers = $currentExaminers->map(fn($u) => (object)[
                                                                    'user' => $u,
                                                                    'user_id' => $u->id,
                                                                    'is_present' => false,
                                                                ]);
                                                            }
                                                            $avgScore = null;
                                                            $passCount = 0;
                                                            $failCount = 0;
                                                            $gradingOutcome = null;

                                                            if ($event && $event->evaluations->count() > 0) {
                                                                $total = 0;
                                                                $count = 0;
                                                                foreach($event->evaluations as $eval) {
                                                                    $verdict = strtolower($eval->verdict ?? $eval->recommendation ?? '');
                                                                    if ($verdict === 'pass') {
                                                                        $passCount++;
                                                                    } elseif ($verdict === 'fail') {
                                                                        $failCount++;
                                                                    }

                                                                    if (isset($eval->score['total'])) {
                                                                        $total += $eval->score['total'];
                                                                        $count++;
                                                                    }
                                                                }
                                                                if ($count > 0) {
                                                                    $avgScore = round($total / $count, 1);
                                                                }
                                                                if (($passCount + $failCount) > 0) {
                                                                    $gradingOutcome = ($passCount >= $failCount) ? 'pass' : 'fail';
                                                                }
                                                            }
                                                            $studentName = $sm->thesis->student->user->name ?? 'N/A';
                                                            $matricNo = $sm->thesis->student->student_id_number ?? 'N/A';
                                                            $cohortId = $sm->thesis->student->cohort_id ?? '';
                                                            $rawDegree = $sm->thesis?->student?->level?->name 
                                                                ?? $sm->thesis?->student?->program?->degree_type 
                                                                ?? ($sm->thesis?->student?->program?->name ?? '');
                                                            $rowDegreeType = str_contains(strtolower($rawDegree), 'phd') || str_contains(strtolower($rawDegree), 'doctor') ? 'PhD' : 'MSc';
                                                        @endphp
                                                        <tr data-milestone-id="{{ $sm->id }}" class="hover:bg-slate-50/50 transition-colors" 
                                                            x-show="(trackFilter === 'all' || trackFilter === '{{ $sm->discipline_group }}') && (degreeFilter === 'all' || degreeFilter === '{{ $rowDegreeType }}') && (cohortFilter === '' || cohortFilter == '{{ $cohortId }}') && (!search || '{{ strtolower($studentName) }}'.includes(search.toLowerCase()) || '{{ strtolower($matricNo) }}'.includes(search.toLowerCase()))">
                                                            @if(!isset($isCoordinator) || !$isCoordinator)
                                                            <td class="px-4 py-3">
                                                                <input type="checkbox" :value="'{{ $sm->id }}'" x-model="selected" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                                                            </td>
                                                            @endif
                                                            <td class="px-4 py-3">
                                                                <a href="{{ route('admin.students.show', $sm->thesis->student->id) }}" class="font-medium text-brand-600 hover:text-brand-700 hover:underline block">
                                                                    {{ $studentName }}
                                                                </a>
                                                                <div class="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5">
                                                                    <span>{{ $matricNo }}</span>
                                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black {{ $rowDegreeType === 'PhD' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-blue-100 text-blue-700 border border-blue-200' }}">
                                                                        {{ $rowDegreeType }}
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            @if($template->slug === 'seminar_as_a_course')
                                                            <td class="px-4 py-3">
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider
                                                                    {{ $sm->discipline_group === 'cybersecurity' ? 'bg-cyan-50 text-cyan-800 border border-cyan-200' : ($sm->discipline_group === 'artificial_intelligence' ? 'bg-violet-50 text-violet-800 border border-violet-200' : 'bg-amber-50 text-amber-800 border border-amber-200') }}">
                                                                    @if($sm->discipline_group === 'cybersecurity') 🛡️ Cyber
                                                                    @elseif($sm->discipline_group === 'artificial_intelligence') 🤖 AI
                                                                    @else 📊 MIS
                                                                    @endif
                                                                </span>
                                                            </td>
                                                            @endif
                                                            <td class="px-4 py-3">
                                                                @if($latestSub && $latestSub->file_url)
                                                                    <button type="button" 
                                                                        @click.prevent="$dispatch('open-document-preview', { 
                                                                            url: '{{ Storage::url($latestSub->file_url) }}', 
                                                                            title: '{{ addslashes($studentName) }} - {{ addslashes($template->name) }} (v.0{{ $latestSub->version }})',
                                                                            type: '{{ str_ends_with(strtolower($latestSub->file_url), '.pdf') ? 'pdf' : (in_array(pathinfo($latestSub->file_url, PATHINFO_EXTENSION), ['jpg','jpeg','png','webp']) ? 'image' : 'other') }}'
                                                                        })"
                                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold {{ $statusColor }} cursor-pointer hover:opacity-90 hover:shadow-sm active:scale-95 transition-all text-left group"
                                                                        title="Click to preview uploaded document">
                                                                        <span>{{ $detailedStatus }}</span>
                                                                        <svg class="w-3.5 h-3.5 opacity-70 group-hover:opacity-100 transition-opacity shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                        </svg>
                                                                    </button>
                                                                @else
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-bold {{ $statusColor }}">
                                                                        {{ $detailedStatus }}
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-3 text-slate-600 text-xs">
                                                                @if($template->slug === 'supervisors_assigned')
                                                                    @php
                                                                        $assignedSups = $sm->thesis->assignments->where('status', 'active');
                                                                    @endphp
                                                                    @if($assignedSups->count() > 0)
                                                                        <div class="text-xs">
                                                                            <span class="font-bold text-slate-800">{{ $assignedSups->count() }} Allocated</span>
                                                                            <div class="text-[11px] text-slate-500 truncate max-w-[200px]" title="{{ $assignedSups->map(fn($a) => ($a->role === 'primary' ? '★ ' : '') . ($a->supervisor?->user?->name ?? 'Supervisor'))->implode(', ') }}">
                                                                                {{ $assignedSups->map(fn($a) => ($a->role === 'primary' ? '★ ' : '') . ($a->supervisor?->user?->name ?? 'Supervisor'))->implode(', ') }}
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                                                            Awaiting Allocation
                                                                        </span>
                                                                    @endif
                                                                @elseif($sm->defence_date)
                                                                    <div class="flex items-center justify-between gap-1">
                                                                        <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                                                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                                            </svg>
                                                                            <span>{{ \Carbon\Carbon::parse($sm->defence_date)->format('M d, Y') }}</span>
                                                                        </div>
                                                                        @if(!isset($isCoordinator) || !$isCoordinator)
                                                                            <button type="button" 
                                                                                @click="slotModalOpen = true; slotStudentId = '{{ $sm->id }}'; slotStudentName = '{{ addslashes($studentName) }}'; slotAction = 'schedule'; slotDate = '{{ \Carbon\Carbon::parse($sm->defence_date)->format('Y-m-d') }}'; slotTime = '{{ $sm->defence_time ? \Carbon\Carbon::parse($sm->defence_time)->format('H:i') : '' }}'; slotLink = '{{ addslashes($sm->meeting_link ?? '') }}'; slotTrack = '{{ $sm->discipline_group ?? 'cybersecurity' }}'; slotFormUrl = '{{ route('admin.milestone-templates.update-student-slot', [$template->id, $sm->id]) }}'"
                                                                                title="Reschedule / Edit slot or remove student"
                                                                                class="p-1 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded transition-colors">
                                                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                                                </svg>
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                    @if($sm->defence_time)
                                                                        <div class="text-[11px] text-emerald-700 font-semibold mt-0.5 flex items-center gap-1 pl-5">
                                                                            <svg class="w-3 h-3 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                            </svg>
                                                                            <span>{{ \Carbon\Carbon::parse($sm->defence_time)->format('g:i A') }}</span>
                                                                        </div>
                                                                    @endif
                                                                    @if($sm->meeting_link)
                                                                        <div class="mt-1 pl-5">
                                                                            <a href="{{ $sm->meeting_link }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded text-[10px] font-bold transition-colors">
                                                                                <svg class="w-3 h-3 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                                                </svg>
                                                                                <span>Zoom Link</span>
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                @else
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-400">
                                                                            Not scheduled
                                                                        </span>
                                                                        @if(!isset($isCoordinator) || !$isCoordinator)
                                                                            <button type="button" 
                                                                                @click="slotModalOpen = true; slotStudentId = '{{ $sm->id }}'; slotStudentName = '{{ addslashes($studentName) }}'; slotAction = 'schedule'; slotDate = '{{ $lastScheduledDate ?? date('Y-m-d') }}'; slotTime = '{{ $existingTime ?? '10:00' }}'; slotLink = '{{ addslashes($existingLink ?? '') }}'; slotTrack = '{{ $sm->discipline_group ?? 'cybersecurity' }}'; slotFormUrl = '{{ route('admin.milestone-templates.update-student-slot', [$template->id, $sm->id]) }}'"
                                                                                class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded text-[10px] font-black uppercase tracking-wider transition-colors shadow-2xs">
                                                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                                                                <span>Add to Day</span>
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            @if(in_array($template->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2', 'internal_defence']))
                                                            <td class="px-4 py-3 text-xs">
                                                                @php
                                                                    $assignedInternalEx = ($template->slug === 'internal_defence') ? $sm->thesis?->internalExaminer : null;
                                                                @endphp
                                                                @if($assignedInternalEx || $rowExaminers->count() > 0)
                                                                    <div class="flex flex-col gap-1">
                                                                        @if($assignedInternalEx)
                                                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-800 text-[11px] font-bold border border-indigo-200 max-w-[170px]" title="Institutional Internal Examiner">
                                                                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                                                                <span class="truncate">⭐ {{ $assignedInternalEx->user->name ?? 'Internal Examiner' }}</span>
                                                                            </span>
                                                                        @endif
                                                                        @foreach($rowExaminers as $ex)
                                                                            @if(!$assignedInternalEx || $ex->user_id !== $assignedInternalEx->user_id)
                                                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-purple-50 text-purple-800 text-[11px] font-bold border border-purple-100 max-w-[170px]" title="{{ $ex->is_present ? 'Present (Graded candidate)' : 'Assigned (Pending grading)' }}">
                                                                                <span class="w-1.5 h-1.5 rounded-full {{ $ex->is_present ? 'bg-emerald-500' : 'bg-purple-300' }}"></span>
                                                                                <span class="truncate">{{ $ex->user?->name ?? 'Examiner' }}</span>
                                                                                @if($ex->is_present)
                                                                                    <span class="text-[9px] text-emerald-600 font-extrabold uppercase shrink-0">Present</span>
                                                                                @endif
                                                                            </span>
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                                                        Unassigned
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            @if($template->slug === 'seminar_as_a_course')
                                                            <td class="px-4 py-3 text-xs">
                                                                @if($sm->submissions->count() > 0)
                                                                    <a href="{{ Storage::url($sm->submissions->first()->file_url) }}" target="_blank" class="text-indigo-600 hover:underline">Download</a>
                                                                @else
                                                                    <span class="text-slate-400">Not uploaded</span>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-3 text-xs font-bold">
                                                                @if($avgScore !== null)
                                                                    <span class="text-emerald-600">{{ $avgScore }}</span>
                                                                @else
                                                                    <span class="text-slate-400">-</span>
                                                                @endif
                                                            </td>
                                                            @else
                                                            <td class="px-4 py-3 text-xs">
                                                                @if($gradingOutcome === 'pass')
                                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                                        <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                        <span>PASS</span>
                                                                        <span class="text-[10px] text-emerald-700 font-bold ml-1">({{ $passCount }}/{{ $passCount + $failCount }})</span>
                                                                    </span>
                                                                @elseif($gradingOutcome === 'fail')
                                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-black uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">
                                                                        <svg class="w-3 h-3 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                        <span>FAIL</span>
                                                                        <span class="text-[10px] text-rose-700 font-bold ml-1">({{ $failCount }}/{{ $passCount + $failCount }})</span>
                                                                    </span>
                                                                @else
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-500">
                                                                        Awaiting Grade
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            @endif
                                                            @endif
                                                            <td class="px-4 py-3 text-right">
                                                                <div x-data="{ menuOpen: false }" class="relative inline-block text-left">
                                                                    <button type="button" @click.prevent.stop="menuOpen = !menuOpen" class="p-1.5 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-colors focus:outline-none">
                                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                                                    </button>
                                                                    <div x-show="menuOpen" @click.outside="menuOpen = false" x-cloak class="absolute right-8 top-0 w-56 bg-white rounded-xl shadow-xl border border-slate-100 z-[60] overflow-hidden text-left" style="display: none;">
                                                                        <div class="p-1.5 border-b border-slate-100">
                                                                            <button type="button" 
                                                                                @click="menuOpen = false; openSingleMessageModal('{{ $sm->id }}', '{{ addslashes($studentName) }}', '{{ addslashes($matricNo) }}')"
                                                                                class="w-full text-left px-3 py-2 text-xs font-bold text-slate-700 hover:bg-brand-50 hover:text-brand-700 rounded-lg transition-colors flex items-center gap-2">
                                                                                <svg class="w-3.5 h-3.5 text-brand-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                                                </svg>
                                                                                <span>Send Inbox / Email</span>
                                                                            </button>
                                                                        </div>
                                                                        @if((!isset($isCoordinator) || !$isCoordinator) && $sm->status !== 'approved')
                                                                            <div class="p-1.5 border-b border-slate-100 space-y-1">
                                                                                @if(!empty($sm->defence_date))
                                                                                <button type="button" 
                                                                                    @click="menuOpen = false; slotModalOpen = true; slotStudentId = '{{ $sm->id }}'; slotStudentName = '{{ addslashes($studentName) }}'; slotAction = 'schedule'; slotDate = '{{ \Carbon\Carbon::parse($sm->defence_date)->format('Y-m-d') }}'; slotTime = '{{ $sm->defence_time ? \Carbon\Carbon::parse($sm->defence_time)->format('H:i') : '' }}'; slotLink = '{{ addslashes($sm->meeting_link ?? '') }}'; slotTrack = '{{ $sm->discipline_group ?? 'cybersecurity' }}'; slotFormUrl = '{{ route('admin.milestone-templates.update-student-slot', [$template->id, $sm->id]) }}'"
                                                                                    class="w-full text-left px-3 py-2 text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors flex items-center gap-2">
                                                                                    <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                                    </svg>
                                                                                    <span>Reschedule Slot</span>
                                                                                </button>
                                                                                <form action="{{ route('admin.milestone-templates.update-student-slot', [$template->id, $sm->id]) }}" method="POST">
                                                                                    @csrf
                                                                                    <input type="hidden" name="action" value="remove">
                                                                                    <button type="submit" 
                                                                                        data-confirm="Are you sure you want to remove {{ addslashes($studentName) }} from the presentation schedule? This will clear their presentation date, time, and link."
                                                                                        data-confirm-title="Remove from Schedule"
                                                                                        data-confirm-type="danger"
                                                                                        data-confirm-btn="Remove Student"
                                                                                        class="w-full text-left px-3 py-2 text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors flex items-center gap-2">
                                                                                        <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                                        </svg>
                                                                                        <span>Remove from Schedule</span>
                                                                                    </button>
                                                                                </form>
                                                                                @else
                                                                                <button type="button" 
                                                                                    @click="menuOpen = false; slotModalOpen = true; slotStudentId = '{{ $sm->id }}'; slotStudentName = '{{ addslashes($studentName) }}'; slotAction = 'schedule'; slotDate = '{{ $lastScheduledDate ?? date('Y-m-d') }}'; slotTime = '{{ $existingTime ?? '10:00' }}'; slotLink = '{{ addslashes($existingLink ?? '') }}'; slotTrack = '{{ $sm->discipline_group ?? 'cybersecurity' }}'; slotFormUrl = '{{ route('admin.milestone-templates.update-student-slot', [$template->id, $sm->id]) }}'"
                                                                                    class="w-full text-left px-3 py-2 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors flex items-center gap-2">
                                                                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                                                                    </svg>
                                                                                    <span>Schedule Slot</span>
                                                                                </button>
                                                                                @endif
                                                                                @if(in_array($template->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2', 'internal_defence']))
                                                                                @php
                                                                                    $assignUrl = (auth()->user()->hasRole('Program Coordinator') && !auth()->user()->hasRole('Admin')) ? route('coordinator.milestone-templates.assign-examiner', $sm->id) : route('admin.milestone-templates.assign-examiner', $sm->id);
                                                                                    $currentExaminerUserIds = $rowExaminers->pluck('user_id')->all();
                                                                                    $currentAssignedProfileIds = $supervisors->whereIn('user_id', $currentExaminerUserIds)->pluck('id')->values()->all();
                                                                                    $currentInternalExaminerProfileId = $sm->thesis?->internal_examiner_profile_id ?? '';
                                                                                @endphp
                                                                                <button type="button" @click="menuOpen = false; openAssignModal('{{ addslashes($studentName) }}', '{{ $assignUrl }}', {{ json_encode($currentAssignedProfileIds) }}, '{{ $currentInternalExaminerProfileId }}')" class="w-full text-left px-3 py-2 text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors flex items-center gap-2">
                                                                                    <svg class="w-3.5 h-3.5 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                                                                    </svg>
                                                                                    <span>Assign Examiner(s)</span>
                                                                                </button>
                                                                                @endif
                                                                                @if(!empty($sm->defence_date) || in_array($template->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2', 'internal_defence']))
                                                                                <form action="{{ route('milestones.end_presentation', $sm) }}" method="POST">
                                                                                    @csrf
                                                                                    <button type="submit" 
                                                                                        data-confirm="Are you sure you want to end the presentation for {{ addslashes($studentName) }}? If all requirements are met, the milestone will be approved and the student advanced."
                                                                                        data-confirm-title="End Presentation Session"
                                                                                        data-confirm-type="success"
                                                                                        data-confirm-btn="End Presentation"
                                                                                        class="w-full text-left px-3 py-2 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors flex items-center gap-2">
                                                                                        <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                                        </svg>
                                                                                        <span>End Presentation</span>
                                                                                    </button>
                                                                                </form>
                                                                                @endif
                                                                                <form action="{{ route('milestones.review.update', $sm) }}" method="POST">
                                                                                    @csrf
                                                                                    @method('PATCH')
                                                                                    <input type="hidden" name="decision" value="approved">
                                                                                    <input type="hidden" name="remarks" value="Approved by Administrator from Milestone Template Management.">
                                                                                    <button type="submit" 
                                                                                        data-confirm="Are you sure you want to officially approve this milestone for {{ addslashes($studentName) }} and advance them to the next stage?"
                                                                                        data-confirm-title="Approve Milestone & Advance"
                                                                                        data-confirm-type="success"
                                                                                        data-confirm-btn="Approve & Advance"
                                                                                        class="w-full text-left px-3 py-2 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors flex items-center gap-2">
                                                                                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                                                        </svg>
                                                                                        <span>Approve & Advance</span>
                                                                                    </button>
                                                                                </form>
                                                                            </div>
                                                                        @endif
                                                                        <div class="px-3 py-2 bg-slate-50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                                                            Jump to Milestone
                                                                        </div>
                                                                        <div class="max-h-48 overflow-y-auto p-1">
                                                                            @foreach($templates as $t)
                                                                                @php
                                                                                    $targetSm = $sm->thesis->milestones->where('milestone_template_id', $t->id)->first();
                                                                                @endphp
                                                                                @if($targetSm)
                                                                                <button type="button" onclick="jumpMilestone('{{ $sm->thesis->student->id }}', '{{ $targetSm->id }}')" class="w-full text-left px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-brand-50 hover:text-brand-600 rounded-lg transition-colors flex items-center justify-between group">
                                                                                    <span>{{ $t->name }}</span>
                                                                                    @if($t->id === $template->id)
                                                                                        <svg class="w-3 h-3 text-brand-500 opacity-50 shrink-0 ml-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                                                    @endif
                                                                                </button>
                                                                                @endif
                                                                            @endforeach
                                                                        </div>
                                                                        @if((!isset($isCoordinator) || !$isCoordinator) && $sm->thesis && $sm->thesis->student)
                                                                        <div class="p-1.5 border-t border-slate-100 bg-rose-50/20">
                                                                            <form action="{{ route('admin.students.destroy', $sm->thesis->student->id) }}" method="POST">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit" 
                                                                                    data-confirm="Are you sure you want to permanently delete the student record for {{ addslashes($studentName) }} ({{ $matricNo }}) from the system? This will remove all their submissions, grades, milestone records, and user account. This action cannot be undone."
                                                                                    data-confirm-title="Delete Student Record"
                                                                                    data-confirm-type="danger"
                                                                                    data-confirm-btn="Delete Student"
                                                                                    class="w-full text-left px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 hover:text-rose-700 rounded-lg transition-colors flex items-center gap-2 group">
                                                                                    <svg class="w-3.5 h-3.5 text-rose-500 group-hover:text-rose-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                                    </svg>
                                                                                    <span>Delete Student Record</span>
                                                                                </button>
                                                                            </form>
                                                                        </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @else
                                    <div class="text-center py-8 text-slate-500 text-sm">
                                        No active students currently at this milestone.
                                    </div>
                                    @endif
                                </div>

                                {{-- Empty state when no students and panel is expanded --}}
                                @if($template->studentMilestones->count() === 0)
                                <div class="text-center py-8 text-slate-500 text-sm">
                                    No active students currently at this milestone.
                                </div>
                                @endif
                            </div>

                            {{-- Individual Student Slot Modal (Teleported to body) --}}
                            <template x-teleport="body">
                                <div x-show="slotModalOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @keydown.escape.window="slotModalOpen = false">
                                    <div @click.outside="slotModalOpen = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-100">
                                        <form :action="slotFormUrl" method="POST" x-ref="slotForm">
                                            @csrf
                                            <input type="hidden" name="action" :value="slotAction">

                                            {{-- Header --}}
                                            <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between gap-4 bg-slate-50/50">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h3 class="text-base font-black text-slate-900" x-text="slotDate ? 'Manage Student Slot' : 'Schedule Student Presentation'"></h3>
                                                        <p class="text-xs text-slate-500 mt-0.5 truncate max-w-[240px]" x-text="slotStudentName"></p>
                                                    </div>
                                                </div>
                                                <button type="button" @click="slotModalOpen = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 cursor-pointer">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>

                                            {{-- Form fields --}}
                                            <div class="p-6 space-y-4 text-xs text-left">
                                                @if($template->slug === 'seminar_as_a_course')
                                                    <div>
                                                        <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                                            Discipline Track / Group
                                                        </label>
                                                        <select name="discipline_group" x-model="slotTrack" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                            <option value="cybersecurity">🛡️ Cybersecurity</option>
                                                            <option value="artificial_intelligence">🤖 Artificial Intelligence</option>
                                                            <option value="management_information_systems">📊 Management Information Systems</option>
                                                        </select>
                                                        <p class="text-[10px] text-slate-400 mt-1">Assign or change the candidate's presentation group.</p>
                                                    </div>
                                                @endif

                                                <div>
                                                    <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                                        Presentation Date <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input type="date" name="defence_date" x-model="slotDate" :required="slotAction === 'schedule'" min="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                    <p class="text-[10px] text-slate-400 mt-1">Assign or move this candidate to any presentation date.</p>
                                                </div>

                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1.5">
                                                            Time (Optional)
                                                        </label>
                                                        <input type="time" name="defence_time" x-model="slotTime" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                    </div>
                                                    <div>
                                                        <div class="flex items-center justify-between mb-1.5">
                                                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider">
                                                                Meeting Link (Optional)
                                                            </label>
                                                            @if($template->slug === 'seminar_as_a_course' && !empty($template->metadata['track_zoom_links']))
                                                                <div class="flex items-center gap-1.5">
                                                                    <span class="text-[9px] text-slate-400 font-semibold">Prefill:</span>
                                                                    @foreach($template->metadata['track_zoom_links'] as $tKey => $zUrl)
                                                                        @if(!empty($zUrl))
                                                                            <button type="button" @click="slotLink = '{{ $zUrl }}'" class="text-[9px] font-bold text-indigo-600 hover:underline">
                                                                                @if($tKey === 'cybersecurity') 🛡️ Cyber
                                                                                @elseif($tKey === 'artificial_intelligence') 🤖 AI
                                                                                @else 📊 MIS
                                                                                @endif
                                                                            </button>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <input type="url" name="meeting_link" x-model="slotLink" placeholder="https://zoom.us/j/..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                                                    </div>
                                                </div>

                                                {{-- Notice --}}
                                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-600 text-[11px] leading-relaxed">
                                                    <span x-show="slotDate">Moving this student will update their defence event, clear their dashboard cache, and send them a slot notification.</span>
                                                    <span x-show="!slotDate">Scheduling this student creates a new defence event and notifies the candidate.</span>
                                                </div>
                                            </div>

                                            {{-- Footer --}}
                                            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between gap-3 bg-slate-50/50">
                                                <div>
                                                    <template x-if="slotDate">
                                                        <button type="button" @click="if(confirm('Are you sure you want to remove ' + slotStudentName + ' from the presentation schedule completely?')) { slotAction = 'remove'; $nextTick(() => $refs.slotForm.submit()); }"
                                                            class="px-3 py-2 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors cursor-pointer">
                                                            Remove from Schedule
                                                        </button>
                                                    </template>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" @click="slotModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 transition-colors cursor-pointer">Cancel</button>
                                                    <button type="submit" @click="slotAction = 'schedule'" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-md shadow-amber-600/20 transition-all font-semibold cursor-pointer">
                                                        <span x-text="slotDate ? 'Update Slot' : 'Schedule Student'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </template>

                            {{-- Individual Student Examiner Assignment Modal (Teleported to body) --}}
                            @if(in_array($template->slug, ['seminar_as_a_course', 'proposal_defence', 'progress_report_1', 'progress_report_2', 'internal_defence']))
                            <template x-teleport="body">
                                <div x-show="assignModalOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @keydown.escape.window="assignModalOpen = false">
                                    <div @click.outside="assignModalOpen = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4 text-left">
                                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                            <div>
                                                <h4 class="text-base font-black text-slate-900">Assign Examiner(s)</h4>
                                                <p class="text-xs text-slate-500 mt-0.5"><span x-text="assignStudentName"></span> — {{ $template->name }}</p>
                                            </div>
                                            <button type="button" @click="assignModalOpen = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 cursor-pointer">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                        <form :action="assignFormUrl" method="POST" class="space-y-4">
                                            @csrf
                                            @if($template->slug === 'internal_defence')
                                            <div>
                                                <label class="block text-xs font-bold text-slate-800 mb-1">
                                                    Institutional Internal Examiner <span class="text-indigo-600 font-semibold">(Milestone 6 Clearance)</span>
                                                </label>
                                                <p class="text-[11px] text-slate-500 mb-2">Select the examiner authorized to review post-defence revisions and give final institutional clearance.</p>
                                                <select name="internal_examiner_profile_id" x-model="assignInternalExaminerProfileId" class="w-full text-xs rounded-xl border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 py-2.5 bg-slate-50 font-medium">
                                                    <option value="">-- None / Select Internal Examiner --</option>
                                                    @foreach($internalExaminers as $iex)
                                                        <option value="{{ $iex->id }}">{{ $iex->user->name ?? 'Examiner' }} ({{ $iex->program->name ?? 'Institutional Examiner' }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Additional Defence Panel Members (Optional)</label>
                                                <div class="max-h-44 overflow-y-auto border border-slate-200 rounded-xl divide-y divide-slate-100 p-1">
                                                    @foreach($supervisors as $sup)
                                                        <label class="flex items-center gap-2.5 p-2 hover:bg-indigo-50/50 rounded-lg cursor-pointer">
                                                            <input type="checkbox" name="supervisor_profile_ids[]" value="{{ $sup->id }}" :value="{{ $sup->id }}" x-model="assignProfileIds" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                            <div class="text-xs">
                                                                <span class="font-bold text-slate-800">{{ $sup->user->name }}</span>
                                                                <span class="text-[10px] text-slate-400 block font-semibold">{{ $sup->programs->first()->code ?? 'Supervisor' }}</span>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @else
                                            <div>
                                                <label class="block text-xs font-bold text-slate-700 mb-2">Select Examiner(s)</label>
                                                <div class="max-h-56 overflow-y-auto border border-slate-200 rounded-xl divide-y divide-slate-100 p-1">
                                                    @foreach($supervisors as $sup)
                                                        <label class="flex items-center gap-2.5 p-2 hover:bg-indigo-50/50 rounded-lg cursor-pointer">
                                                            <input type="checkbox" name="supervisor_profile_ids[]" value="{{ $sup->id }}" :value="{{ $sup->id }}" x-model="assignProfileIds" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                            <div class="text-xs">
                                                                <span class="font-bold text-slate-800">{{ $sup->user->name }}</span>
                                                                <span class="text-[10px] text-slate-400 block font-semibold">{{ $sup->programs->first()->code ?? 'Supervisor' }}</span>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif
                                            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                                                <button type="button" @click="assignProfileIds = []; assignInternalExaminerProfileId = ''; $nextTick(() => $el.closest('form').submit())" class="text-xs font-bold text-rose-600 hover:text-rose-800 transition-colors">Clear All</button>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" @click="assignModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Cancel</button>
                                                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">Save Examiner(s)</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </template>
                            @endif

                            {{-- Send Inbox / Email Modal (Teleported to body) --}}
                            <template x-teleport="body">
                                <div x-show="messageModalOpen" x-cloak x-transition.opacity 
                                    class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" 
                                    @keydown.escape.window="messageModalOpen = false">
                                    <div @click.outside="messageModalOpen = false" 
                                        class="bg-white rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
                                        
                                        <form action="{{ (isset($isCoordinator) && $isCoordinator) ? route('coordinator.milestone-templates.broadcast-message', $template->id) : route('admin.milestone-templates.broadcast-message', $template->id) }}" 
                                            method="POST" 
                                            @submit="isSubmittingMessage = true">
                                            @csrf
                                            <input type="hidden" name="target" :value="messageTarget">
                                            <input type="hidden" name="student_milestone_id" :value="singleStudentId">
                                            <template x-if="messageTarget === 'selected'">
                                                <div>
                                                    <template x-for="id in selected" :key="id">
                                                        <input type="hidden" name="milestone_ids[]" :value="id">
                                                    </template>
                                                </div>
                                            </template>

                                            {{-- Header --}}
                                            <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between gap-4 bg-gradient-to-r from-brand-50/50 via-slate-50 to-white">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-2xl bg-brand-600 text-white flex items-center justify-center shadow-md shadow-brand-600/20 shrink-0">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h3 class="text-base font-black text-slate-900">
                                                            <span x-show="messageTarget === 'single'">Message Candidate</span>
                                                            <span x-show="messageTarget === 'selected'">Message Selected Candidates</span>
                                                            <span x-show="messageTarget === 'all'">Broadcast to All Milestone Candidates</span>
                                                        </h3>
                                                        <p class="text-xs text-slate-500 mt-0.5">
                                                            <span x-show="messageTarget === 'single'" x-text="singleStudentName + (singleStudentMatric ? ' (' + singleStudentMatric + ')' : '') + ' • {{ addslashes($template->name) }}'"></span>
                                                            <span x-show="messageTarget === 'selected'" x-text="selected.length + ' student(s) selected in {{ addslashes($template->name) }}'"></span>
                                                            <span x-show="messageTarget === 'all'">All {{ $template->studentMilestones->count() }} candidate(s) in {{ addslashes($template->name) }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <button type="button" @click="messageModalOpen = false" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition cursor-pointer">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>

                                            {{-- Modal Body --}}
                                            <div class="p-6 space-y-5 overflow-y-auto max-h-[calc(90vh-160px)] text-left">
                                                
                                                {{-- Target Mode Switcher (if not single student) --}}
                                                <template x-if="messageTarget !== 'single'">
                                                    <div>
                                                        <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider mb-2">Recipient Scope</label>
                                                        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
                                                            <button type="button" 
                                                                @click="messageTarget = 'selected'"
                                                                :disabled="selected.length === 0"
                                                                class="py-2 px-3 rounded-lg text-xs font-bold transition-all text-center flex items-center justify-center gap-1.5 cursor-pointer"
                                                                :class="messageTarget === 'selected' ? 'bg-white text-slate-900 shadow-xs' : (selected.length === 0 ? 'text-slate-400 cursor-not-allowed opacity-60' : 'text-slate-600 hover:text-slate-900')">
                                                                <span>Selected</span>
                                                                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="messageTarget === 'selected' ? 'bg-brand-100 text-brand-700' : 'bg-slate-200 text-slate-600'" x-text="selected.length"></span>
                                                            </button>
                                                            <button type="button" 
                                                                @click="messageTarget = 'all'"
                                                                class="py-2 px-3 rounded-lg text-xs font-bold transition-all text-center flex items-center justify-center gap-1.5 cursor-pointer"
                                                                :class="messageTarget === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                                                                <span>All Milestone Students</span>
                                                                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="messageTarget === 'all' ? 'bg-brand-100 text-brand-700' : 'bg-slate-200 text-slate-600'">{{ $template->studentMilestones->count() }}</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>

                                                {{-- Delivery Channels --}}
                                                <div>
                                                    <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider mb-2">Delivery Channels</label>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                        <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border transition-all cursor-pointer select-none"
                                                            :class="messageChannels.includes('inbox') ? 'border-brand-500 bg-brand-50/30 ring-1 ring-brand-500/20' : 'border-slate-200 bg-white hover:bg-slate-50'">
                                                            <input type="checkbox" name="channels[]" value="inbox" x-model="messageChannels" class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                                            <div class="text-xs">
                                                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                                    <span>📥 System Inbox</span>
                                                                </div>
                                                                <div class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                                                    Delivers to candidate's portal inbox with real-time notification.
                                                                </div>
                                                            </div>
                                                        </label>

                                                        <label class="relative flex items-start gap-3 p-3.5 rounded-2xl border transition-all cursor-pointer select-none"
                                                            :class="messageChannels.includes('email') ? 'border-brand-500 bg-brand-50/30 ring-1 ring-brand-500/20' : 'border-slate-200 bg-white hover:bg-slate-50'">
                                                            <input type="checkbox" name="channels[]" value="email" x-model="messageChannels" class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                                            <div class="text-xs">
                                                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                                    <span>✉️ Direct Email</span>
                                                                </div>
                                                                <div class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                                                    Sends official institutional email to registered email address.
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                    <template x-if="messageChannels.length === 0">
                                                        <p class="text-[11px] font-semibold text-rose-600 mt-1.5">⚠️ Please select at least one delivery channel (Inbox or Email).</p>
                                                    </template>
                                                </div>

                                                {{-- Priority / Type --}}
                                                <div>
                                                    <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider mb-2">Priority Level</label>
                                                    <div class="flex items-center gap-2">
                                                        <label class="flex-1 text-center py-2 px-3 rounded-xl border text-xs font-bold cursor-pointer transition-all"
                                                            :class="messagePriority === 'normal' ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'">
                                                            <input type="radio" name="priority" value="normal" x-model="messagePriority" class="sr-only">
                                                            <span>Normal Notice</span>
                                                        </label>
                                                        <label class="flex-1 text-center py-2 px-3 rounded-xl border text-xs font-bold cursor-pointer transition-all"
                                                            :class="messagePriority === 'important' ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'">
                                                            <input type="radio" name="priority" value="important" x-model="messagePriority" class="sr-only">
                                                            <span>⚡ Important</span>
                                                        </label>
                                                        <label class="flex-1 text-center py-2 px-3 rounded-xl border text-xs font-bold cursor-pointer transition-all"
                                                            :class="messagePriority === 'urgent' ? 'bg-rose-600 text-white border-rose-600 shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'">
                                                            <input type="radio" name="priority" value="urgent" x-model="messagePriority" class="sr-only">
                                                            <span>🚨 Urgent</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Subject --}}
                                                <div>
                                                    <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                                        Subject <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input type="text" name="subject" x-model="messageSubject" required 
                                                        placeholder="e.g. Schedule Update for {{ addslashes($template->name) }}"
                                                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                                </div>

                                                {{-- Message Body --}}
                                                <div>
                                                    <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider mb-1.5">
                                                        Message Content <span class="text-rose-500">*</span>
                                                    </label>
                                                    <textarea name="body" x-model="messageBody" rows="5" required 
                                                        placeholder="Type the message instructions, presentation details, deadline reminders, or requirements to be communicated to the student(s)..."
                                                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
                                                </div>
                                            </div>

                                            {{-- Footer --}}
                                            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between gap-3 bg-slate-50/50">
                                                <div class="text-[11px] text-slate-500 font-semibold flex items-center gap-1.5">
                                                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                                    <span x-show="messageTarget === 'single'">Dispatching to 1 candidate</span>
                                                    <span x-show="messageTarget === 'selected'" x-text="'Dispatching to ' + selected.length + ' selected candidate(s)'"></span>
                                                    <span x-show="messageTarget === 'all'">Dispatching to {{ $template->studentMilestones->count() }} candidate(s)</span>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <button type="button" @click="messageModalOpen = false" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 transition-colors cursor-pointer">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" 
                                                        :disabled="messageChannels.length === 0 || !messageSubject.trim() || !messageBody.trim() || isSubmittingMessage || (messageTarget === 'selected' && selected.length === 0)"
                                                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-md shadow-brand-600/20 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none transition-all cursor-pointer">
                                                        <template x-if="!isSubmittingMessage">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                            </svg>
                                                        </template>
                                                        <template x-if="isSubmittingMessage">
                                                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </template>
                                                        <span x-text="isSubmittingMessage ? 'Sending...' : 'Send Message'">Send Message</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </template>
                        </td>
                    </tr>
                    </tbody>

                    <tbody>@empty
                    <tr>
                        <td colspan="5" class="px-10 py-20 text-center bg-slate-50/50">
                            <div class="max-w-xs mx-auto">
                                <div class="w-16 h-16 bg-white rounded-3xl border border-slate-100 flex items-center justify-center mx-auto mb-6 shadow-sm">
                                    <svg class="w-8 h-8 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                                </div>
                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">No Milestones Found</h4>
                                <p class="text-xs text-slate-500 mt-2 font-medium">No milestone templates have been created yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </table>
        </div>
    </div>
</div>

<form id="global-jump-form" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="milestone_id" id="global-jump-target">
</form>

@push('scripts')
<script>
    async function jumpMilestone(studentId, targetSlug) {
        const ok = await window.confirmModal({
            title: 'Change Milestone Protocol',
            message: 'Are you sure you want to change this student\'s milestone? This will reset their progress for future milestones.',
            type: 'warning',
            confirmText: 'Change Milestone',
            cancelText: 'Cancel'
        });
        if (!ok) return;
        const form = document.getElementById('global-jump-form');
        form.action = '{{ url("admin/students") }}/' + studentId + '/set-milestone';
        document.getElementById('global-jump-target').value = targetSlug;
        form.submit();
    }
</script>
@endpush

@endsection

