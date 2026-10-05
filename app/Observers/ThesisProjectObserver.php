<?php

namespace App\Observers;

use App\Models\ThesisProject;
use App\Models\MilestoneTemplate;
use App\Models\StudentMilestone;

class ThesisProjectObserver
{
    /**
     * Handle the ThesisProject "created" event.
     */
    public function created(ThesisProject $thesisProject): void
    {
        $thesisProject->syncMilestones();
    }
}
