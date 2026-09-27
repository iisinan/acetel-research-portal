<?php

namespace App\Http\Controllers;

use App\Services\ThesisService;
use App\Http\Requests\CreateThesisRequest;
use App\Http\Requests\AssignSupervisorRequest;
use App\Models\ThesisProject;
use App\Models\StudentProfile;
use App\Models\SupervisorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThesisController extends Controller
{
    protected $thesisService;

    public function __construct(ThesisService $thesisService)
    {
        $this->thesisService = $thesisService;
    }

    public function store(CreateThesisRequest $request)
    {
        $user = Auth::user();
        $studentProfile = StudentProfile::where('user_id', $user->id)->firstOrFail();

        try {
            $project = $this->thesisService->createProject($studentProfile, $request->validated());
            if ($request->wantsJson()) {
                return response()->json($project, 201);
            }
            return redirect()->back()->with('success', 'Thesis project created successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 400);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, ThesisProject $thesis)
    {
        $this->authorize('update', $thesis);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'abstract' => 'nullable|string',
            'status' => 'sometimes|string|in:proposed,active,submitted,completed,archived'
        ]);

        // Only Admin, Director, or Supervisor can change status
        if ($request->has('status') && $request->status !== $thesis->status) {
             if (!Auth::user()->hasAnyRole(['Admin', 'Director', 'Supervisor'])) {
                 unset($validated['status']);
             }
        }

        $thesis->update($validated);

        return redirect()->back()->with('success', 'Thesis updated successfully.');
    }

    public function show(\App\Models\ThesisProject $thesis)
    {
        $user = Auth::user();

        // Access Control
        $this->authorize('view', $thesis);
        

        $thesis->load(['student.user', 'milestones.template', 'messages.sender', 'supervisors.supervisor.user', 'defenceEvents.panelMembers', 'actionItems']);
        
        // Institutional Isolation: Restrict supervisor pool to student's matching program context
        $allSupervisors = SupervisorProfile::with('user')
            ->whereHas('programs', function($q) use ($thesis) {
                $q->where('programs.id', $thesis->student->program_id);
            })
            ->get();
        $action_items = $thesis->actionItems()->orderBy('due_date', 'asc')->get();

        // Identify Institutional Mentions for the Comm-Link
        $potentialMentions = collect();
        if ($thesis->student && $thesis->student->user) {
            $potentialMentions->push($thesis->student->user);
        }
        foreach ($thesis->supervisors as $assignment) {
            if ($assignment->supervisor && $assignment->supervisor->user) {
                $potentialMentions->push($assignment->supervisor->user);
            }
        }
        
        // Add Coordinators for this program
        $coordinators = \App\Models\CoordinatorProfile::where('program_id', $thesis->student->program_id)
            ->where('active', true)
            ->with('user')
            ->get()
            ->pluck('user');
        
        foreach ($coordinators as $coord) {
            if ($coord) $potentialMentions->push($coord);
        }

        $potentialMentions = $potentialMentions->unique('id');

        return view('theses.show', compact('thesis', 'allSupervisors', 'action_items', 'potentialMentions'));
    }

    public function assignSupervisor(AssignSupervisorRequest $request, ThesisProject $thesis)
    {
        $this->authorize('assignSupervisor', $thesis);
        
        try {
            if ($request->action === 'redistribute') {
                $this->thesisService->proposeRandomSupervisors($thesis);
                
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'proposed_ids' => $thesis->fresh()->proposed_supervisors ?? []
                    ]);
                }
                
                return redirect()->back()->with('success', 'System has proposed a randomized supervision panel. Please review and authorize.');
            }

            if ($request->has('supervisors') || $request->has('supervisor_ids')) {
                $ids = $request->input('supervisors', $request->input('supervisor_ids'));
                
                // Ensure the list of supervisors is unique. A supervisor cannot be assigned more than once
                // to a single student (e.g. as both main and secondary).
                if (count($ids) !== count(array_unique($ids))) {
                    throw new \Exception("A supervisor cannot be attached to one student multiple times.");
                }

                // Bulk Assignment (Replace)
                $this->thesisService->replaceSupervisors($thesis, $ids);
                $message = 'Institutional supervision panel authorized successfully.';
                $data = ['success' => true, 'message' => $message];
            } else {
                // Single Assignment (Legacy)
                $supervisor = SupervisorProfile::findOrFail($request->supervisor_profile_id);
                $data = $this->thesisService->assignSupervisor(
                    $thesis, 
                    $supervisor, 
                    $request->role
                );
                $message = 'Supervisor assigned successfully.';
                $data['message'] = $message;
            }

            if ($request->wantsJson()) {
                return response()->json($data, 201);
            }
            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage(), 'success' => false], 400);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function assignInternalExaminer(Request $request, ThesisProject $thesis)
    {
        $request->validate([
            'internal_examiner_profile_id' => 'required|exists:internal_examiner_profiles,id'
        ]);
        
        // Ensure Admin or Program Coordinator
        if (!Auth::user()->hasAnyRole(['Admin', 'Program Coordinator'])) {
            abort(403, 'Unauthorized action.');
        }
        
        $thesis->update([
            'internal_examiner_profile_id' => $request->internal_examiner_profile_id
        ]);
        
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Internal Examiner assigned successfully.', 'thesis_id' => $thesis->id]);
        }
        
        return redirect()->back()->with('success', 'Internal Examiner assigned successfully.');
    }

    public function assignExternalExaminer(Request $request, ThesisProject $thesis)
    {
        $request->validate([
            'external_examiner_profile_id' => 'required|exists:external_examiner_profiles,id'
        ]);
        
        // Ensure Admin or Program Coordinator
        if (!Auth::user()->hasAnyRole(['Admin', 'Program Coordinator'])) {
            abort(403, 'Unauthorized action.');
        }
        
        $thesis->update([
            'external_examiner_profile_id' => $request->external_examiner_profile_id
        ]);
        
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'External Examiner assigned successfully.', 'thesis_id' => $thesis->id]);
        }
        
        return redirect()->back()->with('success', 'External Examiner assigned successfully.');
    }

    public function clearForInternal(Request $request, ThesisProject $thesis)
    {
        // Only Supervisor (authorized via policy usually, but direct check here for safety)
        if (!Auth::user()->hasRole('Supervisor')) {
            abort(403, 'Only supervisors can clear a thesis for internal defense.');
        }

        // Check if user is assigned
        if (!$thesis->supervisors->contains(Auth::user()->supervisorProfile->id)) {
            abort(403, 'You are not an assigned supervisor for this project.');
        }

        $thesis->update([
            'cleared_for_internal_at' => now(),
            'status' => 'submitted' // Move status to submitted? Or keep active? 'submitted' makes sense.
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Thesis cleared for Internal Defense.', 'thesis_id' => $thesis->id]);
        }
        
        return redirect()->back()->with('success', 'Thesis cleared for Internal Defense. Admin has been notified.');
    }
}
