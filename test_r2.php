<?php
require __DIR__.'/backend/vendor/autoload.php';
$app = require_once __DIR__.'/backend/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

putenv('CLOUDFLARE_R2_ACCESS_KEY_ID=8a2e3384ae960fb16ce6e3612e950b28');
putenv('CLOUDFLARE_R2_SECRET_ACCESS_KEY=7f2f7904c9de1b784f0fc8f65f9304f0a569fa1e8a720faf119b60ebdaa76c83');
putenv('CLOUDFLARE_R2_ENDPOINT=https://cb874f85a74d13d614e1d3d7c6891a1e.r2.cloudflarestorage.com');
putenv('CLOUDFLARE_R2_BUCKET=thesis-monitoring');
putenv('CLOUDFLARE_R2_URL=https://pub-91f1ade6755041368aba8ffe14e95abc.r2.dev');
putenv('FILESYSTEM_DISK=r2');

// Reload config since we just changed env
config(['filesystems.disks.r2.key' => env('CLOUDFLARE_R2_ACCESS_KEY_ID')]);
config(['filesystems.disks.r2.secret' => env('CLOUDFLARE_R2_SECRET_ACCESS_KEY')]);
config(['filesystems.disks.r2.endpoint' => env('CLOUDFLARE_R2_ENDPOINT')]);
config(['filesystems.disks.r2.bucket' => env('CLOUDFLARE_R2_BUCKET')]);
config(['filesystems.disks.r2.url' => env('CLOUDFLARE_R2_URL')]);
config(['filesystems.default' => 'r2']);

try {
    \Illuminate\Support\Facades\Storage::disk('r2')->put('test-upload.txt', 'Hello from R2 integration test!');
    $url = \Illuminate\Support\Facades\Storage::disk('r2')->url('test-upload.txt');
    echo "SUCCESS!\n";
    echo "File URL: " . $url . "\n";
    
    // Check if public URL is accessible
    $content = file_get_contents($url);
    if ($content === 'Hello from R2 integration test!') {
        echo "Public Access: VERIFIED!\n";
    } else {
        echo "Public Access: FAILED!\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
