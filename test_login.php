<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$email = 'malhassan@noun.edu.ng';
$password = 'password123';

$credentials = ['email' => $email, 'password' => $password];

if (Auth::attempt($credentials)) {
    echo "Login successful!\n";
} else {
    echo "Login failed!\n";
}
