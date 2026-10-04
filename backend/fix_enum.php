<?php
$files = [
    "app/Http/Controllers/Admin/MilestoneTemplateController.php",
    "resources/views/presentations/show.blade.php"
];
foreach($files as $f) {
    $c = file_get_contents($f);
    $c = str_replace("\$template->defence_type ?? 'seminar'", "\$template->defence_type ?? 'first_seminar'", $c);
    $c = str_replace("\$template->defence_type ?? 'Seminar'", "\$template->defence_type ?? 'first_seminar'", $c);
    $c = str_replace("\$template->defence_type ?? \"seminar\"", "\$template->defence_type ?? \"first_seminar\"", $c);
    file_put_contents($f, $c);
}
echo "Done";

