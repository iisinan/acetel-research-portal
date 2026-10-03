<?php

namespace App\Http\Controllers;

use App\Models\MilestoneTemplate;
use App\Models\StudentMilestone;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PresentationController extends Controller
{
    public function show(Request $request, MilestoneTemplate $template)
    {
        $today = Carbon::today();
        $todayDateStr = $today->format('Y-m-d');

        // Retrieve all scheduled milestones for this template
        // Include pending scheduled milestones and milestones scheduled for today or future
        $allScheduled = StudentMilestone::where('milestone_template_id', $template->id)
            ->whereNotNull('defence_date')
            ->where(function ($q) use ($todayDateStr) {
                $q->where('status', '!=', 'approved')
                  ->orWhereDate('defence_date', '>=', $todayDateStr);
            })
            ->with([
                'thesis.student.user',
                'thesis.student.program',
                'thesis.student.cohort',
                'thesis.assignments.supervisor.user',
                'thesis.defenceEvents.panelMembers.user',
                'thesis.defenceEvents.evaluations',
                'submissions' => fn($q) => $q->latest()
            ])
            ->orderBy('defence_date', 'asc')
            ->orderBy('defence_time', 'asc')
            ->get();

        // Group by defence date string (Y-m-d)
        $groupedByDate = $allScheduled->groupBy(function($item) {
            return Carbon::parse($item->defence_date)->format('Y-m-d');
        });

        // Today's presenters
        $todayPresenters = $groupedByDate->get($todayDateStr, collect());

        // Master meeting link (prioritize today's link, or the earliest available link)
        $meetingLink = $todayPresenters->first(fn($m) => !empty($m->meeting_link))?->meeting_link 
            ?? $allScheduled->first(fn($m) => !empty($m->meeting_link))?->meeting_link;

        $presentationTitle = $template->presentation_title;

        // Determine date range for display
        $startDate = $allScheduled->first() ? Carbon::parse($allScheduled->first()->defence_date)->format('M d, Y') : null;
        $endDate = $allScheduled->last() ? Carbon::parse($allScheduled->last()->defence_date)->format('M d, Y') : null;

        return view('presentations.show', compact(
            'template',
            'allScheduled',
            'groupedByDate',
            'todayPresenters',
            'todayDateStr',
            'meetingLink',
            'presentationTitle',
            'startDate',
            'endDate'
        ));
    }
}
