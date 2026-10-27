<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Level;
use App\Models\StudentProfile;

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
            $pName = strtolower(($student->program?->name ?? '') . ' ' . ($student->program?->degree_type ?? ''));
            $isPhd = str_contains($pName, 'phd') || str_contains($pName, 'ph.d') || str_contains($pName, 'doctor');

            $matched = $levels->first(function ($lvl) use ($isPhd) {
                $lName = strtolower($lvl->name);
                return $isPhd
                    ? (str_contains($lName, 'phd') || str_contains($lName, 'doctor'))
                    : (str_contains($lName, 'msc') || str_contains($lName, 'master'));
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
        // No action required to reverse backfill
    }
};
