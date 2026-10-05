<?php

namespace App\Http\Controllers;

use App\Models\Cohort;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index()
    {
        $sessions = Cohort::orderBy('start_date', 'desc')->paginate(10);
        return view('admin.sessions.index', compact('sessions'));
    }

    public function create()
    {
        return view('admin.sessions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        Cohort::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'program_id' => null, // Global session
        ]);

        return redirect()->route('sessions.index')->with('success', 'Academic Session created successfully.');
    }
    
    public function edit(Cohort $session)
    {
        return view('admin.sessions.edit', compact('session'));
    }

    public function update(Request $request, Cohort $session)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $session->update($request->only('name', 'start_date', 'end_date'));

        return redirect()->route('sessions.index')->with('success', 'Academic Session updated successfully.');
    }
    public function show(Request $request, Cohort $session)
    {
        $query = $session->students()->with(['user', 'program', 'level']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('student_id_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('program_filter') && $request->program_filter != '') {
             $query->where('program_id', $request->program_filter);
        }

        $students = $query->latest()->paginate(20);
        $programs = \App\Models\Program::all();

        return view('admin.sessions.show', compact('session', 'students', 'programs'));
    }
}
