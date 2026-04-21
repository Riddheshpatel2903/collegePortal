<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Result;
use App\Services\GtuResultImportService;
use App\Services\ResultService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function __construct(
        private GtuResultImportService $importService,
        private ResultService $resultService
    ) {}

    public function index(Request $request)
    {
        $query = Result::query()
            ->with(['student.user', 'student.course'])
            ->searchStudent((string) $request->input('search'))
            ->when($request->filled('semester_number'), fn ($q) => $q->where('semester_number', (int) $request->semester_number))
            ->when($request->filled('course_id'), fn ($q) => $q->where('course_id', (int) $request->course_id))
            ->orderByDesc('semester_number')
            ->orderByDesc('id');

        $results = $query->paginate(20)->withQueryString();
        $courses = Course::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.results.index', compact('results', 'courses'));
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'semester_number' => ['required', 'integer', 'min:1', 'max:8'],
            'result_file' => ['required', 'file', 'mimes:csv,xlsx,pdf', 'max:10240'],
        ]);

        $summary = $this->importService->import(
            $request->file('result_file'),
            (int) $validated['semester_number'],
            (int) auth()->id()
        );

        return back()->with('success', "Import complete. Processed: {$summary['processed']}, matched: {$summary['matched']}, not found: {$summary['not_found']}.");
    }

    public function lock(Result $result)
    {
        $this->resultService->lockResult($result, (int) auth()->id());

        return back()->with('success', 'Result locked successfully.');
    }
}
