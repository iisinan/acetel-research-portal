<?php
$content = file_get_contents('routes/admin.php');

$search = "Route::post('/students/{student}/demote-milestone', [StudentController::class, 'demoteMilestone'])->name('students.demote-milestone');";
$replace = "Route::post('/students/{student}/set-milestone', [StudentController::class, 'setMilestone'])->name('students.set-milestone');\n" . $search;

$content = str_replace($search, $replace, $content);
file_put_contents('routes/admin.php', $content);
