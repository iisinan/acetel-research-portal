import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/app/Http/Controllers/Admin/MilestoneTemplateController.php'
with open(path, 'r') as f:
    content = f.read()

# Replace the incorrect statuses with the correct ones
content = content.replace(
    "['ongoing', 'pending_submission', 'pending_review', 'needs_revision', 'pending_defence']",
    "['in_progress', 'submitted', 'revision_required']"
)

with open(path, 'w') as f:
    f.write(content)
