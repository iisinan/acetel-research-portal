<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50 relative overflow-x-hidden">
        <!-- Background Decoration -->
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none flex items-center justify-center">
            <div class="absolute w-[600px] h-[600px] bg-green-50 rounded-full opacity-50 blur-3xl translate-x-1/3 -translate-y-1/4"></div>
            <div class="absolute w-[500px] h-[500px] bg-emerald-50 rounded-full opacity-40 blur-3xl -translate-x-1/3 translate-y-1/4"></div>
        </div>

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 pb-12 sm:pt-0 relative z-10">
            <div>
                <a href="/" class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 inline-block mb-2">
                    <img src="{{ asset('acetel logo.jpg') }}" alt="ACETEL Logo" class="w-32 h-auto object-contain" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-8 py-10 bg-white border border-gray-100 shadow-xl sm:rounded-3xl">
                {{ $slot }}
            </div>
        </div>
        @livewireScripts
    </body>
</html>
