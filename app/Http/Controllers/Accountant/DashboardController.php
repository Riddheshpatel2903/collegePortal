<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\StudentFee;

class DashboardController extends Controller
{
    public function index()
    {
        $allFees = StudentFee::all();
        $totalExpected = $allFees->sum('total_amount');
        $totalCollected = $allFees->sum('paid_amount');
        $totalPending = $allFees->where('status', '!=', 'paid')->sum('pending_amount');

        // prepare monthly revenue for chart
        $monthly = StudentFee::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month")
                        ->selectRaw('SUM(paid_amount) as revenue')
                        ->groupBy('month')
                        ->orderBy('month')
                        ->get();

        $revenueLabels = $monthly->pluck('month')->toArray();
        $revenueData   = $monthly->pluck('revenue')->toArray();

        $thisMonthRevenue = !empty($revenueData) ? end($revenueData) : 0;

        $collectionLabels = ['Collected','Pending'];
        $collectionData   = [(float)$totalCollected, (float)$totalPending];

        return view('accountant.dashboard', compact(
            'totalExpected',
            'totalCollected',
            'totalPending',
            'revenueLabels',
            'revenueData',
            'thisMonthRevenue',
            'collectionLabels',
            'collectionData'
        ));
    }
}
