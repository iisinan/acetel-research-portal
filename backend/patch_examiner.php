<?php
$file = 'backend/app/Http/Controllers/Coordinator/ExaminerPoolController.php';
$content = file_get_contents($file);

$internalReplacement = <<<PHP
InternalExaminerProfile::firstOrCreate([
            'user_id' => \$supervisor->user_id,
            'program_id' => \$coordinatorProfile->program_id,
        ]);

        if (!\$supervisor->user->hasRole('Internal Examiner')) {
            \$supervisor->user->assignRole('Internal Examiner');
        }
PHP;

$content = str_replace(<<<PHP
InternalExaminerProfile::firstOrCreate([
            'user_id' => \$supervisor->user_id,
            'program_id' => \$coordinatorProfile->program_id,
        ]);
PHP, $internalReplacement, $content);

$externalReplacement = <<<PHP
ExternalExaminerProfile::firstOrCreate([
            'user_id' => \$supervisor->user_id,
        ], [
            'institution' => \$request->institution,
            'expertise' => \$supervisor->specialization,
        ]);

        if (!\$supervisor->user->hasRole('External Examiner')) {
            \$supervisor->user->assignRole('External Examiner');
        }
PHP;

$content = str_replace(<<<PHP
ExternalExaminerProfile::firstOrCreate([
            'user_id' => \$supervisor->user_id,
        ], [
            'institution' => \$request->institution,
            'expertise' => \$supervisor->specialization,
        ]);
PHP, $externalReplacement, $content);

$externalCreateReplacement = <<<PHP
\$user->assignRole('External Examiner');
PHP;

$content = str_replace("\$user->assignRole('Supervisor'); // Or a specific 'Examiner' role if defined", $externalCreateReplacement, $content);

file_put_contents($file, $content);
