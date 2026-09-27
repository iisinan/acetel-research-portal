import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/admin/milestone-templates/index.blade.php'
with open(path, 'r') as f:
    content = f.read()

old_php = """                                                @php
                                                    $event = current($sm->thesis->defenceEvents->where('type', $template->defence_type ?? 'seminar')->all());
                                                    $examiner = $event ? current($event->panelMembers->where('role', 'Examiner')->all()) : null;
                                                @endphp"""

new_php = """                                                @php
                                                    $event = current($sm->thesis->defenceEvents->where('type', $template->defence_type ?? 'seminar')->all());
                                                    $examiner = $event ? current($event->panelMembers->where('role', 'Examiner')->all()) : null;
                                                    $avgScore = null;
                                                    if ($event && $event->evaluations->count() > 0) {
                                                        $total = 0;
                                                        $count = 0;
                                                        foreach($event->evaluations as $eval) {
                                                            if (isset($eval->score['total'])) {
                                                                $total += $eval->score['total'];
                                                                $count++;
                                                            }
                                                        }
                                                        if ($count > 0) {
                                                            $avgScore = round($total / $count, 1);
                                                        }
                                                    }
                                                @endphp"""

content = content.replace(old_php, new_php)

old_th = """                                                @if($template->slug === 'seminar_as_a_course')
                                                <th class="px-4 py-3 font-semibold text-slate-700">Examiner</th>
                                                <th class="px-4 py-3 font-semibold text-slate-700">PPT</th>
                                                @endif"""
new_th = """                                                @if($template->slug === 'seminar_as_a_course')
                                                <th class="px-4 py-3 font-semibold text-slate-700">Examiner</th>
                                                <th class="px-4 py-3 font-semibold text-slate-700">PPT</th>
                                                <th class="px-4 py-3 font-semibold text-slate-700">Score</th>
                                                @endif"""
content = content.replace(old_th, new_th)

old_td = """                                                    <td class="px-4 py-3 text-xs">
                                                        @if($sm->submissions->count() > 0)
                                                            <a href="{{ Storage::url($sm->submissions->first()->file_url) }}" target="_blank" class="text-indigo-600 hover:underline">Download</a>
                                                        @else
                                                            <span class="text-slate-400">Not uploaded</span>
                                                        @endif
                                                    </td>
                                                    @endif"""

new_td = """                                                    <td class="px-4 py-3 text-xs">
                                                        @if($sm->submissions->count() > 0)
                                                            <a href="{{ Storage::url($sm->submissions->first()->file_url) }}" target="_blank" class="text-indigo-600 hover:underline">Download</a>
                                                        @else
                                                            <span class="text-slate-400">Not uploaded</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-xs font-bold">
                                                        @if($avgScore !== null)
                                                            <span class="text-emerald-600">{{ $avgScore }}</span>
                                                        @else
                                                            <span class="text-slate-400">-</span>
                                                        @endif
                                                    </td>
                                                    @endif"""
content = content.replace(old_td, new_td)

with open(path, 'w') as f:
    f.write(content)
