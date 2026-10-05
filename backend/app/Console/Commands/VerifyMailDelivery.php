<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class VerifyMailDelivery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:verify {email? : The recipient email address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test and verify SMTP email delivery configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipient = $this->argument('email') ?? config('mail.from.address');

        $this->info("=== ACETEL TMS Mail Delivery Diagnostic ===");
        $this->table(
            ['Configuration Key', 'Value'],
            [
                ['MAIL_MAILER', config('mail.default')],
                ['MAIL_HOST', config('mail.mailers.smtp.host')],
                ['MAIL_PORT', config('mail.mailers.smtp.port')],
                ['MAIL_ENCRYPTION', config('mail.mailers.smtp.encryption') ?? 'none'],
                ['MAIL_USERNAME', config('mail.mailers.smtp.username') ? (substr(config('mail.mailers.smtp.username'), 0, 3) . '***') : 'not set'],
                ['MAIL_FROM_ADDRESS', config('mail.from.address')],
                ['MAIL_FROM_NAME', config('mail.from.name')],
                ['Test Recipient', $recipient],
            ]
        );

        $this->line("");
        $this->info("Dispatching verification email to: {$recipient} ...");

        try {
            Mail::raw("This is an automated test message dispatched by ACETEL Thesis Monitoring System to verify SMTP inbox delivery.\n\nTimestamp: " . now()->toIso8601String(), function (Message $message) use ($recipient) {
                $message->to($recipient)
                    ->subject('SMTP Verification Test - ACETEL TMS');
            });

            $this->info("✓ Email successfully dispatched to {$recipient}!");
            if (config('mail.default') === 'log') {
                $this->warn("Note: MAIL_MAILER is currently set to 'log', so the email was written to storage/logs/laravel.log rather than an external inbox.");
            } else {
                $this->info("Please check the inbox and spam folder for {$recipient}.");
            }
            return 0;
        } catch (\Throwable $e) {
            $this->error("✗ Mail dispatch failed!");
            $this->error("Error: " . $e->getMessage());
            $this->line("");
            $this->warn("Troubleshooting suggestions:");
            $this->line("1. Verify your MAIL_HOST, MAIL_PORT (typically 587 for TLS, 465 for SSL), and MAIL_ENCRYPTION.");
            $this->line("2. Ensure MAIL_USERNAME and MAIL_PASSWORD are valid.");
            $this->line("3. If using Gmail, ensure 2-Step Verification is enabled and use a 16-character 'App Password'.");
            $this->line("4. Check that outbound SMTP ports are not blocked by the hosting firewall.");
            return 1;
        }
    }
}
