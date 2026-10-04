<?php
$content = file_get_contents("routes/web.php");
if (!str_contains($content, "fix-supervisors-now")) {
    $code = <<<EOT

Route::get("/fix-supervisors-now", function () {
    $ai_programs = \App\Models\Program::where("name", "like", "%Artificial Intelligence%")->pluck("id")->toArray();
    $cs_programs = \App\Models\Program::where("name", "like", "%Cybersecurity%")->orWhere("name", "like", "%Cyber Security%")->pluck("id")->toArray();
    $mis_programs = \App\Models\Program::where("name", "like", "%Management Information System%")->pluck("id")->toArray();

    $json = file_get_contents(base_path("parsed_supervisors.json"));
    $supervisors = json_decode($json, true);
    
    $results = [];
    foreach ($supervisors as $sup) {
        $email = trim(strtolower($sup["email"]));
        $programName = $sup["program"] ?? "";
        
        $user = \App\Models\User::where("email", $email)->first();
        if (\$user) {
            $profile = \App\Models\SupervisorProfile::where("user_id", \$user->id)->first();
            if (\$profile) {
                $suffix = "";
                $pIds = [];
                if (stripos(\$programName, "Artificial Intelligence") !== false) {
                    $suffix = " (AI)";
                    $pIds = $ai_programs;
                } elseif (stripos(\$programName, "Cyber Security") !== false || stripos(\$programName, "Cybersecurity") !== false) {
                    $suffix = " (CS)";
                    $pIds = $cs_programs;
                } elseif (stripos(\$programName, "Management Information System") !== false) {
                    $suffix = " (MIS)";
                    $pIds = $mis_programs;
                }
                
                if (\$suffix && !str_ends_with(\$user->name, \$suffix)) {
                    \$user->name = trim(\$user->name) . \$suffix;
                    \$user->save();
                }
                
                if (!empty(\$pIds)) {
                    \$profile->programs()->syncWithoutDetaching(\$pIds);
                }
                
                \$results[] = "Updated: {\$user->name} mapped to " . count(\$pIds) . " programs.";
            }
        }
    }
    return \$results;
});
EOT;
    file_put_contents("routes/web.php", $code, FILE_APPEND);
}

