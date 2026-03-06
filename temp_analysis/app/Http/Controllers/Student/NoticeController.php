<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Notice;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::with('user')
            ->where('is_active', true)
            ->whereIn('target_role', ['all', 'student'])
            ->latest()
            ->paginate(12);

        return view('student.notices.index', compact('notices'));
    }
}
