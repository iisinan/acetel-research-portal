<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternalExaminerProfile;
use App\Models\User;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InternalExaminerController extends Controller
{
    public function index()
    {
        $examiners = InternalExaminerProfile::with('user', 'program')->latest()->paginate(10);
        return view('admin.internal_examiners.index', compact('examiners'));
    }

    public function create()
    {
        $programs = Program::all();
        return view('admin.internal_examiners.create', compact('programs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'program_id' => 'nullable|exists:programs,id',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => true,
            ]);

            $user->assignRole('Internal Examiner');

            InternalExaminerProfile::create([
                'user_id' => $user->id,
                'program_id' => $request->program_id,
                'active' => true,
            ]);
        });

        return redirect()->route('admin.internal-examiners.index')->with('success', 'Internal Examiner created successfully.');
    }

    public function edit(InternalExaminerProfile $internalExaminer)
    {
        $programs = Program::all();
        $internalExaminer->load('user');
        return view('admin.internal_examiners.edit', compact('internalExaminer', 'programs'));
    }

    public function update(Request $request, InternalExaminerProfile $internalExaminer)
    {
        $user = $internalExaminer->user;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'program_id' => 'nullable|exists:programs,id',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        DB::transaction(function () use ($request, $user, $internalExaminer) {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'is_active' => $request->has('active'),
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            $internalExaminer->update([
                'program_id' => $request->program_id,
                'active' => $request->has('active'),
            ]);
        });

        return redirect()->route('admin.internal-examiners.index')->with('success', 'Internal Examiner updated successfully.');
    }

    public function destroy(InternalExaminerProfile $internalExaminer)
    {
        DB::transaction(function () use ($internalExaminer) {
            $user = $internalExaminer->user;
            $internalExaminer->delete();
            $user->delete();
        });

        return redirect()->route('admin.internal-examiners.index')->with('success', 'Internal Examiner deleted successfully.');
    }
}
