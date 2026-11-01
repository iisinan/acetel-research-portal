@extends('layouts.admin')

@section('content')
<div class="space-y-10 pb-10">
    <!-- Sophisticated Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <div class="p-1.5 rounded-lg bg-acetel-50">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Programs</span>
            </div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Programs</h1>
            <p class="mt-2 text-sm font-medium text-slate-500">Manage and oversee all academic programs in the system.</p>
        </div>
        <div>
            <a href="{{ route('admin.programs.create') }}" class="px-6 py-3 rounded-2xl bg-slate-900 text-white shadow-xl shadow-slate-900/10 hover:shadow-slate-900/20 hover:bg-black transition-all flex items-center gap-3 text-xs font-black uppercase tracking-widest">
                <svg class="w-4 h-4 text-acetel-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add Program
            </a>
        </div>
    </div>

    <!-- Programs Repository -->
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Program Name</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Serial</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 text-center">Students</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50">Coordinator</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($programs as $program)
                    <tr class="hover:bg-slate-50/30 transition-colors group">
                        <td class="px-10 py-7">
                            <div class="flex items-center gap-5">
                                <div class="w-12 h-12 rounded-2xl bg-acetel-50 text-acetel-500 flex items-center justify-center group-hover:bg-acetel-500 group-hover:text-white transition-all duration-500">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                </div>
                                <div class="max-w-xs">
                                    <p class="text-base font-black text-slate-900 leading-tight group-hover:text-acetel-600 transition-colors">{{ $program->name }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">Field of Study</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            <span class="text-xs font-black text-slate-900 tracking-tight">{{ $program->serial_number ?? '---' }}</span>
                        </td>
                        <td class="px-6 py-7 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-sm font-black text-slate-900">{{ number_format($program->students_count) }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-0.5">Students</span>
                            </div>
                        </td>
                        <td class="px-6 py-7">
                            @if($program->coordinatorProfiles->isNotEmpty() && $program->coordinatorProfiles->first()->user)
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-[10px] font-black text-slate-500">
                                        {{ substr($program->coordinatorProfiles->first()->user->name, 0, 1) }}
                                    </div>
                                    <span class="text-xs font-bold text-slate-700">{{ $program->coordinatorProfiles->first()->user->name }}</span>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 text-rose-500 text-[9px] font-black uppercase tracking-widest">
                                    <div class="w-1 h-1 rounded-full bg-rose-500"></div>
                                    No Coordinator
                                </span>
                            @endif
                        </td>
                        <td class="px-6 sm:px-10 py-5 sm:py-7 text-right">
                            <div class="flex items-center justify-end gap-3 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-300">
                                <a href="{{ route('admin.programs.edit', $program) }}" class="p-3 bg-white border border-slate-200 rounded-2xl text-slate-400 hover:text-acetel-500 hover:border-acetel-200 hover:shadow-lg hover:shadow-acetel-500/10 transition-all" title="Edit Program">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Smart Pagination -->
        @if($programs->hasPages())
            <div class="px-10 py-8 bg-slate-50/50 border-t border-slate-50">
                {{ $programs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
