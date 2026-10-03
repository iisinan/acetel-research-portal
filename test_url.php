<?php
require __DIR__.'/backend/vendor/autoload.php';
$app = require_once __DIR__.'/backend/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sub = \App\Models\Submission::whereNotNull('file_url')->latest()->first();
echo $sub->file_url . "\n";
echo \Illuminate\Support\Facades\Storage::url($sub->file_url) . "\n";
