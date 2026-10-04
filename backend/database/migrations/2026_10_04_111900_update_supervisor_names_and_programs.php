<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Program;
use App\Models\SupervisorProfile;

return new class extends Migration
{
    public function up(): void
    {
        $jsonPath = base_path("parsed_supervisors.json");
        if (!file_exists($jsonPath)) return;
        
        $json = file_get_contents($jsonPath);
        $supervisors = json_decode($json, true);
        
        if (!$supervisors) return;

        $ai_programs = Program::where("name", "like", "%Artificial Intelligence%")
            ->orWhere("code", "AI")
            ->pluck("id")->toArray();

        $cs_programs = Program::where("name", "like", "%Cybersecurity%")
            ->orWhere("name", "like", "%Cyber Security%")
            ->orWhere("code", "CYBER")
            ->orWhere("code", "CS")
            ->pluck("id")->toArray();

        $mis_programs = Program::where("name", "like", "%Management Information System%")
            ->orWhere("name", "like", "%MIS%")
            ->orWhere("code", "MIS")
            ->pluck("id")->toArray();

        foreach ($supervisors as $sup) {
            $email = !empty($sup["email"]) ? trim(strtolower($sup["email"])) : null;
            $name = !empty($sup["name"]) ? trim($sup["name"]) : null;
            $programName = $sup["program"] ?? "";
            
            $query = User::query();
            if ($email && $email !== 'unnamed: 2') {
                $query->where("email", $email);
            } elseif ($name && $name !== 'Unnamed: 1') {
                $query->where("name", "like", "%{$name}%");
            } else {
                continue;
            }

            $user = $query->first();
            if (!$user && $name && $name !== 'Unnamed: 1') {
                $user = User::where("name", "like", "%{$name}%")->first();
            }

            if ($user) {
                $profile = SupervisorProfile::where("user_id", $user->id)->first();
                if ($profile) {
                    $suffix = "";
                    $pIds = [];
                    if (stripos($programName, "Artificial Intelligence") !== false) {
                        $suffix = " (AI)";
                        $pIds = $ai_programs;
                    } elseif (stripos($programName, "Cyber Security") !== false || stripos($programName, "Cybersecurity") !== false) {
                        $suffix = " (CS)";
                        $pIds = $cs_programs;
                    } elseif (stripos($programName, "Management Information System") !== false || stripos($programName, "MIS") !== false) {
                        $suffix = " (MIS)";
                        $pIds = array_unique(array_merge($mis_programs, $cs_programs));
                    }
                    
                    if ($suffix && !str_ends_with($user->name, $suffix)) {
                        $user->name = trim(str_replace([" (AI)", " (CS)", " (MIS)"], "", $user->name)) . $suffix;
                        $user->save();
                    }
                    
                    if (!empty($pIds)) {
                        $profile->programs()->syncWithoutDetaching($pIds);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Reverting the name change would require stripping the suffixes
        $users = User::role("Supervisor")->get();
        foreach ($users as $user) {
            $user->name = trim(str_replace([" (AI)", " (CS)", " (MIS)"], "", $user->name));
            $user->save();
        }
    }
};

