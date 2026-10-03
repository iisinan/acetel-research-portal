<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(\App\Services\MilestoneWorkflowService::class);
$milestones = \App\Models\StudentMilestone::where('status', 'partially_approved')->get();
$fixed = 0;
foreach($milestones as $m) {
    if ($service->isApprovalThresholdMet($m)) {
        echo "Fixing milestone ID: {$m->id}\n";
        $m->status = 'approved';
        $m->approved_at = now();
        $m->save();
        $service->afterApproval($m);
        $fixed++;
    }
}
echo "Fixed {$fixed} milestones.\n";
