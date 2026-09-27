import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/app/Http/Controllers/Admin/MilestoneTemplateController.php'
with open(path, 'r') as f:
    content = f.read()

old_query = """        $query = \\App\\Models\\StudentMilestone::where('milestone_template_id', $template->id)
            ->whereIn('status', ['in_progress', 'submitted', 'revision_required'])
            ->with('thesis.student.user');"""

new_query = """        $query = \\App\\Models\\StudentMilestone::where('milestone_template_id', $template->id)
            ->whereIn('status', ['in_progress', 'submitted', 'revision_required'])
            ->with(['thesis.student.user', 'thesis.defenceEvents.evaluations']);"""
content = content.replace(old_query, new_query)

old_columns = """        $columns = ['Name', 'Matric Number', 'Status', 'Date Scheduled'];"""
new_columns = """        $columns = ['Name', 'Matric Number', 'Status', 'Date Scheduled', 'Score'];"""
content = content.replace(old_columns, new_columns)

old_foreach = """            foreach ($milestones as $milestone) {
                fputcsv($file, [
                    $milestone->thesis->student->user->name ?? '',
                    $milestone->thesis->student->matric_number ?? '',
                    $milestone->status,
                    $milestone->defence_date ?? 'Not Scheduled'
                ]);
            }"""

new_foreach = """            foreach ($milestones as $milestone) {
                $avgScore = 'N/A';
                if ($template->slug === 'seminar_as_a_course') {
                    $event = current($milestone->thesis->defenceEvents->where('type', $template->defence_type ?? 'seminar')->all());
                    if ($event && $event->evaluations->count() > 0) {
                        $total = 0;
                        $count = 0;
                        foreach($event->evaluations as $eval) {
                            if (isset($eval->score['total'])) {
                                $total += $eval->score['total'];
                                $count++;
                            }
                        }
                        if ($count > 0) {
                            $avgScore = round($total / $count, 1);
                        }
                    }
                }

                fputcsv($file, [
                    $milestone->thesis->student->user->name ?? '',
                    $milestone->thesis->student->matric_number ?? '',
                    $milestone->status,
                    $milestone->defence_date ?? 'Not Scheduled',
                    $avgScore
                ]);
            }"""
content = content.replace(old_foreach, new_foreach)

with open(path, 'w') as f:
    f.write(content)
