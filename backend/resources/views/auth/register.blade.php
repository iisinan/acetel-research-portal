@extends('layouts.app')

@section('title', 'Student Registration - Thesis Monitoring System')

@section('content')
<style>
    /* Force WebKit browsers to always show the calendar picker icon */
    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 1;
        display: block;
        cursor: pointer;
    }
</style>
<div class="min-h-screen flex items-center justify-center bg-slate-50 py-12 px-4 sm:px-6 lg:px-8 font-sans" x-data="registrationWizard()">
    
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8 relative overflow-hidden">
        
        <!-- Main Header (Shows on Steps 2 and 3) -->
        <div x-show="step > 1" x-cloak class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-900">ACETEL Postgraduate</h1>
            <h2 class="text-xl font-bold text-green-600">Registration</h2>
            

        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-sm font-medium">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="registrationForm" enctype="multipart/form-data">
            @csrf
            
            <!-- Hidden inputs to hold Alpine state for submission -->
            <!-- Hidden inputs to hold Alpine state for submission — one per completed milestone -->
            <template x-for="mid in form.completed_milestones" :key="mid">
                <input type="hidden" name="completed_milestones[]" :value="mid" />
            </template>
            <input type="hidden" name="supervisor_ids[]" x-model="form.principal_supervisor_id" />
            <input type="hidden" name="new_supervisors[0][name]" x-model="form.new_principal_name" />
            <input type="hidden" name="new_supervisors[0][email]" x-model="form.new_principal_email" />

            <input type="hidden" name="supervisor_ids[]" x-model="form.co_supervisor_id" />
            <input type="hidden" name="new_supervisors[1][name]" x-model="form.new_co_name" />
            <input type="hidden" name="new_supervisors[1][email]" x-model="form.new_co_email" />

            <input type="hidden" name="supervisor_ids[]" x-model="form.third_supervisor_id" />
            <input type="hidden" name="new_supervisors[2][name]" x-model="form.new_third_name" />
            <input type="hidden" name="new_supervisors[2][email]" x-model="form.new_third_email" />
            
            <!-- STEP 1: Profile -->
            <div x-show="step === 1" x-transition.opacity.duration.300ms>
                
                <div class="flex items-center gap-3 mb-8 border-b border-slate-100 pb-4">
                    <a href="{{ route('login') }}" class="text-slate-400 hover:text-slate-600 flex items-center gap-1 text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Cancel
                    </a>

                    <h2 class="text-lg font-bold text-slate-900">Student Information</h2>
                </div>

                <div class="space-y-5">
                    
                    <!-- Error Banner -->
                    <div x-show="errorMessage" x-cloak class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl flex items-center gap-2 font-medium" x-transition>
                        <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span x-text="errorMessage"></span>
                    </div>

                    <!-- Full Name -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Full Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <input type="text" name="name" required placeholder="John Doe"
                                   class="pl-11 w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 transition-colors">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <input type="email" name="email" required placeholder="john@example.com"
                                   class="pl-11 w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 transition-colors">
                        </div>
                    </div>

                    <!-- Password Grid -->
                    <div class="grid grid-cols-2 gap-4" x-data="{ showP: false, showC: false }">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <input :type="showP ? 'text' : 'password'" name="password" required placeholder="••••••••"
                                       class="pl-9 pr-10 w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 transition-colors">
                                <button type="button" @click="showP = !showP" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Confirm Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </div>
                                <input :type="showC ? 'text' : 'password'" name="password_confirmation" required placeholder="••••••••"
                                       class="pl-9 pr-10 w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 transition-colors">
                                <button type="button" @click="showC = !showC" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Matric Number -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Matriculation Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                            </div>
                            <input type="text" name="matric_number" required placeholder="E.G. ACE26210011" maxlength="255"
                                   class="pl-11 w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 transition-colors uppercase">
                        </div>
                    </div>

                    <!-- Place of Work -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Place of Work <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <input type="text" name="place_of_work" placeholder="Where do you currently work?" maxlength="255"
                                   class="pl-11 w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 transition-colors">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Phone Number</label>
                            <input type="text" name="phone_number" required placeholder="+234..." maxlength="20"
                                   class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4 transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Gender</label>
                            <select name="gender" required class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4 transition-colors">
                                <option value="" disabled selected>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Nationality</label>
                        <input type="text" name="nationality" required placeholder="E.g. Nigerian" maxlength="100"
                               class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4 transition-colors">
                    </div>

                    

                    <!-- Programme -->
                    <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Programme</label>
                            <select name="program_id" x-model="form.program_id" required class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3">
                                <option value="" disabled selected>Select Programme...</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="button" @click="nextStep()" :disabled="isSubmitting"
                                class="w-full bg-[#18a04b] text-white font-bold py-4 rounded-xl shadow-lg shadow-green-600/20 hover:bg-green-700 hover:shadow-green-700/30 transition-all flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">Continue to Questionnaire</span>
                            <span x-show="isSubmitting">Validating...</span>
                            <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7m0 0l-7 7m7-7H6"/></svg>
                            <svg x-show="isSubmitting" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Milestones -->
            <div x-show="step === 2" x-cloak x-transition.opacity.duration.300ms>
                
                <!-- Main Sub-header -->
                <div x-show="!showingSubStep && !showingSubStepNo" class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                    <button type="button" @click="if(milestoneStep > 1) { milestoneStep-- } else { step = 1 }" class="text-slate-400 hover:text-slate-600 flex items-center gap-1 text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Back
                    </button>

                    <h2 class="text-lg font-bold text-slate-900">Research Progress</h2>
                </div>

                <!-- Main Questionnaire Box -->
                <div x-show="!showingSubStep && !showingSubStepNo" class="bg-[#f2fdf7] border border-green-100 rounded-3xl p-8 relative overflow-hidden">
                    
                    
                    <!-- Error Banner (Global) -->
                    <div x-show="errorMessage" x-cloak class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl flex items-center gap-2 font-medium" x-transition>
                        <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span x-text="errorMessage"></span>
                    </div>

                    <div class="flex items-center gap-3 mb-6">
                        <div class="bg-green-100 text-green-700 font-bold text-xs px-3 py-1 rounded-full whitespace-nowrap">
                            Question <span x-text="milestoneStep"></span> of <span x-text="questions.length"></span>
                        </div>
                        <div class="flex-1 h-2 bg-slate-200 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500 transition-all duration-300" :style="'width: ' + ((milestoneStep / questions.length) * 100) + '%'"></div>
                        </div>
                    </div>

                    <h3 class="text-2xl font-bold text-slate-900 leading-tight mb-8 text-center px-4" x-html="getCurrentQuestionHtml()"></h3>

                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" @click="answerYes()" :disabled="isSubmitting" :class="{'opacity-75 cursor-not-allowed': isSubmitting}" class="bg-[#18a04b] text-white font-bold py-4 rounded-2xl shadow-lg shadow-green-600/20 hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                            <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <svg x-show="isSubmitting" style="display: none;" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Yes, I have
                        </button>
                        <button type="button" @click="answerNo()" :disabled="isSubmitting" :class="{'opacity-75 cursor-not-allowed': isSubmitting}" class="bg-white border-2 border-slate-200 text-slate-700 font-bold py-4 rounded-2xl hover:border-slate-300 hover:bg-slate-50 transition-colors flex items-center justify-center gap-2">
                            <svg x-show="!isSubmitting" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            <svg x-show="isSubmitting" style="display: none;" class="animate-spin w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            No, I have not
                        </button>
                    </div>
                </div>
                


                <!-- Sub-step (Grade or Supervisors) -->
                <div x-show="showingSubStep" x-cloak>
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                        <button type="button" @click="showingSubStep = false" class="text-slate-400 hover:text-slate-600 flex items-center gap-1 text-sm font-semibold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            Back
                        </button>
                        <h2 class="text-lg font-bold text-slate-900 leading-tight" x-html="getCurrentSubTitle()"></h2>
                    </div>

                    <!-- Error Banner -->
                    <div x-show="errorMessage" x-cloak class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl flex items-center gap-2 font-medium" x-transition>
                        <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span x-text="errorMessage"></span>
                    </div>

                    <p class="text-sm text-slate-500 mb-6 font-medium" x-text="getCurrentSubDesc()"></p>
                    
                    <!-- Dynamic Sub-step Content -->
                    <div x-show="getCurrentSubStepType() === 'grade'" x-cloak>
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Course Grade</label>
                            <input type="text" name="seminar_grade" placeholder="e.g. A, B+, 75%"
                                   class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4 transition-colors">
                        </div>
                    </div>
                    
                    <div x-show="getCurrentSubStepType() === 'supervisors'" x-cloak>
                        <div class="mb-6 space-y-4">
                            <div class="p-4 bg-white border border-slate-200 rounded-xl" x-data="{ mode: 'select', search: '', open: false, selectedName: '' }" @click.away="open = false">
                                <span class="text-xs font-bold text-green-700 tracking-widest uppercase mb-2 flex justify-between items-center">
                                    <span>Principal Supervisor</span>
                                    <button type="button" @click="mode = mode === 'select' ? 'manual' : 'select'; if(mode==='manual') form.principal_supervisor_id='';" class="text-blue-500 hover:underline lowercase font-medium" x-text="mode === 'select' ? 'add manually' : 'choose from list'"></button>
                                </span>
                                
                                <div x-show="mode === 'select'" class="relative">
                                    <input type="text" x-model="search" @focus="open = true" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4" placeholder="Search supervisor...">
                                    
                                    <div x-show="open" class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                        <template x-for="supervisor in serverSupervisors.filter(s => s.name.toLowerCase().includes(search.toLowerCase()))">
                                            <div @click="
                                                    if (supervisor.id === form.co_supervisor_id || supervisor.id === form.third_supervisor_id) { return; }
                                                    form.principal_supervisor_id = supervisor.id; search = supervisor.name; open = false; form.new_principal_name=''; form.new_principal_email='';"
                                                 class="px-4 py-3 text-sm font-medium border-b border-slate-100 last:border-0 flex items-center justify-between"
                                                 :class="(supervisor.id === form.co_supervisor_id || supervisor.id === form.third_supervisor_id) ? 'opacity-40 cursor-not-allowed bg-slate-50' : 'hover:bg-slate-50 cursor-pointer text-slate-700'">
                                                <span x-text="supervisor.name"></span>
                                                <span x-show="supervisor.id === form.co_supervisor_id || supervisor.id === form.third_supervisor_id" class="text-xs text-rose-500 font-bold ml-2">Already selected</span>
                                            </div>
                                        </template>
                                        <div x-show="serverSupervisors.filter(s => s.name.toLowerCase().includes(search.toLowerCase())).length === 0" class="px-4 py-3 text-sm text-slate-500">
                                            No supervisors found.
                                        </div>
                                    </div>
                                </div>
                                
                                <div x-show="mode === 'manual'" class="space-y-3 mt-2" x-cloak>
                                    <input type="text" x-model="form.new_principal_name" placeholder="Supervisor Full Name" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4">
                                    <input type="email" x-model="form.new_principal_email" placeholder="Supervisor Email Address" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4">
                                </div>
                            </div>
                            
                            <div class="p-4 bg-white border border-slate-200 rounded-xl" x-data="{ mode: 'select', search: '', open: false, selectedName: '' }" @click.away="open = false">
                                <span class="text-xs font-bold text-green-700 tracking-widest uppercase mb-2 flex justify-between items-center">
                                    <span>Co-Supervisor</span>
                                    <button type="button" @click="mode = mode === 'select' ? 'manual' : 'select'; if(mode==='manual') form.co_supervisor_id='';" class="text-blue-500 hover:underline lowercase font-medium" x-text="mode === 'select' ? 'add manually' : 'choose from list'"></button>
                                </span>
                                
                                <div x-show="mode === 'select'" class="relative">
                                    <input type="text" x-model="search" @focus="open = true" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4" placeholder="Search supervisor...">
                                    
                                    <div x-show="open" class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                        <template x-for="supervisor in serverSupervisors.filter(s => s.name.toLowerCase().includes(search.toLowerCase()))">
                                            <div @click="
                                                    if (supervisor.id === form.principal_supervisor_id || supervisor.id === form.third_supervisor_id) { return; }
                                                    form.co_supervisor_id = supervisor.id; search = supervisor.name; open = false; form.new_co_name=''; form.new_co_email='';"
                                                 class="px-4 py-3 text-sm font-medium border-b border-slate-100 last:border-0 flex items-center justify-between"
                                                 :class="(supervisor.id === form.principal_supervisor_id || supervisor.id === form.third_supervisor_id) ? 'opacity-40 cursor-not-allowed bg-slate-50' : 'hover:bg-slate-50 cursor-pointer text-slate-700'">
                                                <span x-text="supervisor.name"></span>
                                                <span x-show="supervisor.id === form.principal_supervisor_id || supervisor.id === form.third_supervisor_id" class="text-xs text-rose-500 font-bold ml-2">Already selected</span>
                                            </div>
                                        </template>
                                        <div x-show="serverSupervisors.filter(s => s.name.toLowerCase().includes(search.toLowerCase())).length === 0" class="px-4 py-3 text-sm text-slate-500">
                                            No supervisors found.
                                        </div>
                                    </div>
                                </div>
                                
                                <div x-show="mode === 'manual'" class="space-y-3 mt-2" x-cloak>
                                    <input type="text" x-model="form.new_co_name" placeholder="Supervisor Full Name" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4">
                                    <input type="email" x-model="form.new_co_email" placeholder="Supervisor Email Address" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4">
                                </div>
                            </div>

                            <div class="p-4 bg-white border border-slate-200 rounded-xl" x-show="isPhd()" x-data="{ mode: 'select', search: '', open: false, selectedName: '' }" @click.away="open = false">
                                <span class="text-xs font-bold text-green-700 tracking-widest uppercase mb-2 flex justify-between items-center">
                                    <span>Third Supervisor</span>
                                    <button type="button" @click="mode = mode === 'select' ? 'manual' : 'select'; if(mode==='manual') form.third_supervisor_id='';" class="text-blue-500 hover:underline lowercase font-medium" x-text="mode === 'select' ? 'add manually' : 'choose from list'"></button>
                                </span>
                                
                                <div x-show="mode === 'select'" class="relative">
                                    <input type="text" x-model="search" @focus="open = true" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4" placeholder="Search supervisor...">
                                    
                                    <div x-show="open" class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                        <template x-for="supervisor in serverSupervisors.filter(s => s.name.toLowerCase().includes(search.toLowerCase()))">
                                            <div @click="
                                                    if (supervisor.id === form.principal_supervisor_id || supervisor.id === form.co_supervisor_id) { return; }
                                                    form.third_supervisor_id = supervisor.id; search = supervisor.name; open = false; form.new_third_name=''; form.new_third_email='';"
                                                 class="px-4 py-3 text-sm font-medium border-b border-slate-100 last:border-0 flex items-center justify-between"
                                                 :class="(supervisor.id === form.principal_supervisor_id || supervisor.id === form.co_supervisor_id) ? 'opacity-40 cursor-not-allowed bg-slate-50' : 'hover:bg-slate-50 cursor-pointer text-slate-700'">
                                                <span x-text="supervisor.name"></span>
                                                <span x-show="supervisor.id === form.principal_supervisor_id || supervisor.id === form.co_supervisor_id" class="text-xs text-rose-500 font-bold ml-2">Already selected</span>
                                            </div>
                                        </template>
                                        <div x-show="serverSupervisors.filter(s => s.name.toLowerCase().includes(search.toLowerCase())).length === 0" class="px-4 py-3 text-sm text-slate-500">
                                            No supervisors found.
                                        </div>
                                    </div>
                                </div>
                                
                                <div x-show="mode === 'manual'" class="space-y-3 mt-2" x-cloak>
                                    <input type="text" x-model="form.new_third_name" placeholder="Supervisor Full Name" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4">
                                    <input type="email" x-model="form.new_third_email" placeholder="Supervisor Email Address" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-show="getCurrentSubStepType() === 'proposal_defence_details'" x-cloak>
                        <div class="mb-6 space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Date of Proposal Defence</label>
                                <input type="date" name="proposal_defence_date"
                                       class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Approved Thesis Title</label>
                                <input type="text" name="thesis_title" placeholder="Enter your approved thesis title"
                                       class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Approved Thesis Abstract</label>
                                <textarea name="thesis_abstract" rows="5" placeholder="Paste your approved abstract here..."
                                       class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4 transition-colors"></textarea>
                            </div>
                        </div>
                    </div>

                    <div x-show="getCurrentSubStepType() === 'progress_report_1_details'" x-cloak>
                        <div class="mb-6 p-4 bg-green-50 rounded-xl border border-green-200">
                            <p class="text-sm font-semibold text-green-800">✓ Your Progress Report 1 has been recorded as completed. Click "Save & Continue" to proceed.</p>
                        </div>
                    </div>

                    <div x-show="getCurrentSubStepType() === 'progress_report_2_details'" x-cloak>
                        <div class="mb-6 p-4 bg-green-50 rounded-xl border border-green-200">
                            <p class="text-sm font-semibold text-green-800">✓ Your Progress Report 2 has been recorded as completed. Click "Save & Continue" to proceed.</p>
                        </div>
                    </div>

                    <div x-show="getCurrentSubStepType() === 'internal_defence_details'" x-cloak x-data="{ pubs: [1] }">
                        <div class="mb-6 space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Date of Internal Defence</label>
                                <input type="date" name="internal_defence_date"
                                       class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4 transition-colors">
                            </div>

                            <div class="p-4 bg-white border border-slate-200 rounded-xl" x-data="{ mode: 'select', search: '', open: false }" @click.away="open = false">
                                <span class="text-xs font-bold text-green-700 tracking-widest uppercase mb-2 flex justify-between items-center">
                                    <span>Internal Examiner</span>
                                    <button type="button" @click="mode = mode === 'select' ? 'manual' : 'select'; if(mode==='manual') form.internal_examiner_id='';" class="text-blue-500 hover:underline lowercase font-medium" x-text="mode === 'select' ? 'add manually' : 'choose from list'"></button>
                                </span>
                                
                                <div x-show="mode === 'select'" class="relative">
                                    <input type="text" x-model="search" @focus="open = true" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4" placeholder="Search internal examiner...">
                                    
                                    <div x-show="open" class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                        <template x-for="examiner in allInternalExaminers.filter(e => e.name.toLowerCase().includes(search.toLowerCase()))">
                                            <div @click="
                                                    if (examiner.type === 'examiner') {
                                                        form.internal_examiner_id = examiner.id;
                                                        form.new_internal_examiner_name = '';
                                                        form.new_internal_examiner_email = '';
                                                    } else {
                                                        form.internal_examiner_id = '';
                                                        form.new_internal_examiner_name = examiner.name;
                                                        form.new_internal_examiner_email = examiner.email;
                                                    }
                                                    search = examiner.name; 
                                                    open = false;
                                                 " 
                                                 class="px-4 py-3 hover:bg-slate-50 cursor-pointer text-sm font-medium text-slate-700 border-b border-slate-100 last:border-0" 
                                                 x-text="examiner.name + (examiner.type === 'supervisor' ? ' (Supervisor)' : '')"></div>
                                        </template>
                                        <div x-show="allInternalExaminers.filter(e => e.name.toLowerCase().includes(search.toLowerCase())).length === 0" class="px-4 py-3 text-sm text-slate-500">No examiners found.</div>
                                    </div>
                                    <input type="hidden" name="internal_examiner_id" :value="form.internal_examiner_id">
                                </div>

                                <div x-show="mode === 'manual'" class="space-y-3">
                                    <div>
                                        <input type="text" name="internal_examiner_name" x-model="form.new_internal_examiner_name" placeholder="Examiner's Full Name" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4">
                                    </div>
                                    <div>
                                        <input type="email" name="internal_examiner_email" x-model="form.new_internal_examiner_email" placeholder="Examiner's Email Address (Optional)" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2 flex items-center justify-between">
                            <label class="block text-sm font-bold text-slate-700">Publications</label>
                            <button type="button" @click="pubs.push(pubs.length + 1)" class="text-xs font-bold text-green-600 hover:text-green-700 bg-green-50 hover:bg-green-100 px-3 py-1.5 rounded-lg transition-colors">+ Add Publication</button>
                        </div>
                        
                        <div class="space-y-4 mb-6">
                            <template x-for="(pub, index) in pubs" :key="index">
                                <div class="p-4 border border-slate-200 rounded-xl bg-slate-50 relative">
                                    <button x-show="pubs.length > 1" @click="pubs.splice(index, 1)" type="button" class="absolute top-3 right-3 text-slate-400 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <div class="space-y-3">
                                        <div>
                                            <input type="text" :name="'publications[' + index + '][title]'" placeholder="Publication Title (Optional)"
                                                   class="w-full rounded-lg border-slate-200 bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm py-2 px-3">
                                        </div>
                                        <div>
                                            <input type="text" :name="'publications[' + index + '][authors]'" placeholder="Authors (Optional)"
                                                   class="w-full rounded-lg border-slate-200 bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm py-2 px-3">
                                        </div>
                                        <div>
                                            <input type="text" :name="'publications[' + index + '][doi]'" placeholder="DOI or Link (Optional)"
                                                   class="w-full rounded-lg border-slate-200 bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm py-2 px-3">
                                        </div>
                                        <div>
                                            <textarea :name="'publications[' + index + '][abstract]'" placeholder="Publication Abstract (Optional)" rows="2"
                                                      class="w-full rounded-lg border-slate-200 bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm py-2 px-3"></textarea>
                                        </div>
                                        <div>
                                            <input type="file" :name="'publications[' + index + '][file]'" accept=".pdf"
                                                   class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-green-100 file:text-green-700 hover:file:bg-green-200 transition-colors">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div x-show="getCurrentSubStepType() === 'viva_details'" x-cloak>
                        <div class="mb-6 space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Date of Viva</label>
                                <input type="date" name="viva_date"
                                       class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4 transition-colors">
                            </div>
                            <div class="p-4 bg-white border border-slate-200 rounded-xl" x-data="{ mode: 'select', search: '', open: false }" @click.away="open = false">
                                <span class="text-xs font-bold text-green-700 tracking-widest uppercase mb-2 flex justify-between items-center">
                                    <span>External Examiner</span>
                                    <button type="button" @click="mode = mode === 'select' ? 'manual' : 'select'; if(mode==='manual') form.external_examiner_id='';" class="text-blue-500 hover:underline lowercase font-medium" x-text="mode === 'select' ? 'add manually' : 'choose from list'"></button>
                                </span>
                                
                                <div x-show="mode === 'select'" class="relative">
                                    <input type="text" x-model="search" @focus="open = true" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4" placeholder="Search external examiner...">
                                    
                                    <div x-show="open" class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                        <template x-for="examiner in allExternalExaminers.filter(e => e.name.toLowerCase().includes(search.toLowerCase()))">
                                            <div @click="
                                                    if (examiner.type === 'examiner') {
                                                        form.external_examiner_id = examiner.id;
                                                        form.new_external_examiner_name = '';
                                                        form.new_external_examiner_email = '';
                                                    } else {
                                                        form.external_examiner_id = '';
                                                        form.new_external_examiner_name = examiner.name;
                                                        form.new_external_examiner_email = examiner.email;
                                                    }
                                                    search = examiner.name; 
                                                    open = false;
                                                 " 
                                                 class="px-4 py-3 hover:bg-slate-50 cursor-pointer text-sm font-medium text-slate-700 border-b border-slate-100 last:border-0" 
                                                 x-text="examiner.name + (examiner.type === 'supervisor' ? ' (Supervisor)' : '')"></div>
                                        </template>
                                        <div x-show="allExternalExaminers.filter(e => e.name.toLowerCase().includes(search.toLowerCase())).length === 0" class="px-4 py-3 text-sm text-slate-500">No examiners found.</div>
                                    </div>
                                    <input type="hidden" name="external_examiner_id" :value="form.external_examiner_id">
                                </div>

                                <div x-show="mode === 'manual'" class="space-y-3">
                                    <div>
                                        <input type="text" name="external_examiner_name" x-model="form.new_external_examiner_name" placeholder="Examiner's Full Name" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4">
                                    </div>
                                    <div>
                                        <input type="email" name="external_examiner_email" x-model="form.new_external_examiner_email" placeholder="Examiner's Email Address (Optional)" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-green-500/20 focus:border-green-500 text-sm font-medium py-3 px-4">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Upload Final Thesis (PDF)</label>
                                <input type="file" name="final_thesis_file" accept=".pdf"
                                       class="w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 transition-colors">
                            </div>
                        </div>
                    </div>

                    <button type="button" @click="saveSubStep()" :disabled="isSubmitting" :class="{'opacity-75 cursor-not-allowed': isSubmitting}" class="w-full bg-[#18a04b] text-white font-bold py-4 rounded-xl shadow-lg shadow-green-600/20 hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                        <span x-show="!isSubmitting" x-text="getCurrentSubButtonText()"></span>
                        <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7m0 0l-7 7m7-7H6"/></svg>
                        
                        <span x-show="isSubmitting" style="display: none;" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Uploading...
                        </span>
                    </button>
                </div>
                
                <!-- Sub-step for NO answer (PPT Upload) -->
                <div x-show="showingSubStepNo" x-cloak>
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                        <button type="button" @click="showingSubStepNo = false" class="text-slate-400 hover:text-slate-600 flex items-center gap-1 text-sm font-semibold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            Back
                        </button>
                        <div class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold text-sm shrink-0" x-text="milestoneStep"></div>
                        <h3 class="text-lg font-black text-slate-800 leading-tight">Schedule<br>Presentation</h3>
                    </div>

                    <!-- Error Banner -->
                    <div x-show="errorMessage" x-cloak class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl flex items-center gap-2 font-medium" x-transition>
                        <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span x-text="errorMessage"></span>
                    </div>
                    
                    <p class="text-slate-500 font-medium text-sm mb-6">Please upload your Presentation file (PPT) to be scheduled for the next available presentation slot.</p>

                    <div x-show="getCurrentSubStepNoType() === 'progress_presentation_1_schedule'" x-cloak>
                        <div class="mb-6 space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Upload Progress Presentation 1 (PDF)</label>
                                <input type="file" name="progress_presentation_1_ppt" accept=".pdf"
                                       class="w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 transition-colors">
                            </div>
                        </div>
                    </div>

                    <div x-show="getCurrentSubStepNoType() === 'progress_presentation_2_schedule'" x-cloak>
                        <div class="mb-6 space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Upload Progress Presentation 2 (PDF)</label>
                                <input type="file" name="progress_presentation_2_ppt" accept=".pdf"
                                       class="w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 transition-colors">
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" @click="saveSubStepNo()" :disabled="isSubmitting" :class="{'opacity-75 cursor-not-allowed': isSubmitting}" class="w-full bg-[#18a04b] text-white font-bold py-4 rounded-xl shadow-lg shadow-green-600/20 hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                        <span x-show="!isSubmitting">Save & Complete Registration</span>
                        <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7m0 0l-7 7m7-7H6"/></svg>
                        
                        <span x-show="isSubmitting" style="display: none;" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Uploading...
                        </span>
                    </button>
                </div>

            </div>



        </form>
    </div>
</div>

<div class="hidden !ring-2 !ring-red-500 !border-red-500 !bg-red-50 border-slate-200 bg-slate-50"></div>

<style>
    [x-cloak] { display: none !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script>
    // Pass the PHP milestones array to JS
    const serverMilestones = @json($milestones);
    const serverSupervisors = @json($supervisors->map(function($s) { return ['id' => $s->id, 'name' => $s->user->name, 'email' => $s->user->email]; }));
    const serverInternalExaminers = @json($internalExaminers->map(function($e) { return ['id' => $e->id, 'user' => ['name' => $e->user->name, 'email' => $e->user->email]]; }));
    const serverExternalExaminers = @json($externalExaminers->map(function($e) { return ['id' => $e->id, 'user' => ['name' => $e->user->name, 'email' => $e->user->email]]; }));
    const serverLevels = @json($levels);
    const serverPrograms = @json($programs);

    window.registrationWizard = function() {
        return {
            step: 1,
            milestoneStep: 1,
            showingSubStep: false,
            showingSubStepNo: false,
            isSubmitting: false,
            
            allInternalExaminers: [
                ...serverInternalExaminers.map(e => ({ id: e.id, name: e.user.name, email: e.user.email, type: 'examiner' })),
                ...serverSupervisors.map(s => ({ id: s.id, name: s.name, email: s.email, type: 'supervisor' }))
            ],
            
            allExternalExaminers: [
                ...serverExternalExaminers.map(e => ({ id: e.id, name: e.user.name, email: e.user.email, type: 'examiner' })),
                ...serverSupervisors.map(s => ({ id: s.id, name: s.name, email: s.email, type: 'supervisor' }))
            ],
            
            errorMessage: '',
            form: {
                completed_milestones: [],
                supervisor_ids: [],
                principal_supervisor_id: '',
                new_principal_name: '',
                new_principal_email: '',
                co_supervisor_id: '',
                new_co_name: '',
                new_co_email: '',
                third_supervisor_id: '',
                new_third_name: '',
                new_third_email: '',
                internal_examiner_id: '',
                new_internal_examiner_name: '',
                new_internal_examiner_email: '',
                external_examiner_id: '',
                new_external_examiner_name: '',
                new_external_examiner_email: '',
                program_id: ''
            },

            // Hardcode the mapping since we know the 7 exact slugs/names the user just asked for
            // Seminar course, Supervisors asigned, proposal defence, progress presentation 1, Progress Presentation 2, Internal defence, and Viva 
            get questions() {
                let allQuestions = [
                    { id: serverMilestones.find(m => m.slug === 'seminar_as_a_course')?.id, text: "Have you completed your", highlight: "Seminar Course", textAfter: "?", subStep: "grade", title: "Seminar Course Grade", desc: "Since you have completed your Seminar Course, please provide your grade below.", btnText: "Save Grade & Continue" },
                    { id: serverMilestones.find(m => m.slug === 'supervisors_assigned')?.id, text: "Has your", highlight: "Supervisory Committee", textAfter: " been assigned?", subStep: "supervisors", title: "Assign Supervisors", desc: "Since your committee is assigned, please select them below.", btnText: "Save Supervisors & Continue" },
                    { id: serverMilestones.find(m => m.slug === 'proposal_defence')?.id, text: "Have you completed your", highlight: "Proposal Defence", textAfter: "?", subStep: "proposal_defence_details", title: "Proposal Defence Details", desc: "Please provide the date of your Proposal Defence, and your approved Thesis Title & Abstract.", btnText: "Save Details & Continue" },
                    { id: serverMilestones.find(m => m.slug === 'progress_report_1')?.id, text: "Have you completed your", highlight: "Progress Report 1", textAfter: "?", subStep: "progress_report_1_details", title: "Progress Report 1 Details", desc: "Please confirm you have completed and submitted your first progress report.", btnText: "Save & Continue" },
                    { id: serverMilestones.find(m => m.slug === 'progress_report_2')?.id, text: "Have you completed your", highlight: "Progress Report 2", textAfter: "?", subStep: "progress_report_2_details", title: "Progress Report 2 Details", desc: "Please confirm you have completed and submitted your second progress report.", btnText: "Save & Continue" },
                    { id: serverMilestones.find(m => m.slug === 'internal_defence')?.id, text: "Have you completed your", highlight: "Internal Defence", textAfter: "?", subStep: "internal_defence_details", title: "Internal Defence Details", desc: "Since you have completed your Internal Defence, please provide the date, select your internal examiner, and upload your publication.", btnText: "Save Details & Continue" },
                    { id: serverMilestones.find(m => m.slug === 'viva')?.id, text: "Have you completed your", highlight: "Viva", textAfter: "?", subStep: "viva_details", title: "Viva Details", desc: "Since you have completed your Viva, please provide the date, select your external examiner, and upload your final thesis.", btnText: "Save & Complete Registration" }
                ];

                if (this.form.program_id) {
                    const prog = serverPrograms.find(p => p.id == this.form.program_id);
                    if (prog) {
                        const name = prog.name.toLowerCase();
                        if ((name.includes('phd') || name.includes('doctor of philosophy')) && name.includes('artificial intelligence')) {
                            allQuestions = allQuestions.filter(q => q.highlight !== 'Seminar Course');
                        }
                    }
                }
                
                return allQuestions;
            },

            
            isPhd() {
                if (!this.form.program_id) return false;
                const prog = serverPrograms.find(p => p.id == this.form.program_id);
                return prog ? prog.name.toLowerCase().includes('phd') : false;
            },

            validateStep1() {
                this.errorMessage = '';
                const requiredFields = ['name', 'email', 'password', 'password_confirmation', 'matric_number', 'program_id', 'phone_number', 'gender', 'nationality'];

                let isValid = true;

                const setError = (el) => {
                    el.style.outline = '2px solid #ef4444';
                    el.style.outlineOffset = '0px';
                    el.style.borderColor = '#ef4444';
                    el.style.backgroundColor = '#fff1f2';
                };

                const clearError = (el) => {
                    el.style.outline = '';
                    el.style.outlineOffset = '';
                    el.style.borderColor = '';
                    el.style.backgroundColor = '';
                };

                // Reset all fields first
                requiredFields.forEach(field => {
                    let el = document.querySelector(`[name="${field}"]`);
                    if(el) clearError(el);
                });

                // Highlight empty required fields
                for(let field of requiredFields) {
                    let el = document.querySelector(`[name="${field}"]`);
                    if(el && !el.value) {
                        setError(el);
                        isValid = false;
                    }
                }

                if(!isValid) {
                    this.errorMessage = 'Please fill all highlighted fields before continuing.';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return false;
                }

                // Check matric number format
                let matricEl = document.querySelector(`[name="matric_number"]`);
                if(matricEl && matricEl.value) {
                    let val = matricEl.value.trim();
                    if(val.length < 6 || (val.charAt(5) !== '1' && val.charAt(5) !== '2')) {
                        setError(matricEl);
                        this.errorMessage = 'Invalid Matriculation Number. The batch indicator (6th character) must be 1 or 2.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return false;
                    }
                }

                // Check email format
                let emailEl = document.querySelector(`[name="email"]`);
                if(emailEl && emailEl.value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(emailEl.value)) {
                        setError(emailEl);
                        this.errorMessage = 'Please enter a valid email address.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return false;
                    }
                }

                // Check passwords match
                let pw = document.querySelector(`[name="password"]`);
                let pw_conf = document.querySelector(`[name="password_confirmation"]`);
                
                if(pw && pw.value.length < 8) {
                    setError(pw);
                    this.errorMessage = 'The password field must be at least 8 characters.';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return false;
                }
                
                if(pw && pw_conf && pw.value !== pw_conf.value) {
                    setError(pw_conf);
                    this.errorMessage = 'The passwords you entered do not match.';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return false;
                }

                return true;
            },

            async nextStep() {
                try {
                    let valid = this.validateStep1();
                    if (!valid) {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }
                    
                    // Async backend validation for unique email and matric number
                    this.isSubmitting = true;
                    let emailEl = document.querySelector(`[name="email"]`).value;
                    let matricEl = document.querySelector(`[name="matric_number"]`).value;
                    
                    try {
                        const response = await fetch('{{ route("register.validate_step1") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                email: emailEl,
                                matric_number: matricEl
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (!response.ok) {
                            if (data.errors) {
                                let errorMsg = Object.values(data.errors)[0][0];
                                this.errorMessage = errorMsg;
                                
                                if (data.errors.email) {
                                    document.querySelector(`[name="email"]`).style.borderColor = '#ef4444';
                                }
                                if (data.errors.matric_number) {
                                    document.querySelector(`[name="matric_number"]`).style.borderColor = '#ef4444';
                                }
                                
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            } else {
                                this.errorMessage = 'Server error during validation.';
                            }
                            this.isSubmitting = false;
                            return;
                        }
                    } catch (fetchErr) {
                        this.errorMessage = 'Network error checking email/matric. Proceeding...';
                    }
                    
                    this.isSubmitting = false;
                    this.step = 2;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } catch (e) {
                    this.isSubmitting = false;
                    this.errorMessage = 'Error: ' + e.message;
                    if (window.toast) window.toast.error(e.message);
                }
            },

            getCurrentQuestionHtml() {
                let q = this.questions[this.milestoneStep - 1];
                return `${q.text} <span class="text-[#18a04b] underline decoration-2 underline-offset-4">${q.highlight}</span>${q.textAfter || ''}`;
            },
            
            getCurrentSubTitle() {
                return this.questions[this.milestoneStep - 1].title;
            },
            
            getCurrentSubDesc() {
                return this.questions[this.milestoneStep - 1].desc;
            },

            getCurrentSubStepType() {
                return this.questions[this.milestoneStep - 1].subStep;
            },
            
            getCurrentSubStepNoType() {
                return this.questions[this.milestoneStep - 1].subStepNo;
            },
            
            getCurrentSubButtonText() {
                return this.questions[this.milestoneStep - 1].btnText;
            },

            answerYes() {
                this.errorMessage = '';
                let currentQ = this.questions[this.milestoneStep - 1];
                if (currentQ.subStep) {
                    this.showingSubStep = true;
                } else {
                    this.recordMilestoneAndNext(currentQ.id);
                }
            },
            
            answerNo() {
                this.errorMessage = '';
                let currentQ = this.questions[this.milestoneStep - 1];
                if (currentQ.subStepNo) {
                    this.showingSubStepNo = true;
                } else {
                    this.submitForm();
                }
            },
            
            saveSubStep() {
                this.errorMessage = '';
                let type = this.getCurrentSubStepType();
                
                if (type === 'grade') {
                    let gradeInput = document.querySelector('input[name="seminar_grade"]');
                    if (gradeInput && !gradeInput.value.trim()) {
                        this.errorMessage = 'Please provide your Seminar Course Grade.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        gradeInput.focus();
                        return;
                    }
                } else if (type === 'supervisors') {
                    if (!this.form.principal_supervisor_id && !this.form.new_principal_name.trim()) {
                        this.errorMessage = 'Please select or enter a Principal Supervisor.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }
                } else if (type === 'proposal_defence_details' || type === 'internal_defence_details' || type === 'viva_details') {
                    let activeBlock = document.querySelector('[x-show="getCurrentSubStepType() === \'' + type + '\'"]');
                    if (activeBlock) {
                        let dateInput = activeBlock.querySelector('input[type="date"]');
                        if (dateInput && !dateInput.value.trim()) {
                            this.errorMessage = 'Please provide the required date.';
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            dateInput.focus();
                            return;
                        }
                        
                        if (type === 'proposal_defence_details') {
                            let titleInput = activeBlock.querySelector('input[name="thesis_title"]');
                            let abstractInput = activeBlock.querySelector('textarea[name="thesis_abstract"]');
                            if ((titleInput && !titleInput.value.trim()) || (abstractInput && !abstractInput.value.trim())) {
                                this.errorMessage = 'Please provide your approved thesis title and abstract.';
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                                return;
                            }
                        }
                        
                        if (type === 'internal_defence_details') {
                            if (!this.form.internal_examiner_id && !this.form.new_internal_examiner_name.trim()) {
                                this.errorMessage = 'Please select or enter an Internal Examiner.';
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                                return;
                            }
                        }
                        
                        if (type === 'viva_details') {
                            let fileInput = activeBlock.querySelector('input[name="final_thesis_file"]');
                            if (!this.form.external_examiner_id && !this.form.new_external_examiner_name.trim()) {
                                this.errorMessage = 'Please select or enter an External Examiner.';
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                                return;
                            }
                            if (fileInput && !fileInput.value) {
                                this.errorMessage = 'Please upload your final thesis file.';
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                                return;
                            }
                        }
                    }
                }
                
                let currentQ = this.questions[this.milestoneStep - 1];
                this.recordMilestoneAndNext(currentQ.id);
            },
            
            saveSubStepNo() {
                this.errorMessage = '';
                // Validate PPT upload
                let activeBlock = document.querySelector('[x-show="getCurrentSubStepNoType() === \'' + this.getCurrentSubStepNoType() + '\'"]');
                if (activeBlock) {
                    let fileInput = activeBlock.querySelector('input[type="file"]');
                    if (fileInput && !fileInput.value) {
                        this.errorMessage = 'Please upload your Presentation (PPT) file before continuing.';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }
                }
                
                this.submitForm();
            },
            
            recordMilestoneAndNext(id) {
                if (id && !this.form.completed_milestones.includes(id)) {
                    this.form.completed_milestones.push(id);
                }
                
                if (this.milestoneStep < this.questions.length) {
                    this.showingSubStep = false;
                    this.milestoneStep++;
                } else {
                    this.submitForm();
                }
            },
            
            toggleSupervisor(id) {
                if(this.form.supervisor_ids.includes(id)) {
                    this.form.supervisor_ids = this.form.supervisor_ids.filter(v => v !== id);
                } else {
                    this.form.supervisor_ids.push(id);
                }
            },
            
            async submitForm() {
                this.isSubmitting = true;
                this.errorMessage = '';
                
                let formElement = document.getElementById('registrationForm');
                let formData = new FormData(formElement);
                
                try {
                    let response = await fetch(formElement.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    
                    if (response.ok) {
                        let data = await response.json().catch(() => ({}));
                        window.location.href = data.redirect || '{{ route("dashboard") }}';
                    } else if (response.status === 422) {
                        let data = await response.json();
                        if (data.errors) {
                            let errorMsg = Object.values(data.errors)[0][0];
                            this.errorMessage = errorMsg;
                        } else {
                            this.errorMessage = data.message || 'Validation failed. Please check your inputs.';
                        }
                        this.isSubmitting = false;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else if (response.status === 419) {
                        // Session expired (CSRF token mismatch)
                        this.errorMessage = 'Session expired. Refreshing secure token, please wait...';
                        try {
                            let csrfResponse = await fetch('/refresh-csrf', {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            
                            if (csrfResponse.ok) {
                                let csrfData = await csrfResponse.json();
                                let newToken = csrfData.token;
                                
                                // Update meta tag
                                let metaTag = document.querySelector('meta[name="csrf-token"]');
                                if (metaTag) metaTag.setAttribute('content', newToken);
                                
                                // Update form inputs
                                document.querySelectorAll('input[name="_token"]').forEach(el => el.value = newToken);
                                
                                // Update formData
                                formData.set('_token', newToken);
                                
                                this.errorMessage = 'Token refreshed. Retrying submission...';
                                
                                // Retry the submission
                                let retryResponse = await fetch(formElement.action, {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json'
                                    }
                                });
                                
                                if (retryResponse.ok) {
                                    let data = await retryResponse.json().catch(() => ({}));
                                    window.location.href = data.redirect || '{{ route("dashboard") }}';
                                    return;
                                } else if (retryResponse.status === 422) {
                                    let data = await retryResponse.json();
                                    if (data.errors) {
                                        this.errorMessage = Object.values(data.errors)[0][0];
                                    } else {
                                        this.errorMessage = data.message || 'Validation failed. Please check your inputs.';
                                    }
                                } else {
                                    let data = await retryResponse.json().catch(() => ({}));
                                    this.errorMessage = data.message || 'An unexpected error occurred during submission.';
                                }
                            } else {
                                this.errorMessage = 'Could not refresh session. Please refresh the page manually and try again.';
                            }
                        } catch (e) {
                            this.errorMessage = 'Network error during session refresh. Please try again.';
                        }
                        this.isSubmitting = false;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        let data = await response.json().catch(() => ({}));
                        this.errorMessage = data.message || 'An unexpected error occurred during submission.';
                        this.isSubmitting = false;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                } catch (e) {
                    this.errorMessage = 'Network error during submission. Please try again.';
                    this.isSubmitting = false;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
        };
    }
</script>
@endsection
