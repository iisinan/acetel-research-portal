<?php

namespace App\Services;

use App\Models\ThesisProject;
use App\Models\StudentProfile;
use App\Models\SupervisorProfile;
use App\Models\SupervisionAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class ThesisService
{
    /**
     * Create a new thesis project for a student.
     */
    public function createProject(StudentProfile $student, array $data)
    {
        return DB::transaction(function () use ($student, $data) {
            // Ensure student doesn't already have an active project? 
            // Depending on rules, maybe archive old one. For now, assume single active.
            
            $project = ThesisProject::create([
                'student_profile_id' => $student->id,
                'title' => $data['title'],
                'abstract' => $data['abstract'] ?? null,
                'status' => 'proposed',
                'start_date' => now(),
            ]);

            // Generate Milestones from Template
            // Assuming templates are global or filtered by program. 
            // If templates have program_id, filter by student's program.
            // If template has null program_id, it applies to all.
            
            $templates = \App\Models\MilestoneTemplate::where(function($query) use ($student) {
                $query->where('program_id', $student->program_id)
                      ->orWhereNull('program_id');
            })->orderBy('order')->get();

            // Note: Milestones are already generated via ThesisProjectObserver->syncMilestones()
            // We no longer need to create them manually here.

            return $project;
        });
    }

    /**
     * Assign a supervisor to a thesis project.
     */
    public function assignSupervisor(ThesisProject $project, SupervisorProfile $supervisor, string $role = 'primary')
    {
        return DB::transaction(function () use ($project, $supervisor, $role) {
            // Institutional Rule: Only a Professor can be a Lead/Primary supervisor
            if (($role === 'primary' || $role === 'lead') && $supervisor->rank !== 'Professor') {
                throw new Exception("Academic Hierarchy Violation: Only a Professor can serve as a Primary Supervisor.");
            }

            // Check workload
            if ($supervisor->current_load >= $supervisor->max_students) {
                throw new Exception("Supervisor has reached maximum workload capacity.");
            }

            // Check if already assigned
            $existing = SupervisionAssignment::where('thesis_project_id', $project->id)
                ->where('supervisor_profile_id', $supervisor->id)
                ->where('status', 'active')
                ->first();

            if ($existing) {
                throw new Exception("Supervisor is already assigned to this project.");
            }

            // Create assignment
            $assignment = SupervisionAssignment::create([
                'thesis_project_id' => $project->id,
                'supervisor_profile_id' => $supervisor->id,
                'role' => $role,
                'status' => 'active',
                'assigned_at' => now(),
            ]);

            // Increment load
            $supervisor->increment('current_load');

            // Log action (Event/Observer or here directly)
            // AuditLog::create(...) - Handled by Observer ideally or Service calls

            return $assignment;
        });
    }
    /**
     * Replace all supervisors for a thesis project enforcing logic.
     */
    public function replaceSupervisors(ThesisProject $project, array $supervisorIds)
    {
        return DB::transaction(function () use ($project, $supervisorIds) {
            $student = $project->student;
            $levelName = $student->level->name ?? '';
            $isPhD = $levelName && stripos($levelName, 'PhD') !== false;
            $requiredCount = $isPhD ? 3 : 2;

            // 1. Enforce Count Rules (MSc=2, PhD=3)
            if (count($supervisorIds) !== $requiredCount) {
                $msg = $isPhD ? "PhD panels must consist of exactly 3 authorized members." : "MSc panels must consist of exactly 2 authorized members.";
                throw new Exception($msg);
            }

            // 2. End Existing Assignments
            $existingAssignments = SupervisionAssignment::where('thesis_project_id', $project->id)
                ->where('status', 'active')
                ->get();

            foreach ($existingAssignments as $assignment) {
                if ($assignment->supervisor) {
                    $assignment->supervisor->decrement('current_load');
                }
            }

            SupervisionAssignment::where('thesis_project_id', $project->id)
                ->where('status', 'active')
                ->update(['status' => 'ended', 'ended_at' => now()]);

            // 3. Create New Assignments
            foreach ($supervisorIds as $index => $supervisorId) {
                $supervisor = SupervisorProfile::findOrFail($supervisorId);

                // Institutional Rule: First supervisor (index 0) must be a Professor
                if ($index === 0 && $supervisor->rank !== 'Professor') {
                    throw new Exception("Academic Hierarchy Violation: The Lead Supervisor ({$supervisor->user->name}) must hold the rank of Professor.");
                }

                // Check workload
                if ($supervisor->current_load >= $supervisor->max_students) {
                    throw new Exception("Supervisor {$supervisor->user->name} has reached maximum capacity.");
                }

                SupervisionAssignment::create([
                    'thesis_project_id' => $project->id,
                    'supervisor_profile_id' => $supervisorId,
                    'role' => $index === 0 ? 'primary' : 'secondary',
                    'status' => 'active',
                    'assigned_at' => now(),
                ]);

                $supervisor->increment('current_load');
            }

            // 4. Try auto-advancing Milestone 2 if both proposal uploaded and supervisors assigned
            app(\App\Services\MilestoneWorkflowService::class)->tryAutoAdvanceSupervisorsAssigned($project->fresh());

            return true;
        });
    }

    /**
     * Propose a randomized supervision panel based on program scope and capacity.
     */
    public function proposeRandomSupervisors(ThesisProject $project)
    {
        $student = $project->student;
        $levelName = $student->level->name ?? '';
        $isPhD = $levelName && stripos($levelName, 'PhD') !== false;
        $count = $isPhD ? 3 : 2; // Institutional Requirement Update: PhD=3, MSc=2

        $proposedSupervisors = [];

        // Institutional Requirement: Lead supervisor must be a Professor (Lead Role)
        $professor = SupervisorProfile::whereHas('programs', function($q) use ($student) {
                $q->where('programs.id', $student->program_id);
            })
            ->where('current_load', '<', DB::raw('max_students'))
            ->where('rank', 'Professor')
            ->inRandomOrder()
            ->first();

        if (!$professor) {
             throw new Exception("Institutional capacity alert: No available Professors found to serve as Lead Supervisor for this program.");
        }
        
        $proposedSupervisors[] = $professor->id;

        // Get remaining supervisors (excluding the professor already picked)
        $needed = $count - 1;
        $others = SupervisorProfile::whereHas('programs', function($q) use ($student) {
                $q->where('programs.id', $student->program_id);
            })
            ->where('current_load', '<', DB::raw('max_students'))
            ->where('id', '!=', $professor->id)
            ->inRandomOrder()
            ->limit($needed)
            ->get();

        if ($others->count() < $needed) {
            throw new Exception("Institutional capacity alert: Not enough available supervisors found for this protocol. Required: {$count} total.");
        }

        foreach ($others as $other) {
            $proposedSupervisors[] = $other->id;
        }

        $project->update([
            'proposed_supervisors' => $proposedSupervisors
        ]);

        return true;
    }

    /**
     * Verify if a supervisor is authorized for any of the student's institutional programs.
     */
    protected function isSupervisorInStudentPrograms(SupervisorProfile $supervisor, StudentProfile $student): bool
    {
        return $supervisor->programs()
            ->where('programs.id', $student->program_id)
            ->exists();
    }
}
