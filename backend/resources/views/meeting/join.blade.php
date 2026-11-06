@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="bg-white rounded-3xl border border-slate-200/80 p-8 sm:p-12 shadow-xl shadow-slate-200/50 text-center">
        <div class="w-16 h-16 rounded-3xl bg-blue-50 border border-blue-100 flex items-center justify-center mx-auto mb-6 text-blue-600 shadow-sm">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
        </div>
        
        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200 mb-3">
            Virtual Presentation Room
        </span>

        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mb-2">
            Zoom Meeting Session
        </h1>
        <p class="text-sm font-medium text-slate-500 max-w-md mx-auto mb-8">
            Virtual examinations and defences at ACETEL are conducted via Zoom.
        </p>

        @if(!empty($meetingLink))
            <a href="{{ $meetingLink }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-2.5 px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-lg shadow-emerald-600/30 active:scale-95">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                <span>Launch Zoom Meeting</span>
            </a>
        @else
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold max-w-md mx-auto">
                No Zoom link has been assigned to this session yet. Please check back shortly or check your email for the meeting invite.
            </div>
        @endif

        <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-center gap-4 text-xs font-bold text-slate-400">
            <a href="{{ url()->previous() }}" class="text-slate-600 hover:text-slate-900 underline">← Return to Dashboard</a>
        </div>
    </div>
</div>
@endsection
