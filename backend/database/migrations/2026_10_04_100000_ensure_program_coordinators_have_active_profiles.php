<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\CoordinatorProfile;
use App\Models\Program;
use App\Models\Level;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // 1. Activate any inactive or null active coordinator profiles
            CoordinatorProfile::where('active', false)->orWhereNull('active')->update(['active' => true]);

            // 2. Find all users who currently have the 'Program Coordinator' role
            $coordinators = User::role('Program Coordinator')->get();
            $allPrograms = Program::all();
            $allLevels = Level::all();

            if ($allPrograms->isEmpty()) {
                return;
            }

            foreach ($coordinators as $coordinator) {
                $hasActiveProfiles = $coordinator->coordinatorProfiles()->where('active', true)->exists();
                if ($hasActiveProfiles) {
                    continue;
                }

                // Check if coordinator is also a supervisor with assigned programs
                $supervisorPrograms = $coordinator->supervisorProfile?->programs;
                $targetPrograms = ($supervisorPrograms && $supervisorPrograms->isNotEmpty()) 
                    ? $supervisorPrograms 
                    : $allPrograms;

                foreach ($targetPrograms as $prog) {
                    if ($allLevels->isNotEmpty()) {
                        foreach ($allLevels as $lvl) {
                            CoordinatorProfile::firstOrCreate([
                                'user_id' => $coordinator->id,
                                'program_id' => $prog->id,
                                'level_id' => $lvl->id,
                            ], ['active' => true]);
                        }
                    } else {
                        CoordinatorProfile::firstOrCreate([
                            'user_id' => $coordinator->id,
                            'program_id' => $prog->id,
                            'level_id' => null,
                        ], ['active' => true]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Log but do not fail migration if tables not yet populated
            \Illuminate\Support\Facades\Log::warning('Coordinator profile backfill notice: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op on rollback
    }
};
