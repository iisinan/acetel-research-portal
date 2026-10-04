<?php
$content = file_get_contents("routes/admin.php");
$old = "Route::get('milestone-templates/{template}/export-students', [MilestoneTemplateController::class, 'exportStudents'])->name('milestone-templates.export-students');";
$new = $old . "\n    Route::get('milestone-templates/{template}/export-scheduled-scores', [MilestoneTemplateController::class, 'exportScheduledScores'])->name('milestone-templates.export-scheduled-scores');";
$content = str_replace($old, $new, $content);
file_put_contents("routes/admin.php", $content);

$content = file_get_contents("routes/coordinator.php");
$old = "Route::get('milestone-templates/{template}/export-students', [\App\Http\Controllers\Admin\MilestoneTemplateController::class, 'exportStudents'])->name('milestone-templates.export-students');";
$new = $old . "\nRoute::get('milestone-templates/{template}/export-scheduled-scores', [\App\Http\Controllers\Admin\MilestoneTemplateController::class, 'exportScheduledScores'])->name('milestone-templates.export-scheduled-scores');";
$content = str_replace($old, $new, $content);
file_put_contents("routes/coordinator.php", $content);

