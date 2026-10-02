<?php
$content = file_get_contents('app/Http/Controllers/UserController.php');

$old = "\$users = User::with(['roles', 'supervisorProfile.assignments' => function (\$q) {
            \$q->where('status', 'active');
        }])->latest()->paginate(20);";
$new = "\$users = User::with(['roles', 'supervisorProfile.assignments' => function (\$q) {
            \$q->where('status', 'active')->with('thesis.student.user');
        }])->latest()->paginate(20);";

$content = str_replace($old, $new, $content);
file_put_contents('app/Http/Controllers/UserController.php', $content);
