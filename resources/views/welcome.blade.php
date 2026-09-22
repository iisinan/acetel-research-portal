<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-line">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'ACETEL Postgrad System') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-white text-gray-900 font-sans" x-data="{ tab: 'home' }">
        <div class="min-h-screen flex flex-col items-center relative overflow-hidden">
            <!-- Navigation Tabs -->
            <div class="w-full bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
                <div class="max-w-5xl mx-auto px-6 py-4 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('acetel logo.jpg') }}" alt="ACETEL Logo" class="h-8 object-contain mix-blend-multiply">
                        <span class="font-bold text-indigo-900 tracking-tight">ACETEL Postgrad</span>
                    </div>
                    <div class="flex space-x-2 bg-gray-100 p-1 rounded-lg">
                        <button @click="tab = 'home'" :class="tab === 'home' ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-1.5 rounded-md text-sm font-bold transition-all">Home</button>
                        <button @click="tab = 'repository'" :class="tab === 'repository' ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-1.5 rounded-md text-sm font-bold transition-all">Repository</button>
                    </div>
                </div>
            </div>

            <!-- Decorative background -->
            <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-40 -right-40 w-96 h-96 bg-indigo-50 rounded-full opacity-50 blur-3xl"></div>
                <div class="absolute -bottom-40 -left-20 w-80 h-80 bg-indigo-100 rounded-full opacity-50 blur-3xl"></div>
            </div>

            <!-- Home Tab -->
            <div x-show="tab === 'home'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="relative z-10 w-full max-w-4xl px-6 py-12 md:py-24 text-center mt-10">
                <div class="inline-flex items-center justify-center mb-8">
                    <img src="{{ asset('acetel logo.jpg') }}" alt="ACETEL Logo" class="w-32 md:w-40 h-auto object-contain mix-blend-multiply">
                </div>

                <h1 class="text-4xl md:text-6xl font-extrabold text-indigo-900 tracking-tight mb-6">
                    ACETEL Postgraduate <br> Research Tracking
                </h1>
                
                <p class="text-xl md:text-2xl text-indigo-700 mb-10 max-w-2xl mx-auto font-light">
                    Manage your MSc and PhD research journey seamlessly. Track milestones, assign supervisors, and submit progress reports in one unified portal.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-8">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto px-8 py-4 bg-indigo-600 text-white font-bold rounded-lg shadow hover:bg-indigo-700 transition duration-150 ease-in-out text-lg">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 bg-indigo-600 text-white font-bold rounded-lg shadow hover:bg-indigo-700 transition duration-150 ease-in-out text-lg">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 bg-red-600 text-white font-bold rounded-lg shadow hover:bg-red-700 transition duration-150 ease-in-out text-lg">
                            Claim Student Account
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Repository Tab -->
            <div x-cloak x-show="tab === 'repository'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="relative z-10 w-full max-w-5xl px-6 py-12">
                <h2 class="text-3xl font-extrabold text-indigo-900 mb-2">Public Repository</h2>
                <p class="text-gray-500 mb-8">A curated collection of research theses and Scopus indexed publications from ACETEL postgraduates.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($students as $student)
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-3 mb-4 border-b border-gray-50 pb-4">
                                <div class="h-10 w-10 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-bold">
                                    {{ substr($student->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900">{{ $student->user->name }}</h3>
                                    <p class="text-xs text-gray-500">{{ $student->programme->name ?? 'Programme' }} - Cohort {{ $student->cohort_id }}</p>
                                </div>
                            </div>
                            
                            @if($student->thesis_title)
                                <div class="mb-4">
                                    <span class="text-[10px] font-extrabold text-indigo-500 uppercase tracking-widest block mb-1">Thesis Title</span>
                                    <p class="text-sm font-semibold text-gray-800 leading-snug">{{ $student->thesis_title }}</p>
                                </div>
                            @endif

                            @if($student->publications && count(json_decode($student->publications, true)) > 0)
                                <div>
                                    <span class="text-[10px] font-extrabold text-indigo-500 uppercase tracking-widest block mb-2">Publications</span>
                                    <ul class="space-y-2">
                                        @foreach(json_decode($student->publications, true) as $pub)
                                            <li class="text-sm bg-gray-50 p-2 rounded border border-gray-100 flex items-start gap-2">
                                                <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="text-gray-700 font-medium">{{ $pub['title'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12 bg-white rounded-2xl border border-gray-100 border-dashed">
                            <p class="text-gray-500">No public records found yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <footer class="mt-auto w-full py-6 text-center text-gray-500 text-sm z-10 border-t border-gray-100 bg-white/50 backdrop-blur-md">
                &copy; {{ date('Y') }} Africa Centre of Excellence on Technology Enhanced Learning (ACETEL). All rights reserved.
            </footer>
        </div>
    </body>
</html>
