<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$programs = \App\Models\Program::all(["id", "name"]);
foreach ($programs as $p) {
    echo $p->id . " | " . $p->name . "\n";
}
