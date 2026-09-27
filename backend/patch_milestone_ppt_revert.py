import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/app/Http/Controllers/MilestoneController.php'
with open(path, 'r') as f:
    content = f.read()

# Replace validation rules
old_val = r"""        if (in_array('ppt', $subTypes)) {
            $existingCount = $milestone->submissions()->where('type', 'ppt')->count();
            $maxAllowed = 5 - $existingCount;
            $rules['ppt'] = [($existingCount > 0 ? 'nullable' : 'required'), 'array', 'min:1', "max:{$maxAllowed}"];
            $rules['ppt.*'] = 'file|mimes:pdf|max:51200';
        }"""

new_val = r"""        if (in_array('ppt', $subTypes)) {
            $rules['ppt'] = ['required', 'file', 'mimes:pdf', 'max:51200'];
        }"""

content = content.replace(old_val, new_val)

# Replace upload logic
old_upload = r"""        // Handle PPT Upload
        if (in_array('ppt', $subTypes) && $request->hasFile('ppt')) {
            foreach ($request->file('ppt') as $pptFile) {
                $pptPath = $pptFile->store('submissions/' . $milestone->thesis_project_id . '/ppt', 'public');

                $milestone->submissions()->create([
                    'submitted_by' => Auth::id(),
                    'type' => 'ppt',
                    'file_url' => $pptPath,
                    'file_meta' => [
                        'original_name' => $pptFile->getClientOriginalName(),
                        'mime_type' => $pptFile->getMimeType(),
                        'size' => $pptFile->getSize(),
                    ],
                    'checksum' => md5_file($pptFile->getRealPath()),
                    'description' => 'Presentation Slide Deck',
                    'version' => $milestone->submissions()->where('type', 'ppt')->count() + 1,
                ]);
            }
        }"""

new_upload = r"""        // Handle PPT Upload
        if (in_array('ppt', $subTypes) && $request->hasFile('ppt')) {
            $pptFile = $request->file('ppt');
            $pptPath = $pptFile->store('submissions/' . $milestone->thesis_project_id . '/ppt', 'public');

            $milestone->submissions()->create([
                'submitted_by' => Auth::id(),
                'type' => 'ppt',
                'file_url' => $pptPath,
                'file_meta' => [
                    'original_name' => $pptFile->getClientOriginalName(),
                    'mime_type' => $pptFile->getMimeType(),
                    'size' => $pptFile->getSize(),
                ],
                'checksum' => md5_file($pptFile->getRealPath()),
                'description' => 'Presentation Slide Deck',
                'version' => $milestone->submissions()->where('type', 'ppt')->count() + 1,
            ]);
        }"""

content = content.replace(old_upload, new_upload)

with open(path, 'w') as f:
    f.write(content)
