import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/milestones/show.blade.php'
with open(path, 'r') as f:
    content = f.read()

# I will replace from "<!-- Sophisticated Header -->" up to "    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">"

old_block_start = "    <!-- Sophisticated Header -->"
old_block_end = "    <div class=\"grid grid-cols-1 lg:grid-cols-3 gap-8\">"

start_idx = content.find(old_block_start)
end_idx = content.find(old_block_end)

if start_idx != -1 and end_idx != -1:
    old_block = content[start_idx:end_idx]
    
    new_block = r"""    <!-- Sophisticated Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2 text-acetel-600">
                <a href="{{ route('theses.show', $milestone->thesis_project_id) }}" class="p-1.5 rounded-lg bg-acetel-50 hover:bg-acetel-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <span class="text-[10px] font-black uppercase tracking-[0.3em]">Milestone Details</span>
            </div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">{{ $milestone->template->name }}</h1>
            <p class="mt-2 text-sm font-medium text-slate-500 max-w-2xl">{{ $milestone->template->description }}</p>
        </div>
        <div class="flex items-center gap-4">
            @php
                $statusBadge = match($milestone->status) {
                    'not_started' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'in_progress' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                    'submitted' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'revision_required' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                    'approved' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'M5 13l4 4L19 7'],
                    default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z']
                };
            @endphp
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl {{ $statusBadge['bg'] }} border {{ $statusBadge['border'] }} shadow-sm">
                <svg class="w-4 h-4 {{ $statusBadge['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $statusBadge['icon'] }}"></path></svg>
                <span class="text-[10px] font-black {{ $statusBadge['text'] }} uppercase tracking-widest">{{ str_replace('_', ' ', $milestone->status) }}</span>
            </div>
            
            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-lg font-black text-slate-900 shadow-sm">
                0{{ $milestone->template->order }}
            </div>
        </div>
    </div>

"""
    content = content.replace(old_block, new_block)
    with open(path, 'w') as f:
        f.write(content)
else:
    print("Could not find the block to replace.")
