<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Program;
use App\Models\Level;
use App\Models\StudentProfile;
use App\Models\CoordinatorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function student_sees_student_dashboard()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');
        
        // Setup profile
        $level = Level::where('name', 'MSc')->first();
        $program = Program::create(['name' => 'IT', 'code' => 'IT']);
        $cohort = \App\Models\Cohort::create([
            'name' => '2024/2025 Cohort',
            'code' => 'TEST_DB_COHORT',
            'intake_year' => 2024,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addYear()
        ]);
        
        $user->studentProfile()->create([
             'program_id' => $program->id,
             'level_id' => $level->id,
             'cohort_id' => $cohort->id,
             'student_id_number' => 'STU-DB',
             'enrollment_status' => 'active',
             'current_semester' => 1
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewHas('student');
        $response->assertViewHas('stats');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function coordinator_sees_coordinator_dashboard()
    {
        $user = User::factory()->create();
        $user->assignRole('Program Coordinator');
        
        $level = Level::where('name', 'MSc')->first();
        $program = Program::create(['name' => 'Data', 'code' => 'DT']);
        
        $user->coordinatorProfiles()->create([
            'program_id' => $program->id,
            'level_id' => $level->id,
            'active' => true
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewHas('students'); // Variable set for coordinators
        $response->assertViewHas('program_name');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_sees_admin_dashboard()
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertViewHas('recent_logs');
        $response->assertViewHas('projects');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function student_without_profile_sees_student_dashboard()
    {
        $bareStudent = User::factory()->create();
        $bareStudent->assignRole('Student');

        $this->actingAs($bareStudent)->get('/dashboard')->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function multi_role_faculty_sees_internal_examiner_tab_even_when_coordinated_student_lacks_thesis()
    {
        $level = Level::where('name', 'MSc')->first();
        $program = Program::create(['name' => 'Software Eng', 'code' => 'SE', 'serial_number' => '992']);
        $cohort = \App\Models\Cohort::create([
            'name' => '2025/2026 SE Cohort',
            'code' => 'SE_TEST_COHORT',
            'intake_year' => 2025,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addYear(),
        ]);

        $faculty = User::factory()->create(['name' => 'Prof Ishaq Multi']);
        $faculty->assignRole(['Internal Examiner', 'Supervisor', 'Program Coordinator']);

        $faculty->coordinatorProfiles()->create([
            'program_id' => $program->id,
            'level_id' => $level->id,
            'active' => true,
        ]);

        $internalProfile = \App\Models\InternalExaminerProfile::create([
            'user_id' => $faculty->id,
            'program_id' => $program->id,
            'active' => true,
        ]);

        // Coordinated active student with NO ThesisProject
        $noThesisUser = User::factory()->create();
        $noThesisUser->assignRole('Student');
        $noThesisUser->studentProfile()->create([
            'program_id' => $program->id,
            'level_id' => $level->id,
            'cohort_id' => $cohort->id,
            'student_id_number' => 'SE-NO-THESIS',
            'enrollment_status' => 'active',
            'current_semester' => 1,
        ]);

        // Another student WITH a ThesisProject assigned to this Internal Examiner (no current milestone)
        $examinedUser = User::factory()->create();
        $examinedUser->assignRole('Student');
        $examinedStudent = $examinedUser->studentProfile()->create([
            'program_id' => $program->id,
            'level_id' => $level->id,
            'cohort_id' => $cohort->id,
            'student_id_number' => 'SE-EXAMINED',
            'enrollment_status' => 'active',
            'current_semester' => 2,
        ]);

        \App\Models\ThesisProject::create([
            'student_profile_id' => $examinedStudent->id,
            'internal_examiner_profile_id' => $internalProfile->id,
            'title' => 'Null-Safe Multi-Role Verification Thesis',
            'status' => 'active',
        ]);

        $response = $this->actingAs($faculty)->get('/dashboard?role=Internal%20Examiner');

        $response->assertStatus(200);
        $response->assertSee('Null-Safe Multi-Role Verification Thesis');
    }
}
