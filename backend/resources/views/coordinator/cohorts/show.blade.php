@extends('layouts.coordinator')

@section('header', 'Cohort Directory')

@section('content')
<div class="space-y-10 pb-10">
    <!-- Clean Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 sm:gap-6 animate-in-up">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <div class="p-1 rounded bg-acetel-50">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <span class="text-[9px] font-black uppercase tracking-[0.3em]">Cohort Details</span>
            </div>
            <h1 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight">{{ $cohort->name }}</h1>
            <p class="mt-1 sm:mt-2 text-xs sm:text-sm font-bold text-slate-400">
                Code: {{ $cohort->code }} | Intake: {{ $cohort->intake_year }}
            </p>
        </div>

        <div class="flex items-center gap-2 sm:gap-4 flex-wrap">
            <a href="{{ route('coordinator.cohorts.register-students', $cohort) }}" class="w-full sm:w-auto justify-center inline-flex items-center gap-2 px-4 sm:px-6 py-2.5 sm:py-3 bg-acetel-600 text-white rounded-xl sm:rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 transition-all shadow-lg shadow-acetel-500/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                <span>Register Students</span>
            </a>
            <button type="button" @click="$dispatch('open-bulk-modal')" class="w-full sm:w-auto justify-center inline-flex items-center gap-2 px-4 sm:px-6 py-2.5 sm:py-3 bg-emerald-600 text-white rounded-xl sm:rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-900 transition-all shadow-lg shadow-emerald-500/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                <span>Bulk Schedule</span>
            </button>
            <div class="w-full sm:w-auto px-4 sm:px-6 py-2.5 sm:py-3 bg-slate-50 border border-slate-100 rounded-xl sm:rounded-2xl flex items-center justify-between sm:justify-start gap-4">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Students</span>
                <span class="text-base sm:text-lg font-black text-slate-900 tracking-tighter">
                    {{ collect($categorizedStudents)->sum(fn($cat) => $cat['students']->count()) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Flattened Student Directory -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 animate-in-up" style="animation-delay: 100ms;">
        @php
            $allStudents = collect($categorizedStudents)->flatMap(fn($cat) => $cat['students'])->sortBy('profile.user.name');
        @endphp

        @forelse($allStudents as $studentData)
            @php $student = $studentData['profile']; @endphp
            <a href="{{ route('milestones.index', ['thesis_id' => $student->thesis->id]) }}" 
               class="group p-4 sm:p-6 bg-white rounded-2xl sm:rounded-[2rem] border border-slate-100 shadow-md hover:shadow-2xl hover:shadow-acetel-500/10 hover:-translate-y-1 transition-all">
                
                <div class="flex items-center gap-3.5 sm:gap-4 mb-4 sm:mb-5">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-acetel-600 group-hover:text-white transition-all text-xs font-black shadow-sm shrink-0">
                        {{ substr($student->user->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h6 class="text-xs sm:text-sm font-bold text-slate-900 truncate group-hover:text-acetel-600 transition-colors">{{ $student->user->name }}</h6>
                        <p class="text-[9px] font-bold text-slate-400 tracking-widest uppercase mt-0.5 truncate">{{ $student->student_id_number }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3.5 sm:pt-4 border-t border-slate-50">
                    <div class="flex flex-col min-w-0">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Current Milestone</span>
                        <span class="text-[10px] font-black text-slate-900 mt-0.5 sm:mt-1 uppercase truncate">{{ $studentData['milestone']->template->name }}</span>
                    </div>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-acetel-50 group-hover:text-acetel-600 transition-colors shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full py-16 sm:py-20 text-center rounded-2xl sm:rounded-[3rem] border-2 border-dashed border-slate-100 bg-slate-50/30 px-4">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-slate-200 mx-auto mb-4 shadow-sm">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
                <h4 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">No Students Found</h4>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 sm:mt-2">No students found in this cohort for your programs.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Bulk Scheduling Modal -->
<div x-data="{ 
        open: false, 
        target: 'all', 
        type: 'proposal',
        date: ''
    }" 
    @open-bulk-modal.window="open = true"
    x-show="open" 
    class="fixed inset-0 z-50 overflow-y-auto" 
    style="display: none;">
    
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 transition-opacity" 
             @click="open = false" aria-hidden="true">
            <div class="absolute inset-0 bg-slate-900/75 backdrop-blur-sm"></div>
        </div>

        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="inline-block w-full max-w-xl mx-auto overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl sm:rounded-3xl shadow-2xl my-4 sm:my-8 sm:align-middle p-5 sm:p-8 border border-slate-100 relative">
            
            <div class="absolute top-0 right-0 pt-5 sm:pt-6 pr-5 sm:pr-6">
                <button @click="open = false" type="button" class="text-slate-400 hover:text-slate-500 focus:outline-none transition-colors">
                    <span class="sr-only">Close</span>
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight flex items-center gap-3 mb-1">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <span>Bulk Schedule Proposal</span>
            </h3>
            <p class="text-xs text-slate-500 font-medium mb-6 mt-1 ml-12">Set the proposal defence date for the entire cohort or specific students.</p>

            <form action="{{ route('coordinator.cohorts.bulk-schedule', $cohort) }}" method="POST" class="space-y-5 sm:space-y-6">
                @csrf
                <input type="hidden" name="defence_type" value="proposal">
                
                <div class="grid grid-cols-2 gap-4">
                    <!-- Target Population -->
                    <div class="col-span-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Target Scope</label>
                        <select name="target" x-model="target" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-xs sm:text-sm font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 transition-colors">
                            <option value="all">Assigned Program Cohort ({{ collect($categorizedStudents)->sum(fn($c) => $c['students']->count()) }})</option>
                            <option value="selected">Selected Candidates</option>
                        </select>
                    </div>
                </div>

                <!-- Date Selection -->
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Scheduled Date</label>
                    <input type="date" name="defence_date" x-model="date" required 
                           class="w-full bg-white border border-slate-200 text-slate-900 text-xs sm:text-sm font-bold rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 shadow-sm transition-colors cursor-pointer">
                </div>

                <!-- Student Checkboxes -->
                <div x-show="target === 'selected'" x-collapse class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 mt-4">
                    <div class="p-3 sm:p-4 bg-white border-b border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Select target candidates</span>
                    </div>
                    <div class="max-h-60 overflow-y-auto p-2 custom-scrollbar">
                        @foreach($allStudents as $studentData)
                        <label class="flex items-center p-2.5 sm:p-3 mb-1 bg-white rounded-lg border border-slate-100 shadow-sm cursor-pointer hover:border-blue-200 transition-colors">
                            <input type="checkbox" name="student_ids[]" value="{{ $studentData['profile']->id }}" class="w-4 h-4 text-blue-600 bg-slate-100 border-slate-300 rounded focus:ring-blue-500 focus:ring-2">
                            <div class="ml-3 min-w-0">
                                <span class="block text-xs sm:text-sm font-bold text-slate-900 truncate">{{ $studentData['profile']->user->name }}</span>
                                <span class="block text-[10px] text-slate-500 uppercase tracking-widest truncate">{{ $studentData['profile']->student_id_number }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-3 pt-4 border-t border-slate-100 mt-6">
                    <button type="button" @click="open = false" class="w-full sm:w-auto px-5 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors text-center justify-center">
                        Cancel
                    </button>
                    <button type="submit" class="w-full sm:w-auto px-5 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2 text-center">
                        <span>Authorize Bulk Schedule</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
