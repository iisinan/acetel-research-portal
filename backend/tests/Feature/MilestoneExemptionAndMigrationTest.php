<?php

namespace Tests\Feature;

use App\Models\Cohort;
use App\Models\DefenceEvent;
use App\Models\Evaluation;
use App\Models\Feedback;
use App\Models\Level;
use App\Models\Message;
use App\Models\MilestoneTemplate;
use App\Models\Program;
use App\Models\StudentMilestone;
use App\Models\SupervisorProfile;
use App\Models\ThesisProject;
use App\Models\User;
use App\Services\MilestoneWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MilestoneExemptionAndMigrationTest extends TestCase
{
    use RefreshDatabase;

    private Cohort $cohort;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->cohort = Cohort::create([
            'name' => '2025/2026 Cohort',
            'code' => '2025-B1',
            'intake_year' => 2025,
            'status' => 'active',
            'start_date' => now()->subMonths(6),
            'end_date' => now()->addYear(),
        ]);
    }

    private function createProgram(string $name, string $code, string $degreeType, string $serial = '001'): Program
    {
        return Program::create([
            'name' => $name,
            'code' => $code,
            'degree_type' => $degreeType,
            'serial_number' => $serial,
        ]);
    }

    private function createStudentWithThesis(Program $program, ?Level $level, string $matric): array
    {
        $user = User::factory()->create();
        $user->assignRole('Student');

        $profile = $user->studentProfile()->create([
            'program_id' => $program->id,
            'level_id' => $level?->id,
            'cohort_id' => $this->cohort->id,
            'student_id_number' => $matric,
            'enrollment_status' => 'active',
            'current_semester' => 1,
        ]);

        $thesis = ThesisProject::create([
            'student_profile_id' => $profile->id,
            'title' => 'Thesis for ' . $matric,
            'abstract' => 'Test research abstract.',
            'status' => 'in_progress',
            'start_date' => now()->subMonths(3),
        ]);

        return [$user, $profile, $thesis];
    }

    #[Test]
    public function phd_ai_students_do_not_have_seminar_course_but_keep_progress_report_2()
    {
        $phdAi = $this->createProgram(
            'Doctor of Philosophy in Artificial intelligence',
            'DOCT7570',
            'PhD',
            '901'
        );

        // Even with null level (as in production), student must be detected as PhD AI
        [$user, $profile, $thesis] = $this->createStudentWithThesis($phdAi, null, 'ACE2519001');

        $this->assertTrue($profile->isPhd());
        $this->assertTrue($profile->isPhdAI());
        $this->assertFalse($profile->isMsc());

        // Inject a stale seminar row to confirm syncMilestones() purges it
        $seminarTpl = MilestoneTemplate::where('slug', 'seminar_as_a_course')->firstOrFail();
        $pr2Tpl = MilestoneTemplate::where('slug', 'progress_report_2')->firstOrFail();

        StudentMilestone::create([
            'thesis_project_id' => $thesis->id,
            'milestone_template_id' => $seminarTpl->id,
            'status' => 'in_progress',
        ]);

        $thesis->syncMilestones();
        $thesis->refresh();

        $slugs = $thesis->milestones()->with('template')->get()->pluck('template.slug')->all();

        $this->assertNotContains('seminar_as_a_course', $slugs);
        $this->assertContains('progress_report_2', $slugs);
        $this->assertCount(6, $slugs);

        // Admin BulkScheduleController must ignore PhD AI for seminar
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->actingAs($admin)->post(route('admin.bulk-schedule.store'), [
            'defence_type' => 'seminar',
            'defence_date' => now()->addWeek()->toDateString(),
            'student_ids' => [$profile->id],
        ])->assertRedirect();

        $this->assertDatabaseMissing('student_milestones', [
            'thesis_project_id' => $thesis->id,
            'milestone_template_id' => $seminarTpl->id,
        ]);
        $this->assertNotNull($thesis->milestones()->where('milestone_template_id', $pr2Tpl->id)->first());
    }

    #[Test]
    public function msc_registration_and_sync_exclude_pr2_and_advance_from_pr1_to_internal_defence()
    {
        $mscAi = $this->createProgram(
            'Master of Science in Artificial Intelligence',
            'MAST5209',
            'MSc',
            '902'
        );

        // HTTP Registration with PR1 NOT completed yet -> PR1 becomes in_progress, PR2 is skipped
        $response = $this->post('/register', [
            'name' => 'MSc Candidate',
            'email' => 'msc.candidate@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'matric_number' => 'ACE2519002',
            'program_id' => $mscAi->id,
            'phone_number' => '08012345678',
            'gender' => 'Male',
            'nationality' => 'Nigerian',
            'completed_milestones' => [],
        ]);
        $response->assertRedirect();

        $user = User::where('email', 'msc.candidate@example.com')->firstOrFail();
        $thesis = $user->studentProfile->thesis;
        $this->assertNotNull($thesis);

        $slugs = $thesis->milestones()->with('template')->get()->pluck('template.slug')->all();
        $this->assertNotContains('progress_report_2', $slugs);
        $this->assertContains('seminar_as_a_course', $slugs);
        $this->assertContains('progress_report_1', $slugs);
        $this->assertContains('internal_defence', $slugs);
        $this->assertCount(6, $slugs);

        // Approve seminar, supervisors_assigned, proposal_defence, progress_report_1 sequentially
        $workflow = app(MilestoneWorkflowService::class);
        foreach (['seminar_as_a_course', 'supervisors_assigned', 'proposal_defence'] as $slug) {
            $m = $thesis->milestones()->whereHas('template', fn ($q) => $q->where('slug', $slug))->firstOrFail();
            $m->update(['status' => 'approved', 'approved_at' => now(), 'date_approved_at' => now()]);
        }

        $pr1 = $thesis->milestones()->whereHas('template', fn ($q) => $q->where('slug', 'progress_report_1'))->firstOrFail();
        $pr1->update(['status' => 'approved', 'approved_at' => now(), 'date_approved_at' => now()]);
        $workflow->afterApproval($pr1);

        $internal = $thesis->milestones()->whereHas('template', fn ($q) => $q->where('slug', 'internal_defence'))->firstOrFail();
        $this->assertSame('in_progress', $internal->status);
        $this->assertSame((int) round((4 / 6) * 100), $thesis->fresh()->progress_percentage);

        // Admin BulkScheduleController must ignore MSc for progress_report_2
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $pr2Tpl = MilestoneTemplate::where('slug', 'progress_report_2')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.bulk-schedule.store'), [
            'defence_type' => 'progress_report_2',
            'defence_date' => now()->addWeek()->toDateString(),
            'student_ids' => [$user->studentProfile->id],
        ])->assertRedirect();

        $this->assertDatabaseMissing('student_milestones', [
            'thesis_project_id' => $thesis->id,
            'milestone_template_id' => $pr2Tpl->id,
        ]);
    }

    #[Test]
    public function msc_student_on_pr2_is_moved_back_to_pr1_with_all_uploads_feedback_approvals_messages_and_events_intact()
    {
        $mscCyber = $this->createProgram(
            'Master of Science in Cybersecurity',
            'MAST2547',
            'MSc',
            '903'
        );

        [$studentUser, $profile, $thesis] = $this->createStudentWithThesis($mscCyber, null, 'ACE2519003');

        $supervisorUser = User::factory()->create(['name' => 'Dr. Ada Lovelace']);
        $supervisorUser->assignRole('Supervisor');
        $supProfile = SupervisorProfile::create([
            'user_id' => $supervisorUser->id,
            'department' => 'Computer Science',
            'specialization' => 'AI',
        ]);
        $thesis->assignments()->create([
            'supervisor_profile_id' => $supProfile->id,
            'role' => 'primary',
            'status' => 'active',
        ]);

        // Update seeded templates to simulate pre-migration production state for MSc candidate currently on PR2
        foreach (MilestoneTemplate::orderBy('order')->get() as $tpl) {
            $status = match ($tpl->slug) {
                'seminar_as_a_course', 'supervisors_assigned', 'proposal_defence', 'progress_report_1' => 'approved',
                'progress_report_2' => 'submitted',
                default => 'not_started',
            };
            StudentMilestone::updateOrCreate(
                [
                    'thesis_project_id' => $thesis->id,
                    'milestone_template_id' => $tpl->id,
                ],
                [
                    'status' => $status,
                    'approved_at' => $status === 'approved' ? now()->subMonth() : null,
                    'date_approved_at' => $status === 'approved' ? now()->subMonth() : null,
                ]
            );
        }

        $pr1 = $thesis->milestones()->whereHas('template', fn ($q) => $q->where('slug', 'progress_report_1'))->firstOrFail();
        $pr2 = $thesis->milestones()->whereHas('template', fn ($q) => $q->where('slug', 'progress_report_2'))->firstOrFail();

        // Pre-existing PR1 submission
        $pr1OldSub = $pr1->submissions()->create([
            'submitted_by' => $studentUser->id,
            'type' => 'ppt',
            'file_url' => 'submissions/old_pr1.pdf',
            'description' => 'Old PR1 Deck',
            'version' => 1,
        ]);

        // PR2 uploaded PPT, feedback, supervisor approvals, communication log, defence schedule, message, and DefenceEvent/Evaluation
        $pr2->update([
            'status' => 'submitted',
            'submitted_at' => now()->subDays(2),
            'defence_date' => '2026-11-20',
            'defence_time' => '10:00',
            'defence_location' => 'Room 204',
            'meeting_link' => 'https://zoom.us/j/999999999',
            'remark' => 'PR2 slides uploaded for supervisor review',
            'is_supervisor_approved' => true,
            'approvals' => [
                'supervisor_reviews' => [
                    $supervisorUser->id => [
                        'decision' => 'approved',
                        'remarks' => 'Strong slides ready for presentation.',
                        'reviewed_at' => now()->subDay()->toDateTimeString(),
                    ],
                ],
                'communication_log' => [
                    [
                        'sender_id' => $studentUser->id,
                        'message' => 'Uploaded PR2 PPT for approval.',
                        'timestamp' => now()->subDays(2)->toDateTimeString(),
                    ],
                ],
            ],
        ]);

        $pr2Sub = $pr2->submissions()->create([
            'submitted_by' => $studentUser->id,
            'type' => 'ppt',
            'file_url' => 'submissions/pr2_slides.pdf',
            'file_meta' => ['original_name' => 'PR2_Defence_Slides.pdf'],
            'description' => 'Presentation Slide Deck (PDF)',
            'version' => 1,
        ]);

        $feedback = Feedback::create([
            'submission_id' => $pr2Sub->id,
            'decision' => 'approved',
            'remarks' => 'Approved by supervisor.',
            'created_by' => $supervisorUser->id,
        ]);

        $msg = Message::create([
            'thesis_project_id' => $thesis->id,
            'student_milestone_id' => $pr2->id,
            'user_id' => $studentUser->id,
            'content' => 'Please review my uploaded slides.',
            'type' => 'text',
        ]);

        $pr2Event = DefenceEvent::create([
            'thesis_project_id' => $thesis->id,
            'type' => 'progress_report_2',
            'schedule_start' => '2026-11-20 10:00:00',
            'schedule_end' => '2026-11-20 10:30:00',
            'meeting_link' => 'https://zoom.us/j/999999999',
        ]);
        Evaluation::create([
            'defence_event_id' => $pr2Event->id,
            'evaluator_id' => $supervisorUser->id,
            'score' => ['total' => 82],
            'recommendation' => 'pass',
            'comments' => 'Good progress.',
            'submitted_at' => now(),
        ]);

        // Run the actual production migration
        $migration = require database_path('migrations/2026_11_13_160000_enforce_msc_and_phd_ai_milestone_rules.php');
        $migration->up();

        $thesis->refresh();
        $pr1->refresh();

        // PR2 milestone row must be gone, PR1 must now be the active submitted milestone
        $this->assertDatabaseMissing('student_milestones', ['id' => $pr2->id]);
        $this->assertSame($pr1->id, $thesis->currentMilestone()->first()?->id);
        $this->assertSame('submitted', $pr1->status);
        $this->assertNull($pr1->approved_at);
        $this->assertNull($pr1->date_approved_at);
        $this->assertTrue((bool) $pr1->is_supervisor_approved);
        $this->assertSame('2026-11-20', $pr1->defence_date?->toDateString());
        $this->assertSame('10:00', $pr1->defence_time);
        $this->assertSame('Room 204', $pr1->defence_location);
        $this->assertSame('https://zoom.us/j/999999999', $pr1->meeting_link);
        $this->assertArrayHasKey($supervisorUser->id, $pr1->approvals['supervisor_reviews'] ?? []);
        $this->assertCount(1, $pr1->approvals['communication_log'] ?? []);

        // Both PPT submissions preserved on PR1 with clean version numbers, and feedback still linked
        $pr1Subs = $pr1->submissions()->orderBy('version')->get();
        $this->assertCount(2, $pr1Subs);
        $this->assertSame($pr1OldSub->id, $pr1Subs[0]->id);
        $this->assertSame($pr2Sub->id, $pr1Subs[1]->id);
        $this->assertSame(2, $pr1Subs[1]->version);
        $this->assertSame('Approved by supervisor.', $feedback->fresh()->remarks);
        $this->assertSame($pr2Sub->id, $feedback->fresh()->submission_id);

        // Message moved to PR1
        $this->assertSame($pr1->id, $msg->fresh()->student_milestone_id);

        // DefenceEvent re-typed to progress_report_1 with Evaluation intact
        $this->assertSame('progress_report_1', $pr2Event->fresh()->type);
        $this->assertDatabaseMissing('defence_events', [
            'thesis_project_id' => $thesis->id,
            'type' => 'progress_report_2',
        ]);
        $this->assertCount(1, $pr2Event->fresh()->evaluations);
    }

    #[Test]
    public function msc_student_already_past_pr2_loses_dummy_pr2_row_without_being_moved_backward()
    {
        $mscMis = $this->createProgram(
            'Master of Science in Management Information System',
            'MAST5148',
            'MSc',
            '904'
        );

        [$studentUser, $profile, $thesis] = $this->createStudentWithThesis($mscMis, null, 'ACE2519004');

        foreach (MilestoneTemplate::orderBy('order')->get() as $tpl) {
            $status = match ($tpl->slug) {
                'seminar_as_a_course', 'supervisors_assigned', 'proposal_defence', 'progress_report_1', 'progress_report_2' => 'approved',
                'internal_defence' => 'in_progress',
                default => 'not_started',
            };
            StudentMilestone::updateOrCreate(
                [
                    'thesis_project_id' => $thesis->id,
                    'milestone_template_id' => $tpl->id,
                ],
                [
                    'status' => $status,
                    'approved_at' => $status === 'approved' ? now()->subMonth() : null,
                    'date_approved_at' => $status === 'approved' ? now()->subMonth() : null,
                ]
            );
        }

        $migration = require database_path('migrations/2026_11_13_160000_enforce_msc_and_phd_ai_milestone_rules.php');
        $migration->up();

        $thesis->refresh();

        $slugs = $thesis->milestones()->with('template')->get()->pluck('template.slug')->all();
        $this->assertNotContains('progress_report_2', $slugs);
        $this->assertSame('internal_defence', $thesis->currentMilestone()->with('template')->first()?->template?->slug);

        $pr1 = $thesis->milestones()->whereHas('template', fn ($q) => $q->where('slug', 'progress_report_1'))->firstOrFail();
        $this->assertSame('approved', $pr1->status);
    }
}
