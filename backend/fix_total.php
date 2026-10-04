<?php
$content = file_get_contents("resources/views/evaluations/show.blade.php");
$content = str_replace('$total = array_sum($scores);', '$total = $scores[\'total\'] ?? (array_sum($scores) > 0 ? array_sum($scores) / 2 : 0);', $content);
file_put_contents("resources/views/evaluations/show.blade.php", $content);

$content = file_get_contents("resources/views/pdf/evaluation.blade.php");
$content = str_replace('$total = array_sum($scores);', '$total = $scores[\'total\'] ?? (array_sum($scores) > 0 ? array_sum($scores) / 2 : 0);', $content);
file_put_contents("resources/views/pdf/evaluation.blade.php", $content);
