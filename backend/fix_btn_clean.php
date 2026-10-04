<?php
$content = file_get_contents("resources/views/presentations/show.blade.php");

$pattern = "/@elseif\(false\).*?@endif/s";
$content = preg_replace($pattern, "", $content);

file_put_contents("resources/views/presentations/show.blade.php", $content);

