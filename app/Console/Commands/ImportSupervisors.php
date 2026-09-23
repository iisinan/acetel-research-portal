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
            $rows = $xlsx->rows();
            $header = array_shift($rows); // Remove header row
            
            $this->info("Found " . count($rows) . " rows to process.");
            $count = 0;

            Role::firstOrCreate(['name' => 'Supervisor']);

            foreach ($rows as $index => $row) {
                // Assuming columns: 0 => Name, 1 => Email, 2 => Phone, etc.
                // We need to inspect the header to map correctly if it's dynamic, 
                // but let's try to extract standard fields first.
                
                $name = $row[1] ?? 'Unknown Name'; // Usually Name is the second column after S/N
                $email = $row[2] ?? '';            // Usually Email is the third
                
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
                    // Generate a fake email based on name if none exists, just to allow creation
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
                
                // Also create their Supervisor profile record if it doesn't exist
                Supervisor::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'department' => 'ACETEL'
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
