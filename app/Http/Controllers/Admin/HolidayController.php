<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::orderBy('date', 'asc')->get();
        return view('admin.holidays.index', compact('holidays'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date',
            'description' => 'nullable|string',
            'is_recurring' => 'boolean',
        ]);

        Holiday::create($validated);

        return redirect()->back()->with('success', 'Holiday added successfully.');
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date,' . $holiday->id,
            'description' => 'nullable|string',
            'is_recurring' => 'boolean',
        ]);

        $holiday->update($validated);

        return redirect()->back()->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return redirect()->back()->with('success', 'Holiday deleted successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) continue;

            $holidayData = [
                'name' => $row[0],
                'date' => date('Y-m-d', strtotime($row[1])),
                'description' => $row[2] ?? null,
                'is_recurring' => isset($row[3]) ? (bool)$row[3] : false,
            ];

            Holiday::updateOrCreate(
                ['date' => $holidayData['date']],
                $holidayData
            );
            $count++;
        }
        fclose($handle);

        return redirect()->back()->with('success', "Imported $count holidays successfully.");
    }
}
