<?php
$content = file_get_contents('app/Http/Controllers/Auth/RegisteredUserController.php');

// Internal Examiner
$content = preg_replace(
    '/\$ieUser = User::firstOrCreate\(\s*\[\'email\' => \$ieEmail\],\s*\[\s*\'name\' => \$ieName,\s*\'password\' => Hash::make\(Str::random\(12\)\),\s*\]\s*\);/',
    "\$plainIePassword = \Illuminate\Support\Str::random(12);\n                \$ieUser = User::firstOrCreate(\n                    ['email' => \$ieEmail],\n                    [\n                        'name' => \$ieName,\n                        'password' => Hash::make(\$plainIePassword),\n                    ]\n                );\n                if (\$ieUser->wasRecentlyCreated && !str_contains(\$ieEmail, '@examiner.acetel.edu.ng')) {\n                    \Illuminate\Support\Facades\Mail::to(\$ieEmail)->send(new \App\Mail\WelcomeUser(\$ieUser, \$plainIePassword));\n                }",
    $content
);

// External Examiner
$content = preg_replace(
    '/\$eeUser = User::firstOrCreate\(\s*\[\'email\' => \$eeEmail\],\s*\[\s*\'name\' => \$eeName,\s*\'password\' => Hash::make\(Str::random\(12\)\),\s*\]\s*\);/',
    "\$plainEePassword = \Illuminate\Support\Str::random(12);\n                \$eeUser = User::firstOrCreate(\n                    ['email' => \$eeEmail],\n                    [\n                        'name' => \$eeName,\n                        'password' => Hash::make(\$plainEePassword),\n                    ]\n                );\n                if (\$eeUser->wasRecentlyCreated && !str_contains(\$eeEmail, '@external.acetel.edu.ng')) {\n                    \Illuminate\Support\Facades\Mail::to(\$eeEmail)->send(new \App\Mail\WelcomeUser(\$eeUser, \$plainEePassword));\n                }",
    $content
);

// Supervisor
$content = preg_replace(
    '/\$supUser = User::firstOrCreate\(\s*\[\'email\' => \$supEmail\],\s*\[\s*\'name\' => \$newSup\[\'name\'\],\s*\'password\' => Hash::make\(Str::random\(12\)\), \/\/ random password\s*\]\s*\);/',
    "\$plainSupPassword = \Illuminate\Support\Str::random(12);\n                    \$supUser = User::firstOrCreate(\n                        ['email' => \$supEmail],\n                        [\n                            'name' => \$newSup['name'],\n                            'password' => Hash::make(\$plainSupPassword),\n                        ]\n                    );\n                    if (\$supUser->wasRecentlyCreated && !str_contains(\$supEmail, '@supervisor.acetel.edu.ng')) {\n                        \Illuminate\Support\Facades\Mail::to(\$supEmail)->send(new \App\Mail\WelcomeUser(\$supUser, \$plainSupPassword));\n                    }",
    $content
);

file_put_contents('app/Http/Controllers/Auth/RegisteredUserController.php', $content);
echo "Patched emails!\n";
