import re

with open('backend/resources/views/layouts/partials/sidebar-menu.blade.php', 'r') as f:
    content = f.read()

# We'll wrap the entire sidebar in an x-data block if it's not already.
wrapper_top = """<div x-data="{ activeRole: localStorage.getItem('activeDashboardRole') || '{{ Auth::user()->getRoleNames()->first() ?? 'Student' }}' }" x-init="$watch('activeRole', val => { localStorage.setItem('activeDashboardRole', val); window.dispatchEvent(new CustomEvent('role-changed', {detail: val})); })">

@php
    $userRoles = Auth::user()->getRoleNames();
    $hasMultipleRoles = $userRoles->count() > 1;
@endphp

@if($hasMultipleRoles)
    <div class="px-4 mb-6">
        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 block">Switch Role</label>
        <select x-model="activeRole" class="w-full bg-slate-50 border border-slate-200 text-sm font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 text-slate-700 shadow-sm transition-all">
            @foreach($userRoles as $role)
                <option value="{{ $role }}">{{ $role }}</option>
            @endforeach
        </select>
    </div>
@endif

"""

wrapper_bottom = """
</div>
"""

# Replace @role blocks with Alpine x-show
content = content.replace("@role('Admin')", "<div x-show=\"activeRole === 'Admin'\" x-cloak>")
content = content.replace("@endrole", "</div>")

# For other roles, wait, some are @hasanyrole('Admin|Director|Program Coordinator')
# We need to replace @role('Program Coordinator') etc.

# Let's fix the basic ones first.
content = content.replace("@role('Program Coordinator')", "<div x-show=\"activeRole === 'Program Coordinator'\" x-cloak>")
content = content.replace("@role('Supervisor')", "<div x-show=\"activeRole === 'Supervisor'\" x-cloak>")
content = content.replace("@role('Student')", "<div x-show=\"activeRole === 'Student'\" x-cloak>")
content = content.replace("@hasanyrole('Admin|Director|Program Coordinator')", "<div x-show=\"['Admin', 'Director', 'Program Coordinator'].includes(activeRole)\" x-cloak>")
content = content.replace("@endhasanyrole", "</div>")


# Now add examiners!
examiners_block = """
<!-- Examiners Section -->
<div x-show="['Internal Examiner', 'External Examiner'].includes(activeRole)" x-cloak class="mb-4">
    <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Examination</p>
    <div class="space-y-1">
        <a href="#" class="{{ $navClass }} {{ request()->routeIs('examiner.theses.*') ? $activeClass : $inactiveClass }}">
            <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            Assigned Theses
        </a>
        <a href="{{ route('inbox.index') }}" class="{{ $navClass }} {{ request()->routeIs('inbox.*') ? $activeClass : $inactiveClass }}">
            <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            Inbox
        </a>
    </div>
</div>
"""

# Let's insert the examiners block right before the Reports section
content = content.replace("<!-- Reports Section -->", examiners_block + "\n<!-- Reports Section -->")

# Combine everything
content = wrapper_top + content + wrapper_bottom

with open('backend/resources/views/layouts/partials/sidebar-menu.blade.php', 'w') as f:
    f.write(content)

