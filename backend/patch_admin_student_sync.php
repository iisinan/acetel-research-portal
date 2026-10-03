<?php
$content = file_get_contents('app/Http/Controllers/Admin/StudentController.php');

$search = <<<EOT
    public function show(StudentProfile \$student)
    {
        \$student->load([
EOT;

$replace = <<<EOT
    public function show(StudentProfile \$student)
    {
        if (\$student->thesis) {
            \$templatesCount = \\App\\Models\\MilestoneTemplate::whereNull('program_id')
                ->orWhere('program_id', \$student->program_id)->count();
                
            if (\$student->thesis->milestones()->count() < \$templatesCount) {
                \$student->thesis->syncMilestones();
            }
        }

        \$student->load([
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents('app/Http/Controllers/Admin/StudentController.php', $content);
