<?php
$content = file_get_contents('resources/views/milestones/partials/details.blade.php');

$search1 = '@click="showMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($milestone->thesis->student->user->name) }}"';
$replace1 = '@click.prevent.stop="showMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($milestone->thesis->student->user->name) }}"';

$search2 = '@click="showMessageModal = true; messageRecipient = \'{{ addslashes($milestone->thesis->internalExaminer->user->name) }}\'"';
$replace2 = '@click.prevent.stop="showMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($milestone->thesis->internalExaminer->user->name) }}"';

$search3 = '@click="showMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($supervisor->user->name) }}"';
$replace3 = '@click.prevent.stop="showMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($supervisor->user->name) }}"';

$search4 = '@click="showMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($coordinator->user->name) }}"';
$replace4 = '@click.prevent.stop="showMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($coordinator->user->name) }}"';

$content = str_replace($search1, $replace1, $content);
$content = str_replace($search2, $replace2, $content);
$content = str_replace($search3, $replace3, $content);
$content = str_replace($search4, $replace4, $content);

file_put_contents('resources/views/milestones/partials/details.blade.php', $content);
