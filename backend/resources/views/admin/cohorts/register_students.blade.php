@extends('layouts.admin')

@section('header')
    Register Students
@endsection

@section('content')
<div class="space-y-10 animate-in-up">
    <!-- Premium Header -->
    <div class="md:flex md:items-center md:justify-between gap-6">
        <div class="flex-1">
            <div class="inline-flex items-center gap-3 px-4 py-1.5 rounded-full bg-acetel-50 border border-acetel-100 mb-4">
                <div class="w-1.5 h-1.5 rounded-full bg-acetel-600 animate-pulse"></div>
                <span class="text-[9px] font-black text-acetel-700 uppercase tracking-widest">Active</span>
            </div>
            <h2 class="text-4xl font-black text-slate-900 tracking-tighter leading-none mb-3">Register Students</h2>
            <p class="text-slate-500 font-medium text-lg leading-relaxed max-w-2xl">Registering students for cohort <span class="text-acetel-600 font-bold italic underline underline-offset-4 decoration-acetel-200">{{ $cohort->name }}</span>.</p>
        </div>
        <div class="mt-8 flex md:mt-0 shrink-0">
            @php $backRoute = auth()->user()->hasRole('Admin') ? 'admin.cohorts.show' : 'coordinator.cohorts.show'; @endphp
            <a href="{{ route($backRoute, $cohort) }}" class="inline-flex items-center gap-3 px-6 py-4 bg-white border border-slate-200 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-700 hover:bg-slate-50 hover:border-acetel-200 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                View Cohort
            </a>
        </div>
    </div>

    <!-- Interface Switcher -->
    <div class="bg-white shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden rounded-[3rem]" x-data="{ tab: 'single' }">
        <div class="border-b border-slate-200 bg-slate-50/50 px-8 py-2">
            <nav class="flex space-x-10" aria-label="Tabs">
                <button @click="tab = 'single'" 
                    :class="{ 'border-acetel-600 text-acetel-700': tab === 'single', 'border-transparent text-slate-400 hover:text-slate-600 hover:border-slate-200': tab !== 'single' }" 
                    class="relative whitespace-nowrap py-6 px-1 border-b-2 font-black text-[11px] uppercase tracking-widest transition-all focus:outline-none flex items-center gap-3">
                    Single Student
                </button>
                <button @click="tab = 'bulk'" 
                    :class="{ 'border-acetel-600 text-acetel-700': tab === 'bulk', 'border-transparent text-slate-400 hover:text-slate-600 hover:border-slate-200': tab !== 'bulk' }" 
                    class="relative whitespace-nowrap py-6 px-1 border-b-2 font-black text-[11px] uppercase tracking-widest transition-all focus:outline-none flex items-center gap-3">
                    Bulk Import
                </button>
            </nav>
        </div>

        <div class="p-10 md:p-16 w-full max-w-5xl mx-auto">
            <!-- Manual Enrollment Form -->
            <div x-show="tab === 'single'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                @php $storeRoute = auth()->user()->hasRole('Admin') ? 'admin.cohorts.register-students.store' : 'coordinator.cohorts.register-students.store'; @endphp
                <form action="{{ route($storeRoute, $cohort) }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="single">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                        <div>
                            <label for="name" class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">Full Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-300 transition-all outline-none" placeholder="Enter full name" required>
                            @error('name') <p class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">Email Address <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-300 transition-all outline-none" placeholder="Enter email address" required>
                            @error('email') <p class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="matrix_number" class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">Matric Number <span class="text-rose-500">*</span></label>
                            <input type="text" name="matrix_number" id="matrix_number" value="{{ old('matrix_number') }}" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-300 transition-all outline-none" placeholder="e.g. ACE24210001" required>
                            @error('matrix_number') <p class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="program_id" class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">Program <span class="text-rose-500">*</span></label>
                            <select id="program_id" name="program_id" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-300 transition-all outline-none appearance-none" required>
                                <option value="">Select Program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>{{ $program->name }} ({{ $program->code }})</option>
                                @endforeach
                            </select>
                            @error('program_id') <p class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="gender" class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">Gender <span class="text-rose-500">*</span></label>
                            <select id="gender" name="gender" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-300 transition-all outline-none appearance-none" required>
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('Female') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender') <p class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="phone_number" class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">Phone Number <span class="text-rose-500">*</span></label>
                            <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-300 transition-all outline-none" placeholder="+234..." required>
                            @error('phone_number') <p class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="nationality" class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">Nationality <span class="text-rose-500">*</span></label>
                            <input type="text" name="nationality" id="nationality" value="{{ old('nationality') }}" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-300 transition-all outline-none" placeholder="E.g. Nigerian" required>
                            @error('nationality') <p class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="place_of_work" class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 ml-1">Place of Work</label>
                            <input type="text" name="place_of_work" id="place_of_work" value="{{ old('place_of_work') }}" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-6 py-4 font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-300 transition-all outline-none" placeholder="Where do they currently work?">
                            @error('place_of_work') <p class="text-rose-500 text-[10px] mt-2 font-black uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-12 pt-10 border-t border-slate-50 flex justify-end">
                        <button type="submit" class="group flex items-center justify-center gap-4 px-10 py-5 bg-slate-900 rounded-[1.5rem] text-xs font-black uppercase tracking-widest text-white hover:bg-acetel-600 transition-all shadow-xl shadow-slate-900/10">
                            Register Student
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Automated Ingestion Form -->
            <div x-show="tab === 'bulk'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <form action="{{ route($storeRoute, $cohort) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="bulk">
                    
                    <div class="space-y-10">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">CSV File <span class="text-rose-500">*</span></label>
                                <a href="{{ asset('templates/student_import_format.csv') }}" download class="text-[10px] font-black uppercase tracking-widest text-acetel-600 border-b border-acetel-200 hover:text-emerald-600 hover:border-emerald-200 transition-all">
                                    Download Template
                                </a>
                            </div>
                            <div class="relative group cursor-pointer" onclick="document.getElementById('csv_file').click()">
                                <div class="absolute -inset-1 bg-gradient-to-r from-acetel-500/20 to-emerald-500/20 rounded-[2.5rem] blur opacity-0 group-hover:opacity-100 transition-all duration-700"></div>
                                <div class="relative flex flex-col items-center justify-center px-6 py-16 border-2 border-slate-200 border-dashed rounded-[2.5rem] bg-white group-hover:border-acetel-500/50 group-hover:bg-acetel-50/10 transition-all duration-500">
                                    <div class="w-20 h-20 rounded-[2rem] bg-slate-50 flex items-center justify-center group-hover:scale-110 transition-all duration-500 group-hover:bg-acetel-500 group-hover:text-white shadow-inner mb-6">
                                        <svg class="h-10 w-10" stroke="currentColor" fill="none" viewBox="0 0 48 48"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4h-12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </div>
                                    <p class="text-slate-900 font-black text-xs uppercase tracking-widest mb-2">Select CSV File</p>
                                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Max size: 10MB</p>
                                    <input id="csv_file" name="csv_file" type="file" class="sr-only" accept=".csv, .txt">
                                    <p id="file-name" class="hidden mt-6 px-6 py-2 bg-acetel-600 text-white text-[10px] font-black rounded-full uppercase tracking-widest animate-in-up"></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white border border-slate-200 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] rounded-[2rem] p-8 md:p-10">
                            <h3 class="text-xl font-black text-slate-900 mb-6 tracking-tight">Import Guide</h3>
                            <div class="space-y-8">
                                <div>
                                    <h4 class="text-[10px] font-black text-slate-400 border-b border-slate-100 pb-2 uppercase tracking-widest mb-4">Required CSV Headers</h4>
                                    <div class="flex flex-wrap gap-2.5">
                                        @foreach(['S/N', 'Surname', 'Other Names', 'Program', 'Matric Number', 'Gender', 'Email Address', 'Phone Number', 'Nationality'] as $col)
                                            <span class="px-3.5 py-1.5 rounded-[0.5rem] bg-slate-50 border border-slate-200 text-[10px] font-black text-slate-600 uppercase tracking-widest shadow-sm">{{ $col }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="grid md:grid-cols-2 gap-10">
                                    <div>
                                        <h4 class="text-[10px] font-black text-slate-400 border-b border-slate-100 pb-2 uppercase tracking-widest mb-4 text-rose-500">Critical Field Rules</h4>
                                        <ul class="space-y-4 text-sm text-slate-600 font-medium">
                                            <li class="flex items-start gap-3">
                                                <div class="w-5 h-5 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center shrink-0 mt-0.5">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                </div>
                                                <span class="leading-relaxed"><strong>Program:</strong> Must contain exactly the <span class="text-acetel-600 font-bold bg-acetel-50 px-1 py-0.5 rounded">Serial Number</span> of the program (e.g. 1, 2, 3). See Programs directory for mapping.</span>
                                            </li>
                                            <li class="flex items-start gap-3">
                                                <div class="w-5 h-5 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center shrink-0 mt-0.5">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                                                </div>
                                                <span class="leading-relaxed"><strong>Email Address:</strong> Will be used as login ID. Passwords are auto-generated and emailed.</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-black text-slate-400 border-b border-slate-100 pb-2 uppercase tracking-widest mb-4">Formatting & Default Stats</h4>
                                        <ul class="space-y-4 text-sm text-slate-600 font-medium">
                                            <li class="flex items-start gap-3">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-2 shrink-0"></span>
                                                <span class="leading-relaxed">Keep the provided header row intact; the processor skips the first line.</span>
                                            </li>
                                            <li class="flex items-start gap-3">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-2 shrink-0"></span>
                                                <span class="leading-relaxed">Empty names/emails will omit the row seamlessly.</span>
                                            </li>
                                            <li class="flex items-start gap-3">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-2 shrink-0"></span>
                                                <span class="leading-relaxed">Enrollment starts dynamically at 'active', Semester 1.</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-12 pt-10 border-t border-slate-50 flex justify-end">
                            <button type="submit" class="group flex items-center justify-center gap-4 px-10 py-5 bg-slate-900 rounded-[1.5rem] text-xs font-black uppercase tracking-widest text-white hover:bg-acetel-600 transition-all shadow-xl shadow-slate-900/10">
                                Upload Students
                                <svg class="w-5 h-5 group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            </button>
                        </div>
                    </div>
                </form>
                <script>
                    document.getElementById('csv_file').addEventListener('change', function(e) {
                        const fileName = e.target.files[0]?.name || 'No file selected';
                        const el = document.getElementById('file-name');
                        el.textContent = fileName;
                        el.classList.remove('hidden');
                    });
                </script>
            </div>
        </div>
    </div>
</div>
@endsection
