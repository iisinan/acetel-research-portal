import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/admin/milestone-templates/index.blade.php'
with open(path, 'r') as f:
    content = f.read()

# 1. Hide Schedule Form
old_schedule_form = """                                <div class="mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
                                    <form action="{{ route('admin.milestone-templates.schedule') }}" method="POST" class="flex gap-4 items-end">"""
new_schedule_form = """                                @if(!isset($isCoordinator) || !$isCoordinator)
                                <div class="mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
                                    <form action="{{ route('admin.milestone-templates.schedule') }}" method="POST" class="flex gap-4 items-end">"""
content = content.replace(old_schedule_form, new_schedule_form)

old_schedule_form_close = """                                        </div>
                                    </form>
                                </div>"""
new_schedule_form_close = """                                        </div>
                                    </form>
                                </div>
                                @endif"""
content = content.replace(old_schedule_form_close, new_schedule_form_close)

# 2. Schedule Button outside
old_schedule_btn = """<button type="button" @click.stop="openModal = 'schedule'; activeTemplate = '{{ $template->id }}'" class="inline-flex items-center justify-center px-4 py-2 bg-white text-slate-700 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-slate-200 hover:bg-slate-50 transition-colors">
                                        <svg class="w-3.5 h-3.5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        Set Date
                                    </button>"""
new_schedule_btn = """@if(!isset($isCoordinator) || !$isCoordinator)
<button type="button" @click.stop="openModal = 'schedule'; activeTemplate = '{{ $template->id }}'" class="inline-flex items-center justify-center px-4 py-2 bg-white text-slate-700 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-slate-200 hover:bg-slate-50 transition-colors">
                                        <svg class="w-3.5 h-3.5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        Set Date
                                    </button>
@endif"""
content = content.replace(old_schedule_btn, new_schedule_btn)


# 3. Checkboxes in table
old_th_check = """<th class="px-4 py-3"><input type="checkbox" x-model="selectAll" @change="selected = selectAll ? [{{ $template->studentMilestones->map(fn($m) => "'" . $m->id . "'")->implode(',') }}] : []" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"></th>"""
new_th_check = """@if(!isset($isCoordinator) || !$isCoordinator)<th class="px-4 py-3"><input type="checkbox" x-model="selectAll" @change="selected = selectAll ? [{{ $template->studentMilestones->map(fn($m) => "'" . $m->id . "'")->implode(',') }}] : []" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"></th>@endif"""
content = content.replace(old_th_check, new_th_check)

old_td_check = """<td class="px-4 py-3">
                                                        <input type="checkbox" :value="'{{ $sm->id }}'" x-model="selected" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                                    </td>"""
new_td_check = """@if(!isset($isCoordinator) || !$isCoordinator)<td class="px-4 py-3">
                                                        <input type="checkbox" :value="'{{ $sm->id }}'" x-model="selected" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                                    </td>@endif"""
content = content.replace(old_td_check, new_td_check)


# 4. Examiner Form
old_examiner_form = """                                                        <form action="{{ route('admin.milestone-templates.assign-examiner', $sm->id) }}" method="POST" class="flex items-center gap-2">
                                                            @csrf
                                                            <select name="supervisor_profile_id" required class="block w-full py-1 pl-2 pr-8 text-xs border-slate-300 focus:outline-none focus:ring-brand-500 focus:border-brand-500 rounded-md">
                                                                <option value="">Select Examiner</option>
                                                                @foreach($supervisors as $sup)
                                                                    <option value="{{ $sup->id }}" {{ $examiner && $examiner->user_id == $sup->user_id ? 'selected' : '' }}>
                                                                        {{ $sup->user->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button type="submit" class="text-white bg-green-600 hover:bg-green-700 px-2 py-1 rounded text-xs font-bold">Set</button>
                                                        </form>"""

new_examiner_form = """                                                        @if(!isset($isCoordinator) || !$isCoordinator)
                                                        <form action="{{ route('admin.milestone-templates.assign-examiner', $sm->id) }}" method="POST" class="flex items-center gap-2">
                                                            @csrf
                                                            <select name="supervisor_profile_id" required class="block w-full py-1 pl-2 pr-8 text-xs border-slate-300 focus:outline-none focus:ring-brand-500 focus:border-brand-500 rounded-md">
                                                                <option value="">Select Examiner</option>
                                                                @foreach($supervisors as $sup)
                                                                    <option value="{{ $sup->id }}" {{ $examiner && $examiner->user_id == $sup->user_id ? 'selected' : '' }}>
                                                                        {{ $sup->user->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button type="submit" class="text-white bg-green-600 hover:bg-green-700 px-2 py-1 rounded text-xs font-bold">Set</button>
                                                        </form>
                                                        @else
                                                            <span class="text-xs text-slate-700">{{ $examiner ? $examiner->user->name : 'Not assigned' }}</span>
                                                        @endif"""
content = content.replace(old_examiner_form, new_examiner_form)

with open(path, 'w') as f:
    f.write(content)
