<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Student;

class PublicRepository extends Component
{
    public function render()
    {
        // Fetch students who have completed Viva
        $students = Student::whereHas('milestones', function ($query) {
            $query->whereHas('milestone', function ($q) {
                $q->where('name', 'Viva');
            })->where('status', 'Completed'); // Wait, registration marks them as pending.
            // Actually, just fetch students with publications or theses
        })->with(['user', 'documents' => function($q) {
            $q->where('file_name', 'Final_Thesis.pdf');
        }])->get();

        // Alternatively, since registration puts publications on the `students` table:
        $allStudents = Student::with('user')->whereNotNull('publications')->orWhereNotNull('thesis_title')->get();

        return view('livewire.public-repository', [
            'students' => $allStudents
        ]);
    }
}
