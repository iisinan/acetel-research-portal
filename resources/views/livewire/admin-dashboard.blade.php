<div class="flex h-screen w-full bg-[#EAF8F1] overflow-hidden font-sans">
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
    
    <!-- Sidebar -->
    <div class="w-64 bg-white flex flex-col flex-shrink-0 shadow-[4px_0_24px_rgba(0,0,0,0.02)] z-10 relative border-r border-[#EAF8F1]">
        
        <!-- App Logo Area -->
        <div class="h-20 flex items-center px-6 mt-4">
            <img src="{{ asset('acetel logo.jpg') }}" alt="ACETEL Logo" class="h-12 object-contain mix-blend-multiply">
        </div>

        <!-- Navigation List -->
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-8 hide-scrollbar">
            
            <!-- Top Section -->
            <div class="space-y-1">
                <button wire:click="setTab('overview')" class="w-full flex items-center px-3 py-2.5 text-sm font-semibold rounded-lg transition-colors {{ $activeTab === 'overview' ? 'text-green-700 bg-green-50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                    <svg class="mr-3 h-5 w-5 {{ $activeTab === 'overview' ? 'text-green-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </button>
            </div>

            <!-- INSTITUTIONAL CORE -->
            <div>
                <h3 class="px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-3">Institutional Core</h3>
                <div class="space-y-1">
                    <button wire:click="setTab('users')" class="w-full flex items-center px-3 py-2.5 text-sm font-semibold rounded-lg transition-colors {{ $activeTab === 'users' ? 'text-green-700 bg-green-50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                        <svg class="mr-3 h-5 w-5 {{ $activeTab === 'users' ? 'text-green-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        User Registry
                    </button>
                    <button wire:click="setTab('programmes')" class="w-full flex items-center px-3 py-2.5 text-sm font-semibold rounded-lg transition-colors {{ $activeTab === 'programmes' ? 'text-green-700 bg-green-50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                        <svg class="mr-3 h-5 w-5 {{ $activeTab === 'programmes' ? 'text-green-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Programs & Degrees
                    </button>
                    <button wire:click="setTab('students')" class="w-full flex items-center px-3 py-2.5 text-sm font-semibold rounded-lg transition-colors {{ $activeTab === 'students' ? 'text-green-700 bg-green-50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                        <svg class="mr-3 h-5 w-5 {{ $activeTab === 'students' ? 'text-green-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                        Student Directory
                    </button>
                </div>
            </div>

            <!-- RESEARCH ASSETS -->
            <div>
                <h3 class="px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-3">Research Assets</h3>
                <div class="space-y-1">
                    <button class="w-full flex items-center px-3 py-2.5 text-sm font-semibold text-slate-400 cursor-not-allowed rounded-lg">
                        <svg class="mr-3 h-5 w-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Milestone Templates
                    </button>
                </div>
            </div>

        </div>

        <!-- User Profile Footer -->
        <div class="bg-[#EAF8F1] p-4 flex items-center justify-between mt-auto">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 bg-green-200 text-green-800 rounded-xl flex items-center justify-center font-bold text-lg shadow-sm">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-slate-800 leading-tight">Admin</span>
                    <span class="text-[10px] font-extrabold text-green-600 uppercase tracking-wide">System Arch...</span>
                </div>
            </div>
            <div class="flex items-center gap-1 text-slate-400">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-1.5 hover:text-slate-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 overflow-y-auto p-4 md:p-6 bg-[#EAF8F1] relative">
        <!-- Massive white rounded card -->
        <div class="bg-white min-h-full rounded-[2.5rem] shadow-sm p-8 md:p-12 relative overflow-hidden">
            
            <div class="max-w-6xl mx-auto relative z-10">
                <!-- Header -->
                <div class="mb-10">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-green-50 border border-green-100 text-green-700 text-xs font-bold tracking-wider uppercase mb-6">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-2"></span>
                        @if($activeTab === 'overview') Institution Overview 
                        @elseif($activeTab === 'users') Registry 
                        @elseif($activeTab === 'students') Student Management
                        @else Academic Setup @endif
                    </div>
                    
                    <h1 class="text-5xl font-extrabold text-slate-900 mb-4 tracking-tight">
                        @if($activeTab === 'overview') System <br/>Dashboard 
                        @elseif($activeTab === 'users') User <br/>Registry 
                        @elseif($activeTab === 'students') Student <br/>Directory
                        @else Academic <br/>Programs @endif
                    </h1>
                    <p class="text-slate-500 text-lg max-w-xl leading-relaxed">
                        @if($activeTab === 'overview') Comprehensive Monitoring and administrative tools for the ACETEL Postgraduate Research Tracking System.
                        @elseif($activeTab === 'users') Manage access, invite new personnel, and oversee the entire institutional user base.
                        @elseif($activeTab === 'students') Monitor and track research progress, manage approvals, and export student data.
                        @else Configure and manage available academic programmes and degree tracks for enrolling students. @endif
                    </p>
                </div>

                <!-- Overview Tab -->
                @if($activeTab === 'overview')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-12">
                        <div class="bg-green-600 rounded-3xl p-8 text-white relative overflow-hidden shadow-lg">
                            <div class="relative z-10">
                                <div class="text-green-100 font-semibold text-sm uppercase tracking-wider mb-2 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    Total Users
                                </div>
                                <div class="text-6xl font-extrabold mb-1">{{ $stats['total_users'] }}</div>
                                <div class="text-green-200 text-sm font-medium">Registered Personnel</div>
                            </div>
                            <!-- Decoration -->
                            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white opacity-10 rounded-full blur-xl"></div>
                        </div>

                        <div class="bg-white border-2 border-slate-100 rounded-3xl p-8 relative overflow-hidden shadow-sm">
                            <div class="text-slate-400 font-semibold text-sm uppercase tracking-wider mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                                Active Students
                            </div>
                            <div class="text-6xl font-extrabold text-slate-900 mb-1">{{ $stats['active_students'] }}</div>
                            <div class="text-slate-500 text-sm font-medium">Out of {{ $stats['total_students'] }} total students</div>
                        </div>
                    </div>
                @endif

                <!-- Users Tab -->
                @if($activeTab === 'users')
                    @if (session()->has('success'))
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.duration.500ms class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-2xl mb-8 flex justify-between items-center font-medium">
                            <span>{{ session('success') }}</span>
                            <button @click="show = false" class="text-green-700 hover:text-green-900">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                        <!-- Create User Form -->
                        <div class="xl:col-span-1 bg-white rounded-3xl p-6 border-2 border-slate-100 shadow-sm">
                            <h3 class="text-lg font-bold mb-6 text-slate-800">Invite New User</h3>
                            <form wire:submit.prevent="createUser" class="space-y-5">
                                <div>
                                    <label class="block text-sm font-bold text-slate-600 mb-1">Full Name</label>
                                    <input type="text" wire:model="newUserName" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-green-500 focus:ring-green-500 transition-colors">
                                    @error('newUserName') <span class="text-red-500 text-xs font-semibold">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-600 mb-1">Email Address</label>
                                    <input type="email" wire:model="newUserEmail" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-green-500 focus:ring-green-500 transition-colors">
                                    @error('newUserEmail') <span class="text-red-500 text-xs font-semibold">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-600 mb-1">Password</label>
                                    <input type="password" wire:model="newUserPassword" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-green-500 focus:ring-green-500 transition-colors">
                                    @error('newUserPassword') <span class="text-red-500 text-xs font-semibold">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-600 mb-1">Assign Role</label>
                                    <select wire:model="newUserRole" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-green-500 focus:ring-green-500 transition-colors">
                                        <option value="">-- Select Role --</option>
                                        @foreach($availableRoles as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('newUserRole') <span class="text-red-500 text-xs font-semibold">{{ $message }}</span> @enderror
                                </div>
                                <div class="pt-2">
                                    <button type="submit" class="w-full flex justify-center py-3 px-4 rounded-xl font-bold text-white bg-green-600 hover:bg-green-700 shadow-md transition-transform transform hover:-translate-y-0.5">
                                        Create Account
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- User List -->
                        <div class="xl:col-span-2 bg-white rounded-3xl p-6 border-2 border-slate-100 shadow-sm">
                            <h3 class="text-lg font-bold mb-6 text-slate-800">Active Directory</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b-2 border-slate-100">
                                            <th class="pb-3 text-xs font-extrabold text-slate-400 uppercase tracking-widest">Name</th>
                                            <th class="pb-3 text-xs font-extrabold text-slate-400 uppercase tracking-widest">Role</th>
                                            <th class="pb-3 text-xs font-extrabold text-slate-400 uppercase tracking-widest">Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($users as $user)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="py-4 pr-4">
                                                <div class="font-bold text-slate-800">{{ $user->name }}</div>
                                                <div class="text-sm font-medium text-slate-500">{{ $user->email }}</div>
                                            </td>
                                            <td class="py-4 pr-4">
                                                @foreach($user->roles as $role)
                                                    <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-lg bg-green-100 text-green-800 border border-green-200">
                                                        {{ $role->name }}
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td class="py-4 text-sm font-medium text-slate-500">
                                                {{ $user->created_at->format('M d, Y') }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Programmes Tab -->
                @if($activeTab === 'programmes')
                    @if (session()->has('success'))
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.duration.500ms class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-2xl mb-8 flex justify-between items-center font-medium">
                            <span>{{ session('success') }}</span>
                            <button @click="show = false" class="text-green-700 hover:text-green-900">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Programmes -->
                        <div class="bg-white rounded-3xl p-6 border-2 border-slate-100 shadow-sm">
                            <h3 class="text-lg font-bold mb-6 text-slate-800">Academic Programmes</h3>
                            
                            <form wire:submit.prevent="createProgramme" class="flex gap-2 mb-8">
                                <div class="flex-1">
                                    <input type="text" wire:model="newProgrammeName" placeholder="e.g. Artificial Intelligence" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-green-500 focus:ring-green-500 font-medium">
                                    @error('newProgrammeName') <span class="text-red-500 text-xs font-semibold">{{ $message }}</span> @enderror
                                </div>
                                <button type="submit" class="px-5 py-2 font-bold rounded-xl text-white bg-green-600 hover:bg-green-700 shadow-sm transition-transform transform hover:-translate-y-0.5">
                                    Add
                                </button>
                            </form>

                            <ul class="divide-y divide-slate-100 border-t-2 border-slate-100 pt-4">
                                @foreach($programmes as $prog)
                                <li class="py-3 flex justify-between items-center group">
                                    <span class="text-slate-800 font-bold">{{ $prog->name }}</span>
                                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-lg group-hover:bg-slate-200 transition-colors">{{ $prog->students_count }} Students</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        
                        <!-- Degrees -->
                        <div class="bg-white rounded-3xl p-6 border-2 border-slate-100 shadow-sm">
                            <h3 class="text-lg font-bold mb-6 text-slate-800">Degree Tracks</h3>
                            
                            <form wire:submit.prevent="createDegree" class="flex gap-2 mb-8">
                                <div class="flex-1">
                                    <input type="text" wire:model="newDegreeName" placeholder="e.g. Post-Graduate Diploma" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-green-500 focus:ring-green-500 font-medium">
                                    @error('newDegreeName') <span class="text-red-500 text-xs font-semibold">{{ $message }}</span> @enderror
                                </div>
                                <button type="submit" class="px-5 py-2 font-bold rounded-xl text-white bg-green-600 hover:bg-green-700 shadow-sm transition-transform transform hover:-translate-y-0.5">
                                    Add
                                </button>
                            </form>

                            <ul class="divide-y divide-slate-100 border-t-2 border-slate-100 pt-4">
                                @foreach($degrees as $deg)
                                <li class="py-3 flex justify-between items-center group">
                                    <span class="text-slate-800 font-bold">{{ $deg->name }}</span>
                                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-lg group-hover:bg-slate-200 transition-colors">{{ $deg->students_count }} Students</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        
                        <!-- Cohorts -->
                        <div class="bg-white rounded-3xl p-6 border-2 border-slate-100 shadow-sm">
                            <h3 class="text-lg font-bold mb-6 text-slate-800">Academic Cohorts</h3>
                            
                            <form wire:submit.prevent="createCohort" class="flex gap-2 mb-8">
                                <div class="flex-1">
                                    <input type="text" wire:model="newCohortName" placeholder="e.g. 2023/2024" class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-green-500 focus:ring-green-500 font-medium">
                                    @error('newCohortName') <span class="text-red-500 text-xs font-semibold">{{ $message }}</span> @enderror
                                </div>
                                <button type="submit" class="px-5 py-2 font-bold rounded-xl text-white bg-green-600 hover:bg-green-700 shadow-sm transition-transform transform hover:-translate-y-0.5">
                                    Add
                                </button>
                            </form>

                            <ul class="divide-y divide-slate-100 border-t-2 border-slate-100 pt-4">
                                @foreach($cohorts as $coh)
                                <li class="py-3 flex justify-between items-center group">
                                    <span class="text-slate-800 font-bold">{{ $coh->name }}</span>
                                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-lg group-hover:bg-slate-200 transition-colors">{{ $coh->students_count }} Students</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Students Tab -->
                @if($activeTab === 'students')
                    @if (session()->has('success'))
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.duration.500ms class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-2xl mb-8 flex justify-between items-center font-medium">
                            <span>{{ session('success') }}</span>
                            <button @click="show = false" class="text-green-700 hover:text-green-900">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    @endif

                    @if($managedStudent)
                        <div class="bg-white rounded-3xl p-6 border-2 border-slate-100 shadow-sm">
                            <button wire:click="closeManageStudent" class="mb-6 flex items-center text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                Back to Directory
                            </button>
                            
                            <h3 class="text-xl font-extrabold text-slate-800 mb-2">{{ $managedStudent->user->name }}</h3>
                            <p class="text-slate-500 font-medium mb-8">Matric No: <span class="font-bold text-slate-700">{{ $managedStudent->matric_no }}</span> | {{ $managedStudent->programme->name ?? 'N/A' }} ({{ $managedStudent->degree->name ?? 'N/A' }})</p>

                            <h4 class="text-sm font-extrabold text-slate-400 uppercase tracking-widest mb-4">Milestone Progress</h4>
                            <div class="space-y-3">
                                @foreach($allMilestones as $ms)
                                    @php
                                        $isCompleted = $managedStudent->milestones->where('milestone_id', $ms->id)->first();
                                    @endphp
                                    <div class="flex items-center justify-between p-4 rounded-xl border {{ $isCompleted ? 'bg-green-50 border-green-100' : 'bg-slate-50 border-slate-100' }}">
                                        <div>
                                            <h5 class="font-bold {{ $isCompleted ? 'text-green-800' : 'text-slate-600' }}">{{ $ms->name }}</h5>
                                            @if($isCompleted)
                                                <span class="text-xs font-medium text-green-600">Completed on {{ \Carbon\Carbon::parse($isCompleted->completion_date)->format('M d, Y') }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            @if($isCompleted)
                                                <button wire:click="demoteStudent({{ $managedStudent->id }}, {{ $ms->id }})" class="text-xs font-bold px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors">
                                                    Demote (Revoke)
                                                </button>
                                            @else
                                                <button wire:click="advanceStudent({{ $managedStudent->id }}, {{ $ms->id }})" class="text-xs font-bold px-3 py-1.5 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition-colors">
                                                    Mark Completed
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-3xl p-6 border-2 border-slate-100 shadow-sm relative">
                            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 relative z-10 gap-4">
                                <div class="flex flex-wrap items-center gap-4">
                                    <h3 class="text-lg font-bold text-slate-800">Student Directory</h3>
                                    <button wire:click="exportStudents" class="flex items-center text-sm font-bold px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow-sm whitespace-nowrap">
                                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        Download Excel (CSV)
                                    </button>
                                </div>
                                
                                <div class="flex flex-wrap items-center gap-3 w-full xl:w-auto">
                                    <select wire:model.live="filterYear" class="flex-1 min-w-[120px] text-sm font-medium border-slate-200 bg-slate-50 rounded-lg focus:ring-green-500 focus:border-green-500 py-2">
                                        <option value="">All Intake Years</option>
                                        <option value="2023">2023</option>
                                        <option value="2024">2024</option>
                                        <option value="2025">2025</option>
                                        <option value="2026">2026</option>
                                    </select>
                                    
                                    <select wire:model.live="filterBatch" class="flex-1 min-w-[120px] text-sm font-medium border-slate-200 bg-slate-50 rounded-lg focus:ring-green-500 focus:border-green-500 py-2">
                                        <option value="">All Batches</option>
                                        <option value="1">1st Batch</option>
                                        <option value="2">2nd Batch</option>
                                    </select>

                                    <select wire:model.live="filterStage" class="flex-1 min-w-[120px] text-sm font-medium border-slate-200 bg-slate-50 rounded-lg focus:ring-green-500 focus:border-green-500 py-2">
                                        <option value="">All Stages</option>
                                        <option value="Registered">Registered</option>
                                        <option value="Proposal Defence">Proposal Defence</option>
                                        <option value="Progress Presentation 1">Progress Presentation 1</option>
                                        <option value="Internal Defence">Internal Defence</option>
                                        <option value="Viva">Viva</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto relative z-10">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b-2 border-slate-100">
                                            <th class="pb-3 text-xs font-extrabold text-slate-400 uppercase tracking-widest">Student / Matric</th>
                                            <th class="pb-3 text-xs font-extrabold text-slate-400 uppercase tracking-widest">Programme & Degree</th>
                                            <th class="pb-3 text-xs font-extrabold text-slate-400 uppercase tracking-widest">Intake / Batch</th>
                                            <th class="pb-3 text-xs font-extrabold text-slate-400 uppercase tracking-widest">Stage & Status</th>
                                            <th class="pb-3 text-xs font-extrabold text-slate-400 uppercase tracking-widest text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse($studentsList as $stu)
                                        @php
                                            preg_match('/^ACE\d{2}(\d)/', strtoupper($stu->matric_no), $m);
                                            $batch = $m[1] ?? 'Unknown';
                                            $lastMilestone = $stu->milestones->sortByDesc('milestone.order_index')->first();
                                            $stage = $lastMilestone ? $lastMilestone->milestone->name : 'Registered';
                                        @endphp
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="py-4 pr-4">
                                                <div class="font-bold text-slate-800">{{ $stu->user->name }}</div>
                                                <div class="text-xs font-bold text-slate-400 bg-slate-100 inline-block px-2 py-0.5 rounded mt-1">{{ $stu->matric_no }}</div>
                                            </td>
                                            <td class="py-4 pr-4">
                                                <div class="text-sm font-bold text-slate-700">{{ $stu->programme->name ?? 'N/A' }}</div>
                                                <div class="text-xs font-medium text-slate-500">{{ $stu->degree->name ?? 'N/A' }}</div>
                                            </td>
                                            <td class="py-4 pr-4">
                                                <div class="text-sm font-medium text-slate-800">Intake: {{ $stu->admission_year }}</div>
                                                <div class="text-xs font-medium text-slate-500">{{ $batch === '1' ? '1st Batch' : ($batch === '2' ? '2nd Batch' : 'Batch ' . $batch) }}</div>
                                            </td>
                                            <td class="py-4">
                                                <div class="text-sm font-bold text-slate-700 mb-1">{{ $stage }}</div>
                                                @if($stu->registration_status === 'Active')
                                                    <span class="px-2.5 py-1 inline-flex text-[10px] font-bold rounded-lg bg-green-100 text-green-800 border border-green-200 uppercase tracking-wide">Active</span>
                                                @elseif($stu->registration_status === 'Pending')
                                                    <span class="px-2.5 py-1 inline-flex text-[10px] font-bold rounded-lg bg-yellow-100 text-yellow-800 border border-yellow-200 uppercase tracking-wide">Pending</span>
                                                @else
                                                    <span class="px-2.5 py-1 inline-flex text-[10px] font-bold rounded-lg bg-slate-100 text-slate-800 border border-slate-200 uppercase tracking-wide">{{ $stu->registration_status }}</span>
                                                @endif
                                            </td>
                                            <td class="py-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    @if($stu->registration_status === 'Pending')
                                                        <button wire:click="approveStudent({{ $stu->id }})" class="text-xs font-bold px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                                                            Approve
                                                        </button>
                                                    @endif
                                                    <button wire:click="manageStudent({{ $stu->id }})" class="text-xs font-bold px-3 py-1.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition-colors shadow-sm">
                                                        Manage
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="py-8 text-center text-slate-400 font-medium">
                                                No students found matching filters.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Optional: Decorative background shape -->
            <div class="absolute right-0 top-0 w-1/3 h-full bg-gradient-to-l from-green-50 to-transparent opacity-20 pointer-events-none"></div>
        </div>
    </div>
</div>
