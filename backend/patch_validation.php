<?php
$content = file_get_contents('app/Http/Controllers/Auth/RegisteredUserController.php');

$oldValidation = <<<EOD
        \$request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'matric_number' => ['required', 'string', 'max:255', 'unique:student_profiles,student_id_number', new \App\Rules\ValidMatricNumber],
            'program_id' => 'required|exists:programs,id',
            'place_of_work' => 'nullable|string|max:255',
            'supervisor_ids' => 'nullable|array',
            'supervisor_ids.*' => 'nullable|distinct',
            'new_supervisors' => 'nullable|array',
            'completed_milestones' => 'nullable|array',
            'completed_milestones.*' => 'exists:milestone_templates,id',
            'seminar_grade' => 'nullable|string|max:50',
            'proposal_defence_date' => 'nullable|date',
            'progress_presentation_1_date' => 'nullable|date',
            'progress_presentation_2_date' => 'nullable|date',
            'internal_defence_date' => 'nullable|date',
            'publications' => 'nullable|array',
            'publications.*.title' => 'nullable|string|max:255',
            'publications.*.authors' => 'nullable|string|max:500',
            'publications.*.abstract' => 'nullable|string|max:5000',
            'publications.*.doi' => 'nullable|string|max:255',
            'publications.*.file' => 'nullable|file|mimes:pdf|max:10240',
            'progress_presentation_1_ppt' => 'nullable|file|mimes:pdf,ppt,pptx|max:10240',
            'progress_presentation_2_ppt' => 'nullable|file|mimes:pdf,ppt,pptx|max:10240',
            'thesis_title' => 'nullable|string|max:255',
            'thesis_abstract' => 'nullable|string|max:5000',
            'internal_examiner_id' => 'nullable',
            'internal_examiner_name' => 'nullable|string|max:255',
            'internal_examiner_email' => 'nullable|email|max:255',
            'external_examiner_id' => 'nullable',
            'external_examiner_name' => 'nullable|string|max:255',
            'external_examiner_email' => 'nullable|email|max:255',
            'final_thesis_file' => 'nullable|file|mimes:pdf|max:20480',
        ]);
EOD;

$newValidation = <<<EOD
        \$validator = \Illuminate\Support\Facades\Validator::make(\$request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'matric_number' => ['required', 'string', 'max:255', 'unique:student_profiles,student_id_number', new \App\Rules\ValidMatricNumber],
            'program_id' => 'required|exists:programs,id',
            'place_of_work' => 'nullable|string|max:255',
            'supervisor_ids' => 'nullable|array',
            'supervisor_ids.*' => 'nullable|distinct',
            'new_supervisors' => 'nullable|array',
            'completed_milestones' => 'nullable|array',
            'completed_milestones.*' => 'exists:milestone_templates,id',
            'seminar_grade' => 'nullable|string|max:50',
            'proposal_defence_date' => 'nullable|date',
            'progress_presentation_1_date' => 'nullable|date',
            'progress_presentation_2_date' => 'nullable|date',
            'internal_defence_date' => 'nullable|date',
            'publications' => 'nullable|array',
            'publications.*.title' => 'nullable|string|max:255',
            'publications.*.authors' => 'nullable|string|max:500',
            'publications.*.abstract' => 'nullable|string|max:5000',
            'publications.*.doi' => 'nullable|string|max:255',
            'publications.*.file' => 'nullable|file|mimes:pdf|max:10240',
            'progress_presentation_1_ppt' => 'nullable|file|mimes:pdf,ppt,pptx|max:10240',
            'progress_presentation_2_ppt' => 'nullable|file|mimes:pdf,ppt,pptx|max:10240',
            'thesis_title' => 'nullable|string|max:255',
            'thesis_abstract' => 'nullable|string|max:5000',
            'internal_examiner_id' => 'nullable',
            'internal_examiner_name' => 'nullable|string|max:255',
            'internal_examiner_email' => 'nullable|email|max:255',
            'external_examiner_id' => 'nullable',
            'external_examiner_name' => 'nullable|string|max:255',
            'external_examiner_email' => 'nullable|email|max:255',
            'final_thesis_file' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        \$completedIds = \$request->input('completed_milestones', []);
        if (!empty(\$completedIds)) {
            \$templates = \App\Models\MilestoneTemplate::whereIn('id', \$completedIds)->get();
            \$completedSlugs = \$templates->pluck('slug')->toArray();

            \$validator->after(function (\$validator) use (\$request, \$completedSlugs) {
                if (in_array('proposal_defence', \$completedSlugs)) {
                    if (empty(\$request->thesis_title)) {
                        \$validator->errors()->add('thesis_title', 'Thesis title is required since Proposal Defence is completed.');
                    }
                    if (empty(\$request->proposal_defence_date)) {
                        \$validator->errors()->add('proposal_defence_date', 'Proposal Defence date is required.');
                    }
                }
                if (in_array('viva', \$completedSlugs)) {
                    if (!\$request->hasFile('final_thesis_file')) {
                        \$validator->errors()->add('final_thesis_file', 'The final cleared thesis PDF is required since Viva is completed.');
                    }
                    if (empty(\$request->viva_date)) {
                        \$validator->errors()->add('viva_date', 'Viva date is required.');
                    }
                }
            });
        }

        \$validator->validate();
EOD;

$content = str_replace($oldValidation, $newValidation, $content);
file_put_contents('app/Http/Controllers/Auth/RegisteredUserController.php', $content);
echo "Patched validation!\n";
