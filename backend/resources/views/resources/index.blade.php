@extends('layouts.dashboard')

@section('header', 'Institutional Resource Center')

@section('content')
<div class="space-y-10 pb-12">
    {{-- Strategic Hero Header --}}
    <div class="relative overflow-hidden rounded-3xl sm:rounded-[2.5rem] bg-slate-900 p-6 sm:p-10 lg:p-14 shadow-2xl shadow-slate-900/20">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-950 via-slate-900 to-slate-900"></div>
        <div class="absolute top-0 right-0 w-[40rem] h-[40rem] bg-indigo-500/10 rounded-full blur-[100px] -mr-32 -mt-32 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-indigo-500/10 rounded-full blur-[80px] -ml-32 -mb-32 pointer-events-none"></div>
        
        <div class="relative z-10 max-w-3xl">
            <div class="inline-flex items-center gap-2.5 sm:gap-3 px-3.5 sm:px-4 py-1.5 sm:py-2 bg-white/5 border border-white/10 rounded-full backdrop-blur-sm mb-5 sm:mb-8">
                <div class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></div>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200">Institutional Repository</span>
            </div>
            <h1 class="text-2xl sm:text-4xl lg:text-6xl font-black text-white tracking-tight leading-tight">
                Academic <span class="text-indigo-400">Frameworks</span> & Protocols
            </h1>
            <p class="mt-4 sm:mt-6 text-sm sm:text-base lg:text-lg text-slate-300 font-medium leading-relaxed">
                Centralized access to official research documentation, seminar matrices, and administrative forms authorized by the institution.
            </p>
        </div>
    </div>

    {{-- Resource Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
        @forelse($document_templates as $template)
            <div class="group relative bg-white border border-slate-100 rounded-2xl sm:rounded-[2.2rem] p-5 sm:p-8 shadow-sm hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500 hover:-translate-y-2 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-full -mr-16 -mt-16 opacity-0 group-hover:opacity-100 transition-all duration-700"></div>
                
                <div class="relative z-10 flex flex-col h-full">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-5 sm:mb-8 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500 shadow-sm">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>

                    <div class="flex-1">
                        <span class="px-2.5 sm:px-3 py-1 bg-indigo-50 text-indigo-700 text-[9px] font-black uppercase tracking-widest rounded-lg border border-indigo-100">{{ $template->type }}</span>
                        <h3 class="text-lg sm:text-xl font-black text-slate-900 mt-3 sm:mt-4 leading-tight group-hover:text-indigo-600 transition-colors uppercase">{{ $template->title }}</h3>
                        <p class="mt-2 sm:mt-3 text-xs sm:text-sm text-slate-400 font-bold uppercase tracking-widest italic opacity-70">
                            Current Version: {{ $template->version }}
                        </p>
                    </div>

                    <div class="mt-6 sm:mt-10 pt-5 sm:pt-8 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Released</span>
                            <span class="text-[11px] font-bold text-slate-700 mt-1">{{ $template->created_at->format('M d, Y') }}</span>
                        </div>
                        <a href="{{ route('templates.download', $template) }}" 
                           class="inline-flex items-center justify-center w-11 h-11 sm:w-12 sm:h-12 bg-slate-900 text-white rounded-xl hover:bg-indigo-600 transition-all duration-300 shadow-lg shadow-slate-900/10 active:scale-95 group/btn">
                            <svg class="w-5 h-5 group-hover/btn:translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-24 text-center">
                <div class="w-24 h-24 bg-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-8 border-2 border-slate-100">
                    <svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-400 uppercase tracking-widest">Repository Empty</h3>
                <p class="text-slate-400 mt-2 font-medium">Institutional resources are currently being synchronized.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
