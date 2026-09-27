import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/admin/milestone-templates/index.blade.php'
with open(path, 'r') as f:
    content = f.read()

# Replace export link
old_export = """<a href="{{ route('admin.milestone-templates.export-students', $template->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">"""
new_export = """<a href="{{ isset($isCoordinator) && $isCoordinator ? route('coordinator.milestone-templates.export-students', $template->id) : route('admin.milestone-templates.export-students', $template->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">"""
content = content.replace(old_export, new_export)

# Replace schedule route
old_schedule = """<form action="{{ route('admin.milestone-templates.schedule') }}" method="POST" class="flex gap-4 items-end">"""
new_schedule = """<form action="{{ isset($isCoordinator) && $isCoordinator ? '#' : route('admin.milestone-templates.schedule') }}" method="POST" class="flex gap-4 items-end">"""
content = content.replace(old_schedule, new_schedule)

with open(path, 'w') as f:
    f.write(content)
