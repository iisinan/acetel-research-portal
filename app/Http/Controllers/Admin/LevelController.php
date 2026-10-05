<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LevelController extends Controller
{
    public function index()
    {
        $levels = Level::latest()->paginate(10); // View student count?
        return view('admin.levels.index', compact('levels'));
    }

    public function create()
    {
        return view('admin.levels.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:levels',
        ]);

        Level::create($validated);

        return redirect()->route('admin.levels.index')->with('success', 'Level created successfully.');
    }

    public function edit(Level $level)
    {
        return view('admin.levels.edit', compact('level'));
    }

    public function update(Request $request, Level $level)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('levels')->ignore($level->id)],
        ]);

        $level->update($validated);

        return redirect()->route('admin.levels.index')->with('success', 'Level updated successfully.');
    }

    public function destroy(Level $level)
    {
        // Check usage before delete
        // Assuming student_profiles has level_id
        // if ($level->students()->count() > 0) ... 
        
        $level->delete();
        return redirect()->route('admin.levels.index')->with('success', 'Level deleted successfully.');
    }
}
