<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notice;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::with('user')->latest()->get();
        return view('admin.notices.index', compact('notices'));
    }

    public function create()
    {
        return view('admin.notices.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'target_role' => 'required|in:all,student,teacher,hod'
        ]);

        Notice::create([
            'title' => $request->title,
            'content' => $request->content,
            'posted_by' => auth()->id(),
            'target_role' => $request->target_role,
            'notice_for' => 'all',
            'is_active' => true,
        ]);

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice posted successfully.');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();

        return redirect()->route('admin.notices.index')
            ->with('success', 'Notice deleted successfully.');
    }
}
