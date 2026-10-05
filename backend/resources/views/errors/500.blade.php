@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center px-6 py-12">
    <div class="max-w-md w-full text-center space-y-8 relative">
        <div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none">
            <h1 class="text-[12rem] font-black text-brand-900 tracking-tighter">500</h1>
        </div>
        
        <div class="relative z-10 flex flex-col items-center">
            <div class="w-24 h-24 bg-red-50 text-red-500 rounded-3xl flex items-center justify-center mb-8 shadow-sm border border-red-100 transform -rotate-6">
                <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Server Error</h1>
            <p class="mt-4 text-base font-medium text-slate-500">
                Whoops, something went wrong on our servers. We're looking into it. Please try again later.
            </p>
        </div>
        
        <div class="relative z-10 mt-10">
            <button onclick="window.location.reload()" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-brand-600 text-white rounded-xl font-bold uppercase tracking-widest text-sm hover:bg-brand-700 hover:shadow-md transition-all shadow-sm active:scale-95">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Try Again
            </button>
            
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 mt-4 w-full text-brand-600 bg-white border-2 border-brand-100 rounded-xl font-bold uppercase tracking-widest text-sm hover:bg-brand-50 hover:shadow-md transition-all shadow-sm active:scale-95">
                Return Home
            </a>
        </div>
    </div>
</div>
@endsection
