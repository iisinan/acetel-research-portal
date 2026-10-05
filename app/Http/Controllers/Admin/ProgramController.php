<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\User;
use App\Models\Level;
use App\Models\CoordinatorProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::withCount('students')->with('coordinatorProfiles.user')->latest()->paginate(10);
        return view('admin.programs.index', compact('programs'));
    }

    public function create()
    {
        return view('admin.programs.create');
    }

    public function store(Request $request)
    {
        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $request->name), 0, 4)) . rand(1000, 9999);
        $request->merge([
            'code' => $code,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:programs',
            'code' => 'required|string|max:10|unique:programs',
            'serial_number' => 'nullable|string|max:20|unique:programs',
            'degree_type' => 'required|in:MSc,PhD',
        ]);

        Program::create($validated);

        return redirect()->route('admin.programs.index')->with('success', 'Program created successfully.');
    }

    public function edit(Program $program)
    {
        $coordinators = User::role('Program Coordinator')->get();
        // Get current coordinator user id (if any)
        $currentCoordinatorId = $program->coordinatorProfiles()->first()?->user_id;

        return view('admin.programs.edit', compact('program', 'coordinators', 'currentCoordinatorId'));
    }

    public function update(Request $request, Program $program)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('programs')->ignore($program->id)],
            'serial_number' => ['nullable', 'string', 'max:20', 'uppercase', Rule::unique('programs')->ignore($program->id)],
            'degree_type' => ['required', 'in:MSc,PhD'],
            'coordinator_id' => ['nullable', 'exists:users,id'],
        ]);

        $program->update([
            'name' => $validated['name'],
            'degree_type' => $validated['degree_type'],
        ]);

        // Handle Coordinator Assignment
        if ($request->has('coordinator_id')) {
            // Remove existing coordinator assignments for THIS program
            $program->coordinatorProfiles()->delete();

            if ($validated['coordinator_id']) {
                $levels = Level::all();
                foreach ($levels as $level) {
                    CoordinatorProfile::create([
                        'user_id' => $validated['coordinator_id'],
                        'program_id' => $program->id,
                        'level_id' => $level->id,
                        'active' => true,
                    ]);
                }
            }
        }

        return redirect()->route('admin.programs.index')->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program)
    {
        if ($program->students()->count() > 0) {
            return back()->with('error', 'Cannot delete program with assigned students.');
        }

        $program->delete();
        return redirect()->route('admin.programs.index')->with('success', 'Program deleted successfully.');
    }
}
