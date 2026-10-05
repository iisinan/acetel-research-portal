<?php

namespace App\Services;

use App\Models\StudentMilestone;
use App\Models\Submission;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MilestoneService
{
    /**
     * Submit work for a milestone.
     */
    public function submitMilestone(StudentMilestone $milestone, array $data, ?UploadedFile $file = null, User $submitter)
    {
        return DB::transaction(function () use ($milestone, $data, $file, $submitter) {
            $fileUrl = null;
            $fileMeta = [];

            if ($file) {
                try {
                    // Upload logic (S3 or local)
                    $path = $file->store('submissions/' . $milestone->thesis_project_id);
                    $fileUrl = Storage::url($path);
                    $fileMeta = [
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType(),
                    ];
                } catch (\Exception $e) {
                    throw new \Exception("Failed to store submission file: " . $e->getMessage());
                }
            }

            // Create submission record
            $submission = Submission::create([
                'student_milestone_id' => $milestone->id,
                'version' => ($milestone->submissions()->max('version') ?? 0) + 1,
                'file_url' => $fileUrl,
                'file_meta' => $fileMeta,
                'submitted_by' => $submitter->id,
                'description' => $data['description'] ?? null,
            ]);

            // Update milestone status
            $milestone->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            // Notify Supervisors
            $milestone->load('thesis.assignments.supervisor.user');
            foreach ($milestone->thesis->assignments as $assignment) {
                if ($assignment->status === 'active') {
                    $assignment->supervisor->user->notify(new \App\Notifications\SubmissionReceived($submission));
                }
            }

            \App\Models\AuditLog::create([
                'user_id' => $submitter->id,
                'action' => 'submit_milestone_work',
                'entity_type' => Submission::class,
                'entity_id' => $submission->id,
                'new_values' => $submission->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $submission;
        });
    }

    /**
     * Review a milestone submission.
     */
    public function reviewMilestone(StudentMilestone $milestone, array $data, User $reviewer)
    {
        return DB::transaction(function () use ($milestone, $data, $reviewer) {
            // Find latest submission or specific one? Assuming reviewing current state.
            $submission = $milestone->submissions()->latest()->first();
            
            if ($submission) {
                Feedback::create([
                    'submission_id' => $submission->id,
                    'decision' => $data['decision'], // approved, rejected, changes_requested
                    'remarks' => $data['remarks'],
                    'created_by' => $reviewer->id,
                ]);
            }

            if ($data['decision'] === 'approved') {
                $milestone->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    // 'reviewed_at' => now(), // Maybe separate field
                ]);
                
                // Trigger logic for next milestone?
            } else {
                 $milestone->update([
                    'status' => 'revision_required', // or failed
                    'reviewed_at' => now(),
                ]);
            }

            // Notify Student
            $milestone->load('thesis.student.user');
            $milestone->thesis->student->user->notify(new \App\Notifications\MilestoneGraded($milestone));

            \App\Models\AuditLog::create([
                'user_id' => $reviewer->id,
                'action' => 'review_milestone',
                'entity_type' => StudentMilestone::class,
                'entity_id' => $milestone->id,
                'old_values' => ['status' => $milestone->getOriginal('status')], // Note: this might be 'submitted' if loaded before update, need care
                'new_values' => ['status' => $milestone->status, 'decision' => $data['decision']],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $milestone;
        });
    }
}
