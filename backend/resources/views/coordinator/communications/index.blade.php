@extends('layouts.coordinator')

@section('header', 'Communications')

@section('content')
<div class="space-y-10 pb-10">
    <!-- Sophisticated Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <div class="p-1.5 rounded-lg bg-acetel-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Communications Overview</span>
            </div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Messages History</h1>
            <p class="mt-2 text-sm font-medium text-slate-500">Monitoring student and supervisor communications.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-5 py-3 rounded-2xl bg-white border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">{{ $channels->count() }} Active Channels</span>
            </div>
        </div>
    </div>

    <!-- Audit Matrix -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden">
        <div class="px-10 py-8 border-b border-slate-50 bg-slate-50/30 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-black text-slate-900 tracking-tight">Message Logs</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Communication history</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Student & Thesis</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Type</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Last Message</th>
                        <th class="px-10 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($channels as $channel)
                        <tr class="hover:bg-slate-50/30 transition-colors group">
                            <td class="px-10 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center text-xs font-black shadow-lg shadow-slate-900/10">
                                        {{ substr($channel->thesisProject->student->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 group-hover:text-acetel-600 transition-colors">{{ $channel->thesisProject->student->user->name }}</p>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1.5 truncate max-w-[200px]">{{ $channel->thesisProject->title }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-[9px] font-black text-slate-600 uppercase tracking-widest shadow-sm">
                                    {{ $channel->type }}
                                </span>
                            </td>
                            <td class="px-6 py-6 text-center">
                                @php
                                    $daysSinceLast = $channel->last_message_at ? $channel->last_message_at->diffInDays(now()) : 999;
                                    $health = $daysSinceLast > 7 ? 'critical' : ($daysSinceLast > 3 ? 'warning' : 'healthy');
                                    $colors = [
                                        'healthy' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'warning' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        'critical' => 'bg-rose-50 text-rose-600 border-rose-100'
                                    ];
                                    $labels = [
                                        'healthy' => 'Active',
                                        'warning' => 'Delayed',
                                        'critical' => 'Inactive'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-3 py-1.5 rounded-xl {{ $colors[$health] }} border text-[9px] font-black uppercase tracking-widest shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $health == 'healthy' ? 'bg-emerald-500' : ($health == 'warning' ? 'bg-amber-500' : 'bg-rose-500') }} mr-2 {{ $health != 'healthy' ? 'animate-pulse' : '' }}"></span>
                                    {{ $labels[$health] }}
                                </span>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">
                                    {{ $channel->last_message_at ? $channel->last_message_at->format('M d, Y') : 'None' }}
                                </span>
                            </td>
                            <td class="px-10 py-6 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('coordinator.communications.show', $channel->id) }}" class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 hover:text-acetel-600 hover:border-acetel-100 shadow-sm transition-all group/btn active:scale-90">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </a>
                                    <form action="{{ route('coordinator.communications.nudge', $channel->id) }}" method="POST"
                                        data-confirm="Send an official reminder ping to this communication channel?"
                                        data-confirm-title="Send Reminder"
                                        data-confirm-type="warning"
                                        data-confirm-btn="Send Reminder">
                                        @csrf
                                        <button type="submit" class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 hover:text-amber-500 hover:border-amber-100 shadow-sm transition-all group/btn active:scale-90" title="Send Reminder">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-10 py-16 text-center opacity-40">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">No messages found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
