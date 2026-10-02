import re

with open('resources/views/milestones/partials/details.blade.php', 'r') as f:
    content = f.read()

# Replace hardcoded IDs
replacements = {
    "id=\"ppt-name\"": "id=\"ppt-name-{{ $milestone->id }}\"",
    "getElementById('ppt-name')": "getElementById('ppt-name-{{ $milestone->id }}')",
    
    "id=\"file-name\"": "id=\"file-name-{{ $milestone->id }}\"",
    "getElementById('file-name')": "getElementById('file-name-{{ $milestone->id }}')",
    
    "id=\"pub-name\"": "id=\"pub-name-{{ $milestone->id }}\"",
    "getElementById('pub-name')": "getElementById('pub-name-{{ $milestone->id }}')",
    
    "id=\"modal-title\"": "id=\"modal-title-{{ $milestone->id }}\"",
    "getElementById('modal-title')": "getElementById('modal-title-{{ $milestone->id }}')",
    
    "getElementById('milestone-details-container')": "getElementById('milestone-details-container-{{ $milestone->id }}')",
}

for old, new in replacements.items():
    content = content.replace(old, new)

with open('resources/views/milestones/partials/details.blade.php', 'w') as f:
    f.write(content)

print("Fixed hardcoded IDs in details.blade.php")
