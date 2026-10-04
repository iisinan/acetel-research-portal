<?php
$content = file_get_contents("routes/admin.php");
$routes = <<<EOT

Route::get("seminars", [SeminarController::class, "index"])->name("seminars.index");
Route::post("seminars/schedule", [SeminarController::class, "schedule"])->name("seminars.schedule");
Route::post("seminars/{milestone}/assign-examiner", [SeminarController::class, "assignExaminer"])->name("seminars.assign-examiner");
Route::post("seminars/{event}/score", [SeminarController::class, "storeScore"])->name("seminars.score");
Route::get("seminars/attendance", [SeminarController::class, "downloadAttendance"])->name("seminars.attendance");

EOT;
file_put_contents("routes/admin.php", $content . "\n" . $routes);

