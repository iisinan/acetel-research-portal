<?php
require __DIR__.'/backend/vendor/autoload.php';
$app = require_once __DIR__.'/backend/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sub = \App\Models\Submission::latest()->first();
echo "File URL in DB: " . $sub->file_url . "\n";
echo "Generated URL: " . \Illuminate\Support\Facades\Storage::url($sub->file_url) . "\n";
