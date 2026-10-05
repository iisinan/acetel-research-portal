<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThesisProject;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    /**
     * Display a listing of theses that have cleared Internal Defence (Audit Ready).
     */
    public function index(Request $request)
    {
        $query = ThesisProject::auditableByAdmin()
            ->with(['student.user', 'student.program', 'milestones.template']);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('student.user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $theses = $query->latest()->paginate(15);

        return view('admin.audit', compact('theses'));
    }

    /**
     * Display the specified thesis details for administrative audit.
     */
    public function show(ThesisProject $thesis)
    {
        if (!$thesis->isAuditableByAdmin()) {
            abort(403, 'This thesis has not yet reached the administrative audit level (Post-Internal Defence).');
        }

        $thesis->load(['student.user', 'student.program', 'milestones.template', 'milestones.submissions', 'messages.sender', 'assignments.supervisor.user']);

        return view('admin.audit-show', compact('thesis'));
    }
}
