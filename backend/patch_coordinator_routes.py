import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/routes/coordinator.php'
with open(path, 'r') as f:
    content = f.read()

addition = """
// Milestone Templates (Shared with Admin)
Route::get('milestone-templates', [\\App\\Http\\Controllers\\Admin\\MilestoneTemplateController::class, 'index'])->name('milestone-templates.index');
Route::get('milestone-templates/{template}/export-students', [\\App\\Http\\Controllers\\Admin\\MilestoneTemplateController::class, 'exportStudents'])->name('milestone-templates.export-students');
"""

if "milestone-templates" not in content:
    content += addition

with open(path, 'w') as f:
    f.write(content)
