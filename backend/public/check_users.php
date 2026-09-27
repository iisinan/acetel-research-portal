<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Users count: " . \App\Models\User::count() . "<br>";
$users = \App\Models\User::all(['id', 'name', 'email']);
foreach($users as $user) {
    echo $user->email . "<br>";
}
