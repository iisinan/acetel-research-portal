import re

with open('/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/milestones/show.blade.php', 'r') as f:
    content = f.read()

# Look for <div x-data="{ uploading: false }"> and wrap it in the condition
form_start = '<div x-data="{ uploading: false }">'

replacement = '''
                    @if($milestone->template->allow_defence_date && empty($milestone->defence_date))
                        <div class="bg-white border border-amber-200 shadow-sm p-10 rounded-3xl flex flex-col items-center justify-center text-center">
                            <div class="w-12 h-12 bg-amber-50 border border-amber-200 text-amber-500 rounded-xl flex items-center justify-center mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 tracking-tight mb-2">Pending Schedule</h3>
                            <p class="text-sm font-medium text-gray-500 max-w-sm">Please wait for the Admin to schedule your presentation date. File submission will be available once your date is set.</p>
                        </div>
                    @else
                        <div x-data="{ uploading: false }">'''

content = content.replace(form_start, replacement)

# Now we need to close the @else which we opened. 
# We need to find the matching </div> for <div x-data="{ uploading: false }">.
# But it's easier to just find the `</form>\n                        </div>\n                    @endif` and insert `@endif` before `@endif`
end_pattern = '</form>\n                        </div>'
new_end_pattern = '</form>\n                        </div>\n                    @endif'

content = content.replace(end_pattern, new_end_pattern)

with open('/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/milestones/show.blade.php', 'w') as f:
    f.write(content)

