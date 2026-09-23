<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'ACETEL Postgrad System') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gray-50 text-gray-900 font-sans">
        <div class="min-h-screen flex flex-col relative overflow-hidden">
            <!-- Navigation -->
            <div class="w-full bg-white shadow-sm sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 hover:opacity-80 transition">
                        <img src="{{ asset('acetel logo.jpg') }}" alt="ACETEL Logo" class="h-10 object-contain">
                        <span class="font-bold text-green-900 tracking-tight text-lg hidden sm:block">ACETEL Portal</span>
                    </a>
                    <div class="flex space-x-4 sm:space-x-6 items-center">
                        <a href="{{ url('/') }}" class="text-green-700 font-bold border-b-2 border-green-700 text-sm pb-1 transition">Home</a>
                        <a href="{{ url('/repository') }}" class="text-gray-600 hover:text-green-700 font-semibold text-sm transition">Repository</a>
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-4 py-2 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition shadow-sm text-sm">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-green-700 font-semibold text-sm transition">Login</a>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Hero Section -->
            <div class="relative z-10 w-full flex-grow flex flex-col items-center justify-center px-6 py-12 md:py-24 text-center">
                <!-- Background Decoration -->
                <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none flex items-center justify-center">
                    <div class="absolute w-[600px] h-[600px] bg-green-50 rounded-full opacity-70 blur-3xl translate-x-1/3 -translate-y-1/4"></div>
                    <div class="absolute w-[500px] h-[500px] bg-emerald-50 rounded-full opacity-60 blur-3xl -translate-x-1/3 translate-y-1/4"></div>
                </div>

                <div class="relative z-10 max-w-4xl mx-auto">
                    <div class="inline-flex items-center justify-center mb-10 bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                        <img src="{{ asset('acetel logo.jpg') }}" alt="ACETEL Logo" class="w-40 md:w-56 h-auto object-contain">
                    </div>

                    <h1 class="text-4xl md:text-6xl font-extrabold text-green-950 tracking-tight mb-6 leading-tight">
                        Postgraduate Research <br class="hidden md:block"> Management System
                    </h1>
                    
                    <p class="text-lg md:text-xl text-green-800 mb-12 max-w-2xl mx-auto font-medium leading-relaxed">
                        A unified portal to manage your MSc and PhD research journey. Track milestones, assign supervisors, and submit progress reports seamlessly.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto px-8 py-4 bg-green-600 text-white font-bold rounded-xl shadow-md hover:bg-green-700 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 text-lg flex items-center justify-center gap-2">
                                Go to Dashboard
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 bg-green-600 text-white font-bold rounded-xl shadow-md hover:bg-green-700 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 text-lg">
                                Register as Student
                            </a>
                            <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 bg-white text-green-700 border-2 border-green-200 font-bold rounded-xl shadow-sm hover:bg-green-50 hover:border-green-300 transition-all duration-200 text-lg">
                                Login
                            </a>
                        @endauth
                    </div>
                </div>
            </div>

            <footer class="w-full py-8 text-center text-gray-500 text-sm border-t border-gray-200 bg-white z-10">
                <p>&copy; {{ date('Y') }} Africa Centre of Excellence on Technology Enhanced Learning (ACETEL).</p>
                <p class="mt-1">National Open University of Nigeria. All rights reserved.</p>
            </footer>
        </div>
    </body>
</html>
