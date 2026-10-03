<?php
$content = file_get_contents('app/Models/StudentMilestone.php');

$newMethod = <<<EOT
    public function getIsSupervisorApprovedAttribute()
    {
        if (\$this->status === 'approved') return true;
        \$approvals = collect(\$this->approvals ?? []);
        return \$approvals->where('role', 'Supervisor')->isNotEmpty();
    }
EOT;

$content = preg_replace('/public function messages\(\)/', $newMethod . "\n\n    public function messages()", $content);
file_put_contents('app/Models/StudentMilestone.php', $content);
