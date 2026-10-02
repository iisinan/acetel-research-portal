<?php
$content = file_get_contents('app/Services/MilestoneWorkflowService.php');

$search = "    public function isApprovalThresholdMet(StudentMilestone \$milestone): bool
    {
        \$template = \$milestone->template;
        \$requiredRoles = \$template->required_approvers ?? [];
        \$approvals = collect(\$milestone->approvals ?? []);
        
        // 1. Role-based check";

$replace = "    public function isApprovalThresholdMet(StudentMilestone \$milestone): bool
    {
        \$template = \$milestone->template;
        \$requiredRoles = \$template->required_approvers ?? [];
        \$approvals = collect(\$milestone->approvals ?? []);
        
        // Ensure structural requirements are met before allowing final clearance
        if (\$template->show_supervisor_assignment && \$milestone->thesis->assignments()->where('status', 'active')->count() === 0) return false;
        if (\$template->show_internal_examiner_assignment && empty(\$milestone->thesis->internal_examiner_profile_id)) return false;
        if (\$template->show_external_examiner_assignment && empty(\$milestone->thesis->external_examiner_profile_id)) return false;
        if (\$template->allow_defence_date && (empty(\$milestone->defence_date) || empty(\$milestone->date_approved_at))) return false;

        // 1. Role-based check";

$content = str_replace($search, $replace, $content);
file_put_contents('app/Services/MilestoneWorkflowService.php', $content);
