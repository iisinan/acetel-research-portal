import re

with open('backend/routes/web.php', 'r') as f:
    content = f.read()

examiner_routes = """
    // Examiner Routes
    Route::middleware(['role:Internal Examiner|External Examiner'])->group(function () {
        Route::get('/examiner/theses', [\\App\\Http\\Controllers\\Examiner\\ThesisController::class, 'index'])->name('examiner.theses.index');
        Route::get('/examiner/theses/{id}', [\\App\\Http\\Controllers\\Examiner\\ThesisController::class, 'show'])->name('examiner.theses.show');
    });
"""

# Insert after Supervisor Student Management section
content = content.replace("Route::post('/users/{user}/reset-password', [App\\Http\\Controllers\\UserController::class, 'resetPassword'])->name('users.reset_password');", examiner_routes + "\n    Route::post('/users/{user}/reset-password', [App\\Http\\Controllers\\UserController::class, 'resetPassword'])->name('users.reset_password');")

with open('backend/routes/web.php', 'w') as f:
    f.write(content)
