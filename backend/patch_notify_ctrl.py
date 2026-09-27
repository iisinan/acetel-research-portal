import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/app/Http/Controllers/Admin/MilestoneTemplateController.php'
with open(path, 'r') as f:
    content = f.read()

# 1. schedule method
old_schedule_loop = r"""                \App\Models\DefenceEvent::updateOrCreate(
                    [
                        'thesis_project_id' => $thesis->id,
                        'type' => $template->defence_type ?? 'seminar',
                    ],
                    [
                        'schedule_start' => $currentDate->copy()->setHour(9)->setMinute(0),
                        'schedule_end' => $currentDate->copy()->setHour(10)->setMinute(0),
                    ]
                );

                $count++;"""

new_schedule_loop = r"""                $event = \App\Models\DefenceEvent::updateOrCreate(
                    [
                        'thesis_project_id' => $thesis->id,
                        'type' => $template->defence_type ?? 'seminar',
                    ],
                    [
                        'schedule_start' => $currentDate->copy()->setHour(9)->setMinute(0),
                        'schedule_end' => $currentDate->copy()->setHour(10)->setMinute(0),
                    ]
                );
                
                if ($thesis->student && $thesis->student->user) {
                    $thesis->student->user->notify(new \App\Notifications\EventScheduled($event));
                }

                $count++;"""
content = content.replace(old_schedule_loop, new_schedule_loop)


# 2. assignExaminer method
old_assign = r"""        \App\Models\PanelMember::create([
            'defence_event_id' => $event->id,
            'user_id' => $supervisor->user_id,
            'role' => 'Examiner',
            'invitation_status' => 'accepted'
        ]);

        return back()->with('success', 'Examiner assigned successfully.');"""

new_assign = r"""        \App\Models\PanelMember::create([
            'defence_event_id' => $event->id,
            'user_id' => $supervisor->user_id,
            'role' => 'Examiner',
            'invitation_status' => 'accepted'
        ]);
        
        $supervisor->user->notify(new \App\Notifications\EventScheduled($event));

        return back()->with('success', 'Examiner assigned successfully.');"""
content = content.replace(old_assign, new_assign)

with open(path, 'w') as f:
    f.write(content)
