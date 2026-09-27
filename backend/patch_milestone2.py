import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/app/Http/Controllers/Admin/MilestoneTemplateController.php'
with open(path, 'r') as f:
    content = f.read()

content = content.replace("thesisProject.student", "thesis.student")
content = content.replace("thesisProject.defenceEvents", "thesis.defenceEvents")

with open(path, 'w') as f:
    f.write(content)

with open(path, 'r') as f:
    content = f.read()

content = content.replace("$milestone->thesisProject", "$milestone->thesis")

with open(path, 'w') as f:
    f.write(content)
