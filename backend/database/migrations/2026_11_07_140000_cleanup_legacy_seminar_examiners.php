<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\MilestoneTemplate;
use App\Models\StudentMilestone;
use App\Models\DefenceEvent;
use App\Models\PanelMember;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cleans up legacy global examiner assignments on seminar_as_a_course template metadata
     * and deletes erroneously auto-attached PanelMember examiner records for seminar defence events.
     */
    public function up(): void
    {
        $template = MilestoneTemplate::where('slug', 'seminar_as_a_course')->first();
        if (!$template) {
            return;
        }

        $meta = $template->metadata ?? [];
        $groupExaminers = $meta['group_examiners'] ?? [];

        // 1. Remove legacy global examiner IDs from seminar_as_a_course metadata
        $changed = false;
        if (isset($meta['examiner_profile_ids'])) {
            unset($meta['examiner_profile_ids']);
            $changed = true;
        }
        if (isset($meta['examiner_user_ids'])) {
            unset($meta['examiner_user_ids']);
            $changed = true;
        }
        if ($changed) {
            $template->metadata = $meta;
            $template->save();
        }

        // 2. Find all seminar milestones for this template
        $milestones = StudentMilestone::where('milestone_template_id', $template->id)->get();

        foreach ($milestones as $milestone) {
            $thesisId = $milestone->thesis_project_id;
            if (!$thesisId) {
                continue;
            }

            $track = $milestone->discipline_group;
            $validUserIds = $groupExaminers[$track]['user_ids'] ?? [];

            // Find defence events of type 'seminar'
            $events = DefenceEvent::where('thesis_project_id', $thesisId)
                ->where('type', 'seminar')
                ->get();

            foreach ($events as $event) {
                // Find all panel members with role 'examiner'
                $examinerPms = PanelMember::where('defence_event_id', $event->id)
                    ->where('role', 'examiner')
                    ->get();

                foreach ($examinerPms as $pm) {
                    // If this examiner is not legitimately assigned to this track in group_examiners, delete it
                    if (empty($validUserIds) || !in_array($pm->user_id, $validUserIds)) {
                        $pm->delete();
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed
    }
};
