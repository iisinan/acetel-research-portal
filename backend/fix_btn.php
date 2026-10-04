<?php
$content = file_get_contents("resources/views/presentations/show.blade.php");

$old = "@if(\$tableCanEval && \$isToday)";
$new = "@if(\$tableCanEval)
                                                @if(\$tableEval && \$tableEval->submitted_at)
                                                    <a href=\"{{ route('evaluations.show', \$tableEval->id) }}\" class=\"inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition-colors\">
                                                        <span>Evaluated</span>
                                                    </a>
                                                @else
                                                    @if(\$isToday)
                                                        <a href=\"{{ route('evaluations.create', \$tableDefEvent->id) }}\" class=\"inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-indigo-600 hover:bg-indigo-700 text-white transition-colors shadow-sm\">
                                                            <span>Score</span>
                                                        </a>
                                                    @else
                                                        <span title=\"Scoring opens on the presentation date\" class=\"inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 text-slate-400 cursor-not-allowed shadow-sm\">
                                                            <span>Score</span>
                                                        </span>
                                                    @endif
                                                @endif
                                            @elseif(false)";

$content = str_replace($old, $new, $content);
$content = str_replace("Meeting Link</th>", "Actions</th>", $content);

file_put_contents("resources/views/presentations/show.blade.php", $content);

