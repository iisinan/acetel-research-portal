@extends('layouts.app')

@section('content')
<div class="min-h-screen flex bg-white" x-data="{ showPassword: false }">

    {{-- ===== LEFT PANEL: Branding ===== --}}
    <div class="hidden lg:flex lg:w-5/12 xl:w-1/2 relative overflow-hidden flex-col">
        <div class="absolute inset-0 bg-white border-r border-green-50"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-green-50/50 to-white/0"></div>

        <div class="absolute top-0 right-0 w-[40rem] h-[40rem] bg-amber-100/30 blur-[100px] rounded-full -mr-32 -mt-32 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-green-50/50 blur-[80px] rounded-full -ml-32 -mb-32 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col h-full p-12 xl:p-16">
            <a href="/" class="flex items-center gap-3 group w-fit">
                <div class="w-12 h-12 rounded-2xl bg-white border border-green-100 shadow-sm flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-all">
                    <img src="{{ asset('images/acetel-logo.jpeg') }}" alt="ACETEL" class="w-8 h-8 object-contain">
                </div>
                <div>
                    <span class="block text-base font-black text-slate-900 leading-none">ACETEL TMS</span>
                    <span class="block text-[10px] font-black text-green-600 uppercase tracking-widest mt-1">Research Excellence</span>
                </div>
            </a>

            <div class="flex-1 flex flex-col justify-center">
                <h1 class="text-4xl xl:text-5xl font-black text-slate-900 leading-[1.1] mb-8 tracking-tighter">
                    Account <span class="text-amber-500">Recovery</span><br>
                    Protocol.
                </h1>
                <p class="text-slate-500 text-lg leading-relaxed max-w-sm font-medium">
                    Enter your institutional email address and we will dispatch secure instructions for resetting your password.
                </p>
            </div>

            <div class="border-t border-slate-100 pt-6">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">&copy; {{ date('Y') }} ACETEL TRADEMARK RESOURCE</p>
            </div>
        </div>
    </div>

    {{-- ===== RIGHT PANEL: Form ===== --}}
    <div class="flex-1 flex flex-col items-center justify-center px-6 py-12 lg:px-12 xl:px-20 bg-slate-50">

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
            <div class="mb-10">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight mb-2" style="font-family: 'Plus Jakarta Sans', sans-serif;">Forgot Password</h2>
                <p class="text-slate-500 text-sm">Regain access to your institutional workspace.</p>
            </div>

            @auth
                <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-xs text-amber-800 leading-relaxed">
                        <p class="font-bold">You are currently signed in as {{ auth()->user()->name }}.</p>
                        <p class="mt-0.5 text-amber-700">Enter your email below to send a reset link, or <a href="{{ route('dashboard') }}" class="underline font-semibold hover:text-amber-900">return to your dashboard</a>.</p>
                    </div>
                </div>
            @endauth

            @if (session('status'))
                <div class="mb-6 flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-700 mb-1">Request Received</p>
                        <p class="text-xs text-emerald-600">{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 flex items-start gap-3 p-4 bg-red-50 border border-red-100 rounded-2xl">
                    <div class="w-8 h-8 rounded-xl bg-red-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-red-700 mb-1">Dispatch Issue</p>
                        <p class="text-xs text-red-600">{{ $errors->first() }}</p>
                    </div>
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
                @csrf

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
                    @error('email')
                        <p class="text-red-500 text-[10px] mt-1.5 font-black uppercase tracking-widest">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full py-3.5 px-6 flex items-center justify-center gap-2 text-sm font-bold text-white rounded-xl transition-all duration-300 group mt-2"
                    style="background: linear-gradient(135deg, #16a34a, #15803d); box-shadow: 0 4px 24px rgba(22, 163, 74, 0.4);"
                    onmouseover="this.style.boxShadow='0 8px 40px rgba(22, 163, 74, 0.6)'; this.style.transform='translateY(-1px)';"
                    onmouseout="this.style.boxShadow='0 4px 24px rgba(22, 163, 74, 0.4)'; this.style.transform='translateY(0)';"
                >
                    Email Password Reset Link
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </button>
            </form>

            <p class="mt-8 text-center text-xs text-slate-400">
                <a href="{{ route('login') }}" class="font-semibold text-slate-500 hover:text-green-600 transition-colors inline-flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to login
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
