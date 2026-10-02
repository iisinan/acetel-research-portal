@extends('layouts.admin')

@section('header', 'Bulk Defence Scheduling')

@section('content')
<div class="space-y-8 animate-in-up">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2 text-acetel-600">
                <div class="p-1 rounded bg-acetel-50">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <span class="text-[9px] font-black uppercase tracking-[0.3em]">Institutional Operations</span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Mass Defence Scheduler</h1>
            <p class="mt-1 text-sm font-medium text-slate-400">Select candidates across multiple cohorts, assign a defence date and send automated email notifications.</p>
        </div>
    </div>

    <form action="{{ route('admin.bulk-schedule.store') }}" method="POST" id="bsf">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            {{-- ── Left: Settings Panel ──────────────────────────────────── --}}
            <div class="lg:col-span-3 space-y-5">
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm sticky top-4">
                    <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-5 flex items-center gap-2">
                        <span class="w-2 h-2 bg-acetel-500 rounded-full"></span> Protocol Settings
                    </h3>

                    {{-- Defence Type --}}
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Defence Layer</label>
                    <select name="defence_type" id="defenceType" required
                        class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-xl p-3 mb-5 focus:ring-acetel-500 focus:border-acetel-500 transition-colors">
                        <option value="seminar">📚 Seminar as a Course</option>
                        <option value="proposal">📋 Proposal Defence</option>
                        <option value="progress_report_1">📝 Progress Report 1</option>
                        <option value="progress_report_2">📝 Progress Report 2</option>
                        <option value="internal">🏛️ Internal Defence</option>
                        <option value="external">🌐 External Defence</option>
                    </select>

                    {{-- Date --}}
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Scheduled Date</label>
                    <input type="date" name="defence_date" id="defenceDate" required
                        class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-xl p-3 mb-6 focus:ring-acetel-500 focus:border-acetel-500 shadow-inner transition-colors">

                    {{-- Counter --}}
                    <div class="bg-acetel-50 border border-acetel-100 rounded-xl p-4 mb-5 text-center">
                        <span class="block text-3xl font-black text-acetel-700" id="selCount">0</span>
                        <span class="text-[10px] font-black text-acetel-500 uppercase tracking-widest">Candidates Selected</span>
                    </div>

                    {{-- Submit --}}
                    <button type="button" onclick="submitBSF()"
                        class="w-full px-5 py-3.5 bg-acetel-600 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-slate-900 transition-all shadow-lg shadow-acetel-500/20 active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        Authorize Schedule
                    </button>
                    <p class="text-[9px] text-center text-slate-400 font-medium mt-3">Emails will be dispatched automatically on submission.</p>
                </div>
            </div>

            {{-- ── Right: Cohort + Student Tree ────────────────────────── --}}
            <div class="lg:col-span-9 space-y-5">

                {{-- Toolbar --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <div class="relative flex-1 w-full">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" id="bsSearch" oninput="filterBS()" placeholder="Search by name, matric or cohort code…"
                            class="bg-white border border-slate-200 text-slate-900 text-sm font-bold rounded-xl pl-10 pr-4 py-2.5 w-full shadow-sm focus:ring-acetel-500 focus:border-acetel-500 transition-colors">
                    </div>
                    <button type="button" onclick="selectAll()" class="shrink-0 px-4 py-2.5 text-[10px] font-black text-acetel-700 bg-acetel-50 border border-acetel-200 rounded-xl hover:bg-acetel-100 transition-colors uppercase tracking-widest">
                        Select All Visible
                    </button>
                    <button type="button" onclick="clearAll()" class="shrink-0 px-4 py-2.5 text-[10px] font-black text-slate-600 bg-slate-100 border border-slate-200 rounded-xl hover:bg-slate-200 transition-colors uppercase tracking-widest">
                        Clear All
                    </button>
                </div>

                {{-- Cohort Blocks (server-side rendered) --}}
                <div id="cohortList" class="space-y-4">
                    @forelse($cohorts as $cohort)
                        @php
                            $cohortStudents = $cohort->students->filter(fn($s) => $s->user && $s->thesis);
                        @endphp
                        <div class="cohort-block bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden"
                             data-cohort-name="{{ strtolower($cohort->name) }}"
                             data-cohort-code="{{ strtolower($cohort->code) }}">

                            {{-- Cohort Header --}}
                            <div class="px-5 py-4 bg-gradient-to-r from-slate-50 to-white border-b border-slate-100 flex items-center gap-4">
                                <input type="checkbox" class="cohort-toggle w-5 h-5 text-acetel-600 border-slate-300 rounded focus:ring-acetel-500 cursor-pointer"
                                       data-cohort="{{ $cohort->id }}" onchange="toggleCohort(this)">

                                <div class="flex-1 min-w-0 cursor-pointer" onclick="toggleExpand(this)">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-black text-slate-900">{{ $cohort->name }}</span>
                                        <span class="px-2 py-0.5 bg-acetel-100 text-acetel-700 text-[9px] font-black uppercase rounded-md">{{ $cohort->code }}</span>
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[9px] font-bold rounded-md">Intake {{ $cohort->intake_year ?? 'N/A' }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 ml-auto">
                                            {{ $cohortStudents->count() }} eligible / {{ $cohort->students->count() }} enrolled
                                        </span>
                                    </div>
                                </div>

                                <button type="button" onclick="toggleExpand(this.closest('.cohort-block').querySelector('.flex-1'))"
                                    class="shrink-0 w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 expand-btn transition-transform">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                            </div>

                            {{-- Students (initially hidden, expand on click) --}}
                            <div class="student-list hidden">
                                @if($cohortStudents->count() > 0)
                                    <div class="divide-y divide-slate-50">
                                        @foreach($cohortStudents as $student)
                                            <label class="student-row flex items-center gap-4 px-5 py-3 hover:bg-acetel-50/30 cursor-pointer transition-colors"
                                                   data-name="{{ strtolower($student->user->name) }}"
                                                   data-matric="{{ strtolower($student->student_id_number ?? '') }}">
                                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                                       class="student-cb w-4 h-4 text-acetel-600 border-slate-300 rounded focus:ring-acetel-500 cursor-pointer"
                                                       data-cohort="{{ $cohort->id }}"
                                                       onchange="updateCount()">

                                                <div class="w-9 h-9 rounded-xl bg-acetel-50 border border-acetel-100 flex items-center justify-center text-acetel-600 text-xs font-black shrink-0">
                                                    {{ strtoupper(substr($student->user->name, 0, 1)) }}
                                                </div>

                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-bold text-slate-900 truncate">{{ $student->user->name }}</p>
                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">{{ $student->student_id_number ?? 'No Matric' }}</span>
                                                        @if($student->program)
                                                            <span class="text-slate-300 text-[10px]">•</span>
                                                            <span class="text-[10px] text-slate-400">{{ $student->program->code }}</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <span class="shrink-0 px-2.5 py-1 bg-emerald-50 text-emerald-600 text-[8px] font-black uppercase rounded-lg border border-emerald-100">
                                                    Active Thesis
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="px-5 py-6 text-center">
                                        <p class="text-sm font-bold text-slate-400">No eligible students in this cohort.</p>
                                        <p class="text-xs text-slate-300 mt-1">Students must have an active thesis to be scheduled.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-12 bg-white rounded-2xl border border-slate-100 text-center shadow-sm">
                            <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 mx-auto mb-4">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                            <p class="text-base font-black text-slate-900">No Cohorts Found</p>
                            <p class="text-sm text-slate-400 mt-1">Create cohorts and enrol students first.</p>
                        </div>
                    @endforelse
                </div>

                {{-- No-results msg --}}
                <div id="noResults" class="hidden p-10 bg-white rounded-2xl border border-slate-100 text-center shadow-sm">
                    <p class="text-sm font-bold text-slate-900">No matching records</p>
                    <p class="text-xs text-slate-400 mt-1">Try different search terms.</p>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    /* ── helpers ─────────────────────────────── */
    function updateCount() {
        const n = document.querySelectorAll('.student-cb:checked').length;
        document.getElementById('selCount').textContent = n;
    }

    /* Toggle all students inside a cohort */
    function toggleCohort(cb) {
        const cohortId = cb.dataset.cohort;
        const block = cb.closest('.cohort-block');
        // auto-expand when selecting
        if (cb.checked) {
            block.querySelector('.student-list').classList.remove('hidden');
            block.querySelector('.expand-btn').classList.add('rotate-180');
        }
        block.querySelectorAll(`.student-cb[data-cohort="${cohortId}"]`).forEach(s => {
            if (!s.closest('.student-row').classList.contains('hidden')) s.checked = cb.checked;
        });
        updateCount();
    }

    /* Expand/collapse student list */
    function toggleExpand(el) {
        const block = el.closest('.cohort-block');
        const list = block.querySelector('.student-list');
        const btn = block.querySelector('.expand-btn');
        list.classList.toggle('hidden');
        btn.classList.toggle('rotate-180');
    }

    /* Live search filter */
    function filterBS() {
        const q = (document.getElementById('bsSearch').value || '').toLowerCase().trim();
        let anyVisible = false;

        document.querySelectorAll('.cohort-block').forEach(block => {
            const cohortName = block.dataset.cohortName || '';
            const cohortCode = block.dataset.cohortCode || '';
            const cohortMatch = !q || cohortName.includes(q) || cohortCode.includes(q);

            let studentVisible = 0;
            block.querySelectorAll('.student-row').forEach(row => {
                const nameMatch  = (row.dataset.name  || '').includes(q);
                const matricMatch = (row.dataset.matric || '').includes(q);
                const show = !q || cohortMatch || nameMatch || matricMatch;
                row.classList.toggle('hidden', !show);
                if (show) studentVisible++;
            });

            const showBlock = !q || cohortMatch || studentVisible > 0;
            block.classList.toggle('hidden', !showBlock);

            // auto-expand if query matches students inside
            if (q && studentVisible > 0) {
                block.querySelector('.student-list').classList.remove('hidden');
                block.querySelector('.expand-btn').classList.add('rotate-180');
            }

            if (showBlock) anyVisible = true;
        });

        document.getElementById('noResults').classList.toggle('hidden', anyVisible);
    }

    /* Select / clear all currently visible */
    function selectAll() {
        document.querySelectorAll('.student-row:not(.hidden) .student-cb').forEach(cb => cb.checked = true);
        updateCount();
    }

    function clearAll() {
        document.querySelectorAll('.student-cb').forEach(cb => cb.checked = false);
        document.querySelectorAll('.cohort-toggle').forEach(cb => cb.checked = false);
        updateCount();
    }

    /* Submit guard */
    function submitBSF() {
        const n = document.querySelectorAll('.student-cb:checked').length;
        if (n === 0) {
            alert('Please select at least one candidate before scheduling.');
            return;
        }
        const date = document.getElementById('defenceDate').value;
        if (!date) {
            alert('Please select a scheduled date.');
            return;
        }
        document.getElementById('bsf').submit();
    }
</script>
@endpush
