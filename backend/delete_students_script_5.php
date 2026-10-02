<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$students = User::role('Student')->get();
$count = $students->count();

echo "Deleting $count students...\n";

foreach($students as $student) {
    try {
        DB::statement('SET CONSTRAINTS ALL DEFERRED;');
        DB::table('users')->where('id', $student->id)->delete();
        DB::statement('SET CONSTRAINTS ALL IMMEDIATE;');
        echo "Deleted student: {$student->id}\n";
    } catch (\Exception $e) {
        echo "Failed to delete {$student->id}: " . $e->getMessage() . "\n";
    }
}
