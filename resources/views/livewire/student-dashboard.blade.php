<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.duration.500ms class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6 flex justify-between items-center">
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="text-green-700 hover:text-green-900 focus:outline-none">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    @if(!$student)
        <div class="text-gray-500">
            <h3 class="text-xl font-medium mb-2">No Student Profile Found</h3>
            <p>Your account is not linked to a student profile.</p>
        </div>
    @else
        <div class="mb-8 border-b pb-4 flex justify-between items-end">
            <div>
                <h3 class="text-2xl font-semibold text-gray-900">{{ auth()->user()->name }}</h3>
                <p class="text-gray-600">{{ $student->matric_no }} &mdash; {{ optional($student->programme)->name }} ({{ optional($student->degree)->name }})</p>
                <p class="text-sm text-gray-500 mt-1">Cohort: {{ optional($student->cohort)->year }} {{ optional(optional($student->cohort)->intake)->name }}</p>
            </div>
            <div>
                @if($student->registration_status === 'Pending')
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">Registration Pending Verification</span>
                @else
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">Active</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Progress Timeline -->
            <div class="md:col-span-1 border-r pr-6">
                <h4 class="text-lg font-medium mb-4">Research Progress</h4>
                <div class="space-y-4">
                    @foreach($allMilestones as $index => $milestone)
                        @php
                            $status = $studentMilestones[$milestone->id] ?? 'Unstarted';
                            $statusColor = match($status) {
                                'Verified' => 'text-green-600 bg-green-100',
                                'Pending' => 'text-yellow-600 bg-yellow-100',
                                'Rejected' => 'text-red-600 bg-red-100',
                                default => 'text-gray-500 bg-gray-100'
                            };
                            $isNext = $nextMilestone && $nextMilestone->id === $milestone->id;
                            $iconColor = $status === 'Verified' ? 'bg-green-500' : ($isNext ? 'bg-red-500 ring-4 ring-red-100' : 'bg-gray-300');
                        @endphp
                        <div class="flex items-start">
                            <div class="flex flex-col items-center mr-3 mt-1">
                                <div class="w-3 h-3 rounded-full {{ $iconColor }}"></div>
                                @if($index < count($allMilestones) - 1)
                                    <div class="w-0.5 h-full bg-gray-200 my-1"></div>
                                @endif
                            </div>
                            <div class="pb-2">
                                <p class="text-sm font-medium {{ $isNext ? 'text-red-700 font-bold' : 'text-gray-900' }}">{{ $milestone->name }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColor }}">{{ $status }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Next Action Area -->
            <div class="md:col-span-2">
                @if($student->registration_status === 'Pending')
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h4 class="text-lg font-medium text-gray-900 mb-2">Awaiting Coordinator Verification</h4>
                        <p class="text-gray-500 text-sm">Your self-reported registration milestones are currently under review by your Programme Coordinator. You will be able to submit new evidence once your profile is approved.</p>
                    </div>
                @elseif($nextMilestone)
                    <div class="bg-red-50 border border-red-100 rounded-lg p-6">
                        <h4 class="text-lg font-bold text-red-900 mb-2">Next Step: {{ $nextMilestone->name }}</h4>
                        <p class="text-red-700 text-sm mb-6">{{ $nextMilestone->description ?? 'Please upload the required evidence/report to mark this milestone as complete.' }}</p>

                        <form wire:submit.prevent="submitMilestone({{ $nextMilestone->id }})" class="space-y-4 bg-white p-4 rounded border border-red-100">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Supporting Document (PDF)</label>
                                <input type="file" wire:model="evidence_file" accept=".pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                                @error('evidence_file') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Remarks / Comments (Optional)</label>
                                <textarea wire:model="remarks" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
                            </div>

                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                                Submit for Verification
                            </button>
                        </form>
                    </div>
                @else
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h4 class="text-lg font-medium text-green-900 mb-2">Congratulations!</h4>
                        <p class="text-green-700 text-sm">You have completed all research milestones for your programme.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
