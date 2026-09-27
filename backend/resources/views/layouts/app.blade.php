<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <link rel="icon" href="{{ asset('images/acetel-logo.jpeg') }}" type="image/jpeg">
    <link rel="shortcut icon" href="{{ asset('images/acetel-logo.jpeg') }}" type="image/jpeg">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(Auth::check())
        <meta name="user-id" content="{{ Auth::id() }}">
    @endif
    <title>{{ config('app.name', 'TMS') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white text-slate-800 selection:bg-brand-100 selection:text-brand-800 h-full">
    <div class="min-h-screen relative overflow-hidden bg-white">
        <div class="absolute top-0 right-0 w-[40rem] h-[40rem] bg-brand-500/5 blur-[120px] rounded-full pointer-events-none -mt-40 -mr-40"></div>
        <div class="absolute bottom-0 left-0 w-[35rem] h-[35rem] bg-brand-500/5 blur-[100px] rounded-full pointer-events-none -mb-40 -ml-40"></div>

        <main class="relative z-10">
            @if (session('error'))
                <div class="max-w-xl mx-auto pt-8 px-6">
                    <div class="flex items-center gap-4 px-6 py-4 bg-red-50 border border-red-100 rounded-2xl shadow-sm">
                        <div class="w-8 h-8 rounded-lg bg-red-500 flex items-center justify-center text-white shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <span class="text-sm font-bold text-red-700">{{ session('error') }}</span>
                    </div>
                </div>
            @endif
            @if (session('success'))
                <div class="max-w-xl mx-auto pt-8 px-6">
                    <div class="flex items-center gap-4 px-6 py-4 bg-brand-50 border border-brand-100 rounded-2xl shadow-sm">
                        <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center text-white shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm font-bold text-brand-700">{{ session('success') }}</span>
                    </div>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
    <x-document-preview-modal />
    <x-toast />
    <x-force-password-change-modal />
</body>
</html>
