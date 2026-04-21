<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Result;
use Illuminate\Http\Request;

class InternalMarkController extends Controller
{
    public function index(Request $request)
    {
        $department = Department::query()->where('hod_id', auth()->id())->firstOrFail();

        $results = Result::query()
            ->with(['student.user', 'student.course'])
            ->whereHas('student.course', fn ($q) => $q->where('department_id', $department->id))
            ->searchStudent((string) $request->input('search'))
            ->when($request->filled('semester_number'), fn ($q) => $q->where('semester_number', (int) $request->semester_number))
            ->orderByDesc('semester_number')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('hod.internal-marks.index', compact('results'));
    }
}
