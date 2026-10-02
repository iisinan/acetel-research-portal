import re

with open('resources/views/milestones/show.blade.php', 'r') as f:
    content = f.read()

# Extract from <div id="milestone-details-container" ... to end of @section('content')
match = re.search(r'(<div id="milestone-details-container".*?)@endsection', content, re.DOTALL)
if match:
    partial_content = match.group(1).strip()
    # Write to partial
    import os
    os.makedirs('resources/views/milestones/partials', exist_ok=True)
    with open('resources/views/milestones/partials/details.blade.php', 'w') as f:
        f.write(partial_content)
    
    # Replace in show.blade.php
    new_show = content[:match.start()] + "@include('milestones.partials.details')\n" + content[match.end():]
    with open('resources/views/milestones/show.blade.php', 'w') as f:
        f.write(new_show)
    print("Successfully extracted to partials/details.blade.php")
else:
    print("Could not find content to extract in show.blade.php")

