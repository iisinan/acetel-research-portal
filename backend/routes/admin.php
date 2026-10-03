<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\StudentController;

// Bulk Import (MUST BE BEFORE RESOURCE)
Route::get('/users/import', [UserManagementController::class, 'importForm'])->name('users.import-form');
Route::post('/users/import', [UserManagementController::class, 'import'])->name('users.import');

Route::resource('users', UserManagementController::class);
Route::patch('/users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset_password');

Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
Route::post('/students/{student}/assign-examiner', [StudentController::class, 'assignInternalExaminer'])->name('students.assign-examiner');
Route::post('/students/{student}/promote-milestone', [StudentController::class, 'promoteMilestone'])->name('students.promote-milestone');
Route::post('/students/{student}/set-milestone', [StudentController::class, 'setMilestone'])->name('students.set-milestone');
Route::post('/students/{student}/demote-milestone', [StudentController::class, 'demoteMilestone'])->name('students.demote-milestone');

use App\Http\Controllers\Admin\InternalExaminerController;
Route::resource('internal-examiners', InternalExaminerController::class);

use App\Http\Controllers\Admin\ProgramController;
Route::resource('programs', ProgramController::class);

use App\Http\Controllers\Admin\LevelController;
Route::resource('levels', LevelController::class);

use App\Http\Controllers\Admin\CohortController;
Route::post('cohorts/{cohort}/add-student', [CohortController::class, 'addStudent'])->name('cohorts.add-student');
Route::get('cohorts/{cohort}/register-students', [CohortController::class, 'registerStudentsForm'])->name('cohorts.register-students');
Route::post('cohorts/{cohort}/register-students', [CohortController::class, 'registerStudents'])->name('cohorts.register-students.store');
Route::patch('cohorts/{cohort}/toggle-status', [CohortController::class, 'toggleStatus'])->name('cohorts.toggle-status');
Route::post('cohorts/{cohort}/bulk-schedule', [CohortController::class, 'bulkScheduleDefence'])->name('cohorts.bulk-schedule');
Route::resource('cohorts', CohortController::class);

use App\Http\Controllers\Admin\AnnouncementController;
Route::resource('announcements', AnnouncementController::class);

use App\Http\Controllers\Admin\DocumentTemplateController;
// Template Download moved to web.php for universal access
Route::resource('templates', DocumentTemplateController::class);

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AuditController;

Route::resource('audit-logs', AuditLogController::class)->only(['index', 'show']);
Route::resource('activity-logs', ActivityLogController::class)->only(['index']);
Route::get('activity-logs/user/{user}', [ActivityLogController::class, 'userActivity'])->name('activity-logs.user');

// Administrative Audit Hub
Route::get('/audit-hub', [AuditController::class, 'index'])->name('audit.index');
Route::get('/audit-hub/{thesis}', [AuditController::class, 'show'])->name('audit.show');

use App\Http\Controllers\Admin\EmailTemplateController;
Route::resource('email-templates', EmailTemplateController::class)->only(['index', 'edit', 'update']);

use App\Http\Controllers\Admin\SystemOperationController;
Route::get('/operations', [SystemOperationController::class, 'index'])->name('operations.index');
Route::post('/operations/retry/{id}', [SystemOperationController::class, 'retryJob'])->name('operations.retry');
Route::post('/operations/flush', [SystemOperationController::class, 'flushFailedJobs'])->name('operations.flush');

use App\Http\Controllers\Admin\MilestoneTemplateController;
Route::resource('milestone-templates', MilestoneTemplateController::class)->only(['index', 'show']);
Route::post('milestone-templates/{template}/date', [MilestoneTemplateController::class, 'setDate'])->name('milestone-templates.set-date');
    Route::post('milestone-templates/schedule', [MilestoneTemplateController::class, 'schedule'])->name('milestone-templates.schedule');
    Route::post('milestone-templates/{template}/cancel-schedule', [MilestoneTemplateController::class, 'cancelSchedule'])->name('milestone-templates.cancel-schedule');
    Route::post('milestone-templates/{milestone}/assign-examiner', [MilestoneTemplateController::class, 'assignExaminer'])->name('milestone-templates.assign-examiner');
    Route::post('milestone-templates/{template}/assign-examiner-global', [MilestoneTemplateController::class, 'assignExaminerGlobal'])->name('milestone-templates.assign-examiner-global');
    Route::get('milestone-templates/{template}/export-students', [MilestoneTemplateController::class, 'exportStudents'])->name('milestone-templates.export-students');


use App\Http\Controllers\Admin\BulkScheduleController;
Route::get('bulk-schedule', [BulkScheduleController::class, 'index'])->name('bulk-schedule.index');
Route::post('bulk-schedule', [BulkScheduleController::class, 'store'])->name('bulk-schedule.store');

use App\Http\Controllers\Admin\StudentMilestoneController;
Route::get('students/{student}/milestones/{milestone}/edit', [StudentMilestoneController::class, 'edit'])->name('students.milestones.edit');
Route::put('students/{student}/milestones/{milestone}', [StudentMilestoneController::class, 'update'])->name('students.milestones.update');
Route::post('students/{student}/sync-milestones', [StudentMilestoneController::class, 'sync'])->name('students.sync-milestones');


use App\Http\Controllers\Admin\SeminarController;
