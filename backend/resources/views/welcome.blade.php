<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <link rel="icon" href="{{ asset('images/acetel-logo.jpeg') }}" type="image/jpeg">
    <link rel="shortcut icon" href="{{ asset('images/acetel-logo.jpeg') }}" type="image/jpeg">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ACETEL Thesis Monitoring System — Institutional Excellence</title>
    <meta name="description" content="ACETEL's official postgraduate research management platform. Track milestones, collaborate with supervisors, and archive your thesis journey.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @once
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endonce

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Figtree', sans-serif; }

        .hero-pattern {
            background-image: 
                radial-gradient(circle at 2px 2px, rgba(34, 197, 94, 0.05) 1px, transparent 0);
            background-size: 40px 40px;
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(22, 163, 74, 0.1);
        }

        .premium-shadow {
            box-shadow: 0 30px 60px -12px rgba(22, 163, 74, 0.12), 0 18px 36px -18px rgba(0, 0, 0, 0.08);
        }

        .gradient-text {
            background: linear-gradient(135deg, #16a34a 0%, #052e16 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .floating-orb {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(34, 197, 94, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
            z-index: 0;
        }
    </style>
</head>
<body class="antialiased bg-white text-slate-900 hero-pattern overflow-x-hidden" x-data="{ scrolled: false, mobileOpen: false }" @scroll.window="scrolled = window.scrollY > 50">
<div class="relative overflow-x-hidden">
    <!-- Orbs -->
    <div class="floating-orb -top-20 -right-20 animate-float" style="animation-duration: 10s;"></div>
    <div class="floating-orb top-1/2 -left-40 animate-float" style="animation-duration: 15s;"></div>

    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 transition-all duration-500 py-6" 
         :class="scrolled ? 'glass-nav py-4 shadow-sm' : 'bg-transparent'">
        <div class="max-w-7xl mx-auto px-6 lg:px-12 flex items-center justify-between">
            
            <!-- Logo -->
            <a href="/" class="flex items-center gap-4 group">
                <div class="w-11 h-11 rounded-2xl overflow-hidden border-2 border-acetel-200 shadow-premium transition-all group-hover:scale-105 group-hover:rotate-3">
                    <img src="{{ asset('images/acetel-logo.jpeg') }}" alt="ACETEL" class="w-full h-full object-cover">
                </div>
                <div class="leading-tight">
                    <span class="block text-lg font-bold text-slate-900 tracking-tight">ACETEL</span>
                    <span class="block text-[10px] font-black text-acetel-600 uppercase tracking-[0.4em]">Thesis Monitoring</span>
                </div>
            </a>

            <!-- Desktop Nav -->
            <div class="hidden lg:flex items-center gap-10">
                <a href="{{ route('repository.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-500 hover:text-acetel-600 transition-colors">Digital Repository</a>
                <a href="#features" class="text-xs font-black uppercase tracking-widest text-slate-500 hover:text-acetel-600 transition-colors">Core Features</a>
                <a href="#portals" class="text-xs font-black uppercase tracking-widest text-slate-500 hover:text-acetel-600 transition-colors">Role Access</a>
            </div>

            <!-- Action -->
            <div class="flex items-center gap-3 lg:gap-5">
                @auth
                    <a href="{{ url('/dashboard') }}" class="flex items-center gap-2 bg-acetel-600 text-white text-[10px] font-black uppercase tracking-widest px-6 py-3 rounded-2xl hover:bg-acetel-700 hover:scale-105 transition-all shadow-premium">
                        Dashboard
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="hidden sm:inline-block bg-white text-acetel-600 border border-acetel-200 text-[10px] font-black uppercase tracking-widest px-7 py-4 rounded-2xl hover:bg-acetel-50 hover:border-acetel-300 transition-all shadow-sm">
                        Register
                    </a>
                    <a href="{{ route('login') }}" class="bg-acetel-600 text-white text-[10px] font-black uppercase tracking-widest px-7 py-4 rounded-2xl hover:bg-acetel-700 hover:scale-105 transition-all shadow-premium">
                        Sign In
                    </a>
                @endauth

                <!-- Mobile Toggle -->
                <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2.5 rounded-xl border border-acetel-100 text-acetel-700 hover:bg-acetel-50 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-cloak class="lg:hidden glass-nav mt-4 border-t border-acetel-50 px-6 py-8 space-y-6">
            <a href="{{ route('repository.index') }}" class="block text-sm font-bold text-slate-800 tracking-tight">Repository</a>
            <a href="#features" class="block text-sm font-bold text-slate-800 tracking-tight">Features</a>
            <a href="#portals" class="block text-sm font-bold text-slate-800 tracking-tight">Access Portals</a>
            <hr class="border-acetel-50">
            <a href="{{ route('login') }}" class="block bg-acetel-600 text-white text-center py-4 rounded-2xl text-xs font-black uppercase tracking-widest shadow-premium">Access Portal</a>
        </div>
    </nav>

    <!-- Latest Announcement Ticker -->
    <div class="fixed top-[90px] w-full z-40 px-6 lg:px-12 pointer-events-none transition-all duration-500" :class="scrolled ? 'opacity-0 -translate-y-10' : 'opacity-100 translate-y-0'">
        <div class="max-w-7xl mx-auto flex justify-end">
            <div class="bg-slate-950/90 backdrop-blur-md px-5 py-2.5 rounded-full border border-white/10 pointer-events-auto flex items-center gap-3 shadow-premium animate-in-up">
                <span class="flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-acetel-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-acetel-500"></span>
                </span>
                @if($announcements->count() > 0)
                    <a href="{{ route('announcements.show_public', $announcements->first()) }}" class="text-[10px] font-black text-white uppercase tracking-widest whitespace-nowrap">
                        Latest: <span class="text-acetel-400 font-bold ml-1">{{ Str::limit($announcements->first()->title, 40) }}</span>
                    </a>
                @else
                    <p class="text-[10px] font-black text-white uppercase tracking-widest whitespace-nowrap">Institutional Research Portal Active</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <header class="relative min-h-screen flex items-center pt-40 pb-24 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 lg:px-12 w-full grid lg:grid-cols-12 gap-16 items-start relative z-10">
            
            <!-- Content Left -->
            <div class="lg:col-span-6 space-y-12 animate-in-up">
                <!-- Status Badge -->
                <div class="inline-flex items-center gap-3 bg-white/60 border border-acetel-100 px-5 py-2.5 rounded-full shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-acetel-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-acetel-600"></span>
                    </span>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-acetel-700">Digital Thesis Ecosystem Active</span>
                </div>

                <!-- Main Headline -->
                <div class="space-y-8">
                    <h1 class="text-6xl lg:text-8xl font-black text-slate-950 leading-[0.9] tracking-tighter">
                        Research<br>
                        <span class="gradient-text">Redefined.</span>
                    </h1>
                    <p class="text-xl text-slate-500 max-w-lg leading-relaxed font-medium">
                        ACETEL's next-generation platform for postgraduate research monitoring — where academic rigor meets technological innovation.
                    </p>
                </div>

                <!-- CTAs -->
                <div class="flex flex-wrap gap-5">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="group relative flex items-center gap-4 bg-slate-950 text-white px-10 py-5 rounded-3xl hover:bg-acetel-700 transition-all shadow-premium overflow-hidden">
                            <span class="relative z-10 text-[11px] font-black uppercase tracking-[0.2em]">Enter Portal</span>
                            <svg class="w-4 h-4 relative z-10 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            <div class="absolute inset-0 bg-gradient-to-r from-acetel-600 to-acetel-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="group relative flex items-center gap-4 bg-slate-950 text-white px-10 py-5 rounded-3xl hover:bg-acetel-700 transition-all shadow-premium overflow-hidden">
                            <span class="relative z-10 text-[11px] font-black uppercase tracking-[0.2em]">Start Journey</span>
                            <svg class="w-4 h-4 relative z-10 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            <div class="absolute inset-0 bg-gradient-to-r from-acetel-600 to-acetel-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </a>
                    @endauth
                    <a href="{{ route('repository.index') }}" class="flex items-center gap-4 bg-white border border-acetel-100 text-slate-900 px-10 py-5 rounded-3xl hover:border-acetel-300 hover:bg-acetel-50 transition-all font-bold text-[11px] uppercase tracking-[0.2em] shadow-sm">
                        Archives
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </a>
                </div>

                <!-- Stats Strip -->
                <div class="pt-10 flex flex-wrap gap-10 lg:gap-14 border-t border-acetel-50">
                    <div>
                        <p class="text-4xl font-black text-slate-900 tracking-tighter">{{ $stats['projects_count'] }}</p>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Projects</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-acetel-600 tracking-tighter">{{ $stats['students_count'] }}+</p>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Scholars</p>
                    </div>
                    <div>
                        <p class="text-4xl font-black text-slate-900 tracking-tighter">{{ $stats['archived_count'] }}</p>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Archived</p>
                    </div>
                </div>
            </div>

            <!-- Content Right: Announcement Hub -->
            <div class="lg:col-span-6 w-full animate-in-up" style="animation-delay: 0.2s">
                <div class="relative bg-white/50 backdrop-blur-xl border border-acetel-100 rounded-[3.5rem] shadow-premium overflow-hidden h-[700px] flex flex-col">
                    
                    <!-- Hub Header -->
                    <div class="bg-slate-950 p-10 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-5">
                            <div class="w-14 h-14 bg-acetel-600 rounded-2xl flex items-center justify-center text-white shadow-premium">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-white tracking-tight">Institutional Feed</h3>
                                <p class="text-[10px] font-black text-acetel-400 uppercase tracking-[0.3em]">Live Announcements</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 px-4 py-2 bg-white/10 rounded-full border border-white/10">
                            <span class="w-1.5 h-1.5 bg-acetel-500 rounded-full animate-pulse"></span>
                            <span class="text-[9px] font-black text-white uppercase tracking-widest">Real-time</span>
                        </div>
                    </div>

                    <!-- Hub Feed -->
                    <div class="flex-1 overflow-y-auto p-8 space-y-6 custom-scrollbar">
                        @forelse($announcements as $announcement)
                            <div class="group bg-white p-6 rounded-3xl border border-slate-100 hover:border-acetel-200 hover:shadow-premium transition-all duration-500 cursor-default">
                                <div class="flex items-start justify-between gap-4 mb-4">
                                    <span class="px-3 py-1 bg-acetel-50 border border-acetel-100 text-acetel-700 text-[9px] font-black uppercase tracking-widest rounded-lg">
                                        {{ $announcement->type }}
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $announcement->created_at->format('M d, Y') }}</span>
                                </div>
                                <h4 class="text-xl font-black text-slate-900 group-hover:text-acetel-600 transition-colors leading-tight mb-3">{{ $announcement->title }}</h4>
                                <p class="text-sm text-slate-500 font-medium leading-relaxed line-clamp-3">{{ $announcement->content }}</p>
                                <div class="mt-6 flex justify-end">
                                    <a href="{{ route('announcements.show_public', $announcement) }}" class="text-[10px] font-black text-acetel-600 uppercase tracking-widest hover:text-slate-950 transition-colors">Read Full Notice &rarr;</a>
                                </div>
                            </div>
                        @empty
                            <div class="h-full flex flex-col items-center justify-center text-center opacity-50 py-20">
                                <div class="w-20 h-20 bg-acetel-50 rounded-full flex items-center justify-center text-acetel-200 mb-6">
                                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                </div>
                                <p class="text-sm font-black text-slate-400 uppercase tracking-widest">No active announcements</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Hub Footer -->
                    <div class="p-8 border-t border-acetel-50 bg-acetel-50/30">
                        <a href="{{ route('login') }}" class="flex items-center justify-center gap-3 w-full py-4 bg-slate-950 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-acetel-700 transition-all shadow-premium">
                            Sign in to view archive
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>


    <!-- Features Section -->
    <section id="features" class="py-32 relative">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-12 mb-20">
                <div class="max-w-2xl space-y-6">
                    <div class="inline-flex items-center gap-3 px-4 py-2 bg-acetel-600/10 text-acetel-700 rounded-full">
                        <span class="text-[10px] font-black uppercase tracking-[0.2em]">Institutional Engine</span>
                    </div>
                    <h2 class="text-5xl lg:text-6xl font-black text-slate-950 tracking-tighter leading-none">
                        Built for<br>
                        <span class="gradient-text">Scale & Transparency.</span>
                    </h2>
                </div>
                <p class="text-lg text-slate-500 max-w-sm font-medium leading-relaxed">
                    Designed to handle the complex lifecycle of graduate research from proposal to archival.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @php
                $features = [
                    ['title' => 'Milestone Governance', 'desc' => 'Track every phase from project initiation to final VIVA examination with granular precision.', 'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806', 'color' => 'acetel-600'],
                    ['title' => 'Integrated Feedback', 'desc' => 'Real-time communication channels between students, supervisors, and internal examiners.', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c', 'color' => 'blue-600'],
                    ['title' => 'Secure Repository', 'desc' => 'A centralized, OCR-searchable database of all approved institutional research outputs.', 'icon' => 'M12 6.253v13m0-13C10.832 5.477', 'color' => 'amber-600'],
                    ['title' => 'Automated Alerts', 'desc' => 'Instant push notifications for submissions, feedback, and institutional announcements.', 'icon' => 'M15 17h5l-1.405-1.405A2.032', 'color' => 'rose-600'],
                    ['title' => 'Director Dashboards', 'desc' => 'High-level analytics for program coordinators to monitor cohort progress and bottlenecks.', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2', 'color' => 'slate-900'],
                    ['title' => 'Academic Audit', 'desc' => 'A complete immutable log of all thesis interactions ensuring institutional accountability.', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955', 'color' => 'emerald-600'],
                ];
                @endphp

                @foreach($features as $f)
                <div class="group bg-white p-10 rounded-[2.5rem] border border-slate-100 hover:border-acetel-200 hover:shadow-premium transition-all duration-500 hover-lift">
                    <div class="w-14 h-14 bg-{{ $f['color'] }}/10 text-{{ $f['color'] }} rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 group-hover:rotate-6 transition-transform">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 mb-4 tracking-tight">{{ $f['title'] }}</h3>
                    <p class="text-sm text-slate-500 leading-relaxed font-medium">{{ $f['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Role Portals -->
    <section id="portals" class="py-32 bg-slate-950 relative overflow-hidden">
        <div class="floating-orb bottom-0 right-0 opacity-20 bg-acetel-500"></div>
        <div class="max-w-7xl mx-auto px-6 lg:px-12 relative z-10 text-center">
            <div class="mb-20 space-y-6">
                <h2 class="text-5xl lg:text-7xl font-black text-white tracking-tighter">
                    Access your <span class="text-acetel-400">workspace.</span>
                </h2>
                <p class="text-slate-400 max-w-xl mx-auto text-lg">Select your institutional role to enter your dedicated management portal.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                $roles = [
                    ['name' => 'Student', 'role' => 'Researcher', 'icon' => 'M12 6.253v13m0-13C10.832 5.477', 'color' => 'acetel-500'],
                    ['name' => 'Supervisor', 'role' => 'Mentor', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356', 'color' => 'blue-500'],
                    ['name' => 'Coordinator', 'role' => 'Manager', 'icon' => 'M9 12l2 2 4-4m5.618-4.016', 'color' => 'amber-500'],
                    ['name' => 'Admin', 'role' => 'System', 'icon' => 'M10.325 4.317c.426-1.756 2.924', 'color' => 'rose-500'],
                ];
                @endphp

                @foreach($roles as $r)
                <a href="{{ route('login') }}" class="group bg-white/5 border border-white/10 p-10 rounded-[2.5rem] hover:bg-white/10 hover:border-acetel-400/30 transition-all duration-500 text-left relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-{{ $r['color'] }}/20 blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="w-14 h-14 bg-{{ $r['color'] }}/20 text-{{ $r['color'] }} rounded-2xl flex items-center justify-center mb-8 shadow-sm">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $r['icon'] }}"/></svg>
                    </div>
                    <div>
                        <h4 class="text-2xl font-black text-white mb-1">{{ $r['name'] }}</h4>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em]">{{ $r['role'] }} Portal</p>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="mt-20">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-4 bg-acetel-600 text-white px-12 py-6 rounded-3xl text-[11px] font-black uppercase tracking-[0.3em] shadow-premium hover:scale-105 transition-all outline-none">
                    Launch Platform Portal
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Institutional Footer -->
    <footer class="bg-white pt-32 pb-16 px-6 lg:px-12 border-t border-slate-100 relative z-10">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col lg:flex-row justify-between gap-20 mb-20">
                <div class="max-w-md space-y-8">
                    <a href="/" class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl overflow-hidden shadow-premium">
                            <img src="{{ asset('images/acetel-logo.jpeg') }}" alt="ACETEL" class="w-full h-full object-cover">
                        </div>
                        <div class="leading-tight">
                            <span class="block text-xl font-black text-slate-950 tracking-tight">ACETEL TMS</span>
                            <span class="block text-[10px] font-black text-acetel-500 uppercase tracking-[0.4em]">Excellence Portal</span>
                        </div>
                    </a>
                    <p class="text-lg text-slate-500 font-medium leading-relaxed">
                        Redefining institutional research standards through digital innovation and academic transparency.
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-20">
                    <div class="space-y-6">
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Navigation</p>
                        <ul class="space-y-4">
                            <li><a href="{{ route('repository.index') }}" class="text-sm font-bold text-slate-800 hover:text-acetel-600 transition-colors">Repository</a></li>
                            <li><a href="#features" class="text-sm font-bold text-slate-800 hover:text-acetel-600 transition-colors">Features</a></li>
                            <li><a href="#portals" class="text-sm font-bold text-slate-800 hover:text-acetel-600 transition-colors">Role Portals</a></li>
                        </ul>
                    </div>
                    <div class="space-y-6">
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Account</p>
                        <ul class="space-y-4">
                            <li><a href="{{ route('login') }}" class="text-sm font-bold text-slate-800 hover:text-acetel-600 transition-colors">Sign In</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="pt-12 border-t border-slate-50 flex flex-col md:flex-row justify-between items-center gap-10">
                <p class="text-sm font-bold text-slate-400">&copy; {{ date('Y') }} ACETEL Institutional Thesis Management. All Rights Reserved.</p>
                <div class="flex items-center gap-4 bg-acetel-50 px-5 py-2.5 rounded-full border border-acetel-100">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-acetel-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-acetel-600"></span>
                    </span>
                    <p class="text-[10px] font-black uppercase tracking-widest text-acetel-700">Institutional Hub Online</p>
                </div>
            </div>
        </div>
    </footer>
</div>
</body>
</html>
