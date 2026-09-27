import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/resources/views/milestones/show.blade.php'
with open(path, 'r') as f:
    content = f.read()

old_ppt_input = r"""                                        <input type="file" name="ppt[]" accept=".pdf" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                                            @change="
                                                const files = $event.target.files;
                                                document.getElementById('ppt-name').textContent = files.length > 0 ? files.length + ' files selected' : 'Click or drop to select PPT';
                                            "/>"""

new_ppt_input = r"""                                        <input type="file" name="ppt" accept=".pdf" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                                            @change="
                                                const file = $event.target.files[0];
                                                document.getElementById('ppt-name').textContent = file ? file.name : 'Click or drop to select PPT';
                                            "/>"""

content = content.replace(old_ppt_input, new_ppt_input)

# Update label to original
old_ppt_label = r"""                                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                                        Upload Presentation Slide Deck (PDF Only, Select one or more)
                                    </label>"""

new_ppt_label = r"""                                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                                        Upload Presentation Slide Deck (PDF Only)
                                    </label>"""

content = content.replace(old_ppt_label, new_ppt_label)

with open(path, 'w') as f:
    f.write(content)
