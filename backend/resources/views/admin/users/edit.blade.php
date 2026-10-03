@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-extrabold leading-7 text-black sm:text-3xl sm:truncate">Edit User: {{ $user->name }}</h2>
            <p class="mt-2 text-sm text-black">Update access, roles, and profile.</p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-xl shadow-sm text-sm font-bold text-black hover:bg-slate-50 focus:outline-none transition-colors">
                <svg class="w-4 h-4 mr-2 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Directory
            </a>
        </div>
    </div>

    <div class="bg-white shadow-sm border border-slate-200 overflow-hidden rounded-2xl">
        <div class="px-4 py-5 sm:p-8">
            @php
                $existingRoles = $user->roles->pluck('name')->toArray();
                $isStudent = in_array('Student', $existingRoles);
                $isAdmin = in_array('Admin', $existingRoles) || in_array('Director', $existingRoles);
                $initCat = $isStudent ? 'student' : ($isAdmin && count($existingRoles) === 1 ? 'administrative' : 'faculty');
                $defaultRoles = old('roles', $existingRoles);
                if (empty($defaultRoles)) {
                    $defaultRoles = ['Supervisor'];
                }
            @endphp
            <form action="{{ route('admin.users.update', $user) }}" method="POST" x-data="{ 
                accountCategory: '{{ old('account_category', $initCat) }}',
                selectedRoles: {{ json_encode($defaultRoles) }},
                adminRole: '{{ old('admin_role', in_array('Director', $existingRoles) ? 'Director' : 'Admin') }}',
                programs: {{ 
                    json_encode(old('coordinator_programs', array_values(array_unique(array_filter(array_merge(
                        $user->coordinatorProfiles->pluck('program_id')->toArray(),
                        $user->internalExaminerProfiles->pluck('program_id')->toArray(),
                        $user->externalExaminerProfiles->pluck('program_id')->toArray(),
                        $user->supervisorProfile?->programs->pluck('id')->toArray() ?? []
                    )))))) 
                }},
                isFaculty() {
                    return this.accountCategory === 'faculty';
                },
                hasRole(roleName) {
                    return this.selectedRoles.includes(roleName);
                },
                toggleFacultyRole(roleName) {
                    if (this.selectedRoles.includes(roleName)) {
                        if (this.selectedRoles.length > 1) {
                            this.selectedRoles = this.selectedRoles.filter(r => r !== roleName);
                        }
                    } else {
                        this.selectedRoles.push(roleName);
                    }
                    if (this.programs.length === 0) {
                        this.addProgram();
                    }
                },
                setCategory(cat) {
                    this.accountCategory = cat;
                    if (cat === 'student') {
                        this.selectedRoles = ['Student'];
                    } else if (cat === 'administrative') {
                        this.selectedRoles = [this.adminRole];
                    } else {
                        this.selectedRoles = this.selectedRoles.filter(r => ['Supervisor', 'Program Coordinator', 'Internal Examiner', 'External Examiner'].includes(r));
                        if (this.selectedRoles.length === 0) {
                            this.selectedRoles = ['Supervisor'];
                        }
                        if (this.programs.length === 0) {
                            this.addProgram();
                        }
                    }
                },
                setAdminRole(r) {
                    this.adminRole = r;
                    this.selectedRoles = [r];
                },
                addProgram() {
                    this.programs.push('');
                },
                removeProgram(index) {
                    this.programs.splice(index, 1);
                }
            }" x-init="if(isFaculty() && programs.length === 0) addProgram()">
                @csrf
                @method('PUT')
                <input type="hidden" name="account_category" :value="accountCategory">
                <!-- Hidden inputs for roles[] -->
                <template x-for="r in selectedRoles" :key="r">
                    <input type="hidden" name="roles[]" :value="r">
                </template>
                <!-- Fallback single role parameter for backward compatibility -->
                <input type="hidden" name="role" :value="selectedRoles[0] || ''">

                <div class="grid grid-cols-6 gap-6">
                    <!-- Name -->
                    <div class="col-span-6 sm:col-span-3">
                        <label for="name" class="block text-sm font-semibold text-black mb-1">Full Name</label>
                        <input type="text" name="name" id="name" autocomplete="name" class="focus:ring-acetel-500 focus:border-acetel-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-xl px-4 py-2.5 transition-colors" value="{{ old('name', $user->name) }}" required>
                        @error('name') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div class="col-span-6 sm:col-span-3">
                        <label for="email" class="block text-sm font-semibold text-black mb-1">Email Address</label>
                        <input type="email" name="email" id="email" autocomplete="email" class="focus:ring-acetel-500 focus:border-acetel-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-xl px-4 py-2.5 transition-colors" value="{{ old('email', $user->email) }}" required>
                        @error('email') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Role Category Selection Tabs -->
                    <div class="col-span-6">
                        <label class="block text-sm font-semibold text-black mb-2">Account Type & Roles</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-1.5 bg-slate-100 rounded-2xl border border-slate-200">
                            <button type="button" @click="setCategory('faculty')"
                                    :class="accountCategory === 'faculty' ? 'bg-white text-green-700 shadow-sm font-black border border-green-200' : 'text-slate-600 hover:text-black font-semibold'"
                                    class="py-3 px-4 rounded-xl text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-all">
                                <span>🎓 Academic Faculty</span>
                            </button>
                            <button type="button" @click="setCategory('student')"
                                    :class="accountCategory === 'student' ? 'bg-white text-green-700 shadow-sm font-black border border-green-200' : 'text-slate-600 hover:text-black font-semibold'"
                                    class="py-3 px-4 rounded-xl text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-all">
                                <span>🎒 Student Candidate</span>
                            </button>
                            <button type="button" @click="setCategory('administrative')"
                                    :class="accountCategory === 'administrative' ? 'bg-white text-green-700 shadow-sm font-black border border-green-200' : 'text-slate-600 hover:text-black font-semibold'"
                                    class="py-3 px-4 rounded-xl text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-all">
                                <span>🏛️ Administrative</span>
                            </button>
                        </div>
                    </div>

                    <!-- Academic Faculty Multi-Role Selection Box -->
                    <div x-show="accountCategory === 'faculty'" x-cloak class="col-span-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-black text-black uppercase tracking-wider">Faculty Role Assignments</h4>
                                <p class="text-xs text-slate-500 mt-0.5">Faculty members can hold one, several, or all four academic roles simultaneously.</p>
                            </div>
                            <span class="px-2.5 py-1 bg-green-50 border border-green-200 text-green-700 text-[10px] font-black rounded-lg uppercase tracking-wider">Multi-Role Active</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Role 1: Supervisor -->
                            <div @click="toggleFacultyRole('Supervisor')"
                                 :class="hasRole('Supervisor') ? 'bg-emerald-50/70 border-emerald-400 ring-2 ring-emerald-500/20' : 'bg-white border-slate-200 hover:border-slate-300'"
                                 class="p-4 rounded-2xl border transition-all cursor-pointer flex items-start gap-3.5 group select-none">
                                <div class="mt-0.5">
                                    <div :class="hasRole('Supervisor') ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white border-slate-300'"
                                         class="w-5 h-5 rounded-md border flex items-center justify-center transition-colors">
                                        <svg x-show="hasRole('Supervisor')" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h5 class="text-sm font-black text-slate-800 tracking-tight leading-none">Research Supervisor</h5>
                                        <span class="text-[9px] font-bold uppercase tracking-widest text-emerald-700 bg-emerald-100/60 px-1.5 py-0.5 rounded">Mentorship</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-1 font-medium leading-relaxed">Directs candidate research, reviews milestones, and guides scholarly progress.</p>
                                </div>
                            </div>

                            <!-- Role 2: Program Coordinator -->
                            <div @click="toggleFacultyRole('Program Coordinator')"
                                 :class="hasRole('Program Coordinator') ? 'bg-blue-50/70 border-blue-400 ring-2 ring-blue-500/20' : 'bg-white border-slate-200 hover:border-slate-300'"
                                 class="p-4 rounded-2xl border transition-all cursor-pointer flex items-start gap-3.5 group select-none">
                                <div class="mt-0.5">
                                    <div :class="hasRole('Program Coordinator') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white border-slate-300'"
                                         class="w-5 h-5 rounded-md border flex items-center justify-center transition-colors">
                                        <svg x-show="hasRole('Program Coordinator')" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h5 class="text-sm font-black text-slate-800 tracking-tight leading-none">Program Coordinator</h5>
                                        <span class="text-[9px] font-bold uppercase tracking-widest text-blue-700 bg-blue-100/60 px-1.5 py-0.5 rounded">Management</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-1 font-medium leading-relaxed">Coordinates postgraduate cohorts, manages defences, and approves program clearances.</p>
                                </div>
                            </div>

                            <!-- Role 3: Internal Examiner -->
                            <div @click="toggleFacultyRole('Internal Examiner')"
                                 :class="hasRole('Internal Examiner') ? 'bg-purple-50/70 border-purple-400 ring-2 ring-purple-500/20' : 'bg-white border-slate-200 hover:border-slate-300'"
                                 class="p-4 rounded-2xl border transition-all cursor-pointer flex items-start gap-3.5 group select-none">
                                <div class="mt-0.5">
                                    <div :class="hasRole('Internal Examiner') ? 'bg-purple-600 text-white border-purple-600' : 'bg-white border-slate-300'"
                                         class="w-5 h-5 rounded-md border flex items-center justify-center transition-colors">
                                        <svg x-show="hasRole('Internal Examiner')" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h5 class="text-sm font-black text-slate-800 tracking-tight leading-none">Internal Examiner</h5>
                                        <span class="text-[9px] font-bold uppercase tracking-widest text-purple-700 bg-purple-100/60 px-1.5 py-0.5 rounded">Evaluation</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-1 font-medium leading-relaxed">Assesses thesis manuscripts and serves on internal viva defence panels.</p>
                                </div>
                            </div>

                            <!-- Role 4: External Examiner -->
                            <div @click="toggleFacultyRole('External Examiner')"
                                 :class="hasRole('External Examiner') ? 'bg-amber-50/70 border-amber-400 ring-2 ring-amber-500/20' : 'bg-white border-slate-200 hover:border-slate-300'"
                                 class="p-4 rounded-2xl border transition-all cursor-pointer flex items-start gap-3.5 group select-none">
                                <div class="mt-0.5">
                                    <div :class="hasRole('External Examiner') ? 'bg-amber-600 text-white border-amber-600' : 'bg-white border-slate-300'"
                                         class="w-5 h-5 rounded-md border flex items-center justify-center transition-colors">
                                        <svg x-show="hasRole('External Examiner')" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h5 class="text-sm font-black text-slate-800 tracking-tight leading-none">External Examiner</h5>
                                        <span class="text-[9px] font-bold uppercase tracking-widest text-amber-700 bg-amber-100/60 px-1.5 py-0.5 rounded">External</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-1 font-medium leading-relaxed">Conducts external thesis appraisals and evaluates oral defence sessions.</p>
                                </div>
                            </div>
                        </div>
                        @error('roles') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Administrative Role Selector -->
                    <div x-show="accountCategory === 'administrative'" x-cloak class="col-span-6 sm:col-span-6">
                        <label for="admin_role_select" class="block text-sm font-semibold text-black mb-1">Administrative Privilege</label>
                        <select id="admin_role_select" x-model="adminRole" @change="setAdminRole(adminRole)"
                                class="block w-full py-2.5 px-4 border border-slate-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-acetel-500 focus:border-acetel-500 sm:text-sm font-medium text-black">
                            <option value="Admin">System Administrator (Full Governance)</option>
                            <option value="Director">Center Director (Executive Oversight & Analytics)</option>
                        </select>
                    </div>

                    <!-- Rank (For Supervisors) -->
                    <div x-show="isFaculty() && hasRole('Supervisor')" x-cloak class="col-span-6 sm:col-span-6">
                        <label for="rank" class="block text-sm font-semibold text-black mb-1">Academic Rank (Supervisor)</label>
                        <select id="rank" name="rank" class="block w-full py-2.5 px-4 border border-slate-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-acetel-500 focus:border-acetel-500 sm:text-sm font-medium text-black">
                            <option value="">Select Rank...</option>
                            @php 
                                $ranks = ['Professor', 'Associate Professor', 'Reader', 'Senior Lecturer', 'Lecturer I', 'Lecturer II'];
                                $currentRank = $user->supervisorProfile?->rank;
                            @endphp
                            @foreach($ranks as $rank)
                                <option value="{{ $rank }}" {{ (old('rank', $currentRank) == $rank) ? 'selected' : '' }}>{{ $rank }}</option>
                            @endforeach
                        </select>
                        @error('rank') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- External Institution (For External Examiners) -->
                    <div x-show="isFaculty() && hasRole('External Examiner')" x-cloak class="col-span-6 sm:col-span-6">
                        <label for="institution" class="block text-sm font-semibold text-black mb-1">External Home Institution</label>
                        <input type="text" name="institution" id="institution" placeholder="e.g. University of Lagos, MIT, Oxford"
                               value="{{ old('institution', $user->externalExaminerProfiles->first()?->institution) }}"
                               class="focus:ring-acetel-500 focus:border-acetel-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-xl px-4 py-2.5 transition-colors">
                    </div>

                    <!-- Program Assignment (Coordinator, Examiners & Supervisor) -->
                    <div x-show="isFaculty()" x-cloak class="col-span-6 space-y-4 pt-6 mt-4 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold leading-6 text-black">Program Affiliations</h3>
                                <p class="text-sm text-black">Assign the academic programs this faculty member is affiliated with across their roles.</p>
                            </div>
                            <button type="button" @click="addProgram()" class="inline-flex items-center px-3 py-1.5 border border-slate-300 shadow-sm text-xs font-bold rounded-lg text-black bg-white hover:bg-slate-50 focus:outline-none transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                                Add Program
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(prog, index) in programs" :key="index">
                                <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                    <div class="flex-1">
                                        <select :name="'coordinator_programs['+index+']'" x-model="programs[index]" class="block w-full py-2 px-4 border border-slate-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-acetel-500 focus:border-acetel-500 text-sm font-medium text-black">
                                            <option value="">Select Program...</option>
                                            @foreach($programs as $program)
                                                <option value="{{ $program->id }}">{{ $program->name }} ({{ $program->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" @click="removeProgram(index)" class="p-2 text-slate-400 hover:text-red-600 transition-colors" x-show="programs.length > 1">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Student Details Section -->
                    <div x-show="accountCategory === 'student'" x-cloak class="col-span-6 space-y-6 pt-6 mt-4 border-t border-slate-100">
                        <div>
                            <h3 class="text-lg font-bold leading-6 text-black">Student Info</h3>
                            <p class="text-sm text-black">Assign the core academic profile for this student account.</p>
                        </div>

                        <div class="grid grid-cols-6 gap-6">
                            <div class="col-span-6 sm:col-span-3">
                                <label for="student_id_number" class="block text-sm font-semibold text-black mb-1">Matric/Student ID</label>
                                <input type="text" name="student_id_number" id="student_id_number" 
                                    class="focus:ring-acetel-500 focus:border-acetel-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-xl px-4 py-2.5 transition-colors" 
                                    value="{{ old('student_id_number', $user->studentProfile?->student_id_number) }}" placeholder="e.g. ACE2510001">
                                @error('student_id_number') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="cohort_id" class="block text-sm font-semibold text-black mb-1">Academic Cohort</label>
                                <select id="cohort_id" name="cohort_id" 
                                    class="block w-full py-2.5 px-4 border border-slate-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-acetel-500 focus:border-acetel-500 sm:text-sm font-medium text-black">
                                    <option value="">Select Cohort...</option>
                                    @foreach($cohorts as $cohort)
                                        <option value="{{ $cohort->id }}" {{ old('cohort_id', $user->studentProfile?->cohort_id) == $cohort->id ? 'selected' : '' }}>{{ $cohort->name }}</option>
                                    @endforeach
                                </select>
                                @error('cohort_id') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="program_id" class="block text-sm font-semibold text-black mb-1">Program</label>
                                <select id="program_id" name="program_id" 
                                    class="block w-full py-2.5 px-4 border border-slate-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-acetel-500 focus:border-acetel-500 sm:text-sm font-medium text-black">
                                    <option value="">Select Program...</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}" {{ old('program_id', $user->studentProfile?->program_id) == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                                    @endforeach
                                </select>
                                @error('program_id') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="level_id" class="block text-sm font-semibold text-black mb-1">Level</label>
                                <select id="level_id" name="level_id" 
                                    class="block w-full py-2.5 px-4 border border-slate-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-acetel-500 focus:border-acetel-500 sm:text-sm font-medium text-black">
                                    <option value="">Select Level...</option>
                                    @foreach($levels as $level)
                                        <option value="{{ $level->id }}" {{ old('level_id', $user->studentProfile?->level_id) == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                                    @endforeach
                                </select>
                                @error('level_id') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Milestone Management -->
                        @if($user->studentProfile && $user->studentProfile->thesis)
                        <div class="col-span-6 p-4 bg-slate-50 rounded-xl border border-slate-200 mt-6">
                            <h4 class="text-sm font-bold text-black mb-1">Milestone Management</h4>
                            <p class="text-xs text-slate-500 mb-4">Administratively promote or demote this student's thesis progress. The student will be notified.</p>
                            <div class="flex gap-3">
                                <button type="button" 
                                    data-confirm="Are you sure you want to demote this student to the previous milestone? The student will be notified to revise and re-submit."
                                    data-confirm-title="Demote Student Milestone"
                                    data-confirm-type="danger"
                                    data-confirm-btn="Demote Milestone"
                                    onclick="document.getElementById('demote-form').submit();"
                                    class="px-4 py-2 bg-red-50 text-red-600 rounded-lg text-xs font-bold border border-red-200 hover:bg-red-100 transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                    Demote Milestone
                                </button>
                                <button type="button" 
                                    data-confirm="Are you sure you want to promote this student to the next milestone? This will advance their research stage."
                                    data-confirm-title="Promote Student Milestone"
                                    data-confirm-type="success"
                                    data-confirm-btn="Promote Milestone"
                                    onclick="document.getElementById('promote-form').submit();"
                                    class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-lg text-xs font-bold border border-emerald-200 hover:bg-emerald-100 transition flex items-center gap-2">
                                    Promote Milestone
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>



                    <!-- Password (Optional) -->
                    <div class="col-span-6 sm:col-span-3">
                        <label for="password" class="block text-sm font-semibold text-black mb-1">New Password (Optional)</label>
                        <input type="password" name="password" id="password" class="focus:ring-acetel-500 focus:border-acetel-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-xl px-4 py-2.5 transition-colors" placeholder="Leave blank to keep current password">
                         @error('password') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Password Confirm -->
                    <div class="col-span-6 sm:col-span-3">
                        <label for="password_confirmation" class="block text-sm font-semibold text-black mb-1">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="focus:ring-acetel-500 focus:border-acetel-500 block w-full shadow-sm sm:text-sm border-slate-300 rounded-xl px-4 py-2.5 transition-colors">
                    </div>

                    <!-- Active Status -->
                    <div class="col-span-6 mt-4">
                        <div class="flex items-start bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="flex items-center h-5 mt-0.5">
                                <input id="is_active" name="is_active" type="checkbox" value="1" class="focus:ring-acetel-500 h-5 w-5 text-acetel-600 border-slate-300 rounded transition-colors" {{ $user->is_active ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_active" class="font-bold text-black cursor-pointer">Active Account</label>
                                <p class="text-black mt-1">If unchecked, the user will be immediately suspended and unable to log in.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100 flex justify-between items-center">
                    <div class="flex gap-2">
                        @if(auth()->id() !== $user->id)
                            <button type="button" 
                                data-confirm="Warning: Are you sure you want to permanently delete this user? All their associated academic records and roles will be removed."
                                data-confirm-title="Delete User Account"
                                data-confirm-type="danger"
                                data-confirm-btn="Delete User"
                                onclick="document.getElementById('delete-user-form').submit();"
                                class="inline-flex justify-center py-2.5 px-4 border border-slate-300 shadow-sm text-sm font-bold rounded-xl text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                Delete User
                            </button>
                        @endif

                        <button type="button" 
                            data-confirm="Securely reset password for this user? Temporary credentials will be generated and dispatched via email."
                            data-confirm-title="Reset User Password"
                            data-confirm-type="warning"
                            data-confirm-btn="Reset Password"
                            onclick="document.getElementById('reset-password-form').submit();"
                            class="inline-flex justify-center py-2.5 px-4 border border-green-300 shadow-sm text-sm font-bold rounded-xl text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            Reset Password
                        </button>
                    </div>
                    
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-transparent bg-acetel-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-acetel-700 focus:outline-none focus:ring-2 focus:ring-acetel-500 focus:ring-offset-2 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                        Save Changes
                    </button>
                </div>
            </form>
            @if(auth()->id() !== $user->id)
                <form id="delete-user-form" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
            <form id="reset-password-form" action="{{ route('admin.users.reset_password', $user) }}" method="POST" class="hidden">
                @csrf
            </form>
            @if($user->studentProfile && $user->studentProfile->thesis)
            <form id="promote-form" action="{{ route('admin.students.promote-milestone', $user->studentProfile) }}" method="POST" class="hidden">
                @csrf
            </form>
            <form id="demote-form" action="{{ route('admin.students.demote-milestone', $user->studentProfile) }}" method="POST" class="hidden">
                @csrf
            </form>
            @endif
        </div>
    </div>
</div>

<script>
// Keep original logic for non-Alpine components if any, but most is handled by Alpine now.
</script>
@endsection
