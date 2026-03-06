<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notice;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::with('user')
            ->where('is_active', true)
            ->whereIn('target_role', ['all', 'teacher'])
            ->latest()
            ->paginate(12);

        return view('teacher.notices.index', compact('notices'));
    }

    public function create()
    {
        return view('teacher.notices.create');
    }

    public function store(Request $request)
    {
        Notice::create([
            'title' => $request->title,
            'content' => $request->description,
            'posted_by' => auth()->id(),
            'target_role' => $request->target_role ?? 'student',
            'notice_for' => 'students',
            'is_active' => true,
        ]);

        return back()->with('success', 'Notice posted.');
    }
}
