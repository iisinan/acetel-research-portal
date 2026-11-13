<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Program;
use App\Models\StudentProfile;
use App\Models\SupervisorProfile;
use App\Models\SupervisionAssignment;
use App\Models\ThesisProject;
use App\Models\Message;
use App\Services\UserAccountMergeService;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserAccountMergeTest extends TestCase
{
    public function test_merges_two_supervisor_accounts_cleanly(): void
    {
        $role = Role::firstOrCreate(['name' => 'Supervisor']);

        $sourceUser = User::factory()->create([
            'name' => 'Prof Adebukola Onashoga (CS)',
            'email' => 'onashogasa@gmail.com',
            'is_active' => true,
        ]);
        $sourceUser->assignRole('Supervisor');

        $targetUser = User::factory()->create([
            'name' => 'Prof. Bukola Onashoga',
            'email' => 'onashogasa@funaab.edu.ng',
            'is_active' => true,
        ]);
        $targetUser->assignRole('Supervisor');

        $program = Program::first() ?? Program::create(['name' => 'Computer Science', 'code' => 'CS']);

        $sourceSup = SupervisorProfile::create([
            'user_id' => $sourceUser->id,
            'program_id' => $program->id,
            'rank' => 'Professor',
            'expertise' => 'Cybersecurity',
            'current_load' => 1,
            'max_students' => 5,
        ]);

        $targetSup = SupervisorProfile::create([
            'user_id' => $targetUser->id,
            'program_id' => $program->id,
            'rank' => 'Professor',
            'expertise' => 'Cybersecurity',
            'current_load' => 0,
            'max_students' => 5,
        ]);

        $studentUser = User::factory()->create();
        $student = StudentProfile::create([
            'user_id' => $studentUser->id,
            'program_id' => $program->id,
            'student_id_number' => 'TEST/123/' . rand(100, 999),
        ]);

        $thesis = ThesisProject::create([
            'student_profile_id' => $student->id,
            'title' => 'Test Thesis',
            'status' => 'active',
        ]);

        $assignment = SupervisionAssignment::create([
            'thesis_project_id' => $thesis->id,
            'supervisor_profile_id' => $sourceSup->id,
            'role' => 'primary',
            'status' => 'active',
        ]);

        $message = Message::create([
            'thesis_project_id' => $thesis->id,
            'user_id' => $sourceUser->id,
            'content' => 'Review completed for student',
            'type' => 'text',
        ]);

        // Execute merge
        $service = app(UserAccountMergeService::class);
        $result = $service->merge('onashogasa@gmail.com', 'onashogasa@funaab.edu.ng');

        $this->assertTrue($result['success']);

        // Assert source user deleted
        $this->assertNull(User::where('email', 'onashogasa@gmail.com')->first());

        // Assert target user exists
        $this->assertNotNull(User::where('email', 'onashogasa@funaab.edu.ng')->first());

        // Assert assignment moved to target
        $assignment->refresh();
        $this->assertEquals($targetSup->id, $assignment->supervisor_profile_id);

        // Assert message moved to target
        $message->refresh();
        $this->assertEquals($targetUser->id, $message->user_id);

        // Assert target supervisor load updated
        $targetSup->refresh();
        $this->assertEquals(1, $targetSup->current_load);
    }
}
