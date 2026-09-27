import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/admin/milestone-templates/index.blade.php'
with open(path, 'r') as f:
    content = f.read()

# Make showStudents true by default
content = content.replace("x-data=\"{ expanded: false, search: '', showStudents: false, selected: [] }\"", "x-data=\"{ expanded: false, search: '', showStudents: true, selected: [] }\"")

with open(path, 'w') as f:
    f.write(content)
