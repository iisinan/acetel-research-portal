<?php
$content = file_get_contents("resources/views/admin/seminars/index.blade.php");

$header = <<<EOT
<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PPT</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Grade</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
EOT;
$content = str_replace(
    "<th scope=\"col\" class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\">PPT</th>\n                                <th scope=\"col\" class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\">Actions</th>",
    $header,
    $content
);

$body = <<<EOT
@if(\$milestone->submissions->count() > 0)
                                            <a href="{{ Storage::url(\$milestone->submissions->first()->file_url) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">Download</a>
                                        @else
                                            <span class="text-gray-400">Not uploaded</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @php
                                            \$evaluations = \$event ? \$event->evaluations : collect();
                                            \$avg = \$evaluations->count() > 0 ? \$evaluations->avg(function(\$e) { return \$e->score["total"] ?? 0; }) : null;
                                        @endphp
                                        @if(\$avg !== null)
                                            <span class="font-bold text-green-600">{{ number_format(\$avg, 1) }} / 100</span>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex flex-col gap-2">
                                            <form action="{{ route('seminars.assign-examiner', \$milestone->id) }}" method="POST" class="flex items-center gap-2">
                                                @csrf
EOT;
$content = str_replace(
    "@if(\$milestone->submissions->count() > 0)\n                                            <a href=\"{{ Storage::url(\$milestone->submissions->first()->file_url) }}\" target=\"_blank\" class=\"text-indigo-600 hover:text-indigo-900\">Download</a>\n                                        @else\n                                            <span class=\"text-gray-400\">Not uploaded</span>\n                                        @endif\n                                    </td>\n                                    <td class=\"px-6 py-4 whitespace-nowrap text-right text-sm font-medium\">\n                                        <form action=\"{{ route('seminars.assign-examiner', \$milestone->id) }}\" method=\"POST\" class=\"flex items-center gap-2\">\n                                            @csrf",
    $body,
    $content
);

$gradeForm = <<<EOT
<button type="submit" class="text-white bg-green-600 hover:bg-green-700 px-3 py-1 rounded text-xs font-bold">Assign</button>
                                            </form>
                                            @if(\$event)
                                                <form action="{{ route('seminars.score', \$event->id) }}" method="POST" class="flex items-center gap-2">
                                                    @csrf
                                                    @php
                                                        \$myEval = \$event->evaluations->where('evaluator_id', auth()->id())->first();
                                                    @endphp
                                                    <input type="number" name="score" value="{{ \$myEval ? (\$myEval->score['total'] ?? '') : '' }}" min="0" max="100" placeholder="Grade (0-100)" required class="block w-24 py-1 text-sm border-gray-300 rounded-md">
                                                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-xs font-bold">{{ \$myEval ? 'Update Grade' : 'Grade' }}</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
EOT;
$content = str_replace(
    "<button type=\"submit\" class=\"text-white bg-green-600 hover:bg-green-700 px-3 py-1 rounded text-xs font-bold\">Assign</button>\n                                        </form>\n                                    </td>",
    $gradeForm,
    $content
);

$attendanceBtn = <<<EOT
<h3 class="text-lg font-bold text-gray-900 mb-4">Bulk Schedule Seminar Presentations</h3>
                <div class="mb-4">
                    <a href="{{ route('seminars.attendance') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                        Download Examiner Attendance
                    </a>
                </div>
                <form action="{{ route('seminars.schedule') }}" method="POST" class="flex gap-4 items-end">
EOT;
$content = str_replace(
    "<h3 class=\"text-lg font-bold text-gray-900 mb-4\">Bulk Schedule Seminar Presentations</h3>\n                <form action=\"{{ route('seminars.schedule') }}\" method=\"POST\" class=\"flex gap-4 items-end\">",
    $attendanceBtn,
    $content
);

file_put_contents("resources/views/admin/seminars/index.blade.php", $content);

