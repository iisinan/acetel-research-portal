<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\RepositoryController;

Route::get('/', function () {
    if (request()->has('ping')) return 'pong';

    if (request()->has('debug_logs_xyz')) {
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            return response(file_get_contents($logPath))->header('Content-Type', 'text/plain');
        }
        return 'No logs found.';
    }

    try {
    $announcements = \Illuminate\Support\Facades\Cache::remember('public_announcements', 60 * 15, function() {
        return \App\Models\Announcement::active()
            ->orderByRaw('COALESCE(starts_at, created_at) DESC')
            ->take(5)
            ->get();
    });

    $stats = \Illuminate\Support\Facades\Cache::remember('institutional_stats', 60 * 60, function() {
        try {
            return [
                'projects_count' => \App\Models\ThesisProject::count(),
                'students_count' => \App\Models\User::role('Student')->count(),
                'archived_count' => \App\Models\ThesisProject::publiclyVisible()->count(),
            ];
        } catch (\Exception $e) {
            return [
                'projects_count' => 0,
                'students_count' => 0,
                'archived_count' => 0,
            ];
        }
    });
    
    return view('welcome', compact('announcements', 'stats')); } catch (\Throwable $e) { return response((string) $e, 500); }
});

// Institutional Research Repository (Public)
Route::get('/repository', [RepositoryController::class, 'index'])->name('repository.index');
Route::get('/repository/{thesis}', [RepositoryController::class, 'show'])->name('repository.show');
Route::get('/repository/submissions/{submission}/view', [RepositoryController::class, 'viewSubmission'])->name('repository.submissions.view');

