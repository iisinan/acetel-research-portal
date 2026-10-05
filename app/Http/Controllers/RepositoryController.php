<?php

namespace App\Http\Controllers;

use App\Models\ThesisProject;
use Illuminate\Http\Request;

class RepositoryController extends Controller
{
    /**
     * Display a public listing of VIVA-cleared research.
     */
    public function index(Request $request)
    {
        $query = ThesisProject::publiclyVisible()
            ->with(['student.user', 'student.program', 'milestones.template', 'milestones.submissions']);

        // Search Filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('abstract', 'like', "%{$search}%")
                  ->orWhere('keywords', 'like', "%{$search}%")
                  ->orWhereHas('student.user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Year Filters
        if ($request->has('year') && !empty($request->year)) {
            $query->whereYear('created_at', $request->year);
        }

        if ($request->has('year_from') && !empty($request->year_from)) {
            $query->whereYear('created_at', '>=', $request->year_from);
        }

        if ($request->has('year_to') && !empty($request->year_to)) {
            $query->whereYear('created_at', '<=', $request->year_to);
        }

        // Sorting
        $sort = $request->get('sort', 'relevance'); // default to relevance or desc depending on if search is active
        if ($sort === 'date' || empty($request->search)) {
            $query->latest();
        } else {
            // Basic relevance is somewhat addressed by the default ID order if not exact search, 
            // but we'll apply latest as well if sort is specified as date. 
            // In a real full-text-search, relevance would be handled by DB scoring.
            // For now, if 'relevance', we just don't strictly order by date unless requested,
            // or we order by updated_at.
            $query->orderBy('updated_at', 'desc');
        }

        $theses = $query->paginate(15)->appends($request->all());

        return view('repository.index', compact('theses'));
    }

    /**
     * Display the specified public thesis details.
     */
    public function show(ThesisProject $thesis)
    {
        if (!$thesis->isPubliclyVisible()) {
            abort(403, 'This thesis has not yet reached the institutional clearance level for public repository access.');
        }

        $thesis->load(['student.user', 'student.program', 'milestones.template', 'milestones.submissions', 'assignments.supervisor.user']);

        return view('repository.show', compact('thesis'));
    }

    /**
     * Securely serve a public submission artifact.
     */
    public function viewSubmission(\App\Models\Submission $submission)
    {
        $thesis = $submission->milestone->thesis;

        if (!$thesis->isPubliclyVisible()) {
            abort(403, 'Unauthorized access to non-public artifact.');
        }

        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($submission->file_url)) {
            abort(404, 'Artifact not found.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->response($submission->file_url, $submission->file_meta['original_name'] ?? 'artifact');
    }
}
