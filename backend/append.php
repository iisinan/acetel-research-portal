<?php
$content = file_get_contents("resources/views/admin/seminars/index.blade.php");
$content = str_replace(
    "@php\n                                    \$event = \$milestone->thesis->defenceEvents->first();\n                                    \$examiner = \$event ? \$event->panelMembers->where('role', 'Examiner')->first() : null;\n                                @endphp",
    "@php\n                                    \$event = \$milestone->thesis->defenceEvents->first();\n                                    \$examiners = \$event ? \$event->panelMembers->where('role', 'Examiner') : collect();\n                                    \$examinerIds = \$examiners->pluck('user_id')->toArray();\n                                @endphp",
    $content
);
$content = str_replace(
    "<td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-500\">\n                                        @if(\$examiner)\n                                            {{ \$examiner->user->name }}\n                                        @else\n                                            <span class=\"text-red-500\">Unassigned</span>\n                                        @endif\n                                    </td>",
    "<td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-500\">\n                                        @if(\$examiners->count() > 0)\n                                            @foreach(\$examiners as \$ex)\n                                                <div class=\"text-xs\">{{ \$ex->user->name }}</div>\n                                            @endforeach\n                                        @else\n                                            <span class=\"text-red-500\">Unassigned</span>\n                                        @endif\n                                    </td>",
    $content
);
$content = str_replace(
    "<select name=\"supervisor_profile_id\" required class=\"block w-full pl-3 pr-10 py-1 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md\">\n                                                <option value=\"\">Select Examiner</option>\n                                                @foreach(\$supervisors as \$sup)\n                                                    <option value=\"{{ \$sup->id }}\" {{ \$examiner && \$examiner->user_id == \$sup->user_id ? 'selected' : ' }}>\n                                                        {{ \$sup->user->name }}\n                                                    </option>\n                                                @endforeach\n                                            </select>",
    "<select name=\"supervisor_profile_ids[]\" multiple required class=\"block w-full py-1 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md\" style=\"height: 80px;\">\n                                                @foreach(\$supervisors as \$sup)\n                                                    <option value=\"{{ \$sup->id }}\" {{ in_array(\$sup->user_id, \$examinerIds) ? 'selected' : ' }}>\n                                                        {{ \$sup->user->name }}\n                                                    </option>\n                                                @endforeach\n                                            </select>",
    $content
);
file_put_contents("resources/views/admin/seminars/index.blade.php", $content);