// Public Announcements
Route::get('/announcements/{announcement}', [App\Http\Controllers\AnnouncementController::class, 'showPublic'])->name('announcements.show_public');

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);
    Route::post('/register/validate-step1', [App\Http\Controllers\Auth\RegisteredUserController::class, 'validateStep1'])->name('register.validate_step1');
    
    // Forgot Password
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'processForgotPassword'])->name('password.email');

    // Reset Password (the link emailed to the user)
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'processResetPassword'])->name('password.update');

    // Registration disabled - created by admin only
});

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MilestoneController;

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Shared Dashboard (Content varies by role via Controller)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Notifications & Messages (Shared)
    Route::post('/messages', [App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    // Inbox (Email-like messaging)
    Route::get('/inbox', [App\Http\Controllers\InboxController::class, 'index'])->name('inbox.index');
    Route::get('/inbox/sent', [App\Http\Controllers\InboxController::class, 'sent'])->name('inbox.sent');
    Route::get('/inbox/compose', [App\Http\Controllers\InboxController::class, 'compose'])->name('inbox.compose');
    Route::post('/inbox', [App\Http\Controllers\InboxController::class, 'store'])->name('inbox.store');
    Route::get('/inbox/{inboxMessage}', [App\Http\Controllers\InboxController::class, 'show'])->name('inbox.show');
    Route::patch('/inbox/{inboxMessage}/star', [App\Http\Controllers\InboxController::class, 'star'])->name('inbox.star');
    Route::get('/inbox/attachments/{attachment}', [App\Http\Controllers\InboxController::class, 'downloadAttachment'])->name('inbox.attachments.download');

    // Admin & Director (+ Coordinators for Reports)
    Route::middleware(['role:Admin|Director|Program Coordinator'])->group(function () {
        Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [App\Http\Controllers\ReportController::class, 'export'])->name('reports.export');
    });

    Route::middleware(['role:Admin|Director'])->group(function () {
        
        // specific admin only
        // Note: Admin-only routes are now handled in routes/admin.php

    });

    // Program Coordinator & Admin/Director (Event Management)
    Route::middleware(['role:Program Coordinator|Admin|Director|Supervisor|Student|Internal Examiner|External Examiner'])->group(function () {
        Route::get('/events/create', [App\Http\Controllers\EventController::class, 'create'])->name('events.create');
        Route::post('/events', [App\Http\Controllers\EventController::class, 'schedule'])->name('events.store');
        Route::post('/theses', [App\Http\Controllers\ThesisController::class, 'store'])->name('theses.store');
        Route::get('/theses/{thesis}', [App\Http\Controllers\ThesisController::class, 'show'])->name('theses.show');
        Route::patch('/theses/{thesis}', [App\Http\Controllers\ThesisController::class, 'update'])->name('theses.update');
        Route::post('/theses/{thesis}/assign-supervisor', [App\Http\Controllers\ThesisController::class, 'assignSupervisor'])->name('theses.assign_supervisor');
        Route::post('/theses/{thesis}/assign-internal-examiner', [App\Http\Controllers\ThesisController::class, 'assignInternalExaminer'])->name('theses.assign_internal_examiner');
        Route::post('/theses/{thesis}/assign-external-examiner', [App\Http\Controllers\ThesisController::class, 'assignExternalExaminer'])->name('theses.assign_external_examiner');
        Route::post('/theses/{thesis}/clear-internal', [App\Http\Controllers\ThesisController::class, 'clearForInternal'])->name('theses.clear_internal');
    });

    // Student
    // Milestones (Student)
    Route::get('/milestones', [MilestoneController::class, 'index'])->name('milestones.index');
    Route::get('/milestones/{milestone}', [MilestoneController::class, 'show'])->name('milestones.show');
    Route::post('/milestones/{milestone}', [MilestoneController::class, 'store'])->name('milestones.store'); // Submission
    Route::post('/milestones/{milestone}/approve', [MilestoneController::class, 'approve'])->name('milestones.approve');
    Route::post('/milestones/{milestone}/unlock', [MilestoneController::class, 'unlock'])->name('milestones.unlock');
    Route::post('/milestones/{milestone}/defence-date', [MilestoneController::class, 'setDefenceDate'])->name('milestones.set_defence_date');
    Route::post('/milestones/{milestone}/plagiarism', [MilestoneController::class, 'uploadMilestonePlagiarism'])->name('milestones.upload_plagiarism');
    Route::post('/milestones/{milestone}/approve-date', [MilestoneController::class, 'approveDate'])->name('milestones.approve_date');
    Route::post('/milestones/{milestone}/quick-approve', [MilestoneController::class, 'quickApprove'])->name('milestones.quick_approve');
    Route::post('/milestones/{milestone}/accept-upload', [MilestoneController::class, 'acceptUpload'])->name('milestones.accept_upload');
    Route::post('/milestones/{milestone}/reject-upload', [MilestoneController::class, 'rejectUpload'])->name('milestones.reject_upload');


    // Milestone Review (Supervisor, Coordinator, Admin, Director, Internal Examiner, External Examiner)
    Route::middleware(['role:Supervisor|Program Coordinator|Admin|Director|Internal Examiner|External Examiner'])->group(function () {
        Route::get('/milestones/{milestone}/review', [App\Http\Controllers\MilestoneReviewController::class, 'show'])->name('milestones.review');
        Route::patch('/milestones/{milestone}/review', [App\Http\Controllers\MilestoneReviewController::class, 'update'])->name('milestones.review.update');
        
        // Supervisor Student Management
        Route::get('/supervisor/candidates', [App\Http\Controllers\Supervisor\StudentController::class, 'index'])->name('supervisor.students.index');
        
        // Allow jumping milestones globally
        Route::post('/students/{student}/set-milestone-global', [\App\Http\Controllers\Admin\StudentController::class, 'setMilestone'])->name('students.set_milestone_global');
        
        Route::get('/supervisor/seminar-examinations', [App\Http\Controllers\Supervisor\SeminarExaminationController::class, 'index'])->name('supervisor.seminars.index');
        Route::post('/supervisor/seminar-examinations/{event}/score', [App\Http\Controllers\Supervisor\SeminarExaminationController::class, 'storeScore'])->name('supervisor.seminars.score');
        
    });
    
    
    // Examiner Routes
    Route::middleware(['role:Internal Examiner|External Examiner'])->group(function () {
        Route::get('/examiner/theses', [\App\Http\Controllers\Examiner\ThesisController::class, 'index'])->name('examiner.theses.index');
        Route::get('/examiner/theses/{id}', [\App\Http\Controllers\Examiner\ThesisController::class, 'show'])->name('examiner.theses.show');
    });

    Route::post('/users/{user}/reset-password', [App\Http\Controllers\UserController::class, 'resetPassword'])->name('users.reset_password');
    
    // Profile
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Submission Deletion & Plagiarism
    Route::delete('/submissions/{submission}', [MilestoneController::class, 'deleteSubmission'])->name('submissions.destroy');
    Route::post('/submissions/{submission}/plagiarism', [MilestoneController::class, 'uploadPlagiarism'])->name('submissions.plagiarism');
    Route::get('/submissions/{submission}/view', [MilestoneController::class, 'viewSubmission'])->name('submissions.view');

    // Evaluations
    Route::get('/evaluations/defence/{defenceEvent}/evaluate', [\App\Http\Controllers\EvaluationController::class, 'create'])->name('evaluations.create');
    Route::post('/evaluations/defence/{defenceEvent}', [\App\Http\Controllers\EvaluationController::class, 'store'])->name('evaluations.store');
    Route::get('/evaluations/{evaluation}', [\App\Http\Controllers\EvaluationController::class, 'show'])->name('evaluations.show');
    Route::get('/evaluations/{evaluation}/pdf', [\App\Http\Controllers\EvaluationController::class, 'downloadPdf'])->name('evaluations.pdf');

    // Meetings
    Route::get('/meeting/{milestone}', [\App\Http\Controllers\MeetingController::class, 'join'])->name('meeting.join');
    Route::get('/meeting/event/{event}', [\App\Http\Controllers\MeetingController::class, 'joinEvent'])->name('meeting.join_event');

    // Action Items
    Route::post('/action-items/{actionItem}/complete', [\App\Http\Controllers\ActionItemController::class, 'complete'])->name('action-items.complete');
    Route::post('/action-items/{actionItem}/verify', [\App\Http\Controllers\ActionItemController::class, 'verify'])->name('action-items.verify');

    // Document Templates (Resource Center)
    Route::get('/templates/{template}/download', [App\Http\Controllers\Admin\DocumentTemplateController::class, 'download'])->name('templates.download');
    Route::get('/resources', [DashboardController::class, 'resources'])->name('resources.index');

    // Milestone Presentations Schedule (Public to Authenticated Users)
    Route::get('/presentations/{template}', [\App\Http\Controllers\PresentationController::class, 'show'])->name('presentations.show');
});

