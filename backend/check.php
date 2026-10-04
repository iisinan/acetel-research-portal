<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$t = \App\Models\MilestoneTemplate::where("slug", "seminar_as_a_course")->first();
echo "Type: " . ($t->defence_type ?? "NULL");

