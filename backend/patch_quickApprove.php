<?php
$content = file_get_contents('app/Http/Controllers/MilestoneController.php');

$search = "        \$type = \$request->type;
        \$approvals = \$milestone->approvals ?? [];

        if (\$type === 'clear_supervisor') {";

$replace = "        \$type = \$request->type;
        \$approvals = \$milestone->approvals ?? [];

        \$workflowService = app(\\App\\Services\\MilestoneWorkflowService::class);
        \$roleFilled = \$type === 'clear_role' ? \$request->role : (\$type === 'clear_supervisor' ? 'Supervisor' : null);
        
        // Ensure we can actually approve this based on structural constraints
        if (\$type !== 'approve_date') {
            \$error = \$workflowService->canApprove(\$milestone, \$user, \$roleFilled);
            if (\$error && !str_contains(\$error, 'Sequence Blocked')) {
                return response()->json(['success' => false, 'message' => \$error], 403);
            }
        }

        if (\$type === 'clear_supervisor') {";

$content = str_replace($search, $replace, $content);
file_put_contents('app/Http/Controllers/MilestoneController.php', $content);
