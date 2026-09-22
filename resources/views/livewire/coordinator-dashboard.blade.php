<div>
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.duration.500ms class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 flex justify-between items-center">
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="text-green-700 hover:text-green-900 focus:outline-none">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    <div class="mb-4 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
            <li class="mr-2">
                <button wire:click="$set('activeTab', 'pending')" class="inline-block p-4 rounded-t-lg border-b-2 {{ $activeTab === 'pending' ? 'text-indigo-600 border-indigo-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                    Pending Registrations ({{ $pendingStudents->count() }})
                </button>
            </li>
            <li class="mr-2">
                <button wire:click="$set('activeTab', 'active')" class="inline-block p-4 rounded-t-lg border-b-2 {{ $activeTab === 'active' ? 'text-indigo-600 border-indigo-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                    Active Students ({{ $activeStudents->count() }})
                </button>
            </li>
            <li class="mr-2">
                <button wire:click="$set('activeTab', 'evidence')" class="inline-block p-4 rounded-t-lg border-b-2 {{ $activeTab === 'evidence' ? 'text-indigo-600 border-indigo-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                    Pending Evidence Review ({{ $pendingEvidence->count() }})
                </button>
            </li>
            <li class="mr-2">
                <button wire:click="$set('activeTab', 'reports')" class="inline-block p-4 rounded-t-lg border-b-2 {{ $activeTab === 'reports' ? 'text-indigo-600 border-indigo-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                    Management Reports
                </button>
            </li>
        </ul>
    </div>

    @if($activeTab === 'pending')
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Students Awaiting Verification</h3>
            @if($pendingStudents->isEmpty())
                <div class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No pending registrations</h3>
                    <p class="mt-1 text-sm text-gray-500">All new student registrations have been verified.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matric No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Programme</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pendingStudents as $student)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ optional($student->user)->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $student->matric_no }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ optional($student->programme)->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="approveStudent({{ $student->id }})" class="text-indigo-600 hover:text-indigo-900">Verify & Approve</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    @if($activeTab === 'evidence')
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Review Submitted Milestone Evidence</h3>
            @if($pendingEvidence->isEmpty())
                <div class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">All caught up</h3>
                    <p class="mt-1 text-sm text-gray-500">There is no pending student evidence to review at this time.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Milestone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evidence Files</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pendingEvidence as $sm)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ optional($sm->student->user)->name }} <br><span class="text-xs text-gray-500">{{ $sm->student->matric_no }}</span></td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ optional($sm->milestone)->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @foreach($sm->documents as $doc)
                                        <a href="/storage/{{ $doc->file_path }}" target="_blank" class="text-indigo-600 hover:underline text-xs flex items-center mb-1">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            {{ $doc->file_name }}
                                        </a>
                                    @endforeach
                                    @if($sm->remarks)
                                        <p class="text-xs text-gray-600 mt-2 italic">"{!! nl2br(e($sm->remarks)) !!}"</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-3">
                                    <button wire:click="verifyEvidence({{ $sm->id }})" class="text-green-600 hover:text-green-900 font-bold">Approve</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    @if($activeTab === 'active')
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Manage Active Students & Supervisors</h3>
            @if($activeStudents->isEmpty())
                <div class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No active students</h3>
                    <p class="mt-1 text-sm text-gray-500">There are currently no active students in the system.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matric No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisors</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($activeStudents as $student)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ optional($student->user)->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $student->matric_no }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($student->supervisors->isEmpty())
                                        <span class="text-red-500 text-xs font-bold">Unassigned</span>
                                    @else
                                        <ul class="text-xs">
                                            @foreach($student->supervisors as $sup)
                                                <li>{{ $sup->pivot->role }}: {{ optional($sup->user)->name ?? 'Unknown' }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="openSupervisorModal({{ $student->id }})" class="text-indigo-600 hover:text-indigo-900">Assign Supervisors</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    @if($activeTab === 'reports')
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-xl font-bold mb-6 border-b pb-2">Management Reports</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-indigo-50 rounded-lg p-6 border border-indigo-100">
                    <h4 class="text-indigo-900 font-medium text-sm">Total Active Students</h4>
                    <p class="text-3xl font-bold text-indigo-600 mt-2">{{ $reports['total_students'] }}</p>
                </div>
                <div class="bg-red-50 rounded-lg p-6 border border-red-100">
                    <h4 class="text-red-900 font-medium text-sm">Awaiting Supervisors</h4>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ count($reports['awaiting_supervisors']) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h4 class="font-medium text-gray-700 mb-4 border-b pb-2">Students by Programme</h4>
                    <ul class="space-y-2">
                        @foreach($reports['by_programme'] as $prog)
                            <li class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ $prog->name }}</span>
                                <span class="font-bold text-gray-900">{{ $prog->count }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-medium text-gray-700 mb-4 border-b pb-2">Pending Milestones (Bottlenecks)</h4>
                    <ul class="space-y-2">
                        @foreach($reports['by_stage'] as $stage)
                            <li class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ $stage->name }}</span>
                                <span class="font-bold text-gray-900">{{ $stage->count }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            
            @if(count($reports['awaiting_supervisors']) > 0)
                <div class="mt-8">
                    <h4 class="font-medium text-gray-700 mb-4 border-b pb-2">Students Awaiting Supervisors</h4>
                    <ul class="space-y-2">
                        @foreach($reports['awaiting_supervisors'] as $student)
                            <li class="text-sm text-gray-600">
                                <strong>{{ $student->user->name }}</strong> ({{ $student->matric_no }}) - {{ $student->programme->name }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <!-- Supervisor Assignment Modal -->
    @if($assigningStudentId)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeSupervisorModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Assign Supervisors
                        </h3>
                        @php
                            $supOptions = collect($supervisors)->map(function($s) {
                                return [
                                    'value' => $s->id, 
                                    'label' => optional($s->user)->name . ' - ' . optional($s->user)->email . ' (' . $s->students_count . ' active students)'
                                ];
                            })->values()->toArray();
                        @endphp
                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Principal Supervisor</label>
                                <x-searchable-select wire:model="principal_supervisor_id" :options="$supOptions" placeholder="Select Principal Supervisor" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Co-Supervisor 1</label>
                                <x-searchable-select wire:model="co_supervisor_ids.0" :options="$supOptions" placeholder="Select Co-Supervisor 1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Co-Supervisor 2 (PhD Only)</label>
                                <x-searchable-select wire:model="co_supervisor_ids.1" :options="$supOptions" placeholder="Select Co-Supervisor 2" />
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="assignSupervisors" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Save Assignments
                        </button>
                        <button type="button" wire:click="closeSupervisorModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
