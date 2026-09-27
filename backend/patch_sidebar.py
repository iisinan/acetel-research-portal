import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/layouts/partials/sidebar-menu.blade.php'
with open(path, 'r') as f:
    lines = f.readlines()

new_lines = []
skip = False
for line in lines:
    if "route('admin.seminars.index')" in line:
        # this is the start of the <a> tag. We need to skip until </a>
        skip = True
    
    if not skip:
        new_lines.append(line)
        
    if skip and '</a>' in line:
        skip = False

with open(path, 'w') as f:
    f.writelines(new_lines)

