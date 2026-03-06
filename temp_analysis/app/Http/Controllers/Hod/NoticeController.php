<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index()
    {
        $department = Department::where('hod_id', auth()->id())->firstOrFail();

        $notices = Notice::where('department_id', $department->id)
            ->latest()
            ->paginate(15);

        return view('hod.notices.index', compact('notices'));
    }

    public function create()
    {
        return view('hod.notices.create');
    }

    public function store(Request $request)
    {
        $department = Department::where('hod_id', auth()->id())->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_role' => 'required|in:all,student,teacher,hod',
            'priority' => 'required|in:low,medium,high,urgent',
            'expiry_date' => 'nullable|date|after_or_equal:today',
        ]);

        Notice::create($validated + [
            'department_id' => $department->id,
            'notice_for' => 'all',
            'is_active' => true,
            'posted_by' => auth()->id(),
        ]);

        return redirect()->route('hod.notices.index')->with('success', 'Notice published.');
    }
}
