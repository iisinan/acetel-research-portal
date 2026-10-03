<?php
$content = file_get_contents('resources/views/milestones/partials/details.blade.php');

$search = "messageRecipient = '{{ addslashes(\$milestone->thesis->student->user->name) }}'";
$replace = "messageRecipient = {{ \\Illuminate\\Support\\Js::from(\$milestone->thesis->student->user->name) }}";

$content = str_replace($search, $replace, $content);

// Also replace in the coordinators/supervisors list
$search2 = "messageRecipient = '{{ addslashes(\$supervisor->user->name) }}'";
$replace2 = "messageRecipient = {{ \\Illuminate\\Support\\Js::from(\$supervisor->user->name) }}";
$content = str_replace($search2, $replace2, $content);

$search3 = "messageRecipient = '{{ addslashes(\$coordinator->user->name) }}'";
$replace3 = "messageRecipient = {{ \\Illuminate\\Support\\Js::from(\$coordinator->user->name) }}";
$content = str_replace($search3, $replace3, $content);

file_put_contents('resources/views/milestones/partials/details.blade.php', $content);
