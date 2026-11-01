@extends('layouts.admin')

@section('content')
<div class="space-y-10 pb-10">
    <!-- Sophisticated Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <div class="p-1.5 rounded-lg bg-acetel-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Communication</span>
            </div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Announcements</h1>
            <p class="mt-2 text-sm font-medium text-slate-500">Post and manage announcements for all students and staff.</p>
        </div>
        <div>
            <a href="{{ route('admin.announcements.create') }}" class="px-6 py-3 rounded-2xl bg-slate-900 text-white shadow-xl shadow-slate-900/10 hover:shadow-slate-900/20 hover:bg-black transition-all flex items-center gap-3 text-xs font-black uppercase tracking-widest">
                <svg class="w-4 h-4 text-acetel-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Draft Announcement
            </a>
        </div>
    </div>

    <!-- Announcements Feed -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Title</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Type</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Visible to</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Status / Expiry</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($announcements as $announcement)
                    <tr class="hover:bg-slate-50/30 transition-colors group">
                        <td class="px-10 py-7">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-acetel-50 group-hover:text-acetel-500 transition-all">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                                </div>
                                <div class="max-w-md">
                                    <p class="text-base font-black text-slate-900 leading-tight group-hover:text-acetel-600 transition-colors">{{ $announcement->title }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">Created {{ $announcement->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            @php
                                $typeMap = [
                                    'info' => ['bg' => 'bg-acetel-50', 'text' => 'text-acetel-600', 'label' => 'Information'],
                                    'warning' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'label' => 'Alert'],
                                    'danger' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'label' => 'Warning'],
                                    'success' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'label' => 'Update'],
                                ];
                                $config = $typeMap[$announcement->type] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'label' => 'Standard'];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $config['bg'] }} {{ $config['text'] }} border border-transparent">
                                {{ $config['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-7">
                            @if($announcement->target_role)
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-acetel-50 text-acetel-600 text-[10px] font-black uppercase tracking-widest">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    {{ $announcement->target_role }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest">
                                    <svg class="w-3 h-3 text-acetel-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    All Users
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-7">
                            @php
                                $isExpired = $announcement->expires_at && $announcement->expires_at->isPast();
                            @endphp
                            @if($isExpired)
                                <div class="flex flex-col">
                                    <span class="text-xs font-black text-rose-500 uppercase tracking-tighter">Expired</span>
                                    <span class="text-[9px] font-bold text-slate-400 mt-0.5">{{ $announcement->expires_at->format('d M Y') }}</span>
                                </div>
                            @else
                                <div class="flex flex-col">
                                    <span class="text-xs font-black text-emerald-500 uppercase tracking-tighter">Active</span>
                                    <span class="text-[9px] font-bold text-slate-400 mt-0.5">{{ $announcement->expires_at ? $announcement->expires_at->format('d M Y') : 'Never' }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 sm:px-10 py-5 sm:py-7 text-right">
                            <div class="flex items-center justify-end gap-3 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-300">
                                <a href="{{ route('admin.announcements.edit', $announcement) }}" class="p-3 bg-white border border-slate-200 rounded-2xl text-slate-400 hover:text-acetel-500 hover:border-acetel-200 hover:shadow-lg hover:shadow-acetel-500/10 transition-all" title="Edit Announcement">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-10 py-20 text-center bg-slate-50/50">
                            <div class="max-w-xs mx-auto">
                                <div class="w-16 h-16 bg-white rounded-3xl border border-slate-100 flex items-center justify-center mx-auto mb-6 shadow-sm">
                                    <svg class="w-8 h-8 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                                </div>
                                <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">No Announcements Found</h4>
                                <p class="text-xs text-slate-500 mt-2 font-medium">No announcements have been posted yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Smart Pagination -->
        @if($announcements->hasPages())
            <div class="px-10 py-8 bg-slate-50/50 border-t border-slate-50">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
