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
        Role::firstOrCreate(['name' => 'Super Admin']);
        Role::firstOrCreate(['name' => 'Coordinator']);
        Role::firstOrCreate(['name' => 'Supervisor']);
        Role::firstOrCreate(['name' => 'Student']);
        Role::firstOrCreate(['name' => 'Examiner']);

        // Programmes
        Programme::firstOrCreate(['code' => 'AI'],   ['name' => 'Artificial Intelligence']);
        Programme::firstOrCreate(['code' => 'CYB'],  ['name' => 'Cybersecurity']);
        Programme::firstOrCreate(['code' => 'MIS'],  ['name' => 'Management Information Systems']);

        // Degrees
        Degree::firstOrCreate(['name' => 'MSc'], ['required_supervisors' => 2]);
        Degree::firstOrCreate(['name' => 'PhD'], ['required_supervisors' => 3]);

        // Intakes
        Intake::firstOrCreate(['name' => 'First Intake']);
        Intake::firstOrCreate(['name' => 'Second Intake']);

        // Milestones
        $milestones = [
            ['name' => 'Registered',             'order_index' => 1],
            ['name' => 'Seminar Course',          'order_index' => 2],
            ['name' => 'Supervisors Assigned',    'order_index' => 3],
            ['name' => 'Proposal Defence',        'order_index' => 4],
            ['name' => 'Progress Presentation 1', 'order_index' => 5],
            ['name' => 'Progress Presentation 2', 'order_index' => 6],
            ['name' => 'Internal Defence',        'order_index' => 7],
            ['name' => 'Viva',                    'order_index' => 8],
            ['name' => 'Completed',               'order_index' => 9],
        ];

        foreach ($milestones as $m) {
            Milestone::firstOrCreate(['name' => $m['name']], $m);
        }

        // Setup Super Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@acetel.edu.ng'],
            [
                'name'     => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );
        $admin->syncRoles(['Super Admin']);

        // Setup Sample Supervisors
        $supervisor1 = User::firstOrCreate(
            ['email' => 'dr.smith@acetel.edu.ng'],
            [
                'name'     => 'Dr. John Smith',
                'password' => bcrypt('password'),
            ]
        );
        $supervisor1->syncRoles(['Supervisor']);

        $supervisor2 = User::firstOrCreate(
            ['email' => 'prof.doe@acetel.edu.ng'],
            [
                'name'     => 'Prof. Jane Doe',
                'password' => bcrypt('password'),
            ]
        );
        $supervisor2->syncRoles(['Supervisor']);
    }
}
