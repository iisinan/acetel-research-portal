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

        <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <title>{{ config('app.name', 'TMS Coordinator') }}</title>

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
        .coordinator-main {
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
<body class="font-sans antialiased text-slate-800 coordinator-main selection:bg-brand-200 selection:text-brand-800 h-full overflow-hidden">
    <div class="flex h-screen overflow-hidden relative">
        
        <!-- Sidebar Navigation -->
        <aside class="fixed inset-y-0 left-0 z-50 w-72 light-sidebar transition-all duration-500 transform md:translate-x-0 md:relative flex flex-col group"
               :class="$store.sidebar.open ? 'translate-x-0' : '-translate-x-full'"
               x-cloak>
            
            <div class="flex items-center px-8 h-24 shrink-0 border-b border-green-100">
                <div class="flex items-center gap-4 cursor-pointer">
                    <div class="w-12 h-12 bg-green-50 border-2 border-green-200 rounded-2xl flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-300">
                        <img src="{{ asset('images/acetel-logo.jpeg') }}" alt="Logo" class="w-full h-full object-cover rounded-xl">
                    </div>
                    <div>
                        <span class="block text-xl font-black tracking-tight text-slate-800 leading-none uppercase">ACETEL</span>
                        <span class="block text-[9px] font-bold text-green-600 uppercase tracking-[0.35em] mt-1">Coordinator Hub</span>
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
                        <p class="text-[10px] font-semibold text-green-600 uppercase tracking-wider truncate">{{ Auth::user()->getRoleNames()->first() ?? 'Coordinator' }}</p>
                    </div>
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
            <header class="h-20 flex items-center justify-between px-10 light-header z-40 shrink-0">
                <div class="flex items-center gap-6">
                    <button @click="Alpine.store('sidebar').toggle()" class="md:hidden p-3 bg-green-50 rounded-xl text-green-700 border border-green-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div>
                        <h1 class="text-2xl font-black text-slate-800 tracking-tight">
                            @yield('header', 'Program Governance')
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative">
                        <button class="p-3 bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 rounded-xl transition-all duration-300 border border-green-200 relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            @if(Auth::user()->unreadNotifications->count() > 0)
                                <span class="absolute top-1.5 right-1.5 block h-3 w-3 rounded-full ring-2 ring-white bg-red-500 animate-pulse-subtle"></span>
                            @endif
                        </button>
                    </div>
                </div>
            </header>

            <!-- Scrollable Workspace -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-10 custom-scrollbar">
                <div class="max-w-[1400px] mx-auto space-y-8 pb-16 animate-in-up">

                    
                    @if (session('success'))
                        <div class="bg-green-50 text-green-800 px-6 py-4 rounded-2xl flex items-center gap-4 border border-green-200 shadow-sm animate-in-up" role="alert">
                            <div class="w-10 h-10 rounded-xl bg-green-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="font-semibold text-sm">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="bg-amber-50 text-amber-800 px-6 py-4 rounded-2xl flex items-center gap-4 border border-amber-200 shadow-sm animate-in-up" role="alert">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <span class="font-semibold text-sm">{{ session('warning') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-rose-50 text-rose-800 px-6 py-4 rounded-2xl flex items-center gap-4 border border-rose-200 shadow-sm animate-in-up" role="alert">
                            <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <span class="font-semibold text-sm">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if (session('skipped_students') && count(session('skipped_students')) > 0)
                        <div class="bg-amber-50/90 text-amber-900 px-6 py-4 rounded-2xl border border-amber-200 shadow-sm animate-in-up">
                            <div class="flex items-center gap-2 mb-2 font-bold text-xs uppercase tracking-wider text-amber-800">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <span>Students Remaining Scheduled (Requirements Incomplete):</span>
                            </div>
                            <ul class="list-disc list-inside space-y-1 text-xs font-medium text-amber-950">
                                @foreach(session('skipped_students') as $skip)
                                    <li>{{ $skip }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    <x-document-preview-modal />
    <x-toast />
    <x-confirm-modal />
    <x-force-password-change-modal />
</body>
</html>
