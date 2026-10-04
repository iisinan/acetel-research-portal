<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Coordinator\DashboardController;
use App\Http\Controllers\Coordinator\SupervisorController;
use App\Http\Controllers\Coordinator\StudentController;
use App\Http\Controllers\Coordinator\MilestoneController;
use App\Http\Controllers\Coordinator\CohortController;

Route::get('/', function () {
    return redirect()->route('coordinator.dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Supervisors
Route::get('supervisors/template', [SupervisorController::class, 'downloadTemplate'])->name('supervisors.template');
Route::resource('supervisors', SupervisorController::class);
Route::post('supervisors/bulk', [SupervisorController::class, 'bulkStore'])->name('supervisors.bulkStore');
Route::post('supervisors/{supervisor}/reset-password', [SupervisorController::class, 'resetPassword'])->name('supervisors.reset-password');
Route::post('supervisors/{supervisor}/assign-student', [SupervisorController::class, 'assignStudent'])->name('supervisors.assign-student');
Route::delete('supervisors/{supervisor}/unassign-student/{thesis}', [SupervisorController::class, 'unassignStudent'])->name('supervisors.unassign-student');
Route::patch('supervisors/{supervisor}/update-assignment/{thesis}', [SupervisorController::class, 'updateAssignmentRole'])->name('supervisors.update-assignment');

// Students
Route::get('students', [StudentController::class, 'index'])->name('students.index');
Route::get('students/{student}', [StudentController::class, 'show'])->name('students.show');
Route::post('students/{student}/assign-supervisor', [StudentController::class, 'assignSupervisor'])->name('students.assign-supervisor');

// Assigned Supervisors (Milestone 2 dedicated workspace)
Route::get('assigned-supervisors', [\App\Http\Controllers\Coordinator\AssignedSupervisorController::class, 'index'])->name('assigned-supervisors.index');
Route::post('assigned-supervisors/{student}/assign', [\App\Http\Controllers\Coordinator\AssignedSupervisorController::class, 'assign'])->name('assigned-supervisors.assign');

// Milestones
Route::get('milestones', [MilestoneController::class, 'index'])->name('milestones.index');

// Examiner Pool
Route::get('examiners', [App\Http\Controllers\Coordinator\ExaminerPoolController::class, 'index'])->name('examiners.index');
Route::post('examiners/internal', [App\Http\Controllers\Coordinator\ExaminerPoolController::class, 'storeInternal'])->name('examiners.storeInternal');
Route::post('examiners/external', [App\Http\Controllers\Coordinator\ExaminerPoolController::class, 'storeExternal'])->name('examiners.storeExternal');
Route::post('examiners/external/upgrade', [App\Http\Controllers\Coordinator\ExaminerPoolController::class, 'storeExternalFromSupervisor'])->name('examiners.storeExternalFromSupervisor');
Route::post('examiners/{profile}/toggle/{type}', [App\Http\Controllers\Coordinator\ExaminerPoolController::class, 'toggleStatus'])->name('examiners.toggle');

// Cohorts
Route::get('cohorts', [CohortController::class, 'index'])->name('cohorts.index');
Route::get('cohorts/{cohort}', [CohortController::class, 'show'])->name('cohorts.show');
Route::get('cohorts/{cohort}/register-students', [CohortController::class, 'registerStudentsForm'])->name('cohorts.register-students');
Route::post('cohorts/{cohort}/register-students', [CohortController::class, 'registerStudents'])->name('cohorts.register-students.store');
Route::post('cohorts/{cohort}/bulk-schedule', [CohortController::class, 'bulkScheduleDefence'])->name('cohorts.bulk-schedule');

// Communications Audit
Route::get('communications', [App\Http\Controllers\Coordinator\CommunicationController::class, 'index'])->name('communications.index');
Route::get('communications/{channel}', [App\Http\Controllers\Coordinator\CommunicationController::class, 'show'])->name('communications.show');
Route::post('communications/{channel}/nudge', [App\Http\Controllers\Coordinator\CommunicationController::class, 'nudge'])->name('communications.nudge');

// Supervisor Assignments (M2)
Route::post('students/{thesis}/assign-supervisors', [App\Http\Controllers\Coordinator\SupervisorAssignmentController::class, 'store'])->name('students.assign-supervisors');

// Examiner Assignments (M7/M10)
Route::post('students/{thesis}/assign-internal', [App\Http\Controllers\Coordinator\ExaminerController::class, 'assignInternal'])->name('students.assign-internal');
Route::post('students/{thesis}/assign-external', [App\Http\Controllers\Coordinator\ExaminerController::class, 'assignExternal'])->name('students.assign-program');

// Milestone Templates (Shared with Admin)
Route::get('milestone-templates', [\App\Http\Controllers\Admin\MilestoneTemplateController::class, 'index'])->name('milestone-templates.index');
Route::get('milestone-templates/{template}/export-students', [\App\Http\Controllers\Admin\MilestoneTemplateController::class, 'exportStudents'])->name('milestone-templates.export-students');
Route::get('milestone-templates/{template}/export-scheduled-scores', [\App\Http\Controllers\Admin\MilestoneTemplateController::class, 'exportScheduledScores'])->name('milestone-templates.export-scheduled-scores');
