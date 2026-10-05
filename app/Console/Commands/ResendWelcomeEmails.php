<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUser;

class ResendWelcomeEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:resend-welcome {--email= : Send to a specific student email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend welcome emails to students with new passwords';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');

        $query = User::role('Student');

        if ($email) {
            $query->where('email', $email);
        }

        $students = $query->get();

        if ($students->isEmpty()) {
            $this->warn('No students found.');
            return;
        }

        if (!$this->confirm('This will generate new passwords and send emails to ' . $students->count() . ' students. Continue?')) {
            $this->info('Aborted.');
            return;
        }

        $bar = $this->output->createProgressBar($students->count());
        $bar->start();

        $successCount = 0;
        $failCount = 0;

        foreach ($students as $student) {
            try {
                $newPassword = 'ACETEL-' . rand(100000, 999999);
                $student->password = Hash::make($newPassword);
                $student->save();

                Mail::to($student->email)->queue(new WelcomeUser($student, $newPassword));
                $successCount++;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to resend welcome email for {$student->email}: " . $e->getMessage());
                $failCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Completed. Successfully sent: {$successCount}. Failed: {$failCount}.");
    }
}
