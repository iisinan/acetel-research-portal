import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/app/Http/Controllers/Admin/MilestoneTemplateController.php'
with open(path, 'r') as f:
    content = f.read()

old_code = r"""        // Notify the examiners once
        foreach ($supervisors as $supervisor) {
            $supervisor->user->notify(new \App\Notifications\EventScheduled(
                \App\Models\DefenceEvent::where('type', $template->defence_type ?? 'seminar')
                    ->latest()
                    ->first()
            ));
        }"""

new_code = r"""        // Notify the examiners once
        try {
            $latestEvent = \App\Models\DefenceEvent::where('type', $template->defence_type ?? 'seminar')
                ->latest()
                ->first();
                
            if ($latestEvent) {
                foreach ($supervisors as $supervisor) {
                    $supervisor->user->notify(new \App\Notifications\EventScheduled($latestEvent));
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Notification failed in assignExaminerGlobal: ' . $e->getMessage());
        }"""

if old_code in content:
    content = content.replace(old_code, new_code)
    with open(path, 'w') as f:
        f.write(content)
    print("Patched successfully")
else:
    print("Old code not found")
