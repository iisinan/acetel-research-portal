<?php
$content = file_get_contents('app/Http/Controllers/Admin/MilestoneTemplateController.php');
$content = str_replace('student->matric_number', 'student->student_id_number', $content);
file_put_contents('app/Http/Controllers/Admin/MilestoneTemplateController.php', $content);
