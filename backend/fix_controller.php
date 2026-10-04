<?php
$content = file_get_contents("app/Http/Controllers/Admin/MilestoneTemplateController.php");

$old = "->whereIn('status', ['in_progress', 'submitted', 'revision_required', 'partially_approved'])
            ->with('thesis')";
            
$new = "->whereIn('status', ['in_progress', 'submitted', 'revision_required', 'partially_approved'])
            ->whereNotNull('defence_date')
            ->with('thesis')";

$content = str_replace($old, $new, $content);
file_put_contents("app/Http/Controllers/Admin/MilestoneTemplateController.php", $content);

