<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\MilestoneTemplate;
use App\Models\StudentProfile;
use App\Models\Program;
use App\Models\Level;
use App\Models\StudentMilestone;
use App\Models\DefenceEvent;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Deletes all seminar_as_a_course milestone records for PhD Artificial Intelligence students
     * WITHOUT pushing or altering Milestone 2 status.
     */
    public function up(): void
    {
        $seminarTemplate = MilestoneTemplate::where('slug', 'seminar_as_a_course')->first();
        if (!$seminarTemplate) {
            return;
        }

        $programs = Program::all();
        $levels = Level::all();

        // 1. Identify PhD level IDs
        $phdLevelIds = $levels->filter(function ($l) {
            $lName = strtolower($l->name);
            return str_contains($lName, 'phd') || str_contains($lName, 'doctor');
        })->pluck('id')->toArray();

        // 2. Identify PhD AI program IDs
        $phdAiProgramIds = $programs->filter(function ($p) {
            $pName = strtolower($p->name . ' ' . ($p->degree_type ?? ''));
            $isPhd = str_contains($pName, 'phd') || str_contains($pName, 'doctor') || str_contains($pName, 'ph.d');
            $isAI = str_contains($pName, 'artificial intelligence') 
                || str_contains($pName, 'intelligence') 
                || str_contains($pName, ' ai') 
                || str_ends_with($pName, 'ai') 
                || (isset($p->code) && strtoupper($p->code) === 'AI');
            return $isPhd && $isAI;
        })->pluck('id')->toArray();

        // 3. Identify AI program IDs (which combined with PhD level make PhD AI)
        $aiProgramIds = $programs->filter(function ($p) {
            $pName = strtolower($p->name . ' ' . ($p->degree_type ?? ''));
            return str_contains($pName, 'artificial intelligence') 
                || str_contains($pName, 'intelligence') 
                || str_contains($pName, ' ai') 
                || str_ends_with($pName, 'ai') 
                || (isset($p->code) && strtoupper($p->code) === 'AI');
        })->pluck('id')->toArray();

        // 4. Find all PhD AI students
        $students = StudentProfile::where(function ($q) use ($phdAiProgramIds, $aiProgramIds, $phdLevelIds) {
            if (!empty($phdAiProgramIds)) {
                $q->whereIn('program_id', $phdAiProgramIds);
            }
            if (!empty($aiProgramIds) && !empty($phdLevelIds)) {
                $q->orWhere(function ($sub) use ($aiProgramIds, $phdLevelIds) {
                    $sub->whereIn('program_id', $aiProgramIds)
                        ->whereIn('level_id', $phdLevelIds);
                });
            }
        })->get();

        if ($students->isEmpty()) {
            return;
        }

        $studentProfileIds = $students->pluck('id')->toArray();

        // 5. Find thesis project IDs
        $thesisIds = DB::table('thesis_projects')
            ->whereIn('student_profile_id', $studentProfileIds)
            ->pluck('id')
            ->toArray();

        if (empty($thesisIds)) {
            return;
        }

        // 6. Delete seminar_grades linked to the seminar_as_a_course milestones
        $milestoneIds = DB::table('student_milestones')
            ->whereIn('thesis_project_id', $thesisIds)
            ->where('milestone_template_id', $seminarTemplate->id)
            ->pluck('id')
            ->toArray();

        if (!empty($milestoneIds)) {
            if (\Illuminate\Support\Facades\Schema::hasTable('seminar_grades')) {
                DB::table('seminar_grades')->whereIn('student_milestone_id', $milestoneIds)->delete();
            }

            // Delete submissions or messages on seminar milestone if any
            DB::table('submissions')->whereIn('student_milestone_id', $milestoneIds)->delete();
            DB::table('messages')->whereIn('student_milestone_id', $milestoneIds)->delete();

            // Delete the seminar_as_a_course student milestones
            DB::table('student_milestones')->whereIn('id', $milestoneIds)->delete();
        }

        // 7. Delete seminar defence events for these thesis projects
        DB::table('defence_events')
            ->whereIn('thesis_project_id', $thesisIds)
            ->where('type', 'seminar')
            ->delete();

        // Note: As explicitly instructed, Milestone 2 is NOT pushed or modified.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed
    }
};
