<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $students = \App\Models\StudentProfile::with(['thesis.assignments', 'thesis.milestones.template'])->get();
        $removedCount = 0;
        
        foreach ($students as $student) {
            if (!$student->thesis) continue;
            
            $shouldRemove = false;
            
            // Condition 1: Program/Level is strictly Seminar
            if ($student->isSeminarCourseLevel()) {
                $shouldRemove = true;
            } else {
                // Condition 2: Current active milestone is Seminar Course
                $m1 = $student->thesis->milestones->firstWhere('template.slug', 'seminar_as_a_course');
                $m2 = $student->thesis->milestones->firstWhere('template.order', 2);
                
                // If they have seminar as a course and haven't started milestone 2
                if ($m1 && $m1->status !== 'approved' && (!$m2 || $m2->status === 'pending')) {
                    $shouldRemove = true;
                }
            }

            if ($shouldRemove && $student->thesis->assignments->count() > 0) {
                Log::info("Migration removing supervisors for student ID: " . $student->id);
                \App\Models\SupervisionAssignment::where('thesis_project_id', $student->thesis->id)->delete();
                $removedCount++;
            }
        }
        Log::info("Migration removed supervisors from {$removedCount} seminar students.");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way data cleanup migration, cannot be reversed safely automatically
    }
};
