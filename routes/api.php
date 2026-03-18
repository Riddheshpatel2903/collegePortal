<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\StudentApiController;
use App\Http\Controllers\API\TeacherApiController;
use App\Http\Controllers\API\CourseApiController;
use App\Http\Controllers\API\DepartmentApiController;
use App\Http\Controllers\API\SubjectApiController;
use App\Http\Controllers\API\SemesterApiController;
use App\Http\Controllers\API\AttendanceApiController;
use App\Http\Controllers\API\ResultApiController;
use App\Http\Controllers\API\AssignmentApiController;
use App\Http\Controllers\API\NoticeApiController;
use App\Http\Controllers\API\TimetableApiController;
use App\Http\Controllers\API\FeeApiController;
use App\Http\Controllers\API\EventApiController;
use App\Http\Controllers\API\HolidayApiController;

Route::prefix('v1')->group(function () {
    Route::post('login', [\App\Http\Controllers\API\AuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('me', [\App\Http\Controllers\API\AuthController::class, 'me']);
        Route::post('logout', [\App\Http\Controllers\API\AuthController::class, 'logout']);

        Route::apiResource('students', StudentApiController::class);
        Route::apiResource('teachers', TeacherApiController::class);
        Route::apiResource('courses', CourseApiController::class);
        Route::apiResource('departments', DepartmentApiController::class);
        Route::apiResource('subjects', SubjectApiController::class);
        Route::apiResource('semesters', SemesterApiController::class);
        Route::apiResource('attendances', AttendanceApiController::class);
        Route::apiResource('results', ResultApiController::class);
        Route::apiResource('assignments', AssignmentApiController::class);
        Route::apiResource('notices', NoticeApiController::class);
        Route::apiResource('timetables', TimetableApiController::class);
        Route::apiResource('fees', FeeApiController::class);
        Route::apiResource('events', EventApiController::class);
        Route::apiResource('holidays', HolidayApiController::class);
    });
});
