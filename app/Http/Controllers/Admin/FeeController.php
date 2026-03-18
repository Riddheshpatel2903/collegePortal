<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentFee;
use App\Services\FeeService;

class FeeController extends Controller
{
    public function __construct(private FeeService $feeService)
    {
    }

    public function index()
    {
        return view('admin.fees.index');
    }

    public function data(Request $request)
    {
        $query = StudentFee::with(['student.user', 'student.course', 'student.semester']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('roll_number', 'like', "%{$search}%")
                    ->orWhere('gtu_enrollment_no', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->input('per_page', 15);
        $fees = $query->orderBy('due_date', 'desc')->paginate($perPage);

        $summary = [
            'total' => $fees->sum('total_amount'),
            'collected' => $fees->sum('paid_amount'),
            'pending' => $fees->where('status', '!=', 'paid')->sum('pending_amount'),
            'unpaid' => $fees->where('status', 'pending')->sum('total_amount'),
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Data loaded',
            'data' => [
                'fees' => $fees->items(),
                'meta' => [
                    'current_page' => $fees->currentPage(),
                    'last_page' => $fees->lastPage(),
                    'total' => $fees->total(),
                ],
                'summary' => $summary,
            ],
        ]);
    }
}
