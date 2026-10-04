<?php
$content = file_get_contents("app/Http/Controllers/Supervisor/SeminarExaminationController.php");
$content = str_replace(
    "\$event = DefenceEvent::findOrFail(\$eventId);",
    "\$event = DefenceEvent::findOrFail(\$eventId);\n\n        if (now()->format('Y-m-d') !== \$event->schedule_start->format('Y-m-d')) {\n            return back()->with('error', 'You can only grade the student on the exact day of their presentation.');\n        }",
    $content
);
file_put_contents("app/Http/Controllers/Supervisor/SeminarExaminationController.php", $content);

