<div class="animate-fade-in-up">
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.duration.500ms class="bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-xl shadow-sm mb-6 flex justify-between items-center relative overflow-hidden">
            <div class="absolute inset-y-0 left-0 w-1 bg-green-500"></div>
            <div class="flex items-center">
                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-green-500 hover:text-green-700 focus:outline-none transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    @if(!$student)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
            <div class="mx-auto h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">No Student Profile Found</h3>
            <p class="text-gray-500">Your account is not linked to a student profile.</p>
        </div>
    @else
        <!-- Header Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-green-50 rounded-full opacity-50 blur-3xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center relative z-10 gap-4">
                <div class="flex items-center gap-5">
                    <div class="h-16 w-16 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl flex items-center justify-center font-bold text-2xl shadow-md">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight">{{ auth()->user()->name }}</h3>
                        <p class="text-gray-600 font-medium mt-1">
                            <span class="text-green-700">{{ $student->matric_no }}</span> &bull; {{ optional($student->programme)->name }} ({{ optional($student->degree)->name }})
                        </p>
                        <p class="text-sm text-gray-500 mt-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Cohort: {{ optional($student->cohort)->year }} {{ optional(optional($student->cohort)->intake)->name }}
                        </p>
                    </div>
                </div>
                <div>
                    @if($student->registration_status === 'Pending')
                        <span class="inline-flex items-center px-4 py-2 bg-amber-50 text-amber-700 border border-amber-200 rounded-xl text-sm font-bold shadow-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Registration Pending
                        </span>
                    @else
                        <span class="inline-flex items-center px-4 py-2 bg-green-50 text-green-700 border border-green-200 rounded-xl text-sm font-bold shadow-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Active Profile
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Progress Timeline -->
            <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                <h4 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Research Journey
                </h4>
                <div class="space-y-0">
                    @foreach($allMilestones as $index => $milestone)
                        @php
                            $status = $studentMilestones[$milestone->id] ?? 'Unstarted';
                            
                            $statusConfig = match($status) {
                                'Verified' => ['text' => 'text-green-700', 'bg' => 'bg-green-100', 'dot' => 'bg-green-500', 'ring' => 'ring-green-50'],
                                'Pending' => ['text' => 'text-amber-700', 'bg' => 'bg-amber-100', 'dot' => 'bg-amber-400', 'ring' => 'ring-amber-50'],
                                'Rejected' => ['text' => 'text-red-700', 'bg' => 'bg-red-100', 'dot' => 'bg-red-500', 'ring' => 'ring-red-50'],
                                default => ['text' => 'text-gray-500', 'bg' => 'bg-gray-100', 'dot' => 'bg-gray-300', 'ring' => 'ring-transparent']
                            };

                            $isNext = $nextMilestone && $nextMilestone->id === $milestone->id;
                            if ($isNext) {
                                $statusConfig['dot'] = 'bg-green-600';
                                $statusConfig['ring'] = 'ring-green-100 ring-4';
                            }
                            
                            $isLast = $index === count($allMilestones) - 1;
                        @endphp
                        
                        <div class="relative flex gap-4 {{ !$isLast ? 'pb-6' : '' }}">
                            <!-- Line -->
                            @if(!$isLast)
                                <div class="absolute left-2 top-6 bottom-0 w-px bg-gray-200"></div>
                            @endif
                            
                            <!-- Dot -->
                            <div class="relative z-10 mt-1.5 h-4 w-4 rounded-full {{ $statusConfig['dot'] }} {{ $statusConfig['ring'] }} shadow-sm"></div>
                            
                            <!-- Content -->
                            <div class="flex-1">
                                <p class="text-sm font-bold {{ $isNext ? 'text-green-700' : 'text-gray-900' }}">{{ $milestone->name }}</p>
                                <span class="inline-flex mt-1 text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md {{ $statusConfig['text'] }} {{ $statusConfig['bg'] }}">
                                    {{ $status }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Next Action Area -->
            <div class="lg:col-span-2">
                @if($student->registration_status === 'Pending')
                    <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-100 rounded-2xl p-10 text-center shadow-sm h-full flex flex-col justify-center items-center">
                        <div class="h-20 w-20 bg-amber-50 rounded-full flex items-center justify-center mb-6">
                            <svg class="h-10 w-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h4 class="text-2xl font-extrabold text-gray-900 mb-3 tracking-tight">Awaiting Coordinator Verification</h4>
                        <p class="text-gray-500 text-base max-w-md mx-auto leading-relaxed">Your self-reported registration milestones are currently under review by your Programme Coordinator. You will be able to submit new evidence once your profile is approved.</p>
                    </div>
                @elseif($nextMilestone)
                    <div class="bg-white border border-gray-100 rounded-2xl p-6 md:p-8 shadow-sm">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold uppercase tracking-wider rounded-lg">Next Action Required</span>
                        </div>
                        <h4 class="text-2xl font-extrabold text-gray-900 mb-3 tracking-tight">{{ $nextMilestone->name }}</h4>
                        <p class="text-gray-600 text-sm mb-8 leading-relaxed">{{ $nextMilestone->description ?? 'Please upload the required evidence/report to mark this milestone as complete.' }}</p>

                        <form wire:submit.prevent="submitMilestone({{ $nextMilestone->id }})" class="space-y-6 bg-gray-50 p-6 rounded-xl border border-gray-100">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Supporting Document (PDF)</label>
                                <div class="flex items-center w-full">
                                    <input type="file" wire:model="evidence_file" accept=".pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-white file:text-gray-700 hover:file:bg-gray-50 transition-colors cursor-pointer border border-gray-200 rounded-lg p-1 bg-white shadow-sm">
                                </div>
                                @error('evidence_file') <span class="text-red-500 text-xs mt-1.5 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Remarks / Comments <span class="text-gray-400 font-normal">(Optional)</span></label>
                                <textarea wire:model="remarks" rows="3" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 transition-colors text-sm" placeholder="Add any context for your coordinator..."></textarea>
                            </div>

                            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all transform hover:-translate-y-0.5">
                                Submit for Verification
                            </button>
                        </form>
                    </div>
                @else
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-100 rounded-2xl p-10 text-center shadow-sm h-full flex flex-col justify-center items-center">
                        <div class="h-24 w-24 bg-white rounded-full flex items-center justify-center mb-6 shadow-sm">
                            <svg class="h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h4 class="text-3xl font-extrabold text-green-900 mb-4 tracking-tight">Congratulations!</h4>
                        <p class="text-green-700 text-lg max-w-md font-medium">You have successfully completed all research milestones for your programme.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
