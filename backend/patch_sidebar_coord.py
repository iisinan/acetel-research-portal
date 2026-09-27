import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/layouts/partials/sidebar-menu.blade.php'
with open(path, 'r') as f:
    content = f.read()

old_coord_milestones = """            <a href="{{ route('coordinator.milestones.index') }}" class="{{ $navClass }} {{ request()->routeIs('coordinator.milestones.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('coordinator.milestones.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5" /></svg>
                Milestones
            </a>"""

new_coord_milestones = """            <a href="{{ route('coordinator.milestones.index') }}" class="{{ $navClass }} {{ request()->routeIs('coordinator.milestones.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('coordinator.milestones.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5" /></svg>
                Milestones
            </a>
            <a href="{{ route('coordinator.milestone-templates.index') }}" class="{{ $navClass }} {{ request()->routeIs('coordinator.milestone-templates.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors {{ request()->routeIs('coordinator.milestone-templates.*') ? '!text-primary-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Batch Scheduling
            </a>"""

content = content.replace(old_coord_milestones, new_coord_milestones)

with open(path, 'w') as f:
    f.write(content)
