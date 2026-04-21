<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeaveController extends Controller
{
    /**
     * Display a global overview of all student leaves.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'role' => ['nullable', Rule::in(['student', 'teacher', 'hod'])],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        $stats = [
            'total' => Leave::count(),
            'pending' => Leave::where('status', 'pending')->count(),
            'approved' => Leave::where('status', 'approved')->count(),
            'rejected' => Leave::where('status', 'rejected')->count(),
        ];

        $leaves = Leave::query()
            ->withApplicantRelations()
            ->filter($filters)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $trends = Leave::query()
            ->filter($filters)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        return view('admin.leaves.index', [
            'stats' => $stats,
            'leaves' => $leaves,
            'trends' => $trends,
            'filters' => $filters,
        ]);
    }

    /**
     * Remove the specified leave from storage.
     */
    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);
        $leave->delete();

        return redirect()->route('admin.leaves.index')->with('success', 'Leave record deleted successfully.');
    }
}
