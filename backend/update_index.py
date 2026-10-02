import re

with open('resources/views/milestones/index.blade.php', 'r') as f:
    content = f.read()

# Replace between @can('view', $milestone) and @endcan inside milestone-body
# Look for <div id="milestone-body-{{ $milestone->id }}"
# and the matching @endcan

pattern = r'(@can\(\'view\', \$milestone\)).*?(@endcan)'
# wait, there might be multiple @can/@endcan inside the body.
# Let's just use string split/partition if we know the exact line numbers, but they might change.

# Let's read lines:
lines = content.split('\n')
start_idx = -1
end_idx = -1

for i, line in enumerate(lines):
    if '<div id="milestone-body-{{ $milestone->id }}"' in line:
        start_idx = i + 2 # the line after @can('view', $milestone)
        break

for i in range(len(lines)-1, -1, -1):
    if '@endcan' in lines[i] and '</div>' in lines[i-1]:
        # wait, let's find the @endcan just before the @endforeach
        if i > start_idx:
            end_idx = i
            break

print("Start:", start_idx, "End:", end_idx)

if start_idx != -1 and end_idx != -1:
    new_lines = lines[:start_idx] + ["                        <div class=\"px-6 py-8 border-t border-gray-100 bg-white w-full relative\">", "                            @include('milestones.partials.details', ['milestone' => $milestone])", "                        </div>"] + lines[end_idx:]
    with open('resources/views/milestones/index.blade.php', 'w') as f:
        f.write('\n'.join(new_lines))
    print("Successfully updated index.blade.php")
else:
    print("Could not find start/end indices")

