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
        $allScheduled = StudentMilestone::where('milestone_template_id', $template->id)
            ->whereNotNull('defence_date')
            ->where('status', '!=', 'approved')
            ->with([
                'thesis.student.user',
                'thesis.student.program',
                'thesis.assignments.supervisor.user', 'thesis.defenceEvents.panelMembers.user', 'thesis.defenceEvents.evaluations',
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

        // Retrieve all active presentation templates with scheduled candidates
        $allPresentationTabs = MilestoneTemplate::getActivePresentations()
            ->filter(fn($pt) => $pt->studentMilestones->where('status', '!=', 'approved')->whereNotNull('defence_date')->isNotEmpty())
            ->values();

        // If there are NO active scheduled candidates for this template, redirect gracefully
        if ($allScheduled->isEmpty()) {
            if ($allPresentationTabs->isNotEmpty()) {
                return redirect()->route('presentations.show', $allPresentationTabs->first()->id)
                    ->with('info', "Presentation schedule for {$template->name} is not active. Switched to active session.");
            }

            if (auth()->user()?->hasRole('Admin')) {
                return redirect()->route('admin.milestone-templates.index')
                    ->with('info', "Presentation schedule for {$template->name} has been cancelled or has concluded.");
            }

            return redirect()->route('dashboard')
                ->with('info', "There is currently no active presentation session for {$template->name}.");
        }

        // Ensure current active presentation template is present in tabs
        if (!$allPresentationTabs->contains('id', $template->id)) {
            $allPresentationTabs->push($template);
            $allPresentationTabs = $allPresentationTabs->sortBy('order')->values();
        }

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
            'endDate',
            'allPresentationTabs'
        ));
    }
}
