import re

with open('backend/resources/views/auth/register.blade.php', 'r') as f:
    content = f.read()

# Add the error banner right above the question progress bar
error_banner = """
                    <!-- Error Banner (Global) -->
                    <div x-show="errorMessage" x-cloak class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl flex items-center gap-2 font-medium" x-transition>
                        <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span x-text="errorMessage"></span>
                    </div>
"""

content = content.replace(
    '<div class="flex items-center gap-3 mb-6">',
    error_banner + '\n                    <div class="flex items-center gap-3 mb-6">'
)

with open('backend/resources/views/auth/register.blade.php', 'w') as f:
    f.write(content)
