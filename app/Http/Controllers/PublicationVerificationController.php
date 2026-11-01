<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentMilestone;
use App\Models\ThesisProject;
use App\Services\ScopusVerificationService;

class PublicationVerificationController extends Controller
{
    /**
     * Live AJAX endpoint to verify a publication DOI against Scopus & Crossref/DataCite
     * and cross-reference its author list against assigned supervisors.
     */
    public function verifyDoi(Request $request, ScopusVerificationService $service)
    {
        $doi = $request->query('doi') ?? $request->input('doi');

        if (empty($doi)) {
            return response()->json([
                'valid' => false,
                'message' => 'Please provide a valid DOI string.',
            ], 422);
        }

        // Determine assigned supervisors for cross-referencing
        $supervisors = [];
        $milestoneId = $request->query('milestone_id') ?? $request->input('milestone_id');
        $thesisId = $request->query('thesis_id') ?? $request->input('thesis_id');

        if ($milestoneId) {
            $milestone = StudentMilestone::with('thesis.assignments.supervisor.user')->find($milestoneId);
            if ($milestone?->thesis) {
                $supervisors = $milestone->thesis->getSupervisorNames();
            }
        } elseif ($thesisId) {
            $thesis = ThesisProject::with('assignments.supervisor.user')->find($thesisId);
            if ($thesis) {
                $supervisors = $thesis->getSupervisorNames();
            }
        }

        // Fallback to currently logged-in student's thesis if available
        if (empty($supervisors) && auth()->check() && auth()->user()->hasRole('Student')) {
            $studentProfile = auth()->user()->studentProfile;
            if ($studentProfile?->thesis) {
                $supervisors = $studentProfile->thesis->getSupervisorNames();
            }
        }

        $result = $service->verify($doi, $supervisors);

        return response()->json($result);
    }
}
