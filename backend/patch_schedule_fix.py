import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/app/Http/Controllers/Admin/MilestoneTemplateController.php'
with open(path, 'r') as f:
    content = f.read()

old_schedule = r"""    public function schedule(Request $request)
    {
        $request->validate([
            'student_milestone_ids' => 'required|string',
            'start_date' => 'required|date',
            'students_per_day' => 'required|integer|min:1'
        ]);

        $ids = array_filter(explode(',', $request->student_milestone_ids));
        $currentDate = \Carbon\Carbon::parse($request->start_date);"""

new_schedule = r"""    public function schedule(Request $request)
    {
        $request->validate([
            'milestone_ids' => 'required|array',
            'start_date' => 'required|date',
            'students_per_day' => 'required|integer|min:1'
        ]);

        $ids = $request->milestone_ids;
        $currentDate = \Carbon\Carbon::parse($request->start_date);"""

content = content.replace(old_schedule, new_schedule)

with open(path, 'w') as f:
    f.write(content)
