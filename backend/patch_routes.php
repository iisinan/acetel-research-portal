<?php
$content = file_get_contents('routes/web.php');

$search = "Route::get('/supervisor/candidates', [App\Http\Controllers\Supervisor\StudentController::class, 'index'])->name('supervisor.students.index');";

$replace = <<<EOT
Route::get('/supervisor/candidates', [App\Http\Controllers\Supervisor\StudentController::class, 'index'])->name('supervisor.students.index');
        
        // Allow jumping milestones globally
        Route::post('/students/{student}/set-milestone-global', [\App\Http\Controllers\Admin\StudentController::class, 'setMilestone'])->name('students.set_milestone_global');
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents('routes/web.php', $content);
