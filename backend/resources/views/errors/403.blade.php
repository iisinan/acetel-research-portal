@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center px-6 py-12">
    <div class="max-w-md w-full text-center space-y-8 relative">
        <div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none">
            <h1 class="text-[12rem] font-black text-brand-900 tracking-tighter">403</h1>
        </div>
        
        <div class="relative z-10 flex flex-col items-center">
            <div class="w-24 h-24 bg-slate-50 text-slate-500 rounded-3xl flex items-center justify-center mb-8 shadow-sm border border-slate-200 transform -rotate-6">
                <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Access Denied</h1>
            <p class="mt-4 text-base font-medium text-slate-500">
                You do not have permission to access this page. If you believe this is a mistake, please contact the administrator.
            </p>
        </div>
        
        <div class="relative z-10 mt-10">
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-brand-600 text-white rounded-xl font-bold uppercase tracking-widest text-sm hover:bg-brand-700 hover:shadow-md transition-all shadow-sm active:scale-95">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Return Home
            </a>
        </div>
    </div>
</div>
@endsection
