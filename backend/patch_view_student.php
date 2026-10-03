<?php
$content = file_get_contents('resources/views/admin/students/show.blade.php');

$search = "                        <form action=\"{{ route('admin.students.demote-milestone', \$student) }}\" method=\"POST\" onsubmit=\"return confirm('Are you sure you want to demote this student to the previous milestone?');\">";
$replace = <<<EOT
                        <form action="{{ route('admin.students.set-milestone', \$student) }}" method="POST" class="flex items-center gap-2 mr-2">
                            @csrf
                            <select name="milestone_id" class="text-[10px] font-black uppercase tracking-widest text-slate-600 bg-slate-100 border-none rounded-lg focus:ring-brand-500 cursor-pointer" onchange="if(confirm('Are you sure you want to set the student to this milestone?')) this.form.submit();">
                                <option value="" disabled selected>Jump to Milestone</option>
                                @foreach(\$student->thesis->milestones->sortBy('template.order') as \$m)
                                    <option value="{{ \$m->id }}">{{ \$m->template->order }}. {{ \$m->template->name }}</option>
                                @endforeach
                            </select>
                        </form>

                        <div class="w-px h-4 bg-slate-200"></div>

                        <form action="{{ route('admin.students.demote-milestone', \$student) }}" method="POST" onsubmit="return confirm('Are you sure you want to demote this student to the previous milestone?');">
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents('resources/views/admin/students/show.blade.php', $content);