// Utility Route for CSRF Token Refresh (No Auth Required)
Route::get('/refresh-csrf', function() {
    return response()->json(['token' => csrf_token()]);
});

Route::get('/debug/send-test-email', function() {
    $user = auth()->user();
    if (!$user) return response()->json(['error' => 'Please login to the Trajectory Hub first.'], 401);
    
    $project = \App\Models\ThesisProject::first();
    if (!$project) return response()->json(['error' => 'No thesis project found in database to run this test.'], 404);
    
    try {
        $user->notify(new \App\Notifications\SupervisorAssigned($project));
        return response()->json([
            'success' => true,
            'message' => 'Institutional Branded Email has been dispatched!',
            'recipient' => $user->email,
            'branding' => 'Templated: supervisor_assigned',
            'note' => 'If using SMTP, check your inbox. If using log driver, check storage/logs/laravel.log'
        ]);


    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to send email: ' . $e->getMessage()], 500);
    }
})->middleware(['auth']);







Route::get('/debug-log', function() {
    return response()->file(storage_path('logs/laravel.log'));
});

Route::get('/debug-s3', function() {
    try {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $disk->put('test.txt', 'Hello World');
        return "SUCCESS! Wrote test.txt. Driver: " . config('filesystems.disks.public.driver');
    } catch (\Exception $e) {
        return "ERROR: " . $e->getMessage() . "\n\n" . $e->getTraceAsString();
    }
});
Route::get('/phpinfo', function() {
    return [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
    ];
});
