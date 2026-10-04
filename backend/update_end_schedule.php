<?php

$content = file_get_contents('app/Http/Controllers/Admin/MilestoneTemplateController.php');

$search = <<<PHP
    public function endSchedule(MilestoneTemplate \$template)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Institutional authority required. Only an Administrator can end presentation sessions.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            \$milestones = \App\Models\StudentMilestone::where('milestone_template_id', \$template->id)
                ->whereNotNull('defence_date')
                ->where('status', '!=', 'approved')
                ->get();

            foreach (\$milestones as \$sm) {
                // Update to approved so it counts as completed and disappears from active schedules
                \$sm->update([
                    'status' => 'approved'
                ]);

                \$studentUser = \$sm->thesis?->student?->user;
                if (\$studentUser) {
                    \Illuminate\Support\Facades\Cache::forget('user_thesis_' . \$studentUser->id);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('admin.milestone-templates.index')->with('success', "Presentation session for {\$template->name} has been marked as ended. The students have been approved and the active schedule has been cleared.");
        } catch (\Exception \$e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Failed to end schedule: ' . \$e->getMessage());
        }
    }
PHP;

$replace = <<<PHP
    public function endSchedule(MilestoneTemplate \$template)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Institutional authority required. Only an Administrator can end presentation sessions.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            \$milestones = \App\Models\StudentMilestone::where('milestone_template_id', \$template->id)
                ->whereNotNull('defence_date')
                ->where('status', '!=', 'approved')
                ->get();

            foreach (\$milestones as \$sm) {
                \$sm->update([
                    'status' => 'approved'
                ]);

                \$studentUser = \$sm->thesis?->student?->user;
                if (\$studentUser) {
                    \Illuminate\Support\Facades\Cache::forget('user_thesis_' . \$studentUser->id);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            // Set a flag to trigger the auto-download in the past presentations view
            return redirect()->route('admin.past-presentations.index')
                ->with('success', "Presentation session for {\$template->name} has been marked as ended. The records have been archived here.")
                ->with('auto_download_scores', route('admin.past-presentations.export-scores'))
                ->with('auto_download_attendance', route('admin.past-presentations.export-attendance'));

        } catch (\Exception \$e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Failed to end schedule: ' . \$e->getMessage());
        }
    }
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents('app/Http/Controllers/Admin/MilestoneTemplateController.php', $content);
echo "Replaced controller successfully.";

// Modify the view to trigger the download
$view = file_get_contents('resources/views/admin/past-presentations/index.blade.php');

$js = <<<HTML
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('auto_download_scores'))
            setTimeout(function() {
                window.open("{{ session('auto_download_scores') }}", "_blank");
            }, 1000);
        @endif
        
        @if(session('auto_download_attendance'))
            setTimeout(function() {
                window.open("{{ session('auto_download_attendance') }}", "_blank");
            }, 2500);
        @endif
    });
</script>
@endpush
HTML;

$view = str_replace('@endsection', $js, $view);
file_put_contents('resources/views/admin/past-presentations/index.blade.php', $view);
echo "\nReplaced view successfully.";
