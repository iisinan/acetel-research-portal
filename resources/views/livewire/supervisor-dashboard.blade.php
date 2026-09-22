<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.duration.500ms class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6 flex justify-between items-center">
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="text-green-700 hover:text-green-900 focus:outline-none">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    <h3 class="text-xl font-bold mb-6 border-b pb-2">My Assigned Students</h3>

    @if(!auth()->user()->supervisor)
        <p class="text-gray-500">Your account is not linked to a supervisor profile.</p>
    @elseif($students->isEmpty())
        <p class="text-gray-500">You currently have no students assigned to you.</p>
    @else
        <div class="space-y-8">
            @foreach($students as $student)
                <div class="border rounded-lg p-6 bg-gray-50">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="text-lg font-bold text-gray-900">{{ optional($student->user)->name }}</h4>
                            <p class="text-sm text-gray-600">{{ $student->matric_no }} &mdash; {{ optional($student->programme)->name }} ({{ optional($student->degree)->name }})</p>
                            <p class="text-xs text-indigo-600 font-medium mt-1">Your Role: {{ $student->pivot->role }}</p>
                        </div>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">{{ $student->registration_status }}</span>
                    </div>

                    <h5 class="text-sm font-semibold text-gray-700 mb-3 mt-4">Research Milestones</h5>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Milestone</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Status</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Evidence</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($student->milestones->sortBy('milestone.order_index') as $sm)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-900">{{ optional($sm->milestone)->name }}</td>
                                        <td class="px-4 py-3">
                                            @if($sm->status === 'Verified')
                                                <span class="text-green-600 font-medium">Verified</span>
                                            @elseif($sm->status === 'Pending')
                                                <span class="text-yellow-600 font-medium">Pending Review</span>
                                            @else
                                                <span class="text-gray-500">{{ $sm->status }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @forelse($sm->documents as $doc)
                                                <a href="#" class="text-indigo-600 hover:underline text-xs flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                    {{ $doc->file_name }}
                                                </a>
                                            @empty
                                                <span class="text-xs text-gray-400">None</span>
                                            @endforelse
                                        </td>
                                        <td class="px-4 py-3 flex space-x-3">
                                            @if($sm->status === 'Pending')
                                                <button wire:click="verifyMilestone({{ $sm->id }})" class="text-xs text-green-600 hover:text-green-900 font-medium">Verify</button>
                                            @endif
                                            <button wire:click="openCommentModal({{ $sm->id }})" class="text-xs text-indigo-600 hover:text-indigo-900 font-medium">Add Comment</button>
                                        </td>
                                    </tr>
                                    @if($sm->remarks)
                                        <tr class="bg-gray-50">
                                            <td colspan="4" class="px-4 py-2 text-xs text-gray-600 italic">
                                                <strong>Remarks:</strong> {!! nl2br(e($sm->remarks)) !!}
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-3 text-center text-gray-500">No milestones recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Comment Modal -->
    @if($selectedMilestoneId)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('selectedMilestoneId', null)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Add Comment to Milestone</h3>
                        <textarea wire:model="commentText" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Type your feedback here..."></textarea>
                        @error('commentText') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="addComment" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Save Comment</button>
                        <button type="button" wire:click="$set('selectedMilestoneId', null)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
