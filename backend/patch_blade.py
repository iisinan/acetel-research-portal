import re

with open('/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/admin/milestone-templates/index.blade.php', 'r') as f:
    content = f.read()

# Replace <tbody class="..."> with nothing, because we will use multiple tbodys inside forelse
content = re.sub(r'<tbody[^>]*>', '', content)
content = re.sub(r'</tbody>', '', content)

row_start = r'<tr class="hover:bg-slate-50/30 transition-colors group" data-id="{{ \$template->id }}">'
new_row_start = '''<tbody x-data="{ expanded: false, selectAll: false, selected: [] }" class="divide-y divide-slate-50 border-t border-slate-50">
                    <tr class="hover:bg-slate-50/30 transition-colors group cursor-pointer" data-id="{{ $template->id }}" @click="if(!$event.target.closest('button') && !$event.target.closest('a') && !$event.target.closest('input') && !$event.target.closest('form')) expanded = !expanded">'''

content = content.replace(row_start, new_row_start)

# Now find the end of the </tr> inside the loop
# We'll split by @empty and process the first part

parts = content.split('@empty')

loop_body = parts[0]
empty_body = '@empty' + parts[1]

expanded_html = '''
                    <tr x-cloak x-show="expanded" x-transition class="bg-slate-50/30">
                        <td colspan="5" class="p-6">
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 cursor-default" @click.stop>
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900">Active Students at this Stage</h3>
                                        <p class="text-xs text-slate-500">{{ $template->studentMilestones->count() }} students currently processing this milestone.</p>
                                    </div>
                                    <div class="flex gap-3">
                                        <a href="{{ route('admin.milestone-templates.export-students', $template->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                            Export CSV
                                        </a>
                                    </div>
                                </div>

                                @if($template->studentMilestones->count() > 0)
                                <div class="mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
                                    <form action="{{ route('admin.milestone-templates.schedule') }}" method="POST" class="flex gap-4 items-end">
                                        @csrf
                                        <template x-for="id in selected">
                                            <input type="hidden" name="milestone_ids[]" :value="id">
                                        </template>
                                        
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1">Start Date</label>
                                            <input type="date" name="start_date" required class="px-3 py-1.5 rounded-lg border border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1">Students / Day</label>
                                            <input type="number" name="students_per_day" value="5" min="1" required class="w-24 px-3 py-1.5 rounded-lg border border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                        </div>
                                        <div>
                                            <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-xs font-bold transition-colors" :disabled="selected.length === 0" :class="{'opacity-50 cursor-not-allowed': selected.length === 0}">
                                                Generate Schedule
                                            </button>
                                        </div>
                                        <div class="ml-auto text-xs text-slate-500 self-center">
                                            Select students below to schedule.
                                        </div>
                                    </form>
                                </div>

                                <div class="overflow-x-auto border border-slate-100 rounded-xl">
                                    <table class="w-full text-left text-sm">
                                        <thead class="bg-slate-50 border-b border-slate-100">
                                            <tr>
                                                <th class="px-4 py-3"><input type="checkbox" x-model="selectAll" @change="selected = selectAll ? [{{ $template->studentMilestones->map(fn($m) => \"'\" . $m->id . \"'\")->implode(',') }}] : []" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500"></th>
                                                <th class="px-4 py-3 font-semibold text-slate-700">Student</th>
                                                <th class="px-4 py-3 font-semibold text-slate-700">Status</th>
                                                <th class="px-4 py-3 font-semibold text-slate-700">Date</th>
                                                @if($template->slug === 'seminar_as_a_course')
                                                <th class="px-4 py-3 font-semibold text-slate-700">Examiner</th>
                                                <th class="px-4 py-3 font-semibold text-slate-700">PPT</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($template->studentMilestones as $sm)
                                                @php
                                                    $event = current($sm->thesisProject->defenceEvents->where('type', $template->defence_type ?? 'seminar')->all());
                                                    $examiner = $event ? current($event->panelMembers->where('role', 'Examiner')->all()) : null;
                                                @endphp
                                                <tr class="hover:bg-slate-50/50 transition-colors">
                                                    <td class="px-4 py-3">
                                                        <input type="checkbox" :value="'{{ $sm->id }}'" x-model="selected" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="font-medium text-slate-900">{{ $sm->thesisProject->student->user->name ?? 'N/A' }}</div>
                                                        <div class="text-xs text-slate-500">{{ $sm->thesisProject->student->matric_number ?? 'N/A' }}</div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-medium">{{ ucfirst(str_replace('_', ' ', $sm->status)) }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 text-slate-600 text-xs">
                                                        {{ $sm->defence_date ? \Carbon\Carbon::parse($sm->defence_date)->format('M d, Y') : 'Not scheduled' }}
                                                    </td>
                                                    @if($template->slug === 'seminar_as_a_course')
                                                    <td class="px-4 py-3">
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
                                                    </td>
                                                    <td class="px-4 py-3 text-xs">
                                                        @if($sm->submissions->count() > 0)
                                                            <a href="{{ Storage::url($sm->submissions->first()->file_url) }}" target="_blank" class="text-indigo-600 hover:underline">Download</a>
                                                        @else
                                                            <span class="text-slate-400">Not uploaded</span>
                                                        @endif
                                                    </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="text-center py-8 text-slate-500 text-sm">
                                    No active students currently at this milestone.
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    </tbody>
'''

# Find the last </tr> before @empty
last_tr_index = loop_body.rfind('</tr>')
if last_tr_index != -1:
    loop_body = loop_body[:last_tr_index + 5] + expanded_html + loop_body[last_tr_index + 5:]

# Add tbody back around the @empty part
new_content = loop_body + '<tbody>' + empty_body + '</tbody>'

with open('/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/admin/milestone-templates/index.blade.php', 'w') as f:
    f.write(new_content)
