<?php

use Illuminate\Database\Migrations\Migration;
use App\Services\UserAccountMergeService;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Consolidates Prof. Adebukola Onashoga's duplicate accounts (onashogasa@gmail.com)
     * into her official institutional account (onashogasa@funaab.edu.ng).
     */
    public function up(): void
    {
        try {
            $mergeService = app(UserAccountMergeService::class);
            $result = $mergeService->merge('onashogasa@gmail.com', 'onashogasa@funaab.edu.ng');
            Log::info("Migration merge_prof_onashoga_accounts result: " . json_encode($result));
        } catch (\Throwable $e) {
            Log::error("Migration merge_prof_onashoga_accounts encountered error: " . $e->getMessage());
            // Do not break migration pipeline if accounts were already merged manually
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Account merge cannot be automatically un-merged.
    }
};
