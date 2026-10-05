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
    protected $description = 'Test and verify email delivery configuration (Resend API or SMTP)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipient = $this->argument('email') ?? config('mail.from.address');
        $mailer = config('mail.default');

        $rows = [
            ['MAIL_MAILER', $mailer],
            ['MAIL_FROM_ADDRESS', config('mail.from.address')],
            ['MAIL_FROM_NAME', config('mail.from.name')],
        ];

        if ($mailer === 'resend') {
            $key = config('mail.mailers.resend.key') ?? config('services.resend.key');
            $rows[] = ['RESEND_API_KEY', $key ? (substr($key, 0, 6) . '...' . substr($key, -4)) : 'NOT SET'];
        } elseif ($mailer === 'smtp') {
            $rows[] = ['MAIL_HOST', config('mail.mailers.smtp.host')];
            $rows[] = ['MAIL_PORT', config('mail.mailers.smtp.port')];
            $rows[] = ['MAIL_ENCRYPTION', config('mail.mailers.smtp.encryption') ?? 'none'];
            $rows[] = ['MAIL_USERNAME', config('mail.mailers.smtp.username') ? (substr(config('mail.mailers.smtp.username'), 0, 3) . '***') : 'not set'];
        }

        $rows[] = ['Test Recipient', $recipient];

        $this->info("=== ACETEL TMS Mail Delivery Diagnostic ===");
        $this->table(['Configuration Key', 'Value'], $rows);

        $this->line("");
        $this->info("Dispatching verification email to: {$recipient} via [{$mailer}] transport...");

        try {
            Mail::raw("This is an automated test message dispatched by ACETEL Thesis Monitoring System to verify inbox delivery via " . strtoupper($mailer) . ".\n\nTimestamp: " . now()->toIso8601String(), function (Message $message) use ($recipient, $mailer) {
                $message->to($recipient)
                    ->subject('Mail Delivery Test (' . strtoupper($mailer) . ') - ACETEL TMS');
            });

            $this->info("✓ Email successfully dispatched to {$recipient}!");
            if ($mailer === 'log') {
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
            if ($mailer === 'resend') {
                $this->line("1. Verify your RESEND_API_KEY is active on https://resend.com/api-keys.");
                $this->line("2. Ensure your MAIL_FROM_ADDRESS domain is verified in Resend (e.g. nou.edu.ng or onboarding@resend.dev).");
                $this->line("3. If using unverified domain in test mode, you can only send to your own registered Resend account email.");
            } else {
                $this->line("1. Verify your MAIL_HOST, MAIL_PORT (typically 587 for TLS, 465 for SSL), and MAIL_ENCRYPTION.");
                $this->line("2. Ensure MAIL_USERNAME and MAIL_PASSWORD are valid.");
                $this->line("3. Check that outbound SMTP ports are not blocked by the hosting firewall.");
            }
            return 1;
        }
    }
}
