<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::whereHas('roles', function($q) { $q->where('name', 'Supervisor'); })->get();
$invalidCount = 0;
foreach($users as $user) {
    if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
        echo "INVALID FORMAT: " . $user->name . " -> " . $user->email . "\n";
        $invalidCount++;
        
        // Let's try fixing it if it's obvious
        $fixed = trim($user->email, " \t\n\r\0\x0B);");
        
        if (filter_var($fixed, FILTER_VALIDATE_EMAIL)) {
            echo "  FIXED TO: " . $fixed . "\n";
            $user->email = $fixed;
            $user->save();
        } else {
            echo "  UNABLE TO AUTO-FIX! Deleting supervisor...\n";
            $user->delete();
        }
    } else if (str_ends_with($user->email, '.nd')) {
        echo "TYPO IN DOMAIN: " . $user->name . " -> " . $user->email . "\n";
        $fixed = str_replace('.nd', '.ng', $user->email);
        echo "  FIXED TO: " . $fixed . "\n";
        $user->email = $fixed;
        $user->save();
        $invalidCount++;
    }
}
echo "Total invalid/fixed: " . $invalidCount . "\n";
