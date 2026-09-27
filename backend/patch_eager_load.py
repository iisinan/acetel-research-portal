import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/app/Http/Controllers/Admin/MilestoneTemplateController.php'
with open(path, 'r') as f:
    content = f.read()

content = content.replace(
    "->with(['thesis.student.user', 'thesis.defenceEvents.panelMembers.user', 'submissions']);",
    "->with(['thesis.student.user', 'thesis.defenceEvents.panelMembers.user', 'thesis.defenceEvents.evaluations', 'submissions']);"
)

with open(path, 'w') as f:
    f.write(content)
