<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Repository - {{ config('app.name', 'ACETEL Postgrad System') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-gray-50 text-gray-900 font-sans">
        <div class="min-h-screen flex flex-col items-center relative">
            <!-- Navigation -->
            <div class="w-full bg-white shadow-sm sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 hover:opacity-80 transition">
                        <img src="{{ asset('acetel logo.jpg') }}" alt="ACETEL Logo" class="h-10 object-contain">
                        <span class="font-bold text-green-900 tracking-tight text-lg">ACETEL Portal</span>
                    </a>
                    <div class="flex space-x-6 items-center">
                        <a href="{{ url('/') }}" class="text-gray-600 hover:text-green-700 font-semibold text-sm transition">Home</a>
                        <a href="{{ url('/repository') }}" class="text-green-700 font-bold border-b-2 border-green-700 text-sm pb-1 transition">Repository</a>
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-4 py-2 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition shadow-sm text-sm">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-green-700 font-semibold text-sm transition">Login</a>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Header -->
            <div class="w-full bg-green-900 text-white py-12 md:py-20 relative overflow-hidden">
                <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                <div class="max-w-7xl mx-auto px-6 relative z-10 text-center">
                    <h1 class="text-4xl md:text-5xl font-extrabold mb-4">Research Repository</h1>
                    <p class="text-green-100 text-lg max-w-2xl mx-auto">Explore a curated collection of research theses and indexed publications from our esteemed ACETEL postgraduates.</p>
                </div>
            </div>

            <!-- Repository Content -->
            <div class="relative z-10 w-full max-w-7xl px-6 py-12 flex-grow">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    @forelse($students as $student)
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200 hover:shadow-lg transition duration-300 flex flex-col h-full">
                            <div class="flex items-center gap-4 mb-5 border-b border-gray-100 pb-4">
                                <div class="h-12 w-12 bg-green-100 text-green-800 rounded-full flex items-center justify-center font-bold text-xl shadow-inner">
                                    {{ substr($student->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 text-lg leading-tight">{{ $student->user->name }}</h3>
                                    <p class="text-xs font-semibold text-green-700 mt-1">{{ $student->programme->name ?? 'Programme' }} &bull; Cohort {{ $student->cohort_id }}</p>
                                </div>
                            </div>
                            
                            @if($student->thesis_title)
                                <div class="mb-5 flex-grow">
                                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Thesis Title</span>
                                    <p class="text-sm font-semibold text-gray-800 leading-relaxed">{{ $student->thesis_title }}</p>
                                </div>
                            @endif

                            @if($student->publications && count(json_decode($student->publications, true)) > 0)
                                <div class="mt-auto border-t border-gray-100 pt-4">
                                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Publications ({{ count(json_decode($student->publications, true)) }})</span>
                                    <ul class="space-y-2">
                                        @foreach(array_slice(json_decode($student->publications, true), 0, 2) as $pub)
                                            <li class="text-xs bg-gray-50 p-2.5 rounded-lg border border-gray-200 flex items-start gap-2">
                                                <svg class="w-3.5 h-3.5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="text-gray-700 font-medium line-clamp-2" title="{{ $pub['title'] }}">{{ $pub['title'] }}</span>
                                            </li>
                                        @endforeach
                                        @if(count(json_decode($student->publications, true)) > 2)
                                            <li class="text-xs text-center text-green-600 font-semibold pt-1">
                                                +{{ count(json_decode($student->publications, true)) - 2 }} more publication(s)
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="col-span-full text-center py-20 bg-white rounded-2xl border border-gray-200 border-dashed">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <p class="text-gray-500 text-lg font-medium">No public records found yet.</p>
                            <p class="text-gray-400 text-sm mt-1">Research theses will appear here once approved.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <footer class="w-full py-8 text-center text-gray-500 text-sm border-t border-gray-200 bg-white mt-auto">
                <p>&copy; {{ date('Y') }} Africa Centre of Excellence on Technology Enhanced Learning (ACETEL).</p>
                <p class="mt-1">National Open University of Nigeria. All rights reserved.</p>
            </footer>
        </div>
    </body>
</html>
