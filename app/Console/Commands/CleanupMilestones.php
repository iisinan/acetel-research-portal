<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupMilestones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-milestones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $duplicates = \Illuminate\Support\Facades\DB::table('student_milestones')
            ->select('thesis_project_id', 'milestone_template_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
            ->groupBy('thesis_project_id', 'milestone_template_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $totalDeleted = 0;

        foreach ($duplicates as $dup) {
            $milestones = \App\Models\StudentMilestone::where('thesis_project_id', $dup->thesis_project_id)
                ->where('milestone_template_id', $dup->milestone_template_id)
                ->orderBy('updated_at', 'desc')
                ->get();

            $statuses = ['approved' => 1, 'completed' => 2, 'submitted' => 3, 'in_progress' => 4, 'revision_required' => 5, 'not_started' => 6];
            
            $milestones = $milestones->sortBy(function($m) use ($statuses) {
                return $statuses[$m->status] ?? 99;
            });

            // Keep the first one (most progressed/recent), delete the rest
            $keep = $milestones->first();
            foreach ($milestones as $m) {
                if ($m->id !== $keep->id) {
                    $m->delete();
                    $totalDeleted++;
                }
            }
        }

        $this->info("Deleted {$totalDeleted} duplicate milestones.");
    }
}
