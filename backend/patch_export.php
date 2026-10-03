<?php
$content = file_get_contents('app/Http/Controllers/Admin/MilestoneTemplateController.php');

$search = "->whereIn('status', ['in_progress', 'submitted', 'revision_required'])";
$replace = "->whereIn('status', ['in_progress', 'submitted', 'revision_required', 'partially_approved'])";

$content = str_replace($search, $replace, $content);
file_put_contents('app/Http/Controllers/Admin/MilestoneTemplateController.php', $content);
