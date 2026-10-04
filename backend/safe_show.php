<?php
$content = file_get_contents("resources/views/evaluations/show.blade.php");

$content = str_replace('student->program->name', 'student->program->name ?? \'N/A\'', $content);
$content = str_replace('student->user->name', 'student->user->name ?? \'Unknown\'', $content);
$content = str_replace('evaluator->name', 'evaluator->name ?? \'Unknown\'', $content);
$content = str_replace('submitted_at->format', 'submitted_at?->format', $content);
$content = str_replace('/40</span>', '/100</span>', $content);

file_put_contents("resources/views/evaluations/show.blade.php", $content);
