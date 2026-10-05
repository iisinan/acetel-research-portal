@extends('layouts.app')

@section('content')
<div class="min-h-screen flex bg-white" x-data="{ showPassword: false }">

    {{-- ===== LEFT PANEL: Branding ===== --}}
    <div class="hidden lg:flex lg:w-5/12 xl:w-1/2 relative overflow-hidden flex-col">
        {{-- Modern white/green transition --}}
        <div class="absolute inset-0 bg-white border-r border-green-50"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-green-50/50 to-white/0"></div>

        {{-- Sophisticated decorative elements --}}
        <div class="absolute top-0 right-0 w-[40rem] h-[40rem] bg-green-100/30 blur-[100px] rounded-full -mr-32 -mt-32 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-green-50/50 blur-[80px] rounded-full -ml-32 -mb-32 pointer-events-none"></div>


        {{-- Content --}}
        <div class="relative z-10 flex flex-col h-full p-12 xl:p-16">
            {{-- Logo --}}
            <a href="/" class="flex items-center gap-3 group w-fit">
                <div class="w-12 h-12 rounded-2xl bg-white border border-green-100 shadow-sm flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-all">
                    <img src="{{ asset('images/acetel-logo.jpeg') }}" alt="ACETEL" class="w-8 h-8 object-contain">
                </div>
                <div>
                    <span class="block text-base font-black text-slate-900 leading-none">ACETEL TMS</span>
                    <span class="block text-[10px] font-black text-green-600 uppercase tracking-widest mt-1">Research Excellence</span>
                </div>
            </a>


            {{-- Main copy --}}
            <div class="flex-1 flex flex-col justify-center">
                <div class="mb-10">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-50 border border-green-200 text-green-700 text-xs font-black uppercase tracking-wider shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        Secure Institutional Access
                    </span>
                </div>
                <h1 class="text-4xl xl:text-6xl font-black text-slate-900 leading-[1.1] mb-8 tracking-tighter">
                    Elevate <span class="text-green-600">Postgraduate</span><br>
                    Research Experience.
                </h1>
                <p class="text-slate-500 text-lg leading-relaxed max-w-sm font-medium">
                    The professional platform for tracking thesis milestones and orchestrating collaboration between students and supervisors.
                </p>


                {{-- Feature pills --}}
                <div class="mt-12 space-y-5">
                    <div class="flex items-center gap-4 group">
                        <div class="w-10 h-10 rounded-xl bg-white border border-green-100 shadow-sm flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-sm text-slate-600 font-black uppercase tracking-wider">Milestone Intelligence</p>
                    </div>
                    <div class="flex items-center gap-4 group">
                        <div class="w-10 h-10 rounded-xl bg-white border border-green-100 shadow-sm flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <p class="text-sm text-slate-600 font-black uppercase tracking-wider">Faculty Collaboration</p>
                    </div>
                    <div class="flex items-center gap-4 group">
                        <div class="w-10 h-10 rounded-xl bg-white border border-green-100 shadow-sm flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <p class="text-sm text-slate-600 font-black uppercase tracking-wider">Real-time Synchronization</p>
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="border-t border-slate-100 pt-6">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">&copy; {{ date('Y') }} ACETEL TRADEMARK RESOURCE</p>
            </div>

        </div>
    </div>

    {{-- ===== RIGHT PANEL: Form ===== --}}
    <div class="flex-1 flex flex-col items-center justify-center px-6 py-12 lg:px-12 xl:px-20 bg-slate-50">

        {{-- Mobile logo (only visible on small screens) --}}
        <div class="lg:hidden mb-10 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl overflow-hidden flex-shrink-0">
                <img src="{{ asset('images/acetel-logo.jpeg') }}" alt="ACETEL" class="w-full h-full object-cover">
            </div>
            <div>
                <span class="block text-sm font-black text-slate-900 leading-none">ACETEL TMS</span>
                <span class="block text-[9px] font-semibold text-green-600 uppercase tracking-widest mt-0.5">Thesis Monitoring</span>
            </div>
        </div>

        <div class="w-full max-w-md">
            {{-- Heading --}}
            <div class="mb-10">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight mb-2" style="font-family: 'Plus Jakarta Sans', sans-serif;">Sign in</h2>
                <p class="text-slate-500 text-sm">Enter your credentials to access your dashboard.</p>
            </div>

            {{-- Status alert (e.g. password reset confirmation) --}}
            @if (session('status'))
                <div class="mb-6 flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-700 mb-1">Success</p>
                        <p class="text-xs text-emerald-600">{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            {{-- Error alert --}}
            @if ($errors->any())
                <div class="mb-6 flex items-start gap-3 p-4 bg-red-50 border border-red-100 rounded-2xl">
                    <div class="w-8 h-8 rounded-xl bg-red-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-red-700 mb-1">Sign in failed</p>
                        <p class="text-xs text-red-500">{{ $errors->first() }}</p>
                    </div>
                </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            required
                            value="{{ old('email') }}"
                            placeholder="you@acetel.edu.ng"
                            class="w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-sm font-medium placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                            Password
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-green-600 hover:text-green-700 transition-colors">
                                Forgot password?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input
                            id="password"
                            name="password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            placeholder="••••••••••••"
                            class="w-full pl-11 pr-12 py-3.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-sm font-medium placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                        >
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-green-600 transition-colors"
                        >
                            <template x-if="!showPassword">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </template>
                            <template x-if="showPassword">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.04m4.066-1.56A10.048 10.048 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-2.31 3.872M3 3l18 18"/>
                                </svg>
                            </template>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center gap-3">
                    <input
                        id="remember_me"
                        name="remember"
                        type="checkbox"
                        class="w-4 h-4 text-green-600 bg-white border-slate-300 rounded focus:ring-green-500/20 cursor-pointer"
                    >
                    <label for="remember_me" class="text-sm text-slate-500 font-medium cursor-pointer select-none">
                        Keep me signed in
                    </label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    id="login-submit-btn"
                    class="w-full py-3.5 px-6 flex items-center justify-center gap-2 text-sm font-bold text-white rounded-xl transition-all duration-300 group mt-2"
                    style="background: linear-gradient(135deg, #16a34a, #15803d); box-shadow: 0 4px 24px rgba(22, 163, 74, 0.4);"
                    onmouseover="this.style.boxShadow='0 8px 40px rgba(22, 163, 74, 0.6)'; this.style.transform='translateY(-1px)';"
                    onmouseout="this.style.boxShadow='0 4px 24px rgba(22, 163, 74, 0.4)'; this.style.transform='translateY(0)';"
                >
                    Sign In
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </button>
            </form>

            {{-- Divider --}}
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="px-4 bg-slate-50 text-xs text-slate-400 font-medium">or</span>
                </div>
            </div>

            {{-- Repository link --}}
            <a
                href="{{ route('repository.index') }}"
                class="w-full flex items-center justify-center gap-2 py-3.5 px-6 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:border-green-300 hover:text-green-700 transition-all"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Browse Research Repository
            </a>

            {{-- Register link --}}
            <p class="mt-8 text-center text-sm text-slate-500">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-bold text-green-600 hover:text-green-700 transition-colors">Register as Student</a>
            </p>

            {{-- Back to home --}}
            <p class="mt-4 text-center text-xs text-slate-400">
                <a href="/" class="font-semibold text-slate-500 hover:text-green-600 transition-colors inline-flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to home
                </a>
            </p>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
