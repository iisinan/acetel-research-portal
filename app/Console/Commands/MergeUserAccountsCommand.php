<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UserAccountMergeService;

class MergeUserAccountsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:merge {source : The email of the duplicate account to merge and remove} {target : The email of the primary account to retain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consolidate two user accounts into one, transferring all profiles, assignments, evaluations, and history.';

    /**
     * Execute the console command.
     */
    public function handle(UserAccountMergeService $mergeService)
    {
        $source = $this->argument('source');
        $target = $this->argument('target');

        $this->info("Merging user {$source} into {$target}...");

        try {
            $result = $mergeService->merge($source, $target);

            if ($result['success']) {
                $this->info($result['message']);
                if (!empty($result['report'])) {
                    $this->table(
                        ['Entity', 'Count / Details'],
                        collect($result['report'])->map(fn($val, $key) => [
                            $key, 
                            is_array($val) ? implode(', ', $val) : (string)$val
                        ])
                    );
                }
                return Command::SUCCESS;
            } else {
                $this->warn($result['message']);
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Failed to merge accounts: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
