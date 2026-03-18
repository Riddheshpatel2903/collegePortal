<?php

namespace App\Services\Timetable;

use App\Models\Subject;
use Illuminate\Support\Collection;

class CurriculumAnalyzer
{
    /**
     * Calculate total required slots per week for a set of subjects.
     */
    public function analyze(Collection $subjects): array
    {
        $totalLectureSlots = 0;
        $totalLabSlots = 0;
        $labBlocks = [];

        // Group subjects by year/semester to find max parallel load
        $loadsBySem = [];

        foreach ($subjects as $subject) {
            $sem = $subject->semester_number;
            $lectures = ($subject->lecture_hours ?: 0) + ($subject->tutorial_hours ?: 0);
            $practicals = $subject->practical_hours ?: 0;

            if ($lectures === 0 && $practicals === 0) {
                if ($subject->is_lab) {
                    $practicals = $subject->weekly_hours ?: 4;
                } else {
                    $lectures = $subject->weekly_hours ?: 4;
                }
            }

            $totalLectureSlots += $lectures;
            $totalLabSlots += $practicals;
            $loadsBySem[$sem] = ($loadsBySem[$sem] ?? 0) + $lectures + $practicals;

            if ($practicals > 0) {
                $duration = $subject->lab_duration ?: 2;
                $numSessions = ceil($practicals / $duration);
                for ($i = 0; $i < $numSessions; $i++) {
                    $labBlocks[] = [
                        'subject_id' => $subject->id,
                        'duration' => $duration
                    ];
                }
            }
        }

        $maxRequiredSlotsPerWeek = !empty($loadsBySem) ? max($loadsBySem) : 0;

        return [
            'total_lecture_slots' => $totalLectureSlots,
            'total_lab_slots' => $totalLabSlots,
            'total_required_slots' => $maxRequiredSlotsPerWeek, // This is now per-year max
            'total_institutional_slots' => $totalLectureSlots + $totalLabSlots,
            'lab_blocks' => $labBlocks,
            'subjects' => $subjects
        ];
    }
}
