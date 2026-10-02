import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/layouts/partials/sidebar-menu.blade.php'
with open(path, 'r') as f:
    content = f.read()

old_supervisor_block = r"""<!-- Supervisor Section -->
@role('Supervisor')
    <div class="mb-4">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Supervision</p>
        <div class="space-y-1">
            <a href="{{ route('supervisor.students.index') }}" class="{{ $navClass }} {{ request()->routeIs('supervisor.students.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('supervisor.students.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Students
            </a>
            <a href="{{ route('supervisor.seminars.index') }}" class="{{ $navClass }} {{ request()->routeIs('supervisor.seminars.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('supervisor.seminars.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                Seminar Examination
            </a>
            <a href="#" class="{{ $navClass }} {{ request()->routeIs('supervisor.evaluations') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('supervisor.evaluations') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Evaluations
            </a>
        </div>
    </div>
@endrole"""

new_supervisor_block = r"""<!-- Supervisor Section -->
@role('Supervisor')
    @php
        $pendingSeminarCount = \App\Models\DefenceEvent::where('type', 'seminar')
            ->whereHas('panelMembers', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->whereDoesntHave('evaluations', function($q) {
                $q->where('evaluator_id', auth()->id());
            })
            ->count();
    @endphp
    <div class="mb-4">
        <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Supervision</p>
        <div class="space-y-1">
            <a href="{{ route('supervisor.students.index') }}" class="{{ $navClass }} {{ request()->routeIs('supervisor.students.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('supervisor.students.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Students
            </a>
            @if($pendingSeminarCount > 0)
                <a href="{{ route('supervisor.seminars.index') }}" class="{{ $navClass }} {{ request()->routeIs('supervisor.seminars.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('supervisor.seminars.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                    Seminar Examination
                    <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold rounded-full bg-brand-100 text-brand-700">{{ $pendingSeminarCount }}</span>
                </a>
            @endif
            <a href="#" class="{{ $navClass }} {{ request()->routeIs('supervisor.evaluations') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('supervisor.evaluations') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Evaluations
            </a>
        </div>
    </div>
@endrole"""

content = content.replace(old_supervisor_block, new_supervisor_block)

with open(path, 'w') as f:
    f.write(content)
