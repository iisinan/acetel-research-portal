<?php

namespace App\Services;

use App\Models\User;
use App\Models\SupervisorProfile;
use App\Models\SupervisionAssignment;
use App\Models\InternalExaminerProfile;
use App\Models\ExternalExaminerProfile;
use App\Models\CoordinatorProfile;
use App\Models\StudentProfile;
use App\Models\ThesisProject;
use App\Models\StudentMilestone;
use App\Models\MilestoneTemplate;
use App\Models\DefenceEvent;
use App\Models\PanelMember;
use App\Models\Evaluation;
use App\Models\Submission;
use App\Models\Feedback;
use App\Models\ActionItem;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Exception;

class UserAccountMergeService
{
    /**
     * Merge source user account into target user account.
     * All profiles, assignments, evaluations, panel memberships, reviews,
     * and messages are consolidated into the target user account.
     * The source user account is then deleted.
     */
    public function merge(string $sourceEmail, string $targetEmail): array
    {
        return DB::transaction(function () use ($sourceEmail, $targetEmail) {
            $sourceEmailNorm = strtolower(trim($sourceEmail));
            $targetEmailNorm = strtolower(trim($targetEmail));

            if ($sourceEmailNorm === $targetEmailNorm) {
                throw new Exception("Source and target emails cannot be the same.");
            }

            $sourceUser = User::whereRaw('LOWER(email) = ?', [$sourceEmailNorm])->first();
            $targetUser = User::whereRaw('LOWER(email) = ?', [$targetEmailNorm])->first();

            if (!$sourceUser) {
                Log::info("Merge skipped: Source user {$sourceEmail} does not exist.");
                return [
                    'success' => false,
                    'message' => "Source user '{$sourceEmail}' not found. It may have already been merged.",
                ];
            }

            if (!$targetUser) {
                // If target doesn't exist, update source user's email to target
                $sourceUser->email = $targetEmail;
                $sourceUser->save();
                Log::info("Target user {$targetEmail} did not exist; updated source user {$sourceEmail} to {$targetEmail}.");
                return [
                    'success' => true,
                    'message' => "Target user did not exist. Source user email updated to '{$targetEmail}'.",
                ];
            }

            Log::info("Starting merge of user {$sourceUser->id} ({$sourceUser->email}) into {$targetUser->id} ({$targetUser->email})");

            $report = [
                'roles_merged' => [],
                'supervision_assignments_moved' => 0,
                'programs_attached' => 0,
                'internal_examiner_theses_moved' => 0,
                'external_examiner_theses_moved' => 0,
                'coordinator_profiles_handled' => 0,
                'panel_members_moved' => 0,
                'evaluations_moved' => 0,
                'seminar_grades_moved' => 0,
                'messages_moved' => 0,
                'submissions_moved' => 0,
                'feedback_moved' => 0,
                'action_items_moved' => 0,
                'milestone_reviews_migrated' => 0,
                'template_examiners_migrated' => 0,
            ];

            // 1. Roles & Permissions (Spatie)
            foreach ($sourceUser->roles as $role) {
                if (!$targetUser->hasRole($role->name)) {
                    $targetUser->assignRole($role->name);
                    $report['roles_merged'][] = $role->name;
                }
            }

            // Ensure target user is active
            $targetUser->is_active = true;
            $targetUser->save();

            // 2. Supervisor Profiles & Supervision Assignments
            $sourceSups = SupervisorProfile::where('user_id', $sourceUser->id)->get();
            $targetSup = SupervisorProfile::where('user_id', $targetUser->id)->first();

            foreach ($sourceSups as $sourceSup) {
                if ($targetSup) {
                    // Move supervision assignments
                    $assignments = SupervisionAssignment::where('supervisor_profile_id', $sourceSup->id)->get();
                    foreach ($assignments as $a) {
                        $existing = SupervisionAssignment::where('supervisor_profile_id', $targetSup->id)
                            ->where('thesis_project_id', $a->thesis_project_id)
                            ->first();
                        if ($existing) {
                            $a->delete();
                        } else {
                            $a->update(['supervisor_profile_id' => $targetSup->id]);
                            $report['supervision_assignments_moved']++;
                        }
                    }

                    // Move program_supervisor pivot entries
                    if (Schema::hasTable('program_supervisor')) {
                        $pivots = DB::table('program_supervisor')
                            ->where('supervisor_profile_id', $sourceSup->id)
                            ->get();
                        foreach ($pivots as $piv) {
                            $exists = DB::table('program_supervisor')
                                ->where('supervisor_profile_id', $targetSup->id)
                                ->where('program_id', $piv->program_id)
                                ->exists();
                            if (!$exists) {
                                DB::table('program_supervisor')->insert([
                                    'id' => (string) Str::uuid(),
                                    'supervisor_profile_id' => $targetSup->id,
                                    'program_id' => $piv->program_id,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                                $report['programs_attached']++;
                            }
                        }
                        DB::table('program_supervisor')->where('supervisor_profile_id', $sourceSup->id)->delete();
                    }

                    // Refresh load and details on target
                    $activeLoad = SupervisionAssignment::where('supervisor_profile_id', $targetSup->id)
                        ->where('status', 'active')
                        ->count();
                    $targetSup->update([
                        'current_load' => $activeLoad,
                        'rank' => $targetSup->rank ?: ($sourceSup->rank ?: 'Professor'),
                        'expertise' => $targetSup->expertise ?: $sourceSup->expertise,
                    ]);

                    $sourceSup->delete();
                } else {
                    // Target doesn't have a supervisor profile; transfer source profile to target
                    $sourceSup->update(['user_id' => $targetUser->id]);
                    $targetSup = $sourceSup;
                }
            }

            // 3. Internal Examiner Profiles
            $sourceIes = InternalExaminerProfile::where('user_id', $sourceUser->id)->get();
            $targetIe = InternalExaminerProfile::where('user_id', $targetUser->id)->first();

            foreach ($sourceIes as $sourceIe) {
                if ($targetIe) {
                    $updated = ThesisProject::where('internal_examiner_profile_id', $sourceIe->id)
                        ->update(['internal_examiner_profile_id' => $targetIe->id]);
                    $report['internal_examiner_theses_moved'] += $updated;
                    $sourceIe->delete();
                } else {
                    $sourceIe->update(['user_id' => $targetUser->id]);
                    $targetIe = $sourceIe;
                }
            }

            // 4. External Examiner Profiles
            $sourceEes = ExternalExaminerProfile::where('user_id', $sourceUser->id)->get();
            $targetEe = ExternalExaminerProfile::where('user_id', $targetUser->id)->first();

            foreach ($sourceEes as $sourceEe) {
                if ($targetEe) {
                    $updated = ThesisProject::where('external_examiner_profile_id', $sourceEe->id)
                        ->update(['external_examiner_profile_id' => $targetEe->id]);
                    $report['external_examiner_theses_moved'] += $updated;
                    $sourceEe->delete();
                } else {
                    $sourceEe->update(['user_id' => $targetUser->id]);
                    $targetEe = $sourceEe;
                }
            }

            // 5. Coordinator Profiles
            $sourceCoords = CoordinatorProfile::where('user_id', $sourceUser->id)->get();
            foreach ($sourceCoords as $sc) {
                $exists = CoordinatorProfile::where('user_id', $targetUser->id)
                    ->where('program_id', $sc->program_id)
                    ->where('level_id', $sc->level_id)
                    ->first();
                if ($exists) {
                    $sc->delete();
                } else {
                    $sc->update(['user_id' => $targetUser->id]);
                }
                $report['coordinator_profiles_handled']++;
            }

            // 6. Student Profile (safeguard)
            $sourceStudent = StudentProfile::where('user_id', $sourceUser->id)->first();
            $targetStudent = StudentProfile::where('user_id', $targetUser->id)->first();
            if ($sourceStudent && !$targetStudent) {
                $sourceStudent->update(['user_id' => $targetUser->id]);
            }

            // 7. Panel Memberships (Defence Events)
            $sourcePms = PanelMember::where('user_id', $sourceUser->id)->get();
            foreach ($sourcePms as $pm) {
                $exists = PanelMember::where('defence_event_id', $pm->defence_event_id)
                    ->where('user_id', $targetUser->id)
                    ->first();
                if ($exists) {
                    $pm->delete();
                } else {
                    $pm->update(['user_id' => $targetUser->id]);
                    $report['panel_members_moved']++;
                }
            }

            // 8. Evaluations
            $sourceEvals = Evaluation::where('evaluator_id', $sourceUser->id)->get();
            foreach ($sourceEvals as $ev) {
                $exists = Evaluation::where('defence_event_id', $ev->defence_event_id)
                    ->where('evaluator_id', $targetUser->id)
                    ->first();
                if ($exists) {
                    $ev->delete();
                } else {
                    $ev->update(['evaluator_id' => $targetUser->id]);
                    $report['evaluations_moved']++;
                }
            }

            // 9. Seminar Grades
            if (Schema::hasTable('seminar_grades')) {
                $grades = DB::table('seminar_grades')->where('examiner_id', $sourceUser->id)->get();
                foreach ($grades as $g) {
                    $exists = DB::table('seminar_grades')
                        ->where('student_milestone_id', $g->student_milestone_id)
                        ->where('examiner_id', $targetUser->id)
                        ->exists();
                    if ($exists) {
                        DB::table('seminar_grades')->where('id', $g->id)->delete();
                    } else {
                        DB::table('seminar_grades')->where('id', $g->id)->update(['examiner_id' => $targetUser->id]);
                        $report['seminar_grades_moved']++;
                    }
                }
            }

            // 10. Examiner Assignments
            if (Schema::hasTable('examiner_assignments')) {
                DB::table('examiner_assignments')->where('examiner_id', $sourceUser->id)->update(['examiner_id' => $targetUser->id]);
                DB::table('examiner_assignments')->where('assigned_by', $sourceUser->id)->update(['assigned_by' => $targetUser->id]);
            }

            // 11. Submissions, Feedback, Action Items
            $report['submissions_moved'] = Submission::where('submitted_by', $sourceUser->id)->update(['submitted_by' => $targetUser->id]);
            $report['feedback_moved'] = Feedback::where('created_by', $sourceUser->id)->update(['created_by' => $targetUser->id]);
            $report['action_items_moved'] = ActionItem::where('assigned_to', $sourceUser->id)->update(['assigned_to' => $targetUser->id]);

            // 12. Messages & Read States
            $report['messages_moved'] = Message::where('user_id', $sourceUser->id)->update(['user_id' => $targetUser->id]);

            if (Schema::hasTable('message_read_states')) {
                $readStates = DB::table('message_read_states')->where('user_id', $sourceUser->id)->get();
                foreach ($readStates as $rs) {
                    $exists = DB::table('message_read_states')
                        ->where('message_id', $rs->message_id)
                        ->where('user_id', $targetUser->id)
                        ->exists();
                    if ($exists) {
                        DB::table('message_read_states')->where('id', $rs->id)->delete();
                    } else {
                        DB::table('message_read_states')->where('id', $rs->id)->update(['user_id' => $targetUser->id]);
                    }
                }
            }

            // 13. Inbox Messages & Recipients
            if (Schema::hasTable('inbox_messages')) {
                if (Schema::hasColumn('inbox_messages', 'sender_id')) {
                    DB::table('inbox_messages')->where('sender_id', $sourceUser->id)->update(['sender_id' => $targetUser->id]);
                }
                if (Schema::hasColumn('inbox_messages', 'recipient_id')) {
                    DB::table('inbox_messages')->where('recipient_id', $sourceUser->id)->update(['recipient_id' => $targetUser->id]);
                }
            }

            if (Schema::hasTable('inbox_message_recipients')) {
                $imrs = DB::table('inbox_message_recipients')->where('user_id', $sourceUser->id)->get();
                foreach ($imrs as $imr) {
                    $exists = DB::table('inbox_message_recipients')
                        ->where('inbox_message_id', $imr->inbox_message_id)
                        ->where('user_id', $targetUser->id)
                        ->exists();
                    if ($exists) {
                        DB::table('inbox_message_recipients')->where('id', $imr->id)->delete();
                    } else {
                        DB::table('inbox_message_recipients')->where('id', $imr->id)->update(['user_id' => $targetUser->id]);
                    }
                }
            }

            // 14. Communication Channels
            if (Schema::hasTable('communication_channels')) {
                DB::table('communication_channels')->where('created_by', $sourceUser->id)->update(['created_by' => $targetUser->id]);
            }

            // 15. Student Milestones (date_approved_by & supervisor reviews in approvals JSON)
            StudentMilestone::where('date_approved_by', $sourceUser->id)->update(['date_approved_by' => $targetUser->id]);

            $milestones = StudentMilestone::whereNotNull('approvals')->get();
            foreach ($milestones as $m) {
                $approvals = $m->approvals;
                $changed = false;
                if (isset($approvals['supervisor_reviews'][(string)$sourceUser->id])) {
                    $review = $approvals['supervisor_reviews'][(string)$sourceUser->id];
                    $review['user_id'] = $targetUser->id;
                    $review['supervisor_name'] = $targetUser->name;
                    $approvals['supervisor_reviews'][(string)$targetUser->id] = $review;
                    unset($approvals['supervisor_reviews'][(string)$sourceUser->id]);
                    $changed = true;
                    $report['milestone_reviews_migrated']++;
                }
                if ($changed) {
                    $m->approvals = $approvals;
                    $m->save();
                }
            }

            // 16. Milestone Templates (group_examiners user_ids in metadata)
            $templates = MilestoneTemplate::whereNotNull('metadata')->get();
            foreach ($templates as $t) {
                $meta = $t->metadata;
                $changed = false;
                if (isset($meta['group_examiners']) && is_array($meta['group_examiners'])) {
                    foreach ($meta['group_examiners'] as $groupKey => $groupData) {
                        if (isset($groupData['user_ids']) && is_array($groupData['user_ids'])) {
                            if (in_array($sourceUser->id, $groupData['user_ids'])) {
                                $userIds = array_diff($groupData['user_ids'], [$sourceUser->id]);
                                if (!in_array($targetUser->id, $userIds)) {
                                    $userIds[] = $targetUser->id;
                                }
                                $meta['group_examiners'][$groupKey]['user_ids'] = array_values($userIds);
                                $changed = true;
                                $report['template_examiners_migrated']++;
                            }
                        }
                    }
                }
                if ($changed) {
                    $t->metadata = $meta;
                    $t->save();
                }
            }

            // 17. Audit Logs & Login Activities
            if (Schema::hasTable('audit_logs')) {
                DB::table('audit_logs')->where('user_id', $sourceUser->id)->update(['user_id' => $targetUser->id]);
            }
            if (Schema::hasTable('login_activities')) {
                DB::table('login_activities')->where('user_id', $sourceUser->id)->update(['user_id' => $targetUser->id]);
            }

            // 18. Clean up Spatie roles/permissions and delete source user
            DB::table('model_has_roles')->where('model_id', $sourceUser->id)->delete();
            DB::table('model_has_permissions')->where('model_id', $sourceUser->id)->delete();

            $sourceUser->delete();

            Log::info("Successfully merged {$sourceEmail} into {$targetEmail}", $report);

            return [
                'success' => true,
                'message' => "Successfully merged {$sourceEmail} into {$targetEmail}.",
                'report' => $report,
            ];
        });
    }
}
