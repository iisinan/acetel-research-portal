<?php
$logPath = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logPath)) {
    header('Content-Type: text/plain');
    echo file_get_contents($logPath);
} else {
    echo 'No logs found.';
}
