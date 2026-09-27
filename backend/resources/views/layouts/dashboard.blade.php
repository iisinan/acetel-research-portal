<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ darkMode: false }"
      class="h-full bg-slate-50">
<head>
    <link rel="icon" href="{{ asset('images/acetel-logo.jpeg') }}" type="image/jpeg">
    <link rel="shortcut icon" href="{{ asset('images/acetel-logo.jpeg') }}" type="image/jpeg">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">

        <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <title>{{ config('app.name', 'TMS Dashboard') }}</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#16a34a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/images/acetel-logo.jpeg">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        .light-sidebar {
            background-color: #ffffff;
            border-right: 1px solid #dcfce7;
            box-shadow: 2px 0 12px rgba(22,163,74,0.06);
        }
        .light-header {
            background-color: #ffffff;
            border-bottom: 2px solid #dcfce7;
            box-shadow: 0 2px 8px rgba(22,163,74,0.06);
        }
        .dashboard-main {
            background-color: #f0fdf4;
            background-image: 
                radial-gradient(at 0% 0%, rgba(34,197,94,0.07) 0px, transparent 55%),
                radial-gradient(at 100% 100%, rgba(22,163,74,0.05) 0px, transparent 55%);
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f0fdf4; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #bbf7d0; border-radius: 10px; }
        .sidebar-avatar {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #15803d;
        }
    </style>
</head>
<body class="font-sans antialiased text-slate-800 dashboard-main selection:bg-brand-200 selection:text-brand-800 h-full overflow-hidden">
    <div class="flex h-screen overflow-hidden relative">
        
        <!-- Sidebar Navigation -->
        <aside class="fixed inset-y-0 left-0 z-50 w-72 light-sidebar transition-all duration-500 transform md:translate-x-0 md:relative flex flex-col group"
               :class="$store.sidebar.open ? 'translate-x-0' : '-translate-x-full'">

            
            <div class="flex items-center px-8 h-24 shrink-0 border-b border-green-100">
                <div class="flex items-center gap-4 cursor-pointer">
                    <div class="w-12 h-12 bg-green-50 border-2 border-green-200 rounded-2xl flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-300">
                        <img src="{{ asset('images/acetel-logo.jpeg') }}" alt="Logo" class="w-full h-full object-cover rounded-xl">
                    </div>
                    <div>
                        <span class="block text-xl font-black tracking-tight text-slate-800 leading-none uppercase">ACETEL</span>
                        <span class="block text-[9px] font-bold text-green-600 uppercase tracking-[0.35em] mt-1">Trajectory Hub</span>
                    </div>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto pt-6 pb-6 px-4 space-y-1 custom-scrollbar">
                @include('layouts.partials.sidebar-menu')
            </nav>
            
            <div class="p-6 bg-green-50 border-t border-green-100 shrink-0">
                <div class="flex items-center gap-4 group/user">
                    <div class="w-10 h-10 rounded-xl sidebar-avatar flex items-center justify-center font-black text-sm shadow-sm group-hover/user:scale-105 transition-transform">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] font-semibold text-green-600 uppercase tracking-wider truncate">{{ Auth::user()->getRoleNames()->first() ?? 'Researcher' }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="p-2 text-slate-400 hover:text-brand-500 transition-all duration-300 rounded-xl hover:bg-brand-50" title="My Profile">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 text-slate-400 hover:text-red-500 transition-all duration-300 rounded-xl hover:bg-red-50" title="Log out">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Viewport -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden relative">
            
            <!-- Global Header -->
            <header class="h-16 md:h-20 flex items-center justify-between px-6 md:px-10 light-header z-40 shrink-0">
                <div class="flex items-center gap-4 md:gap-6">
                    <button @click="Alpine.store('sidebar').toggle()" class="p-2.5 bg-green-50 rounded-xl text-green-700 border border-green-200">
                        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div>
                        <h1 class="text-lg md:text-2xl font-black text-slate-800 tracking-tight truncate max-w-[150px] md:max-w-none">
                            @yield('header', 'Trajectory Oversight')
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative">
                        <button class="p-2.5 bg-green-50 text-green-700 hover:bg-green-100 rounded-xl transition-all border border-green-200 relative group">
                            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            @php 
                                $ncount = 0;
                                if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                                    $ncount = Auth::user()->unreadNotifications->count();
                                }
                            @endphp
                            @if($ncount > 0)
                                <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center bg-red-500 text-white text-[8px] font-bold rounded-full border-2 border-white shadow-sm">{{ $ncount }}</span>
                            @endif
                        </button>
                    </div>
                </div>
            </header>

            <!-- Scrollable Workspace -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 md:p-10 pb-24 md:pb-16 custom-scrollbar">
                <div class="max-w-[1400px] mx-auto space-y-6 md:space-y-8 animate-in-up">

                    @if (session('success'))
                        <div class="bg-green-50 text-green-800 px-6 py-4 rounded-2xl flex items-center gap-4 border border-green-200 shadow-sm animate-in-up" role="alert">
                            <div class="w-10 h-10 rounded-xl bg-green-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="font-semibold text-sm">{{ session('success') }}</span>
                        </div>
                    @endif
                    
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    {{-- Mobile Bottom Navigation (Native App Feel) --}}
    <div class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-2xl border-t border-green-100 px-6 py-3 pb-safe flex items-center justify-between shadow-[0_-8px_30px_rgb(0,0,0,0.08)]">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('dashboard') ? 'text-green-600 font-bold' : 'text-slate-400' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="{{ request()->routeIs('dashboard') ? '2.5' : '2' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
            <span class="text-[8px] uppercase tracking-widest leading-none">Home</span>
        </a>
        @unlessrole('Admin')
        <a href="{{ route('resources.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('resources.*') ? 'text-green-600 font-bold' : 'text-slate-400' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="{{ request()->routeIs('resources.*') ? '2.5' : '2' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
            <span class="text-[8px] uppercase tracking-widest leading-none">Resources</span>
        </a>
        @endunlessrole
        <a href="{{ route('inbox.index') }}" class="flex flex-col items-center gap-1 relative {{ request()->routeIs('inbox.*') ? 'text-green-600 font-bold' : 'text-slate-400' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="{{ request()->routeIs('inbox.*') ? '2.5' : '2' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            @php 
                $mcount = 0;
                if (\Illuminate\Support\Facades\Schema::hasTable('inbox_message_recipients')) {
                    $mcount = \Illuminate\Support\Facades\DB::table('inbox_message_recipients')
                        ->where('user_id', auth()->id())
                        ->whereNull('read_at')
                        ->where('is_archived', false)
                        ->count(); 
                }
            @endphp
            @if($mcount > 0)
                <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center bg-red-500 text-white text-[8px] font-bold rounded-full border-2 border-white shadow-sm">{{ $mcount }}</span>
            @endif
            <span class="text-[8px] uppercase tracking-widest leading-none">Inbox</span>
        </a>
        <a href="{{ route('profile.edit') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('profile.*') ? 'text-green-600 font-bold' : 'text-slate-400' }}">
            <div class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center font-black text-[10px] {{ request()->routeIs('profile.*') ? 'bg-green-600 text-white' : 'text-slate-500' }}">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <span class="text-[8px] uppercase tracking-widest leading-none">Profile</span>
        </a>
        <button @click="Alpine.store('sidebar').toggle()" class="flex flex-col items-center gap-1 text-slate-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
            <span class="text-[8px] uppercase tracking-widest leading-none">Explore</span>
        </button>
    </div>

    <x-document-preview-modal />
    <x-toast />
    <x-force-password-change-modal />
    @stack('scripts')
</body>
</html>
