<?php
$content = file_get_contents('resources/views/admin/milestone-templates/index.blade.php');

$search = "querySelector('input[type=\\'checkbox\\']')";
$replace = "querySelector('input[type=checkbox]')";

$content = str_replace($search, $replace, $content);
file_put_contents('resources/views/admin/milestone-templates/index.blade.php', $content);
