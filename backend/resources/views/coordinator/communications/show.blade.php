@extends('layouts.coordinator')

@section('header', 'Message History')

@section('content')
<div class="space-y-6 sm:space-y-8 pb-10 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
            <a href="{{ route('coordinator.communications.index') }}" class="w-10 h-10 rounded-xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-400 hover:border-acetel-500 hover:text-acetel-500 transition-all shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
            </a>
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[9px] font-black uppercase tracking-widest text-acetel-600 bg-acetel-50 px-2 py-0.5 rounded-md">{{ ucfirst($channel->type) }} Channel</span>
                </div>
                <h1 class="text-lg sm:text-2xl font-black text-slate-900 tracking-tight truncate">{{ $channel->thesisProject->student->user->name ?? 'Candidate' }}</h1>
                <p class="text-xs font-medium text-slate-500 truncate max-w-md">{{ $channel->thesisProject->title ?? 'Thesis' }}</p>
            </div>
        </div>

        <div class="shrink-0 hidden sm:block">
            <span class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-600 shadow-sm">
                {{ $channel->messages->count() }} Messages
            </span>
        </div>
    </div>

    <!-- Chat Thread Container -->
    <div class="bg-white rounded-2xl sm:rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden flex flex-col">
        <div class="px-5 sm:px-8 py-4 sm:py-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Read-Only Channel Audit</span>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Coordinator Supervision</span>
        </div>

        <!-- Messages Area -->
        <div class="p-4 sm:p-6 space-y-4 overflow-y-auto max-h-[550px] bg-slate-50/30">
            @forelse($channel->messages as $message)
                @php
                    $isSelf = $message->user_id === auth()->id();
                @endphp
                <div class="flex {{ $isSelf ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%] sm:max-w-[75%] space-y-1">
                        <div class="flex items-center gap-2 px-1 {{ $isSelf ? 'justify-end' : 'justify-start' }}">
                            <span class="text-[10px] font-bold text-slate-600">{{ $message->user->name }}</span>
                            <span class="text-[9px] font-medium text-slate-400">{{ $message->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="p-3.5 sm:p-4 rounded-2xl text-xs sm:text-sm leading-relaxed shadow-sm {{ $isSelf ? 'bg-slate-900 text-white rounded-tr-none' : 'bg-white text-slate-800 border border-slate-100 rounded-tl-none' }}">
                            {{ $message->content }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-300 mx-auto">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    </div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">No messages recorded in this channel yet.</p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 text-center">
            <p class="text-[11px] font-medium text-slate-500 italic">This is an authorized oversight view. Direct messaging is disabled in audit mode.</p>
        </div>
    </div>
</div>
@endsection
