<?php
$content = file_get_contents('routes/web.php');
$content .= "\n\nRoute::get('/reset-muktar', function () {\n    \$u = App\Models\User::where('email', 'malhassan@noun.edu.ng')->first();\n    if (\$u) {\n        \$u->password = Hash::make('password123');\n        \$u->save();\n        return 'Success! Password for Dr. Alhassan has been reset to: password123';\n    }\n    return 'User not found in this database.';\n});\n";
file_put_contents('routes/web.php', $content);
echo "Done\n";
