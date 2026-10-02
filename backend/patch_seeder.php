<?php
$content = file_get_contents('database/seeders/MilestoneTemplateSeeder.php');

$search = "            [
                'name' => 'Seminar course',";

$replace = "            [
                'name' => 'Seminar course',";

// Let's just use str_replace for each to make sure they explicitly nullify those fields
$content = str_replace(
    "'submission_type' => ['file'], // Document in PDF format",
    "'submission_type' => ['file'], // Document in PDF format\n                'submission_requires_approval' => false,\n                'submission_approver_roles' => null,",
    $content
);

file_put_contents('database/seeders/MilestoneTemplateSeeder.php', $content);
