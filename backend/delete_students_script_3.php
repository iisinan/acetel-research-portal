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
    DB::transaction(function() use ($student) {
        DB::table('submissions')->where('submitted_by', $student->id)->delete();
        DB::table('inbox_messages')->where('sender_id', $student->id)->orWhere('recipient_id', $student->id)->delete();
        DB::table('messages')->where('sender_id', $student->id)->orWhere('recipient_id', $student->id)->delete(); // Try recipient_id instead of receiver_id, or just wrap in try catch
    });
}
