<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

App\Models\MilestoneTemplate::where('slug', 'progress_report_1')->update([
    'allow_defence_date' => true,
    'defence_type' => 'progress_report_1'
]);

App\Models\MilestoneTemplate::where('slug', 'progress_report_2')->update([
    'allow_defence_date' => true,
    'defence_type' => 'progress_report_2'
]);
echo "Updated.\n";
