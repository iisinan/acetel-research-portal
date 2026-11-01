@extends('layouts.admin')

@section('content')
<div class="md:flex md:items-center md:justify-between mb-6">
    <div class="flex-1 min-w-0">
        <h2 class="text-2xl font-extrabold leading-7 text-black sm:text-3xl sm:truncate">Cohort Details: {{ $cohort->name }}</h2>
        <p class="mt-2 text-sm text-black font-medium">
            {{ $cohort->code }} &bull; Class of {{ $cohort->intake_year ?? 'N/A' }}
        </p>
    </div>
    <div class="mt-4 flex flex-wrap md:mt-0 md:ml-4 gap-2.5 sm:gap-3">
        <a href="{{ route('admin.cohorts.index') }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-xl shadow-sm text-sm font-bold text-black bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-acetel-500 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Back to Directory
        </a>
        <a href="{{ route('admin.cohorts.edit', $cohort) }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-xl shadow-sm text-sm font-bold text-black bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-acetel-500 transition-colors">
            <svg class="w-4 h-4 mr-2 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            Edit Cohort
        </a>
        <a href="{{ route('admin.cohorts.register-students', $cohort) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-acetel-600 hover:bg-acetel-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-acetel-500 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
            Register Students
        </a>
        <button type="button" @click="$dispatch('open-bulk-modal')" class="inline-flex items-center px-4 py-2 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            Bulk Schedule Defence
        </button>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-slate-200">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-acetel-50 rounded-xl p-3">
                    <svg class="h-6 w-6 text-acetel-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-black truncate">Total Students</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-bold text-black">{{ $cohort->students->count() }}</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-slate-200">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-50 rounded-xl p-3">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-black truncate">Active Status</dt>
                        <dd class="flex items-baseline">
                            <div class="text-lg font-bold text-black capitalize">{{ $cohort->status }}</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-1 lg:col-span-2 bg-white overflow-hidden shadow-sm rounded-2xl border border-slate-200">
        <div class="p-5">
            <dl class="grid grid-cols-2 gap-x-4 gap-y-4">

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-black">Created By</dt>
                    <dd class="mt-1 text-sm text-black font-medium">{{ $cohort->creator?->name ?? 'System' }} on {{ $cohort->created_at->format('M d, Y') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>

<div class="bg-white shadow-sm border border-slate-200 overflow-hidden rounded-2xl mb-8">
    <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
        <h3 class="text-lg leading-6 font-extrabold text-black">Enrolled Students</h3>
        <p class="mt-1 max-w-2xl text-sm text-black font-medium">List of all students currently assigned to this cohort.</p>
    </div>
    
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-white">
                            <tr>
                                <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-black uppercase tracking-wider">Name / Email</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold text-black uppercase tracking-wider">Matric Number</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold text-black uppercase tracking-wider">Program</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold text-black uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-bold text-black uppercase tracking-wider">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($cohort->students as $student)
                            <tr class="hover:bg-slate-50 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-black">{{ $student->user->name ?? 'Unknown User' }}</span>
                                        <span class="text-sm text-black">{{ $student->user->email ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-black font-medium">
                                    {{ $student->student_id_number }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-black">
                                    {{ $student->program?->name ?? 'N/A' }}
                                    <span class="text-xs text-black block">{{ $student->level?->name ?? '' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    @if($student->enrollment_status === 'active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-black border border-slate-200 capitalize">{{ $student->enrollment_status }}</span>
                                    @endif
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-3">
                                        <a href="{{ $student->thesis ? route('milestones.index', ['thesis_id' => $student->thesis->id]) : route('admin.students.show', $student->id) }}" class="inline-flex items-center px-3 py-1.5 bg-acetel-50 text-acetel-700 hover:bg-acetel-100 font-bold text-xs rounded-lg transition-colors border border-acetel-200">
                                            View Progress
                                        </a>
                                        <a href="{{ route('admin.users.edit', $student->user_id) }}" class="text-black hover:text-acetel-600 transition-colors" title="Edit Student">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-black">
                                    No students enrolled in this cohort yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Scheduling Modal -->
<div x-data="{ 
        open: false, 
        target: 'all', 
        type: 'internal',
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
             class="inline-block w-full max-w-xl mx-auto overflow-hidden text-left align-bottom transition-all transform bg-white rounded-3xl shadow-2xl sm:my-8 sm:align-middle p-8 border border-slate-100 relative">
            
            <div class="absolute top-0 right-0 pt-6 pr-6">
                <button @click="open = false" type="button" class="text-slate-400 hover:text-slate-500 focus:outline-none transition-colors">
                    <span class="sr-only">Close</span>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <h3 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                Bulk Defence Scheduling
            </h3>
            <p class="text-xs text-slate-500 font-medium mb-6 mt-1 ml-13">Override active milestones for cohort members instantaneously.</p>

            <form action="{{ route('admin.cohorts.bulk-schedule', $cohort) }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-2 gap-4">
                    <!-- Defence Type -->
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Protocol Layer</label>
                        <select name="defence_type" x-model="type" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block p-3 transition-colors">
                            <option value="proposal">Proposal Defence</option>
                            <option value="internal">Internal Defence</option>
                            <option value="external">External Defence</option>
                        </select>
                    </div>

                    <!-- Target Population -->
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Target Scope</label>
                        <select name="target" x-model="target" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block p-3 transition-colors">
                            <option value="all">Entire Cohort ({{ $cohort->students->count() }})</option>
                            <option value="selected">Selected Candidates</option>
                        </select>
                    </div>
                </div>

                <!-- Date Selection -->
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Scheduled Date</label>
                    <input type="date" name="defence_date" x-model="date" required 
                           class="w-full bg-white border border-slate-200 text-slate-900 text-sm font-bold rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block p-3 shadow-sm transition-colors cursor-pointer">
                </div>

                <!-- Student Checkboxes -->
                <div x-show="target === 'selected'" x-collapse class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 mt-4">
                    <div class="p-4 bg-white border-b border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Select target candidates</span>
                    </div>
                    <div class="max-h-60 overflow-y-auto p-2 custom-scrollbar">
                        @foreach($cohort->students as $student)
                        <label class="flex items-center p-3 mb-1 bg-white rounded-lg border border-slate-100 shadow-sm cursor-pointer hover:border-emerald-200 transition-colors">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="w-4 h-4 text-emerald-600 bg-slate-100 border-slate-300 rounded focus:ring-emerald-500 focus:ring-2">
                            <div class="ml-3">
                                <span class="block text-sm font-bold text-slate-900">{{ $student->user->name }}</span>
                                <span class="block text-[10px] text-slate-500 uppercase tracking-widest">{{ $student->student_id_number }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 mt-6">
                    <button type="button" @click="open = false" class="px-5 py-2.5 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-sm flex items-center gap-2">
                        Authorize Bulk Schedule
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
