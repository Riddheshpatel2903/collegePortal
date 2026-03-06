<?php

namespace App\Services;

use App\Models\Result;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GtuResultImportService
{
    public function import(UploadedFile $file, int $semesterNumber, int $lockedByUserId): array
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $rows = match ($extension) {
            'csv' => $this->parseCsv($file),
            'xlsx' => $this->parseXlsx($file),
            'pdf' => $this->parsePdfText($file),
            default => throw new RuntimeException('Unsupported file type. Please upload CSV, XLSX, or PDF.'),
        };

        if ($rows->isEmpty()) {
            throw new RuntimeException('No valid rows found in uploaded file.');
        }

        $summary = [
            'processed' => 0,
            'matched' => 0,
            'not_found' => 0,
            'locked' => 0,
        ];

        DB::transaction(function () use ($rows, $semesterNumber, $lockedByUserId, &$summary) {
            foreach ($rows as $row) {
                $summary['processed']++;
                $enrollment = strtoupper(trim((string) ($row['gtu_enrollment_no'] ?? '')));
                if ($enrollment === '') {
                    $summary['not_found']++;
                    continue;
                }

                $student = Student::query()->where('gtu_enrollment_no', $enrollment)->first();
                if (!$student) {
                    $summary['not_found']++;
                    continue;
                }
                $summary['matched']++;

                $spi = (float) ($row['spi'] ?? 0);
                $cpi = (float) ($row['cpi'] ?? $spi);
                $backlog = max(0, (int) ($row['backlog_subjects'] ?? 0));
                $status = $backlog > 0 ? 'fail' : 'pass';

                $result = Result::query()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'semester_number' => $semesterNumber,
                    ],
                    [
                        'course_id' => $student->course_id,
                        'academic_year' => (int) ceil($semesterNumber / 2),
                        'sgpa' => $spi,
                        'cgpa' => $cpi,
                        'total_credits_earned' => 0,
                        'backlog_subjects' => $backlog,
                        'result_status' => $status,
                        'promoted' => $status === 'pass',
                        'result_declared_date' => now(),
                        'locked_at' => now(),
                        'locked_by' => $lockedByUserId,
                    ]
                );

                $student->update([
                    'cgpa' => $cpi,
                    'cpi' => $cpi,
                    'academic_status' => $backlog > 0 ? 'backlog' : 'active',
                    'backlog_count' => $backlog,
                    'current_year' => max(1, (int) ceil($semesterNumber / 2)),
                ]);

                if ($result->locked_at) {
                    $summary['locked']++;
                }
            }
        });

        return $summary;
    }

    private function parseCsv(UploadedFile $file): Collection
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            throw new RuntimeException('Could not read CSV file.');
        }

        $header = fgetcsv($handle) ?: [];
        $header = collect($header)->map(fn ($h) => $this->normalizeHeader((string) $h))->all();
        $rows = collect();
        while (($line = fgetcsv($handle)) !== false) {
            if (empty(array_filter($line, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }
            $row = [];
            foreach ($header as $i => $column) {
                $row[$column] = $line[$i] ?? null;
            }
            $rows->push($this->normalizeRow($row));
        }
        fclose($handle);

        return $rows;
    }

    private function parseXlsx(UploadedFile $file): Collection
    {
        // Minimal XLSX parser: reads first worksheet and shared strings.
        $zip = new \ZipArchive();
        if ($zip->open($file->getRealPath()) !== true) {
            throw new RuntimeException('Could not open XLSX file.');
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $sx = simplexml_load_string($sharedXml);
            if ($sx && isset($sx->si)) {
                foreach ($sx->si as $si) {
                    $sharedStrings[] = (string) ($si->t ?? '');
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheetXml === false) {
            throw new RuntimeException('XLSX sheet1 not found.');
        }

        $sx = simplexml_load_string($sheetXml);
        if (!$sx) {
            throw new RuntimeException('Could not parse XLSX worksheet.');
        }

        $rows = [];
        foreach ($sx->sheetData->row ?? [] as $rowNode) {
            $cells = [];
            foreach ($rowNode->c as $c) {
                $value = (string) ($c->v ?? '');
                $type = (string) ($c['t'] ?? '');
                if ($type === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                }
                $cells[] = $value;
            }
            $rows[] = $cells;
        }

        if (count($rows) < 2) {
            return collect();
        }

        $header = collect($rows[0])->map(fn ($h) => $this->normalizeHeader((string) $h))->all();
        return collect(array_slice($rows, 1))
            ->filter(fn ($line) => !empty(array_filter($line, fn ($v) => $v !== null && $v !== '')))
            ->map(function ($line) use ($header) {
                $row = [];
                foreach ($header as $i => $column) {
                    $row[$column] = $line[$i] ?? null;
                }
                return $this->normalizeRow($row);
            })
            ->values();
    }

    private function parsePdfText(UploadedFile $file): Collection
    {
        // Basic fallback for text-based PDFs; image PDFs need OCR and are intentionally out of scope.
        $content = (string) @file_get_contents($file->getRealPath());
        if ($content === '') {
            throw new RuntimeException('Could not read PDF file.');
        }

        preg_match_all('/(GTU[0-9A-Z-]{6,})\s+([0-9]+(?:\.[0-9]+)?)\s+([0-9]+(?:\.[0-9]+)?)/i', $content, $matches, PREG_SET_ORDER);
        if (empty($matches)) {
            throw new RuntimeException('No parseable GTU rows found in PDF. Use CSV/XLSX format for reliable import.');
        }

        return collect($matches)->map(function ($m) {
            return [
                'gtu_enrollment_no' => strtoupper(trim((string) ($m[1] ?? ''))),
                'spi' => (float) ($m[2] ?? 0),
                'cpi' => (float) ($m[3] ?? 0),
                'backlog_subjects' => 0,
            ];
        })->values();
    }

    private function normalizeHeader(string $header): string
    {
        $key = strtolower(trim($header));
        $key = str_replace([' ', '-', '.'], '_', $key);

        return match ($key) {
            'enrollment_no', 'enrollment', 'gtu_enrollment', 'gtu_enrollment_no' => 'gtu_enrollment_no',
            'spi', 'sgpa' => 'spi',
            'cpi', 'cgpa' => 'cpi',
            'backlog', 'backlogs', 'backlog_subjects' => 'backlog_subjects',
            default => $key,
        };
    }

    private function normalizeRow(array $row): array
    {
        return [
            'gtu_enrollment_no' => strtoupper(trim((string) ($row['gtu_enrollment_no'] ?? ''))),
            'spi' => (float) ($row['spi'] ?? 0),
            'cpi' => (float) ($row['cpi'] ?? ($row['spi'] ?? 0)),
            'backlog_subjects' => max(0, (int) ($row['backlog_subjects'] ?? 0)),
        ];
    }
}

