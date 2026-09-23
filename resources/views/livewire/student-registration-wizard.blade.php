<div class="max-w-3xl mx-auto p-8 bg-white border border-gray-100 shadow-xl rounded-2xl mt-4">
    <div class="text-center mb-8">
        <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">ACETEL Postgraduate</h2>
        <h2 class="text-2xl font-bold text-green-600 tracking-tight mt-1">Registration</h2>
    </div>

    <!-- Stepper UI -->
    @if($step !== 99)
    <div class="mb-10 relative">
        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 rounded-full"></div>
        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 h-1 bg-green-600 rounded-full transition-all duration-500 ease-in-out" 
             style="width: {{ $step === 1 ? '0%' : ($step === 2 ? '50%' : '100%') }}"></div>
        
        <div class="relative flex justify-between w-full">
            <!-- Step 1 Indicator -->
            <div class="relative flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shadow-sm transition-colors duration-300 {{ $step >= 1 ? 'bg-green-600 text-white border-2 border-green-600' : 'bg-white text-gray-400 border-2 border-gray-200' }}">
                    1
                </div>
                <span class="absolute -bottom-6 text-xs font-semibold {{ $step >= 1 ? 'text-green-600' : 'text-gray-400' }}">Profile</span>
            </div>
            
            <!-- Step 2 Indicator -->
            <div class="relative flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shadow-sm transition-colors duration-300 {{ $step >= 2 ? 'bg-green-600 text-white border-2 border-green-600' : 'bg-white text-gray-400 border-2 border-gray-200' }}">
                    2
                </div>
                <span class="absolute -bottom-6 text-xs font-semibold {{ $step >= 2 ? 'text-green-600' : 'text-gray-400' }}">Milestones</span>
            </div>
            
            <!-- Step 3 Indicator -->
            <div class="relative flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shadow-sm transition-colors duration-300 {{ $step >= 3 ? 'bg-green-600 text-white border-2 border-green-600' : 'bg-white text-gray-400 border-2 border-gray-200' }}">
                    3
                </div>
                <span class="absolute -bottom-6 text-xs font-semibold {{ $step >= 3 ? 'text-green-600' : 'text-gray-400' }}">Thesis</span>
            </div>
        </div>
    </div>
    @endif

    @if (session()->has('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.duration.500ms class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg relative mb-6 flex justify-between items-center shadow-sm">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-green-500 hover:text-green-700 focus:outline-none">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    <!-- STEP 1: Basic Information -->
    @if($step === 1)
        <div wire:key="step-1" class="pt-6 animate-fade-in-up">
            <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-3">
                <div class="w-20">
                    <a href="/" class="text-gray-500 hover:text-green-600 flex items-center transition-colors font-medium text-sm">
                        <svg class="w-5 h-5 sm:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        <span class="hidden sm:inline">Cancel</span>
                    </a>
                </div>
                <div class="flex items-center justify-center flex-1">
                    <div class="bg-green-100 text-green-700 rounded-full h-8 w-8 flex items-center justify-center font-bold mr-3 text-sm flex-shrink-0">1</div>
                    <h3 class="text-xl font-semibold text-gray-800 truncate">Student Information</h3>
                </div>
                <div class="w-20"></div>
            </div>
            
            <form wire:submit="validateBasicInfo" class="space-y-5">
                
                <div class="relative">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <input type="text" wire:model="name" class="block w-full pl-10 pr-3 py-2.5 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors bg-gray-50 focus:bg-white" placeholder="John Doe">
                    </div>
                    @error('name') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="relative">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <input type="email" wire:model="email" class="block w-full pl-10 pr-3 py-2.5 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors bg-gray-50 focus:bg-white" placeholder="john@example.com">
                    </div>
                    @error('email') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="relative" x-data="{ show: false }">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <input :type="show ? 'text' : 'password'" wire:model="password" class="block w-full pl-10 pr-10 py-2.5 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors bg-gray-50 focus:bg-white" placeholder="••••••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg x-cloak x-show="show" class="h-5 w-5" style="display: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </button>
                        </div>
                        @error('password') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                    <div class="relative" x-data="{ show: false }">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Confirm Password</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <input :type="show ? 'text' : 'password'" wire:model="password_confirmation" class="block w-full pl-10 pr-10 py-2.5 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors bg-gray-50 focus:bg-white" placeholder="••••••••">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg x-cloak x-show="show" class="h-5 w-5" style="display: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Matriculation Number</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                        </div>
                        <input type="text" wire:model="matric_no" placeholder="e.g. ACE26210011" class="block w-full pl-10 pr-3 py-2.5 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 uppercase transition-colors bg-gray-50 focus:bg-white">
                    </div>

                    @error('matric_no') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="relative">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Place of Work <span class="text-gray-400 font-normal">(Optional)</span></label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <input type="text" wire:model="place_of_work" placeholder="Where do you currently work?" class="block w-full pl-10 pr-3 py-2.5 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors bg-gray-50 focus:bg-white">
                    </div>
                    @error('place_of_work') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Programme</label>
                        @php
                            $progOptions = collect($programmes)->map(function($p) {
                                return ['value' => $p['id'], 'label' => $p['name']];
                            })->values()->toArray();
                        @endphp
                        <x-custom-select wire:model="programme_id" :options="$progOptions" placeholder="Select Programme" />
                        @error('programme_id') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Degree</label>
                        @php
                            $degOptions = collect($degrees)->map(function($d) {
                                return ['value' => $d['id'], 'label' => $d['name']];
                            })->values()->toArray();
                        @endphp
                        <x-custom-select wire:model="degree_id" :options="$degOptions" placeholder="Select Degree" />
                        @error('degree_id') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md text-base font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all transform hover:-translate-y-0.5">
                        Continue to Questionnaire 
                        <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- STEP 2: Questionnaire -->
    @if($step === 2)
        <div wire:key="step-2" wire:transition.opacity.duration.300ms class="text-center pt-6">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-100">
                <div class="w-20">
                    <button wire:click="goBackQuestion" class="text-sm text-gray-500 hover:text-green-600 flex items-center transition-colors font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Back
                    </button>
                </div>
                <div class="flex items-center justify-center flex-1">
                    <div class="bg-green-100 text-green-700 rounded-full h-8 w-8 flex items-center justify-center font-bold mr-3 text-sm">2</div>
                    <h3 class="text-xl font-semibold text-gray-800">Research Progress</h3>
                </div>
                <div class="w-20"></div>
            </div>
            
            <div class="bg-gradient-to-br from-green-50 to-white p-8 rounded-2xl border border-green-100 shadow-sm mt-4">
                <div class="flex justify-between items-center mb-6">
                    <span class="text-green-600 text-sm font-semibold bg-green-100 px-3 py-1 rounded-full">Question {{ $currentQuestionIndex + 1 }} of {{ count($questions) }}</span>
                    <div class="w-32 bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full transition-all duration-500 ease-out" style="width: {{ (($currentQuestionIndex + 1) / count($questions)) * 100 }}%"></div>
                    </div>
                </div>
                
                @php
                    $msName = $questions[$currentQuestionIndex]['name'];
                    $qPrefix = "Have you completed your";
                    $qSuffix = "?";
                    $highlightText = $msName;
                    
                    if ($msName === 'Supervisors Assigned') {
                        $qPrefix = "Has your";
                        $highlightText = "Supervisory Committee";
                        $qSuffix = " been assigned?";
                    } elseif ($msName === 'Internal Defence') {
                        $qPrefix = "Have you conducted your";
                    } elseif ($msName === 'Viva') {
                        $qPrefix = "Have you completed your";
                        $highlightText = "Viva Voce";
                    }
                @endphp
                <h4 class="text-2xl font-bold text-gray-800 mb-8 leading-relaxed">{{ $qPrefix }} <span class="text-green-600 border-b-2 border-green-200">{{ $highlightText }}</span>{{ $qSuffix }}</h4>
                
                <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                    <button wire:click="answerQuestion('yes')" class="w-full sm:w-auto px-8 py-3 bg-emerald-500 text-white font-bold rounded-xl shadow hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all transform hover:-translate-y-0.5 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Yes, I have
                    </button>
                    <button wire:click="answerQuestion('no')" class="w-full sm:w-auto px-8 py-3 bg-white text-gray-700 border-2 border-gray-200 font-bold rounded-xl hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-200 focus:ring-offset-2 transition-all flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        No, I have not
                    </button>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-6 flex items-center justify-center"><svg class="w-4 h-4 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg> Selecting "No" will establish this as your current stage and complete your profile.</p>
        </div>
    @endif

    <!-- STEP 3: Thesis Upload -->
    @if($step === 3)
        <div wire:key="step-3" wire:transition.opacity.duration.300ms class="pt-6">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-100">
                <div class="w-20">
                    <button wire:click="goBackToStep2" class="text-sm text-gray-500 hover:text-green-600 flex items-center transition-colors font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Back
                    </button>
                </div>
                <div class="flex items-center justify-center flex-1">
                    <div class="bg-green-100 text-green-700 rounded-full h-8 w-8 flex items-center justify-center font-bold mr-3 text-sm">3</div>
                    <h3 class="text-xl font-semibold text-gray-800">Final Thesis Submission</h3>
                </div>
                <div class="w-20"></div>
            </div>
            
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 font-medium">Since you have completed your Viva, please provide your external examiner details and upload your final thesis.</p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-6 bg-gray-50 p-6 rounded-xl border border-gray-100 max-w-lg mx-auto">
                
                @if ($errors->any())
                    <div class="bg-red-50 text-red-600 p-4 rounded-lg text-sm mb-4">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <!-- External Examiner -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">External Examiner</label>
                    @php
                        $exOptions = collect($available_examiners)->map(function($s) {
                            return [
                                'value' => $s['id'], 
                                'label' => $s['name'] . ' (' . $s['email'] . ')'
                            ];
                        })->values()->toArray();
                    @endphp
                    <x-searchable-select wire:model.live="external_examiner_id" :options="$exOptions" placeholder="-- Select External Examiner --" :allowOther="true" />
                    @error('external_examiner_id') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    
                    @if($external_examiner_id === 'other')
                        <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg shadow-sm animate-fade-in-down">
                            <h5 class="text-xs font-bold text-gray-600 uppercase tracking-wide mb-3 border-b pb-2">Manual Examiner Entry</h5>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Full Name</label>
                                    <input type="text" wire:model="external_examiner_name" class="block w-full py-2 px-3 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors" placeholder="e.g. Dr. Jane Smith">
                                    @error('external_examiner_name') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address</label>
                                    <input type="email" wire:model="external_examiner_email" class="block w-full py-2 px-3 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors" placeholder="e.g. jsmith@acetel.edu.ng">
                                    @error('external_examiner_email') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Final Thesis Document (PDF)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg bg-white hover:bg-gray-50 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-green-600 hover:text-green-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-green-500">
                                    <span>Upload a file</span>
                                    <input id="file-upload" type="file" wire:model="thesis_file" accept=".pdf" class="sr-only">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PDF up to 10MB</p>
                        </div>
                    </div>
                    @error('thesis_file') <span class="text-red-500 text-xs mt-2 block font-medium">{{ $message }}</span> @enderror
                    
                    @if($thesis_file)
                    <div class="mt-3 flex items-center text-sm text-green-600 font-medium">
                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        File selected: {{ $thesis_file->getClientOriginalName() }}
                    </div>
                    @endif
                </div>

                <div class="pt-2">
                    <button type="button" wire:click="submitThesisAndFinish" wire:loading.attr="disabled" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md text-base font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading.remove wire:target="submitThesisAndFinish" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <svg wire:loading wire:target="submitThesisAndFinish" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Submit and Complete Registration
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- STEP 4: Supervisor Details -->
    @if($step === 4)
        <div wire:key="step-4" wire:transition.opacity.duration.300ms class="pt-6">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-100">
                <div class="w-20">
                    <button wire:click="goBackToStep2FromSupervisors" class="text-gray-500 hover:text-green-600 flex items-center transition-colors font-medium text-sm">
                        <svg class="w-5 h-5 sm:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        <span class="hidden sm:inline">Cancel</span>
                    </button>
                </div>
                <div class="flex items-center justify-center flex-1">
                    <div class="bg-green-100 text-green-700 rounded-full h-8 w-8 flex items-center justify-center font-bold mr-3 text-sm flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 truncate">Supervisor Details</h3>
                </div>
                <div class="w-20"></div>
            </div>

            <div class="mb-6 text-sm text-gray-600 text-center">
                Please provide the details of your assigned supervisors. You need to provide {{ count($supervisors_data) }} supervisors based on your degree program.
            </div>

            <form wire:submit="submitSupervisors" class="space-y-6">
                @foreach($supervisors_data as $index => $supervisor)
                    <div class="bg-gray-50 p-5 rounded-xl border border-gray-100 shadow-sm relative">
                        <div class="absolute top-0 right-0 -mt-3 -mr-3 bg-white border border-gray-200 text-gray-500 font-bold rounded-full w-8 h-8 flex items-center justify-center shadow-sm text-xs">
                            #{{ $index + 1 }}
                        </div>
                        <h4 class="text-sm font-bold text-green-700 mb-4 uppercase tracking-wider">{{ $index === 0 ? 'Principal Supervisor' : 'Co-Supervisor' }}</h4>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Select Supervisor</label>
                                @php
                                    $supOptions = collect($available_supervisors)->map(function($s) {
                                        return [
                                            'value' => $s['id'], 
                                            'label' => optional($s->user)->name . ' (' . optional($s->user)->email . ')'
                                        ];
                                    })->values()->toArray();
                                @endphp
                                <x-searchable-select wire:model.live="supervisors_data.{{ $index }}.supervisor_id" :options="$supOptions" placeholder="-- Select a Supervisor --" :allowOther="true" />
                                @error('supervisors_data.'.$index.'.supervisor_id') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>
                            
                            @if(isset($supervisors_data[$index]['supervisor_id']) && $supervisors_data[$index]['supervisor_id'] === 'other')
                                <div class="mt-4 p-4 bg-white border border-gray-200 rounded-lg shadow-sm animate-fade-in-down">
                                    <h5 class="text-xs font-bold text-gray-600 uppercase tracking-wide mb-3 border-b pb-2">Manual Supervisor Entry</h5>
                                    <div class="grid grid-cols-1 gap-4">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Full Name</label>
                                            <input type="text" wire:model="supervisors_data.{{ $index }}.name" class="block w-full py-2 px-3 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors" placeholder="e.g. Dr. Jane Smith">
                                            @error('supervisors_data.'.$index.'.name') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address</label>
                                            <input type="email" wire:model="supervisors_data.{{ $index }}.email" class="block w-full py-2 px-3 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors" placeholder="e.g. jsmith@acetel.edu.ng">
                                            @error('supervisors_data.'.$index.'.email') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="pt-4 flex justify-between items-center border-t border-gray-100">
                    <button type="button" wire:click="goBackToStep2FromSupervisors" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-green-600 border border-transparent text-white font-bold rounded-xl shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-transform transform hover:-translate-y-0.5">
                        Save & Continue
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- STEP 5: Proposal Details -->
    @if ($step === 5)
        <div wire:key="step-5" wire:transition.opacity.duration.300ms class="pt-6">
            <div class="animate-fade-in-up">
                <div class="mb-8 border-b pb-4">
                    <button type="button" wire:click="goBackToStep2FromProposal" class="text-gray-500 hover:text-gray-700 flex items-center text-sm font-medium transition-colors">
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Back
                    </button>
                </div>

                <div class="mb-6 text-center">
                    <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold uppercase tracking-widest mb-3 shadow-sm border border-green-200">Proposal Details</span>
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight">Research Details</h3>
                    <p class="text-gray-500 mt-2 text-sm">Please provide your approved thesis title and abstract.</p>
                </div>

                <form wire:submit.prevent="submitProposalDetails" class="space-y-6 max-w-lg mx-auto">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Thesis Title</label>
                        <input type="text" wire:model="thesis_title" class="block w-full py-2.5 px-3 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors bg-white shadow-sm" placeholder="Enter your approved thesis title">
                        @error('thesis_title') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Thesis Abstract</label>
                        <textarea wire:model="thesis_abstract" rows="6" class="block w-full py-2.5 px-3 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors bg-white shadow-sm" placeholder="Paste your approved abstract here..."></textarea>
                        @error('thesis_abstract') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            Save Details & Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- STEP 6: Presentation Date -->
    @if ($step === 6)
        <div wire:key="step-6" wire:transition.opacity.duration.300ms class="pt-6">
            <div class="animate-fade-in-up">
                <div class="mb-8 border-b pb-4">
                    <button type="button" wire:click="goBackToStep2FromPresentation" class="text-gray-500 hover:text-gray-700 flex items-center text-sm font-medium transition-colors">
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Back
                    </button>
                </div>

                <div class="mb-6 text-center">
                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold uppercase tracking-widest mb-3 shadow-sm border border-blue-200">Presentation Details</span>
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $current_intercept_milestone }}</h3>
                    <p class="text-gray-500 mt-2 text-sm">Please provide the date you successfully completed this presentation.</p>
                </div>

                <form wire:submit.prevent="submitPresentationDate" class="space-y-6 max-w-sm mx-auto">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Presentation Date</label>
                        <input type="date" wire:model="presentation_date" max="{{ date('Y-m-d') }}" class="block w-full py-2.5 px-3 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors bg-white shadow-sm">
                        @error('presentation_date') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            Save Date & Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- STEP 7: Internal Defence Details -->
    @if ($step === 7)
        <div wire:key="step-7" wire:transition.opacity.duration.300ms class="pt-6">
            <div class="animate-fade-in-up">
                <div class="mb-8 border-b pb-4">
                    <button type="button" wire:click="goBackToStep2FromInternalDefence" class="text-gray-500 hover:text-gray-700 flex items-center text-sm font-medium transition-colors">
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Back
                    </button>
                </div>

                <div class="mb-6 text-center">
                    <span class="inline-block px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-bold uppercase tracking-widest mb-3 shadow-sm border border-purple-200">Internal Defence</span>
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight">Defence Details</h3>
                    <p class="text-gray-500 mt-2 text-sm">Please provide your internal examiner, defence date, publications, and thesis.</p>
                </div>

                <form wire:submit.prevent="submitInternalDefence" class="space-y-6 max-w-lg mx-auto">
                    <!-- Presentation Date -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Defence Date</label>
                        <input type="date" wire:model="presentation_date" max="{{ date('Y-m-d') }}" class="block w-full py-2.5 px-3 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors bg-white shadow-sm">
                        @error('presentation_date') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Internal Examiner -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Internal Examiner</label>
                        @php
                            $exOptions = collect($available_examiners)->map(function($s) {
                                return [
                                    'value' => $s['id'], 
                                    'label' => $s['name'] . ' (' . $s['email'] . ')'
                                ];
                            })->values()->toArray();
                        @endphp
                        <x-searchable-select wire:model.live="internal_examiner_id" :options="$exOptions" placeholder="-- Select Internal Examiner --" :allowOther="true" />
                        @error('internal_examiner_id') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        
                        @if($internal_examiner_id === 'other')
                            <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg shadow-sm animate-fade-in-down">
                                <h5 class="text-xs font-bold text-gray-600 uppercase tracking-wide mb-3 border-b pb-2">Manual Examiner Entry</h5>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Full Name</label>
                                        <input type="text" wire:model="internal_examiner_name" class="block w-full py-2 px-3 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors" placeholder="e.g. Dr. Jane Smith">
                                        @error('internal_examiner_name') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address</label>
                                        <input type="email" wire:model="internal_examiner_email" class="block w-full py-2 px-3 sm:text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors" placeholder="e.g. jsmith@acetel.edu.ng">
                                        @error('internal_examiner_email') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Publications -->
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Scopus Indexed Publications (Q2+)</label>
                        <p class="text-xs text-gray-500 mb-4">Please upload each publication one at a time along with its Title/DOI.</p>

                        <div class="space-y-4">
                            @foreach($publications_data as $index => $pub)
                                <div class="bg-white p-5 border border-gray-200 rounded-xl shadow-sm transition-all" wire:key="pub-{{ $index }}">
                                    <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-3">
                                        <h6 class="text-sm font-bold text-gray-800">Publication #{{ $index + 1 }}</h6>
                                        @if(count($publications_data) > 1)
                                            <button type="button" wire:click="removePublication({{ $index }})" class="flex items-center text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 px-2.5 py-1.5 rounded-lg transition-colors">
                                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                Remove
                                            </button>
                                        @endif
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Publication Title or DOI</label>
                                            <input type="text" wire:model="publications_data.{{ $index }}.title" class="block w-full py-2.5 px-3.5 text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors shadow-sm" placeholder="e.g. 10.1016/j.jocs.2023.10214">
                                            @error('publications_data.'.$index.'.title') <span class="text-red-500 text-xs mt-1.5 block font-medium">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Publication File (PDF)</label>
                                            <div class="flex items-center w-full">
                                                <input type="file" wire:model="publications_data.{{ $index }}.file" accept=".pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 transition-colors cursor-pointer border border-gray-200 rounded-lg p-1 bg-gray-50">
                                            </div>
                                            @error('publications_data.'.$index.'.file') <span class="text-red-500 text-xs mt-1.5 block font-medium">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <button type="button" wire:click="addPublication" class="mt-5 flex items-center justify-center w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-sm font-bold text-gray-600 hover:text-green-600 hover:border-green-400 hover:bg-green-50 transition-colors bg-white shadow-sm">
                            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            Add Another Publication
                        </button>
                    </div>

                    <!-- Thesis Copy -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Copy of Thesis (PDF)</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg bg-white hover:bg-gray-50 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="internal-thesis" class="relative cursor-pointer bg-white rounded-md font-medium text-green-600 hover:text-green-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-green-500">
                                        <span>Upload a file</span>
                                        <input id="internal-thesis" type="file" wire:model="internal_thesis_file" accept=".pdf" class="sr-only">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF up to 10MB</p>
                            </div>
                        </div>
                        @error('internal_thesis_file') <span class="text-red-500 text-xs mt-2 block font-medium">{{ $message }}</span> @enderror
                        @if($internal_thesis_file)
                        <div class="mt-3 flex items-center text-sm text-green-600 font-medium">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            File selected: {{ $internal_thesis_file->getClientOriginalName() }}
                        </div>
                        @endif
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            Save Details & Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- STEP 8: Seminar Course Grade -->
    @if($step === 8)
        <div wire:key="step-8" wire:transition.opacity.duration.300ms class="pt-6">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-gray-100">
                <div class="w-20">
                    <button wire:click="goBackToStep2FromSeminar" class="text-sm text-gray-500 hover:text-green-600 flex items-center transition-colors font-medium">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Back
                    </button>
                </div>
                <div class="flex items-center justify-center flex-1">
                    <div class="bg-green-100 text-green-700 rounded-full h-8 w-8 flex items-center justify-center font-bold mr-3 text-sm">2</div>
                    <h3 class="text-xl font-semibold text-gray-800">Seminar Course Grade</h3>
                </div>
                <div class="w-20"></div>
            </div>

            <div class="mb-6 text-sm text-gray-600 text-center">
                Since you have completed your Seminar Course, please provide your grade below.
            </div>

            <form wire:submit="submitSeminarGrade" class="space-y-6 max-w-md mx-auto">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Course Grade</label>
                    <div class="relative rounded-md shadow-sm">
                        <input type="text" wire:model="seminar_grade" class="block w-full py-3 px-4 sm:text-sm border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500 transition-colors bg-gray-50 focus:bg-white" placeholder="e.g. A, B+, 75%">
                    </div>
                    @error('seminar_grade') <span class="text-red-500 text-xs mt-1.5 block font-medium">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md text-base font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all transform hover:-translate-y-0.5">
                    Save Grade & Continue
                    <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                </button>
            </form>
        </div>
    @endif

    <!-- STEP 99: Success State -->
    @if($step === 99)
        <div wire:key="step-99" wire:transition.opacity.duration.500ms class="py-12 text-center animate-fade-in-up">
            <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-100 mb-6">
                <svg class="h-12 w-12 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-3xl font-extrabold text-gray-900 mb-2 tracking-tight">Congratulations!</h3>
            <p class="text-lg text-gray-500 mb-8 max-w-md mx-auto">Your postgraduate registration has been successfully saved. Welcome to the ACETEL Research Portal.</p>
            
            <a href="{{ url('/dashboard') }}" class="inline-flex justify-center items-center py-3 px-8 border border-transparent rounded-xl shadow-md text-base font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all transform hover:-translate-y-0.5">
                Go to Dashboard
                <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
            </a>
        </div>
    @endif
</div>
