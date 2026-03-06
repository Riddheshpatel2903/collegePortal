<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPhase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AcademicPhaseController extends Controller
{
    public function index()
    {
        $phases = AcademicPhase::query()->orderBy('id')->get();
        return view('admin.academic-phase.index', compact('phases'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'phase_name' => ['required', Rule::in(['Odd', 'Even'])],
        ]);

        DB::transaction(function () use ($validated) {
            AcademicPhase::query()->update(['is_active' => false]);
            AcademicPhase::query()
                ->where('phase_name', $validated['phase_name'])
                ->update(['is_active' => true]);
        });

        return back()->with('success', "Academic phase switched to {$validated['phase_name']}.");
    }
}
