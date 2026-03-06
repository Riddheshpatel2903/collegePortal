<?php

namespace App\Services;

use RuntimeException;

class TimetableRetryEngine
{
    /**
     * @param callable(int):mixed $callback
     */
    public function run(int $maxAttempts, callable $callback): mixed
    {
        $maxAttempts = max(1, $maxAttempts);
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return $callback($attempt);
            } catch (\Throwable $exception) {
                $lastException = $exception;
            }
        }

        if ($lastException) {
            throw $lastException;
        }

        throw new RuntimeException('Timetable generation failed after retry attempts.');
    }
}

