<!-- Sidebar for Desktop -->
<nav class="hidden md:flex flex-col w-72 bg-white min-h-screen shadow-sm border-r border-gray-100 relative z-10">
    <!-- Logo Area -->
    <div class="px-8 py-8 flex items-center justify-center border-b border-gray-50">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-2">
            <img src="{{ asset('acetel logo.jpg') }}" alt="ACETEL Logo" class="h-16 w-auto object-contain drop-shadow-sm" />
            <span class="font-extrabold text-sm text-green-800 tracking-wider">RESEARCH PORTAL</span>
        </a>
    </div>

    <!-- Navigation Links -->
    <div class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 mt-4">Menu</p>
        
        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-2xl {{ request()->routeIs('dashboard') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} transition-all group">
            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-green-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Dashboard
        </a>

        @if(Auth::user()->hasRole('Coordinator'))
            <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 mt-6">Administration</p>
            <a href="#" class="flex items-center px-4 py-3 rounded-2xl text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium transition-all group">
                <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Manage Students
            </a>
            <a href="#" class="flex items-center px-4 py-3 rounded-2xl text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium transition-all group">
                <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Pending Verifications
            </a>
        @endif

        @if(Auth::user()->hasRole('Supervisor'))
            <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 mt-6">Supervision</p>
            <a href="#" class="flex items-center px-4 py-3 rounded-2xl text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium transition-all group">
                <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                My Students
            </a>
            <a href="#" class="flex items-center px-4 py-3 rounded-2xl text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium transition-all group">
                <svg class="w-5 h-5 mr-3 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                Messages
            </a>
        @endif
        
        <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 mt-6">Settings</p>
        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-3 rounded-2xl {{ request()->routeIs('profile.edit') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 font-medium' }} transition-all group">
            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('profile.edit') ? 'text-green-600' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            My Profile
        </a>
    </div>

    <!-- User Profile Dropdown at bottom -->
    <div class="p-6 mt-auto">
        <x-dropdown align="top" width="64">
            <x-slot name="trigger">
                <button class="flex items-center w-full px-4 py-3 bg-gray-50 text-sm leading-5 font-medium rounded-2xl text-gray-700 hover:bg-green-50 hover:text-green-800 border border-gray-100 hover:border-green-100 focus:outline-none transition-all duration-200">
                    <div class="flex-shrink-0 h-10 w-10 bg-green-100 text-green-700 rounded-full flex items-center justify-center font-extrabold shadow-sm border border-green-200">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 ml-3 text-left overflow-hidden">
                        <div class="font-bold text-gray-900 truncate">{{ Auth::user()->name }}</div>
                        <div class="text-[11px] uppercase tracking-wider font-bold text-gray-500 truncate">{{ Auth::user()->roles->first()->name ?? 'Student' }}</div>
                    </div>
                    <svg class="h-4 w-4 ml-1 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </button>
            </x-slot>

            <x-slot name="content">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full px-4 py-2 text-start text-sm leading-5 font-bold text-red-600 hover:bg-red-50 hover:text-red-700 focus:outline-none transition duration-150 ease-in-out">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</nav>

<!-- Mobile Navigation (Top Bar) -->
<div x-data="{ open: false }" class="md:hidden bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
    <div class="flex justify-between items-center px-4 py-3">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <img src="{{ asset('acetel logo.jpg') }}" alt="ACETEL Logo" class="h-10 w-auto object-contain" />
            <span class="font-extrabold text-xs text-green-800 tracking-wider">PORTAL</span>
        </a>
        <button @click="open = !open" class="text-gray-500 hover:text-green-700 focus:outline-none p-2 rounded-lg bg-gray-50">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Mobile Menu dropdown -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden absolute w-full bg-white border-b border-gray-100 shadow-xl">
        <div class="pt-2 pb-4 space-y-1 px-4">
            <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-green-50 text-green-700 font-bold' : 'text-gray-600 font-medium' }}">
                Dashboard
            </a>
            @if(Auth::user()->hasRole('Coordinator'))
                <a href="#" class="block px-4 py-3 rounded-xl text-gray-600 font-medium">Manage Students</a>
            @endif
            @if(Auth::user()->hasRole('Supervisor'))
                <a href="#" class="block px-4 py-3 rounded-xl text-gray-600 font-medium">My Students</a>
            @endif
            <a href="{{ route('profile.edit') }}" class="block px-4 py-3 rounded-xl text-gray-600 font-medium">Profile</a>
            
            <form method="POST" action="{{ route('logout') }}" class="mt-2 pt-2 border-t border-gray-100">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-3 rounded-xl text-red-600 font-bold hover:bg-red-50">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</div>
