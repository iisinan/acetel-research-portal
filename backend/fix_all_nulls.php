<?php
$content = file_get_contents("resources/views/admin/dashboard.blade.php");

$content = str_replace("\$login->user->name", "\$login->user?->name", $content);
$content = str_replace("\$log->user->name", "\$log->user?->name", $content);
$content = str_replace("\$alert->thesis->student->user->name", "\$alert->thesis?->student?->user?->name", $content);
$content = str_replace("\$login->logout_at", "\$login?->logout_at", $content);
$content = str_replace("\$login->login_at", "\$login?->login_at", $content);
$content = str_replace("\$login->ip_address", "\$login?->ip_address", $content);
$content = str_replace("\$login->device_type", "\$login?->device_type", $content);
$content = str_replace("\$login->browser", "\$login?->browser", $content);
$content = str_replace("\$login->platform", "\$login?->platform", $content);
$content = str_replace("\$log->action", "\$log?->action", $content);
$content = str_replace("\$log->ip_address", "\$log?->ip_address", $content);
$content = str_replace("\$log->created_at", "\$log?->created_at", $content);
$content = str_replace("\$user->name", "\$user?->name", $content);
$content = str_replace("\$user->last_login_at", "\$user?->last_login_at", $content);
$content = str_replace("\$user->total_logins", "\$user?->total_logins", $content);
$content = str_replace("\$user->last_session_browser", "\$user?->last_session_browser", $content);
$content = str_replace("\$user->getRoleNames", "\$user?->getRoleNames", $content);

file_put_contents("resources/views/admin/dashboard.blade.php", $content);

