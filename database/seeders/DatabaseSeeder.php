<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Programme;
use App\Models\Degree;
use App\Models\Intake;
use App\Models\Milestone;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Coordinator']);
        Role::create(['name' => 'Supervisor']);
        Role::create(['name' => 'Student']);

        // Programmes
        Programme::create(['name' => 'Artificial Intelligence', 'code' => 'AI']);
        Programme::create(['name' => 'Cybersecurity', 'code' => 'CYB']);
        Programme::create(['name' => 'Management Information Systems', 'code' => 'MIS']);

        // Degrees
        Degree::create(['name' => 'MSc', 'required_supervisors' => 2]);
        Degree::create(['name' => 'PhD', 'required_supervisors' => 3]);

        // Intakes
        Intake::create(['name' => 'First Intake']);
        Intake::create(['name' => 'Second Intake']);

        // Milestones
        $milestones = [
            ['name' => 'Registered', 'order_index' => 1],
            ['name' => 'Seminar Course', 'order_index' => 2],
            ['name' => 'Supervisors Assigned', 'order_index' => 3],
            ['name' => 'Proposal Defence', 'order_index' => 4],
            ['name' => 'Progress Presentation 1', 'order_index' => 5],
            ['name' => 'Progress Presentation 2', 'order_index' => 6],
            ['name' => 'Internal Defence', 'order_index' => 7],
            ['name' => 'Viva', 'order_index' => 8],
            ['name' => 'Completed', 'order_index' => 9],
        ];

        foreach ($milestones as $m) {
            Milestone::create($m);
        }
        
        // Setup Super Admin User
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@acetel.edu.ng',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('Super Admin');
    }
}
