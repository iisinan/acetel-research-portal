<?php
$content = file_get_contents('app/Http/Controllers/Auth/RegisteredUserController.php');

$old = <<<EOD
            'place_of_work' => 'nullable|string|max:255',
            'supervisor_ids' => 'nullable|array',
EOD;

$new = <<<EOD
            'place_of_work' => 'nullable|string|max:255',
            'phone_number' => 'required|string|max:20',
            'gender' => 'required|string|in:Male,Female',
            'nationality' => 'required|string|max:100',
            'supervisor_ids' => 'nullable|array',
EOD;

$content = str_replace($old, $new, $content);
file_put_contents('app/Http/Controllers/Auth/RegisteredUserController.php', $content);
echo "Patched demographic validation rules!\n";
