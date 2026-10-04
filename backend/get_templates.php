<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$templates = \App\Models\MilestoneTemplate::all();
foreach($templates as $t) {
    echo $t->name . " -> type: " . $t->defence_type . "\n";
}

