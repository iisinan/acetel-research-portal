<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. RBAC Reset & Setup
        $this->call([
            RolesAndPermissionsSeeder::class,
            MilestoneTemplateSeeder::class,
        ]);
        
        // 2. Setup Academic Structure
        $mscLevel = \App\Models\Level::firstOrCreate(['name' => 'MSc']);
        $phdLevel = \App\Models\Level::firstOrCreate(['name' => 'PhD']);

        $progAI = \App\Models\Program::updateOrCreate(['code' => 'AI'], ['name' => 'Artificial Intelligence']);
        $progCyber = \App\Models\Program::updateOrCreate(['code' => 'CYBER'], ['name' => 'Cybersecurity']);
        $progMIS = \App\Models\Program::updateOrCreate(['code' => 'MIS'], ['name' => 'MIS']);

        $cohort = \App\Models\Cohort::firstOrCreate([
            'code' => '2025/2026',
        ], [
            'name' => '2025/2026',
            'intake_year' => 2025,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addYear()
        ]);


        // 3. Create Users

        // Admin
        $admin = User::updateOrCreate([
            'email' => 'isinan@noun.edu.ng',
        ], [
            'name' => 'ACETEL Administrator', 
            'password' => bcrypt('Sinan3367#'),
            'is_active' => true
        ]);
        $admin->syncRoles(['Admin']);

        // Dummy Program Coordinator
        $coordUser = User::updateOrCreate(['email' => 'coordinator@noun.edu.ng'], [
            'name' => 'Dr. Coordinator', 'password' => bcrypt('password'), 'is_active' => true
        ]);
        $coordUser->syncRoles(['Program Coordinator']);

        // Dummy Supervisor
        $supervisorUser = User::updateOrCreate(['email' => 'supervisor@noun.edu.ng'], [
            'name' => 'Prof. Supervisor', 'password' => bcrypt('password'), 'is_active' => true
        ]);
        $supervisorUser->syncRoles(['Supervisor']);
        if (!$supervisorUser->supervisorProfile) {
            $supervisorUser->supervisorProfile()->create([
                'staff_id' => 'STF123456',
                'max_students' => 5,
                'specialization' => 'Artificial Intelligence'
            ]);
            $supervisorUser->load('supervisorProfile');
        }

        // Dummy Students
        if (User::role('Student')->count() < 2) {
            $student1 = $this->createStudent('Alice Scholar', 'student1@noun.edu.ng', $progAI, $mscLevel, $cohort, 'NOU123456789');
            $thesis1 = $this->createThesis($student1, 'Advanced Machine Learning in Cybersecurity', 'active');
            $this->assignSupervisor($thesis1, $supervisorUser);

            $student2 = $this->createStudent('Bob Researcher', 'student2@noun.edu.ng', $progCyber, $phdLevel, $cohort, 'NOU987654321');
            $this->createThesis($student2, 'Quantum Cryptography for Modern Networks', 'proposed');
        }

    }

    private function createStudent($name, $email, $program, $level, $cohort, $idNumber) {
        $user = User::updateOrCreate(['email' => $email], ['name' => $name, 'password' => bcrypt('password')]);
        $user->assignRole('Student');
        if (!$user->studentProfile) {
            return $user->studentProfile()->create([
                'program_id' => $program->id, 'level_id' => $level->id, 'cohort_id' => $cohort->id,
                'student_id_number' => $idNumber, 'enrollment_status' => 'active', 'current_semester' => 2
            ]);
        }
        return $user->studentProfile;
    }


    private function createThesis($studentProfile, $title, $status) {
        $thesis = \App\Models\ThesisProject::create([
            'student_profile_id' => $studentProfile->id,
            'title' => $title,
            'abstract' => 'Lorem ipsum description...',
            'status' => $status,
            'start_date' => now(),
        ]);
        
        // Manually sync milestones since model events are disabled in the seeder
        $thesis->syncMilestones();
        
        return $thesis;
    }

    private function assignSupervisor($thesis, $supervisorUser) {
        \App\Models\SupervisionAssignment::create([
            'thesis_project_id' => $thesis->id,
            'supervisor_profile_id' => $supervisorUser->supervisorProfile->id,
            'role' => 'primary',
            'status' => 'active',
            'assigned_at' => now(),
        ]);
    }
}
