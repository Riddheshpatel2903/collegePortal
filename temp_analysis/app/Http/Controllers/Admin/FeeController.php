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
        $fees = StudentFee::with(['student.user', 'student.course', 'student.semester'])->get();

        $summary = [
            'total' => $fees->sum('total_amount'),
            'collected' => $fees->sum('paid_amount'),
            'pending' => $fees->where('status', '!=', 'paid')->sum('pending_amount'),
            'unpaid' => $fees->where('status', 'pending')->sum('total_amount'), // Using pending as "unpaid"
        ];

        return view('admin.fees.index', compact('fees', 'summary'));
    }
}
