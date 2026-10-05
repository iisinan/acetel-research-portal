<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SupervisorProfile;
use App\Models\InternalExaminerProfile;
use App\Models\ExternalExaminerProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExaminerPoolController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $scopes = $user->coordinatorScopes();
        
        if ($scopes->isEmpty()) {
            abort(403, 'NO ACTIVE COORDINATOR PROFILE FOUND.');
        }

        $programIds = $scopes->pluck('program_id')->unique()->toArray();

        // Internal Examiners (Supervisors from this program who are also examiners)
        $internalExaminers = InternalExaminerProfile::with('user')
            ->whereIn('program_id', $programIds)
            ->get();

        // External Examiners
        $externalExaminers = ExternalExaminerProfile::with('user')->get();

        // Potential internal examiners (Supervisors in this program not yet examiners)
        $potentialInternal = SupervisorProfile::with('user')
            ->whereHas('programs', function($q) use ($programIds) {
                $q->whereIn('programs.id', $programIds);
            })
            ->whereDoesntHave('user.internalExaminerProfiles', function($q) use ($programIds) {
                $q->whereIn('program_id', $programIds);
            })
            ->get();

        return view('coordinator.examiners.index', compact(
            'internalExaminers', 
            'externalExaminers', 
            'potentialInternal'
        ));
    }

    public function storeInternal(Request $request)
    {
        $request->validate([
            'supervisor_id' => 'required|exists:supervisor_profiles,id',
        ]);

        $supervisor = SupervisorProfile::with('programs')->findOrFail($request->supervisor_id);
        
        $user = Auth::user();
        if ($user->hasRole('Program Coordinator')) {
            $user->ensureCoordinatorProfiles();
        }
        $coordinatorProfile = $user->coordinatorProfiles()->where('active', true)->first();
        if (!$user->hasAnyRole(['Admin', 'Director']) && (!$coordinatorProfile || !$supervisor->programs->contains($coordinatorProfile->program_id))) {
             abort(403, 'Unauthorized. Supervisor is not in your program.');
        }

        $progId = $coordinatorProfile ? $coordinatorProfile->program_id : $supervisor->programs->first()?->id;

        InternalExaminerProfile::firstOrCreate([
            'user_id' => $supervisor->user_id,
            'program_id' => $progId,
        ]);

        if (!$supervisor->user->hasRole('Internal Examiner')) {
            $supervisor->user->assignRole('Internal Examiner');
        }

        return back()->with('success', 'Supervisor added to examiner pool.');
    }

    public function storeExternal(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'institution' => 'required|string|max:255',
            'expertise' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make(Str::random(16)),
                'is_active' => true,
            ]);

            $user->assignRole('External Examiner');

            ExternalExaminerProfile::create([
                'user_id' => $user->id,
                'institution' => $validated['institution'],
                'expertise' => $validated['expertise'],
            ]);

            DB::commit();
            return back()->with('success', 'External examiner created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create external examiner: ' . $e->getMessage());
        }
    }

    public function storeExternalFromSupervisor(Request $request)
    {
        $request->validate([
            'supervisor_id' => 'required|exists:supervisor_profiles,id',
            'institution' => 'required|string|max:255',
        ]);

        $supervisor = SupervisorProfile::findOrFail($request->supervisor_id);

        ExternalExaminerProfile::firstOrCreate([
            'user_id' => $supervisor->user_id,
        ], [
            'institution' => $request->institution,
            'expertise' => $supervisor->specialization,
        ]);

        if (!$supervisor->user->hasRole('External Examiner')) {
            $supervisor->user->assignRole('External Examiner');
        }

        return back()->with('success', 'Supervisor upgraded to External Examiner.');
    }

    public function toggleStatus($id, $type)
    {
        $profile = null;
        if ($type === 'internal') {
            $profile = InternalExaminerProfile::findOrFail($id);
        } else {
            $profile = ExternalExaminerProfile::findOrFail($id);
        }

        $profile->update(['active' => !$profile->active]);

        return back()->with('success', 'Status updated successfully.');
    }
}
