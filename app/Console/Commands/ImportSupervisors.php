<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Supervisor;
use Illuminate\Console\Command;
use Shuchkin\SimpleXLSX;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class ImportSupervisors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:supervisors {path?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import supervisors from an Excel file in the storage/app/imports directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->argument('path') ?? storage_path('app/imports/supervisors.xlsx');

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return;
        }

        $this->info("Parsing Excel file: {$path}");

        if ( $xlsx = SimpleXLSX::parse($path) ) {
            $rows = $xlsx->rows(1); // Read from the second sheet "Supervosors Details"
            
            $this->info("Found " . count($rows) . " rows to process.");
            
            // Delete existing supervisors
            $this->info("Deleting all existing supervisors from database to start fresh...");
            $supervisors = User::role('Supervisor')->get();
            foreach ($supervisors as $sup) {
                Supervisor::where('user_id', $sup->id)->delete();
                $sup->delete();
            }

            Role::firstOrCreate(['name' => 'Supervisor']);

            $count = 0;
            $currentDepartment = 'ACETEL';

            foreach ($rows as $index => $row) {
                // Determine if this is a section header (e.g. "Artificial Intelligence")
                // Usually it's in column 0 or 1 and other columns are empty
                $colA = trim((string)($row[0] ?? ''));
                $colB = trim((string)($row[1] ?? ''));
                $colC = trim((string)($row[2] ?? ''));

                if (!empty($colB) && empty($colC) && empty($colA)) {
                    if (in_array(strtolower($colB), ['artificial intelligence', 'cyber security', 'management information system', 'management information systems'])) {
                        $currentDepartment = $colB;
                        continue;
                    }
                }

                // Skip rows that are empty or are headers
                if (empty($colB) || strtolower($colB) === 'supervisor name' || str_contains(strtolower($colB), 'list of acetel')) {
                    continue;
                }
                
                $name = $colB;
                $email = $colC;
                
                // Fallback for weird formats - just look for an @ symbol to find the email
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    foreach ($row as $cell) {
                        if (is_string($cell) && filter_var(trim($cell), FILTER_VALIDATE_EMAIL)) {
                            $email = trim($cell);
                            break;
                        }
                    }
                }

                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    // Generate a fake email based on name if none exists
                    $slug = Str::slug($name);
                    $email = "{$slug}@acetel.edu.ng";
                }

                if (strlen($name) < 2) continue;

                $user = User::firstOrCreate(
                    ['email' => strtolower($email)],
                    [
                        'name' => $name,
                        'password' => bcrypt('password'), // Default password
                    ]
                );

                if (!$user->hasRole('Supervisor')) {
                    $user->assignRole('Supervisor');
                }
                
                // Create their Supervisor profile record
                Supervisor::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'department' => $currentDepartment
                    ]
                );

                $count++;
            }

            $this->info("Successfully imported/updated {$count} supervisors!");
        } else {
            $this->error(SimpleXLSX::parseError());
        }
    }
}
