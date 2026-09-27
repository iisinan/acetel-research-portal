<?php
$content = file_get_contents('app/Http/Requests/Admin/RegisterStudentRequest.php');
$old = "'program_id' => 'required_if:type,single|nullable|exists:programs,id',";
$new = "'program_id' => 'required_if:type,single|nullable|exists:programs,id',
            'phone_number' => 'required_if:type,single|nullable|string|max:20',
            'gender' => 'required_if:type,single|nullable|string|in:Male,Female',
            'nationality' => 'required_if:type,single|nullable|string|max:100',
            'place_of_work' => 'nullable|string|max:255',";
$content = str_replace($old, $new, $content);
file_put_contents('app/Http/Requests/Admin/RegisterStudentRequest.php', $content);

$content = file_get_contents('app/Http/Controllers/Coordinator/CohortController.php');
// Patch single
$oldSingle = "                'student_id_number' => \$request->matrix_number,
                'enrollment_status' => 'active',
                'current_semester' => 1,
            ]);";
$newSingle = "                'student_id_number' => \$request->matrix_number,
                'enrollment_status' => 'active',
                'current_semester' => 1,
                'phone_number' => \$request->phone_number,
                'gender' => \$request->gender,
                'nationality' => \$request->nationality,
                'place_of_work' => \$request->place_of_work,
            ]);";
$content = str_replace($oldSingle, $newSingle, $content);

// Patch bulk
$oldBulkExtract = <<<EOD
                \$name = trim(\$data[0] ?? '');
                \$email = trim(\$data[1] ?? '');
                \$programSearch = trim(\$data[2] ?? '');
                \$matrixNumber = trim(\$data[3] ?? '');
EOD;
$newBulkExtract = <<<EOD
                \$name = trim(\$data[0] ?? '');
                \$email = trim(\$data[1] ?? '');
                \$programSearch = trim(\$data[2] ?? '');
                \$matrixNumber = trim(\$data[3] ?? '');
                \$gender = trim(\$data[4] ?? '');
                \$phone = trim(\$data[5] ?? '');
                \$nationality = trim(\$data[6] ?? '');
                \$placeOfWork = trim(\$data[7] ?? '');
EOD;
$content = str_replace($oldBulkExtract, $newBulkExtract, $content);

$oldBulkSave = "                \$profile = \$user->studentProfile()->create([
                    'cohort_id' => \$cohort->id,
                    'program_id' => \$program->id,
                    'student_id_number' => \$matrixNumber,
                    'enrollment_status' => 'active',
                    'current_semester' => 1,
                ]);";
$newBulkSave = "                \$profile = \$user->studentProfile()->create([
                    'cohort_id' => \$cohort->id,
                    'program_id' => \$program->id,
                    'student_id_number' => \$matrixNumber,
                    'enrollment_status' => 'active',
                    'current_semester' => 1,
                    'gender' => !empty(\$gender) ? \$gender : null,
                    'phone_number' => !empty(\$phone) ? \$phone : null,
                    'nationality' => !empty(\$nationality) ? \$nationality : null,
                    'place_of_work' => !empty(\$placeOfWork) ? \$placeOfWork : null,
                ]);";
$content = str_replace($oldBulkSave, $newBulkSave, $content);

file_put_contents('app/Http/Controllers/Coordinator/CohortController.php', $content);
echo "Patched admin registration workflows!\n";
