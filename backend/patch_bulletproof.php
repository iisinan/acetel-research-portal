<?php
$content = file_get_contents('resources/views/milestones/partials/details.blade.php');

// Rename variable
$content = str_replace('showMessageModal', 'showStudentMessageModal', $content);

// Add timeout to buttons
$search1 = '@click.prevent.stop="showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($milestone->thesis->student->user->name) }}"';
$replace1 = '@click.prevent.stop="setTimeout(() => { showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($milestone->thesis->student->user->name) }}; }, 50)"';
$content = str_replace($search1, $replace1, $content);

$search2 = '@click.prevent.stop="showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($milestone->thesis->internalExaminer->user->name) }}"';
$replace2 = '@click.prevent.stop="setTimeout(() => { showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($milestone->thesis->internalExaminer->user->name) }}; }, 50)"';
$content = str_replace($search2, $replace2, $content);

$search3 = '@click.prevent.stop="showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($supervisor->user->name) }}"';
$replace3 = '@click.prevent.stop="setTimeout(() => { showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($supervisor->user->name) }}; }, 50)"';
$content = str_replace($search3, $replace3, $content);

$search4 = '@click.prevent.stop="showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($coordinator->user->name) }}"';
$replace4 = '@click.prevent.stop="setTimeout(() => { showStudentMessageModal = true; messageRecipient = {{ \Illuminate\Support\Js::from($coordinator->user->name) }}; }, 50)"';
$content = str_replace($search4, $replace4, $content);

// Change the backdrop click handler to prevent double-click issues
$backdropSearch = <<<EOT
            <div x-show="showStudentMessageModal" 
                 @click="showStudentMessageModal = false"
EOT;
$backdropReplace = <<<EOT
            <div x-show="showStudentMessageModal" 
                 @click.self="setTimeout(() => { showStudentMessageModal = false }, 50)"
EOT;
$content = str_replace($backdropSearch, $backdropReplace, $content);

file_put_contents('resources/views/milestones/partials/details.blade.php', $content);
