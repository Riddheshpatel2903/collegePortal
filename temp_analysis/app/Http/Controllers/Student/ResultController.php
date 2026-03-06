<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\Student;

class ResultController extends Controller
{
    public function index()
    {
        $student = Student::with(['user', 'course'])->where('user_id', auth()->id())->first();
        if (!$student) {
            abort(403, 'Student profile not found.');
        }

        $results = Result::query()
            ->with(['resultSubjects.subject'])
            ->where('student_id', $student->id)
            ->where('result_status', '!=', 'pending')
            ->orderByDesc('semester_number')
            ->paginate(8);

        return view('student.results.index', compact('student', 'results'));
    }
}
