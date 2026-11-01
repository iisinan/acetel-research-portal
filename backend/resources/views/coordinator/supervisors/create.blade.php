@extends('layouts.coordinator')

@section('content')
<div class="space-y-6 sm:space-y-10 pb-10">
    <!-- Sophisticated Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 sm:gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <div class="p-1.5 rounded-lg bg-acetel-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Add Supervisor</span>
            </div>
            <h1 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight">Create Supervisor</h1>
            <p class="mt-1 sm:mt-2 text-xs sm:text-sm font-medium text-slate-500">Add a new supervisor to the system.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('coordinator.supervisors.index') }}" class="w-full sm:w-auto px-4 sm:px-5 py-2.5 rounded-xl sm:rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-md hover:border-slate-300 transition-all flex items-center justify-center gap-2 sm:gap-3 text-xs font-black text-slate-600 uppercase tracking-widest">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Supervisors
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 sm:p-5 rounded-2xl bg-rose-50 border border-rose-100 flex gap-3 sm:gap-4">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-rose-500 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <h3 class="text-xs sm:text-sm font-black text-rose-900 uppercase tracking-widest mb-1">Validation Failure</h3>
                <ul class="text-xs font-medium text-rose-600 space-y-1 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-10">
        
        <!-- Individual Provisioning Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden relative">
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-acetel-500 to-acetel-600"></div>
                <div class="p-5 sm:p-8 md:p-10">
                    <div class="mb-6 sm:mb-8">
                        <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">Supervisor Profile</h3>
                        <p class="text-xs sm:text-sm font-medium text-slate-500 mt-1">Provide the details for the new supervisor.</p>
                    </div>

                    <form action="{{ route('coordinator.supervisors.store') }}" method="POST" class="space-y-6 sm:space-y-8">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                            <div>
                                <label for="name" class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 sm:mb-3 ml-1">Full Legal Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g. Dr. Adamu Bello" class="block w-full px-4 py-3 sm:py-3.5 bg-slate-50 border-transparent rounded-xl sm:rounded-2xl text-sm font-bold text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-500 transition-all">
                            </div>

                            <div>
                                <label for="email" class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 sm:mb-3 ml-1">Email Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="e.g. abello@noun.edu.ng" class="block w-full px-4 py-3 sm:py-3.5 bg-slate-50 border-transparent rounded-xl sm:rounded-2xl text-sm font-bold text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-500 transition-all">
                            </div>

                            <div class="md:col-span-2">
                                <label for="rank" class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 sm:mb-3 ml-1">Academic Rank</label>
                                <select id="rank" name="rank" required class="block w-full px-4 py-3 sm:py-3.5 bg-slate-50 border-transparent rounded-xl sm:rounded-2xl text-sm font-bold text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-500 transition-all">
                                    <option value="">Select Rank...</option>
                                    <option value="Professor" {{ old('rank') == 'Professor' ? 'selected' : '' }}>Professor</option>
                                    <option value="Associate Professor" {{ old('rank') == 'Associate Professor' ? 'selected' : '' }}>Associate Professor</option>
                                    <option value="Reader" {{ old('rank') == 'Reader' ? 'selected' : '' }}>Reader</option>
                                    <option value="Senior Lecturer" {{ old('rank') == 'Senior Lecturer' ? 'selected' : '' }}>Senior Lecturer</option>
                                    <option value="Lecturer I" {{ old('rank') == 'Lecturer I' ? 'selected' : '' }}>Lecturer I</option>
                                    <option value="Lecturer II" {{ old('rank') == 'Lecturer II' ? 'selected' : '' }}>Lecturer II</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 sm:mb-3 ml-1">Assigned Programs (Max 2)</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                    @foreach($programs as $program)
                                        <label class="relative flex items-center p-3.5 sm:p-4 border border-slate-100 rounded-xl sm:rounded-2xl cursor-pointer hover:bg-slate-50 transition-colors group">
                                            <input name="program_ids[]" type="checkbox" value="{{ $program->id }}" class="peer sr-only" {{ in_array($program->id, old('program_ids', [])) ? 'checked' : '' }}>
                                            <div class="w-5 h-5 rounded border-2 border-slate-300 peer-checked:bg-acetel-500 peer-checked:border-acetel-500 flex items-center justify-center transition-colors mr-3 shrink-0">
                                                <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity transform scale-50 peer-checked:scale-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                            </div>
                                            <span class="text-xs sm:text-sm font-bold text-slate-700 group-hover:text-slate-900">{{ $program->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label for="expertise" class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 sm:mb-3 ml-1">Area of Expertise / Research Focus</label>
                                <textarea id="expertise" name="expertise" rows="3" placeholder="e.g. Artificial Intelligence, Cryptography, Educational Tech..." class="block w-full px-4 py-3 sm:py-3.5 bg-slate-50 border-transparent rounded-xl sm:rounded-2xl text-sm font-bold text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-4 focus:ring-acetel-500/10 focus:border-acetel-500 transition-all resize-none">{{ old('expertise') }}</textarea>
                            </div>
                        </div>

                        <!-- Security Note -->
                        <div class="p-4 sm:p-6 bg-acetel-50/50 border border-acetel-100 rounded-xl sm:rounded-2xl flex items-start gap-3 sm:gap-4">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-acetel-500 rounded-xl flex items-center justify-center text-white shrink-0 shadow-lg shadow-acetel-500/20">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            </div>
                            <div>
                                <p class="text-xs font-black text-acetel-900 uppercase tracking-widest leading-none mb-1.5 sm:mb-2">Automatic Password Email</p>
                                <p class="text-[11px] font-medium text-acetel-700/80 leading-relaxed uppercase tracking-tighter">An email will be sent to the supervisor with their login details once created.</p>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full flex items-center justify-center gap-3 px-6 py-3.5 sm:py-4 bg-slate-900 text-white rounded-xl sm:rounded-2xl text-xs sm:text-sm font-black uppercase tracking-widest hover:bg-black hover:shadow-xl hover:shadow-slate-900/20 transition-all group">
                                Create Supervisor
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-acetel-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bulk Upload Section -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 p-5 sm:p-8 md:p-10 sticky top-10">
                <div class="mb-6 sm:mb-8">
                    <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">Bulk Import</h3>
                    <p class="text-xs sm:text-sm font-medium text-slate-500 mt-1">Upload CSV files to add multiple supervisors quickly.</p>
                </div>

                <div class="space-y-4 mb-6 sm:mb-8">
                    <div class="p-3.5 sm:p-4 bg-slate-50 rounded-xl sm:rounded-2xl border border-slate-100">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Required Headers:</p>
                        <code class="text-xs font-mono font-bold text-acetel-600 px-2.5 py-1 bg-white rounded-lg border border-acetel-100 block truncate">S/n, fullname, email</code>
                    </div>
                    
                    <a href="{{ route('coordinator.supervisors.template') }}" class="flex items-center gap-3 p-3.5 sm:p-4 bg-acetel-50 rounded-xl sm:rounded-2xl border border-acetel-100 group hover:bg-acetel-100 transition-colors">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-acetel-600 shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-black text-acetel-900 uppercase tracking-widest leading-none mb-1">Download Template</p>
                            <p class="text-[11px] font-medium text-acetel-600 uppercase tracking-tighter truncate">Ready-to-use CSV payload</p>
                        </div>
                    </a>
                </div>

                <form action="{{ route('coordinator.supervisors.bulkStore') }}" method="POST" enctype="multipart/form-data" class="space-y-5 sm:space-y-6" x-data="{ selectedPrograms: {{ json_encode(old('bulk_program_ids', [])) }} }">
                    @csrf
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 sm:mb-3 ml-1">Target Programs</label>
                        <div class="grid grid-cols-1 gap-2.5 max-h-[180px] sm:max-h-[200px] overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($programs as $program)
                                <label class="relative flex items-center p-3 border border-slate-100 rounded-xl sm:rounded-2xl cursor-pointer hover:bg-slate-50 transition-colors group">
                                    <input name="bulk_program_ids[]" type="checkbox" value="{{ $program->id }}" class="peer sr-only" x-model="selectedPrograms">
                                    <div class="w-4 h-4 rounded border-2 border-slate-300 peer-checked:bg-acetel-500 peer-checked:border-acetel-500 flex items-center justify-center transition-colors mr-3 shrink-0">
                                        <svg class="w-2.5 h-2.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity transform scale-50 peer-checked:scale-100" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-[10px] font-black text-slate-500 uppercase shrink-0">#{{ $program->serial_number ?? 'N/A' }}</span>
                                        <span class="text-xs font-bold text-slate-700 group-hover:text-slate-900 truncate" title="{{ $program->name }}">{{ $program->name }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <p x-show="selectedPrograms.length === 0" class="mt-2 text-[10px] font-bold text-rose-500 uppercase tracking-wider">Please select at least one program</p>
                    </div>

                    <div>
                        <div class="relative group cursor-pointer">
                            <input id="csv_file" name="csv_file" type="file" accept=".csv" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required onchange="document.getElementById('file-name').textContent = this.files[0].name.substring(0, 20) + (this.files[0].name.length > 20 ? '...' : '')">
                            <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 sm:p-8 text-center bg-slate-50 group-hover:bg-acetel-50 group-hover:border-acetel-300 transition-colors">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white rounded-full mx-auto flex items-center justify-center shadow-sm mb-3 text-slate-400 group-hover:text-acetel-500 transition-colors">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                </div>
                                <span class="text-xs sm:text-sm font-bold text-acetel-600">Select Dataset</span>
                                <p id="file-name" class="mt-1 sm:mt-2 text-xs font-medium text-slate-500">CSV payload (max 10MB)</p>
                            </div>
                        </div>
                    </div>
                    <button type="submit" 
                        :disabled="selectedPrograms.length === 0"
                        :class="selectedPrograms.length === 0 ? 'opacity-50 cursor-not-allowed bg-slate-100 grayscale' : 'hover:bg-slate-50 hover:border-slate-300'"
                        class="w-full px-6 py-3 sm:py-3.5 bg-white border border-slate-200 shadow-sm rounded-xl sm:rounded-2xl text-xs font-black text-slate-700 uppercase tracking-widest transition-all flex justify-center items-center gap-2 group">
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-acetel-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        Import CSV
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
