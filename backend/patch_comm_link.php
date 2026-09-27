<?php
$content = file_get_contents('resources/views/components/comm-link.blade.php');

$oldPhoto = '<input type="file" name="file" x-ref="fileInput" class="hidden" @change="sendMessage($event.target.closest(\'form\'))">';
$newPhoto = '<input type="file" name="file" x-ref="fileInput" accept=".pdf" class="hidden" @change="sendMessage($event.target.closest(\'form\'))">';
$content = str_replace($oldPhoto, $newPhoto, $content);

$oldDoc = '<input type="file" name="file" class="hidden" @change="sendMessage($event.target.closest(\'form\'))">';
$newDoc = '<input type="file" name="file" accept=".pdf" class="hidden" @change="sendMessage($event.target.closest(\'form\'))">';
$content = str_replace($oldDoc, $newDoc, $content);

file_put_contents('resources/views/components/comm-link.blade.php', $content);
echo "Patched comm-link!\n";
