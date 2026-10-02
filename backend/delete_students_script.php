<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$students = User::role('Student')->get();
$count = $students->count();

foreach($students as $student) {
    $student->delete();
}

echo "Deleted $count students.\n";
