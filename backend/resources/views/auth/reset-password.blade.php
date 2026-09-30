@extends('layouts.app')

@section('content')
<div class="min-h-screen flex bg-white">

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
                    Set Your <span class="text-green-600">New</span><br>Password.
                </h1>
                <p class="text-slate-500 text-lg leading-relaxed max-w-sm font-medium">
                    Choose a strong, unique password to secure your institutional research workspace.
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
                <h2 class="text-3xl font-black text-slate-900 tracking-tight mb-2" style="font-family: 'Plus Jakarta Sans', sans-serif;">Reset Password</h2>
                <p class="text-slate-500 text-sm">Enter and confirm your new secure password below.</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 flex items-start gap-3 p-4 bg-red-50 border border-red-100 rounded-2xl">
                    <div class="w-8 h-8 rounded-xl bg-red-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-red-700 mb-1">Reset Failed</p>
                        @foreach ($errors->all() as $error)
                            <p class="text-xs text-red-600">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" class="space-y-5" x-data="{ showPwd: false, showConfirm: false }">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Email Address</label>
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
                            value="{{ old('email', $email) }}"
                            placeholder="your@email.com"
                            class="w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-sm font-medium placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                        >
                    </div>
                    @error('email') <p class="text-red-500 text-[10px] mt-1.5 font-black uppercase tracking-widest">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-600 uppercase tracking-wider">New Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input
                            id="password"
                            name="password"
                            :type="showPwd ? 'text' : 'password'"
                            required
                            minlength="8"
                            placeholder="Minimum 8 characters"
                            class="w-full pl-11 pr-12 py-3.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-sm font-medium placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                        >
                        <button type="button" @click="showPwd = !showPwd" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600">
                            <svg x-show="!showPwd" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPwd" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    @error('password') <p class="text-red-500 text-[10px] mt-1.5 font-black uppercase tracking-widest">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Confirm New Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            :type="showConfirm ? 'text' : 'password'"
                            required
                            placeholder="Re-enter your new password"
                            class="w-full pl-11 pr-12 py-3.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-sm font-medium placeholder-slate-300 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                        >
                        <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600">
                            <svg x-show="!showConfirm" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showConfirm" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full py-3.5 px-6 flex items-center justify-center gap-2 text-sm font-bold text-white rounded-xl transition-all duration-300 group mt-2"
                    style="background: linear-gradient(135deg, #16a34a, #15803d); box-shadow: 0 4px 24px rgba(22, 163, 74, 0.4);"
                    onmouseover="this.style.boxShadow='0 8px 40px rgba(22, 163, 74, 0.6)'; this.style.transform='translateY(-1px)';"
                    onmouseout="this.style.boxShadow='0 4px 24px rgba(22, 163, 74, 0.4)'; this.style.transform='translateY(0)';"
                >
                    Reset Password &amp; Login
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
