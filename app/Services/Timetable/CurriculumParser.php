<?php

namespace App\Services\Timetable;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;

class CurriculumParser
{
    /**
     * Parse a CSV file and return a collection of semester-grouped subject data.
     */
    public function parseCsv(UploadedFile $file): array
    {
        $semesters = [];
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $header = fgetcsv($handle);
            // Header mapping (heuristic)
            $map = $this->mapCsvHeaders($header);

            while (($data = fgetcsv($handle)) !== false) {
                $sem = $data[$map['semester']] ?? '1';
                if (! isset($semesters[$sem])) {
                    $semesters[$sem] = [
                        'semester' => $sem,
                        'subjects' => [],
                    ];
                }

                $semesters[$sem]['subjects'][] = [
                    'subject_code' => $data[$map['code']] ?? '',
                    'subject_name' => $data[$map['name']] ?? '',
                    'lecture_hours' => (int) ($data[$map['L']] ?? 0),
                    'tutorial_hours' => (int) ($data[$map['T']] ?? 0),
                    'practical_hours' => (int) ($data[$map['P']] ?? 0),
                    'credits' => (int) ($data[$map['C']] ?? 0),
                    'internal_marks' => (int) ($data[$map['internal']] ?? 0),
                    'external_marks' => (int) ($data[$map['external']] ?? 0),
                    'total_marks' => (int) ($data[$map['total']] ?? 0),
                    'subject_type' => $this->inferType($data[$map['type']] ?? '', (int) ($data[$map['P']] ?? 0)),
                    'teacher' => trim($data[$map['teacher']] ?? ''),
                ];
            }
            fclose($handle);
        }

        return array_values($semesters);
    }

    /**
     * Parse a GTU Curriculum PDF and extract subject information.
     */
    public function parseGtuPdf(UploadedFile $file): array
    {
        $parser = new Parser;
        $pdf = $parser->parseFile($file->getRealPath());
        $text = $pdf->getText();

        $semesters = [];
        $currentSemester = '1';

        // More robust semester detection
        if (preg_match('/Semester\s*[:\-]?\s*(\d+)/i', $text, $semMatch)) {
            $currentSemester = $semMatch[1];
        }

        $subjects = [];

        // Normalize text: Replace multiple spaces/tabs with single space
        $text = preg_replace('/[ \t]+/', ' ', $text);

        // Pattern 1: Full GTU Teaching Scheme
        if (preg_match_all('/(\d{7})\s+(.+?)\s+(\d)\s+(\d)\s+(\d)\s+(\d)\s+([\d\-\*]+)\s+([\d\-\*]+)\s+([\d\-\*]+)\s+([\d\-\*]+)\s+([\d\-\*]+)/', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $subjects[] = [
                    'subject_code' => $m[1],
                    'subject_name' => trim($m[2]),
                    'lecture_hours' => (int) $m[3],
                    'tutorial_hours' => (int) $m[4],
                    'practical_hours' => (int) $m[5],
                    'credits' => (int) $m[6],
                    'external_marks' => (int) preg_replace('/[^\d]/', '', $m[7]),
                    'internal_marks' => (int) preg_replace('/[^\d]/', '', $m[8]),
                    'total_marks' => (int) preg_replace('/[^\d]/', '', $m[11]),
                    'subject_type' => ((int) $m[5] > 0) ? 'Practical' : 'Theory',
                ];
            }
        }

        // Fallback Pattern 2: Minimal (Code, Name, L, T, P, C)
        if (empty($subjects)) {
            if (preg_match_all('/(\d{7})\s+([A-Za-z\s\(\)\-\&\/\.#0-9]+?)\s+(\d)\s+(\d)\s+(\d)\s+(\d)/', $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $subjects[] = [
                        'subject_code' => $m[1],
                        'subject_name' => trim($m[2]),
                        'lecture_hours' => (int) $m[3],
                        'tutorial_hours' => (int) $m[4],
                        'practical_hours' => (int) $m[5],
                        'credits' => (int) $m[6],
                        'external_marks' => 0,
                        'internal_marks' => 0,
                        'total_marks' => 0,
                        'subject_type' => ((int) $m[5] > 0) ? 'Practical' : 'Theory',
                    ];
                }
            }
        }

        if (! empty($subjects)) {
            // Deduplicate by code
            $uniqueSubjects = [];
            foreach ($subjects as $s) {
                $uniqueSubjects[$s['subject_code']] = $s;
            }
            $semesters[] = [
                'semester' => $currentSemester,
                'subjects' => array_values($uniqueSubjects),
            ];
        }

        return $semesters;
    }

    private function mapCsvHeaders(array $header): array
    {
        $header = array_map('strtolower', $header);
        $res = [
            'name' => 0, 'code' => 1, 'type' => 2, 'L' => 3, 'T' => 4, 'P' => 5, 'C' => 6,
            'internal' => 7, 'external' => 8, 'total' => 9, 'semester' => 10, 'teacher' => 11,
        ];
        foreach ($header as $i => $h) {
            if (str_contains($h, 'name')) {
                $res['name'] = $i;
            }
            if (str_contains($h, 'code')) {
                $res['code'] = $i;
            }
            if (str_contains($h, 'sem')) {
                $res['semester'] = $i;
            }
            if ($h == 'l' || str_contains($h, 'lecture')) {
                $res['L'] = $i;
            }
            if ($h == 't' || str_contains($h, 'tutorial')) {
                $res['T'] = $i;
            }
            if ($h == 'p' || str_contains($h, 'practical')) {
                $res['P'] = $i;
            }
            if ($h == 'c' || str_contains($h, 'credit')) {
                $res['C'] = $i;
            }
            if (str_contains($h, 'internal') || str_contains($h, 'pa')) {
                $res['internal'] = $i;
            }
            if (str_contains($h, 'external') || str_contains($h, 'ese')) {
                $res['external'] = $i;
            }
            if (str_contains($h, 'total')) {
                $res['total'] = $i;
            }
            if (str_contains($h, 'type')) {
                $res['type'] = $i;
            }
            if (str_contains($h, 'teacher') || str_contains($h, 'faculty') || str_contains($h, 'prof')) {
                $res['teacher'] = $i;
            }
        }

        return $res;
    }

    private function inferType(string $type, int $p): string
    {
        if (str_contains(strtolower($type), 'lab') || str_contains(strtolower($type), 'practical') || $p > 0) {
            return 'Practical';
        }
        if (str_contains(strtolower($type), 'project')) {
            return 'Project';
        }
        if (str_contains(strtolower($type), 'elective')) {
            return 'Elective';
        }

        return 'Theory';
    }
}
