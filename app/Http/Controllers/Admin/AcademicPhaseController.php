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

            // Sync Semesters based on phase
            $currentSession = \App\Models\AcademicSession::where('is_current', true)->first();
            if ($currentSession) {
                $isOdd = $validated['phase_name'] === 'Odd';

                // Update based on parity
                \App\Models\Semester::where('academic_session_id', $currentSession->id)
                    ->whereRaw('MOD(semester_number, 2) = ?', [$isOdd ? 1 : 0])
                    ->update([
                        'status' => 'active',
                        'is_current' => true,
                    ]);

                \App\Models\Semester::where('academic_session_id', $currentSession->id)
                    ->whereRaw('MOD(semester_number, 2) = ?', [$isOdd ? 0 : 1])
                    ->update([
                        'status' => $isOdd ? 'upcoming' : 'completed',
                        'is_current' => false,
                    ]);
            }
        });

        return back()->with('success', "Academic phase switched to {$validated['phase_name']}.");
    }
}
