<?php

namespace App\Services;

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\SupervisorProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AuthService
{
    public function register(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'must_change_password' => true,
            ]);

            $user->assignRole($data['role']);

            if ($data['role'] === 'Student') {
                $this->createStudentProfile($user, $data);
            } elseif ($data['role'] === 'Supervisor') {
                $this->createSupervisorProfile($user, $data);
            }

            return $user;
        });
    }

    protected function createStudentProfile(User $user, array $data)
    {
        StudentProfile::create([
            'user_id' => $user->id,
            'program_id' => $data['program_id'] ?? null,
            'cohort_id' => $data['cohort_id'] ?? null,
            'student_id_number' => $data['student_id_number'],
            // Defaults
        ]);
    }

    protected function createSupervisorProfile(User $user, array $data)
    {
        SupervisorProfile::create([
            'user_id' => $user->id,
            'department_id' => $data['department_id'] ?? null,
            'staff_id' => $data['staff_id'],
        ]);
    }
}
