<?php
$content = file_get_contents('resources/views/admin/milestone-templates/index.blade.php');

$search = <<<EOT
                                                            <td class="px-4 py-3">
                                                                <div class="font-medium text-slate-900">{{ \$studentName }}</div>
                                                                <div class="text-xs text-slate-500">{{ \$matricNo }}</div>
                                                            </td>
EOT;

$replace = <<<EOT
                                                            <td class="px-4 py-3">
                                                                <a href="{{ route('admin.students.show', \$sm->thesis->student->id) }}" class="font-medium text-brand-600 hover:text-brand-700 hover:underline block">
                                                                    {{ \$studentName }}
                                                                </a>
                                                                <div class="text-xs text-slate-500">{{ \$matricNo }}</div>
                                                            </td>
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents('resources/views/admin/milestone-templates/index.blade.php', $content);
