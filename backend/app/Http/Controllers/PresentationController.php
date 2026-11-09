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

        $targetEventType = $template->defence_type ?? match($template->slug) {
            'seminar_as_a_course' => 'seminar',
            'proposal_defence' => 'proposal',
            'progress_report_1' => 'progress_report_1',
            'progress_report_2' => 'progress_report_2',
            'internal_defence' => 'internal',
            'viva' => 'external',
            default => 'first_seminar',
        };

        // Ensure all scheduled milestones have their corresponding DefenceEvent created
        $missingEventFound = false;
        foreach ($allScheduled as $sm) {
            if ($sm->thesis && !$sm->thesis->defenceEvents->where('type', $targetEventType)->first()) {
                $ev = \App\Models\DefenceEvent::create([
                    'thesis_project_id' => $sm->thesis->id,
                    'type' => $targetEventType,
                    'schedule_start' => $sm->defence_date ? \Carbon\Carbon::parse($sm->defence_date)->setHour(9) : now(),
                    'schedule_end' => $sm->defence_date ? \Carbon\Carbon::parse($sm->defence_date)->setHour(10) : now()->addHour(),
                ]);

                $examinersToAttach = [];
                if ($template->slug === 'seminar_as_a_course') {
                    $examinersToAttach = $template->metadata['group_examiners'][$sm->discipline_group]['user_ids'] ?? [];
                } elseif (!empty($template->metadata['examiner_user_ids'])) {
                    $examinersToAttach = $template->metadata['examiner_user_ids'];
                }

                foreach ($examinersToAttach as $uId) {
                    \App\Models\PanelMember::firstOrCreate([
                        'defence_event_id' => $ev->id,
                        'user_id' => $uId,
                        'role' => 'examiner',
                    ], [
                        'invitation_status' => 'accepted'
                    ]);
                }
                $missingEventFound = true;
            }
        }

        if ($missingEventFound) {
            $allScheduled->load([
                'thesis.defenceEvents.panelMembers.user',
                'thesis.defenceEvents.evaluations',
            ]);
        }

        $user = auth()->user();
        $isAdmin = $user && $user->hasRole('Admin');
        $isCoordinator = $user && $user->hasRole('Program Coordinator');
        $isSeminarCourse = $template->slug === 'seminar_as_a_course';

        // Check if current user is an examiner assigned to specific discipline track(s)
        $examinerTracks = [];
        $isScopedExaminer = false;

        if ($isSeminarCourse && $user && !$isAdmin) {
            // 1. Check template metadata for group_examiners
            $groupExaminers = $template->metadata['group_examiners'] ?? [];
            foreach ($groupExaminers as $trackKey => $info) {
                $uIds = $info['user_ids'] ?? [];
                $pIds = $info['profile_ids'] ?? [];
                if (in_array($user->id, $uIds) || ($user->supervisorProfile && in_array($user->supervisorProfile->id, $pIds))) {
                    $examinerTracks[] = $trackKey;
                }
            }

            // 2. Check panel members of defence events for this milestone template only if not found in group_examiners
            if (empty($examinerTracks)) {
                $panelTracks = $allScheduled->filter(function($sm) use ($user) {
                    return $sm->thesis?->defenceEvents?->contains(function($de) use ($user) {
                        return $de->panelMembers->contains(fn($pm) => $pm->user_id === $user->id && strtolower($pm->role ?? '') === 'examiner');
                    });
                })->pluck('discipline_group')->filter()->unique()->all();

                $examinerTracks = array_values(array_unique($panelTracks));
            }

            // If user is assigned as an examiner for specific track(s), scope to those track(s) only
            if (!empty($examinerTracks)) {
                $isScopedExaminer = true;
                $allScheduled = $allScheduled->whereIn('discipline_group', $examinerTracks)->values();
            }
        }

        $initialTrack = 'all';
        if ($isScopedExaminer && count($examinerTracks) === 1) {
            $initialTrack = $examinerTracks[0];
        } elseif ($user && $user->hasRole('Student') && $user->studentProfile?->discipline_group) {
            $initialTrack = $user->studentProfile->discipline_group;
        }

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
            if ($isScopedExaminer) {
                return redirect()->route('dashboard')
                    ->with('info', "There is currently no active presentation session scheduled for your assigned track in {$template->name}.");
            }

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

        $trackData = [];
        if ($isSeminarCourse) {
            $tracks = [
                'cybersecurity' => 'Cybersecurity',
                'artificial_intelligence' => 'Artificial Intelligence',
                'management_information_systems' => 'Management Information Systems',
            ];

            // If scoped examiner, only display their assigned track(s) in trackData
            if ($isScopedExaminer) {
                $tracks = array_intersect_key($tracks, array_flip($examinerTracks));
            }

            foreach ($tracks as $trackKey => $trackLabel) {
                $trackMilestones = $allScheduled->filter(fn($m) => $m->discipline_group === $trackKey);
                $trackToday = $todayPresenters->filter(fn($m) => $m->discipline_group === $trackKey);
                $trackMeetingLink = $trackToday->first(fn($m) => !empty($m->meeting_link))?->meeting_link
                    ?? $trackMilestones->first(fn($m) => !empty($m->meeting_link))?->meeting_link;

                $trackExaminerUserIds = $template->metadata['group_examiners'][$trackKey]['user_ids'] ?? [];
                $trackExaminers = !empty($trackExaminerUserIds) 
                    ? \App\Models\User::whereIn('id', $trackExaminerUserIds)->get()
                    : collect();

                $trackData[$trackKey] = [
                    'key' => $trackKey,
                    'label' => $trackLabel,
                    'total' => $trackMilestones->count(),
                    'today_count' => $trackToday->count(),
                    'meeting_link' => $trackMeetingLink,
                    'examiners' => $trackExaminers,
                ];
            }

            // If scoped examiner with 1 track, ensure master meeting link points to that track's Zoom room
            if ($isScopedExaminer && count($examinerTracks) === 1 && !empty($trackData[$examinerTracks[0]]['meeting_link'])) {
                $meetingLink = $trackData[$examinerTracks[0]]['meeting_link'];
            }
        }

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
            'allPresentationTabs',
            'isSeminarCourse',
            'trackData',
            'isScopedExaminer',
            'examinerTracks',
            'initialTrack'
        ));
    }
}
