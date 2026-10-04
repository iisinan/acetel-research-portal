<?php

// 1. Add route in routes/admin.php
$routeContent = file_get_contents('routes/admin.php');
if (!str_contains($routeContent, 'end-schedule')) {
    $search = "Route::post('/milestone-templates/{template}/cancel-schedule', [MilestoneTemplateController::class, 'cancelSchedule'])->name('milestone-templates.cancel-schedule');";
    $replace = $search . "\n    Route::post('/milestone-templates/{template}/end-schedule', [MilestoneTemplateController::class, 'endSchedule'])->name('milestone-templates.end-schedule');";
    $routeContent = str_replace($search, $replace, $routeContent);
    file_put_contents('routes/admin.php', $routeContent);
}

// 2. Add method in MilestoneTemplateController.php
$controllerPath = 'app/Http/Controllers/Admin/MilestoneTemplateController.php';
$controllerContent = file_get_contents($controllerPath);
if (!str_contains($controllerContent, 'function endSchedule')) {
    $method = <<<'PHP'
    public function endSchedule(MilestoneTemplate $template)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Institutional authority required. Only an Administrator can end presentation sessions.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $milestones = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
                ->whereNotNull('defence_date')
                ->where('status', '!=', 'approved')
                ->get();

            foreach ($milestones as $sm) {
                // Update to approved so it counts as completed and disappears from active schedules
                $sm->update([
                    'status' => 'approved'
                ]);

                $studentUser = $sm->thesis?->student?->user;
                if ($studentUser) {
                    \Illuminate\Support\Facades\Cache::forget('user_thesis_' . $studentUser->id);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('admin.milestone-templates.index')->with('success', "Presentation session for {$template->name} has been marked as ended. The students have been approved and the active schedule has been cleared.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Failed to end schedule: ' . $e->getMessage());
        }
    }
PHP;

    $searchController = "    public function cancelSchedule(MilestoneTemplate \$template)";
    $controllerContent = str_replace($searchController, $method . "\n\n" . $searchController, $controllerContent);
    file_put_contents($controllerPath, $controllerContent);
}

// 3. Add button in resources/views/admin/milestone-templates/index.blade.php
$viewPath = 'resources/views/admin/milestone-templates/index.blade.php';
$viewContent = file_get_contents($viewPath);

if (!str_contains($viewContent, 'End Presentation Session')) {
    $searchView = <<<'HTML'
                                            <form action="{{ route('admin.milestone-templates.cancel-schedule', $template->id) }}" method="POST">
                                                @csrf
                                                <button type="button" 
                                                    data-confirm="Are you sure you want to cancel the presentation schedule for {{ addslashes($template->name) }}? This will clear all presentation dates, times, and Zoom links for {{ $scheduledCount }} scheduled student(s), and remove the live presentation tab."
                                                    data-confirm-title="Cancel Presentation Schedule"
                                                    data-confirm-type="danger"
                                                    data-confirm-btn="Cancel Schedule"
                                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    <span>Cancel Presentation Schedule</span>
                                                </button>
                                            </form>
HTML;

    $replaceView = <<<'HTML'
                                            <form action="{{ route('admin.milestone-templates.end-schedule', $template->id) }}" method="POST">
                                                @csrf
                                                <button type="button" 
                                                    data-confirm="Are you sure you want to end this presentation session? This will mark all {{ $scheduledCount }} currently scheduled presentations as 'Approved' and clear the active global examiners for the next batch."
                                                    data-confirm-title="End Presentation Session"
                                                    data-confirm-type="success"
                                                    data-confirm-btn="End Session"
                                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span>End Presentation Session</span>
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.milestone-templates.cancel-schedule', $template->id) }}" method="POST">
                                                @csrf
                                                <button type="button" 
                                                    data-confirm="Are you sure you want to cancel the presentation schedule for {{ addslashes($template->name) }}? This will clear all presentation dates, times, and Zoom links for {{ $scheduledCount }} scheduled student(s), and remove the live presentation tab."
                                                    data-confirm-title="Cancel Presentation Schedule"
                                                    data-confirm-type="danger"
                                                    data-confirm-btn="Cancel Schedule"
                                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm">
                                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    <span>Cancel Presentation Schedule</span>
                                                </button>
                                            </form>
HTML;

    $viewContent = str_replace($searchView, $replaceView, $viewContent);
    file_put_contents($viewPath, $viewContent);
}

echo "Done.";
