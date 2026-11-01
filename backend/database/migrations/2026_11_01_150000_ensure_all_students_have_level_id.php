<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Level;
use App\Models\StudentProfile;
use App\Models\Program;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $levels = Level::all();
        if ($levels->isEmpty()) {
            return;
        }

        $students = StudentProfile::whereNull('level_id')->with('program')->get();
        foreach ($students as $student) {
            $pName = strtolower(($student->program?->code ?? '') . ' ' . ($student->program?->name ?? '') . ' ' . ($student->program?->degree_type ?? ''));
            $isPhd = str_contains($pName, 'phd') || str_contains($pName, 'ph.d') || str_contains($pName, 'doctor');
            $isPgd = str_contains($pName, 'pgd') || str_contains($pName, 'diploma');

            $matched = $levels->first(function ($lvl) use ($isPhd, $isPgd) {
                $lName = strtolower($lvl->name);
                if ($isPhd) {
                    return str_contains($lName, 'phd') || str_contains($lName, 'doctor');
                }
                if ($isPgd) {
                    return str_contains($lName, 'pgd') || str_contains($lName, 'diploma');
                }
                return str_contains($lName, 'msc') || str_contains($lName, 'master');
            });

            $levelId = $matched?->id ?? $levels->first()?->id;
            if ($levelId) {
                DB::table('student_profiles')
                    ->where('id', $student->id)
                    ->update(['level_id' => $levelId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for data healing
    }
};
