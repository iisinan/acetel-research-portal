<?php
$content = file_get_contents("app/Http/Controllers/Admin/SeminarController.php");
$content = str_replace(
    "\"thesis.defenceEvents\" => function(\$q) {\n                \$q->where(\"type\", \"seminar\")->with(\"panelMembers.user\");\n            }])",
    "\"thesis.defenceEvents\" => function(\$q) {\n                \$q->where(\"type\", \"seminar\")->with([\"panelMembers.user\", \"evaluations\"]);\n            }])",
    $content
);
file_put_contents("app/Http/Controllers/Admin/SeminarController.php", $content);

