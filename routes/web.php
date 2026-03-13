<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\{
    StudentController,
    FeeController,
    AttendanceController,
    ResultController,
    DashboardController
};

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Default Login (Student + Teacher)
Route::get('/', function () {
    return redirect()->route('login');
});

// Admin Login Page
Route::get('/admin', [AdminLoginController::class, 'create'])->name('admin.login');
Route::post('/admin', [AdminLoginController::class, 'store'])->name('admin.login.submit');


/*
|--------------------------------------------------------------------------
| Dashboard Redirect (After Login)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'page.access'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    |*/
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {

        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

        // Student Management with AJAX
        Route::get('students/fetch', [\App\Http\Controllers\Admin\StudentController::class, 'fetchStudents'])->name('students.fetch');
        Route::get('students/semesters', [\App\Http\Controllers\Admin\StudentController::class, 'getSemestersByDepartment'])->name('students.semesters-by-dept');
        Route::patch('students/{student}/toggle-status', [\App\Http\Controllers\Admin\StudentController::class, 'toggleStatus'])->name('students.toggle-status');
        Route::resource('students', \App\Http\Controllers\Admin\StudentController::class);
        Route::resource('teachers', \App\Http\Controllers\Admin\TeacherController::class);
        Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class);
        Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class);
        Route::resource('classrooms', \App\Http\Controllers\Admin\ClassroomController::class);
        Route::post('classrooms/assign', [\App\Http\Controllers\Admin\ClassroomController::class, 'assign'])->name('classrooms.assign');
        Route::post('classrooms/{classroom}/unassign', [\App\Http\Controllers\Admin\ClassroomController::class, 'unassign'])->name('classrooms.unassign');
        Route::resource('semesters', \App\Http\Controllers\Admin\SemesterController::class)
            ->only(['index', 'create', 'store', 'destroy']);
        Route::post('subjects/import', [\App\Http\Controllers\Admin\SubjectController::class, 'import'])->name('subjects.import');
        Route::delete('subjects/delete-all', [\App\Http\Controllers\Admin\SubjectController::class, 'deleteAll'])->name('subjects.delete-all');
        Route::resource('subjects', \App\Http\Controllers\Admin\SubjectController::class);
        Route::middleware('module:timetable')->group(function () {
            Route::get('timetable-auto', [\App\Http\Controllers\Admin\AutoTimetableController::class, 'index'])->name('timetable-auto.index');
            Route::post('timetable-auto/generate', [\App\Http\Controllers\Admin\AutoTimetableController::class, 'generate'])
                ->name('timetable-auto.generate')
                ->middleware('feature:timetable_edit_enabled');
            Route::put('timetable-auto/entries/{entry}', [\App\Http\Controllers\Admin\AutoTimetableController::class, 'updateEntry'])
                ->name('timetable-auto.entries.update')
                ->middleware(['feature:timetable_edit_enabled', 'feature:semester_lock,false']);
        });
        Route::middleware('module:notice')->group(function () {
            Route::resource('notices', \App\Http\Controllers\Admin\NoticeController::class);
        });
        Route::resource('events', \App\Http\Controllers\Admin\EventController::class);
        Route::middleware('module:fees')->group(function () {
            Route::resource('fees', \App\Http\Controllers\Admin\FeeController::class);
        });
        Route::middleware('module:leave')->group(function () {
            Route::get('leaves', [\App\Http\Controllers\Admin\LeaveController::class, 'index'])->name('leaves.index');
            Route::delete('leaves/{leaf}', [\App\Http\Controllers\Admin\LeaveController::class, 'destroy'])
                ->name('leaves.destroy')
                ->middleware('feature:delete_button_enabled');
        });
        Route::get('results', [\App\Http\Controllers\Admin\ResultController::class, 'index'])->name('results.index');
        Route::post('results/import', [\App\Http\Controllers\Admin\ResultController::class, 'import'])->name('results.import');
        Route::post('results/{result}/lock', [\App\Http\Controllers\Admin\ResultController::class, 'lock'])->name('results.lock');
        Route::get('academic-phase', [\App\Http\Controllers\Admin\AcademicPhaseController::class, 'index'])->name('academic-phase.index');
        Route::put('academic-phase', [\App\Http\Controllers\Admin\AcademicPhaseController::class, 'update'])->name('academic-phase.update');
        Route::get('settings', [\App\Http\Controllers\Admin\PortalSettingsController::class, 'index'])
            ->name('settings.index')
            ->middleware('permission:settings.manage');

        // Role Management Routes
        Route::post('settings/roles', [\App\Http\Controllers\Admin\PortalSettingsController::class, 'storeRole'])
            ->name('settings.roles.store')
            ->middleware('permission:settings.manage');
        Route::put('settings/roles/{role}', [\App\Http\Controllers\Admin\PortalSettingsController::class, 'updateRole'])
            ->name('settings.roles.update')
            ->middleware('permission:settings.manage');
        Route::delete('settings/roles/{role}', [\App\Http\Controllers\Admin\PortalSettingsController::class, 'destroyRole'])
            ->name('settings.roles.destroy')
            ->middleware('permission:settings.manage');

        Route::put('settings/permissions', [\App\Http\Controllers\Admin\PortalSettingsController::class, 'updateRolePermissions'])
            ->name('settings.permissions.update')
            ->middleware('permission:settings.manage');
        Route::put('settings/pages', [\App\Http\Controllers\Admin\PortalSettingsController::class, 'updatePagePermissions'])
            ->name('settings.pages.update')
            ->middleware('permission:settings.manage');
        Route::put('settings/features', [\App\Http\Controllers\Admin\PortalSettingsController::class, 'updateFeatureToggles'])
            ->name('settings.features.update')
            ->middleware('permission:settings.manage');
        Route::put('settings/modules', [\App\Http\Controllers\Admin\PortalSettingsController::class, 'updateModuleSettings'])
            ->name('settings.modules.update')
            ->middleware('permission:settings.manage');
        Route::put('settings/smart', [\App\Http\Controllers\Admin\PortalSettingsController::class, 'updateSmartSettings'])
            ->name('settings.smart.update')
            ->middleware('permission:settings.manage');
        Route::put('settings/general', [\App\Http\Controllers\Admin\PortalSettingsController::class, 'updateGeneralSettings'])
            ->name('settings.general.update')
            ->middleware('permission:settings.manage');

        // Holidays
        Route::post('holidays/import', [\App\Http\Controllers\Admin\HolidayController::class, 'import'])->name('holidays.import');
        Route::resource('holidays', \App\Http\Controllers\Admin\HolidayController::class);
        // Route::get('audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])
        //    ->name('audit-logs.index')
        //    ->middleware('permission:audit_logs.view');

        // Admin Assignment Routes
        Route::get('/assignments', [\App\Http\Controllers\Admin\AssignmentController::class, 'adminIndex'])->name('assignments.index');
        Route::post('/assignments/{id}/extend', [\App\Http\Controllers\Admin\AssignmentController::class, 'extendDeadline'])->name('assignments.extend');
        Route::post('/assignments/{id}/force-close', [\App\Http\Controllers\Admin\AssignmentController::class, 'forceClose'])->name('assignments.force-close');
        Route::delete('/assignments/{id}', [\App\Http\Controllers\Admin\AssignmentController::class, 'destroy'])
            ->name('assignments.destroy')
            ->middleware('feature:delete_button_enabled');
    });

    Route::prefix('hod')->name('hod.')->middleware('role:hod')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Hod\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/teacher-assignments', [\App\Http\Controllers\Hod\TeacherSubjectAssignmentController::class, 'index'])->name('teacher-assignments.index');
        Route::post('/teacher-assignments', [\App\Http\Controllers\Hod\TeacherSubjectAssignmentController::class, 'store'])->name('teacher-assignments.store');
        Route::middleware('module:timetable')->group(function () {
            Route::get('/timetable', [\App\Http\Controllers\Hod\ScheduleController::class, 'index'])->name('timetable.index');
            Route::post('/timetable/generate', [\App\Http\Controllers\Hod\ScheduleController::class, 'generate'])
                ->name('timetable.generate')
                ->middleware('feature:timetable_edit_enabled');
            Route::get('/timetable/{schedule}/edit', [\App\Http\Controllers\Hod\ScheduleController::class, 'edit'])->name('timetable.edit');
            Route::put('/timetable/{schedule}', [\App\Http\Controllers\Hod\ScheduleController::class, 'update'])
                ->name('timetable.update')
                ->middleware(['feature:timetable_edit_enabled', 'feature:semester_lock,false']);
            Route::post('/timetable/availability', [\App\Http\Controllers\Hod\ScheduleController::class, 'setAvailability'])
                ->name('timetable.availability.store')
                ->middleware('feature:timetable_edit_enabled');
            Route::delete('/timetable/availability/{availability}', [\App\Http\Controllers\Hod\ScheduleController::class, 'deleteAvailability'])
                ->name('timetable.availability.destroy')
                ->middleware('feature:delete_button_enabled');
        });
        Route::middleware('module:leave')->group(function () {
            Route::get('/leaves', [\App\Http\Controllers\Hod\LeaveController::class, 'index'])->name('leaves.index');
            Route::post('/leaves/{leave}/approve', [\App\Http\Controllers\Hod\LeaveController::class, 'approve'])
                ->name('leaves.approve')
                ->middleware('feature:approve_leave_enabled');
            Route::post('/leaves/{leave}/reject', [\App\Http\Controllers\Hod\LeaveController::class, 'reject'])
                ->name('leaves.reject')
                ->middleware('feature:approve_leave_enabled');
        });
        Route::get('/internal-marks', [\App\Http\Controllers\Hod\InternalMarkController::class, 'index'])->name('internal-marks.index');
        Route::resource('notices', \App\Http\Controllers\Hod\NoticeController::class)->only(['index', 'create', 'store']);
    });

    /*
    |--------------------------------------------------------------------------
    | Teacher Routes
    |--------------------------------------------------------------------------
    |*/
    Route::prefix('teacher')->name('teacher.')->middleware('role:teacher')->group(function () {

        Route::get('/dashboard', [\App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');

        // Attendance marking
        Route::get('/attendance/mark', [\App\Http\Controllers\Teacher\AttendanceController::class, 'index'])->name('attendance.create');
        Route::post('/attendance/mark', [\App\Http\Controllers\Teacher\AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/attendance/sessions', [\App\Http\Controllers\Teacher\AttendanceController::class, 'sessions'])->name('attendance.sessions');
        Route::get('/attendance/session/{id}', [\App\Http\Controllers\Teacher\AttendanceController::class, 'show'])->name('attendance.show');
        Route::get('/attendance/history', [\App\Http\Controllers\Teacher\AttendanceController::class, 'index'])->name('attendance.index');

        // Result entry
        Route::get('/results', [\App\Http\Controllers\Teacher\ResultController::class, 'index'])->name('results.index');
        Route::get('/results/search', [\App\Http\Controllers\Teacher\ResultController::class, 'search'])->name('results.search');
        Route::get('/results/entry', [\App\Http\Controllers\Teacher\ResultController::class, 'index'])->name('results.create');
        Route::post('/results/entry', [\App\Http\Controllers\Teacher\ResultController::class, 'store'])->name('results.store');
        Route::get('/results/students', [\App\Http\Controllers\Teacher\ResultController::class, 'load'])->name('results.load');
        Route::post('/results/publish/{id}', [\App\Http\Controllers\Teacher\ResultController::class, 'publish'])->name('results.publish');
        Route::post('/results/unlock/{id}', [\App\Http\Controllers\Teacher\ResultController::class, 'unlock'])->name('results.unlock');

        Route::get('/assignments', [\App\Http\Controllers\Teacher\AssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/create', [\App\Http\Controllers\Teacher\AssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/assignments', [\App\Http\Controllers\Teacher\AssignmentController::class, 'store'])->name('assignments.store');
        Route::get('/assignments/{id}/edit', [\App\Http\Controllers\Teacher\AssignmentController::class, 'edit'])->name('assignments.edit');
        Route::put('/assignments/{id}', [\App\Http\Controllers\Teacher\AssignmentController::class, 'update'])->name('assignments.update');
        Route::delete('/assignments/{id}', [\App\Http\Controllers\Teacher\AssignmentController::class, 'destroy'])
            ->name('assignments.destroy')
            ->middleware('feature:delete_button_enabled');
        Route::get('/assignments/{id}/submissions', [\App\Http\Controllers\Teacher\AssignmentController::class, 'submissions'])->name('assignments.submissions');
        Route::post('/assignments/submissions/{id}/grade', [\App\Http\Controllers\Teacher\AssignmentController::class, 'grade'])->name('assignments.grade');

        Route::get('/schedule', [\App\Http\Controllers\Teacher\ScheduleController::class, 'index'])
            ->name('schedule.index')
            ->middleware('module:timetable');

        Route::middleware('module:notice')->group(function () {
            Route::resource('notices', \App\Http\Controllers\Teacher\NoticeController::class)
                ->only(['index', 'create', 'store', 'destroy']);
        });

        Route::middleware('module:leave')->group(function () {
            Route::get('/leaves', [\App\Http\Controllers\Teacher\LeaveController::class, 'index'])->name('leaves.index');
            Route::post('/leaves', [\App\Http\Controllers\Teacher\LeaveController::class, 'store'])->name('leaves.store');
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Student Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {

        Route::get('/dashboard', [\App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/attendance', [\App\Http\Controllers\Student\AttendanceController::class, 'index'])->name('attendance.index');

        Route::get('/assignments', [\App\Http\Controllers\Student\AssignmentController::class, 'index'])->name('assignments.index');
        Route::post('/assignments/{id}/submit', [\App\Http\Controllers\Student\AssignmentController::class, 'submit'])->name('assignments.submit');
        Route::post('/assignments/{id}/resubmit', [\App\Http\Controllers\Student\AssignmentController::class, 'resubmit'])->name('assignments.resubmit');

        Route::get('/fees', [\App\Http\Controllers\Student\FeeController::class, 'index'])->name('fees.index')->middleware('module:fees');
        Route::get('/results', [\App\Http\Controllers\Student\ResultController::class, 'index'])->name('results.index');
        Route::middleware('module:leave')->group(function () {
            Route::get('/leaves', [\App\Http\Controllers\Student\LeaveController::class, 'index'])->name('leaves.index');
            Route::post('/leaves', [\App\Http\Controllers\Student\LeaveController::class, 'store'])->name('leaves.store');
        });
        Route::get('/schedule', [\App\Http\Controllers\Student\ScheduleController::class, 'index'])->name('schedule.index')->middleware('module:timetable');
        Route::get('/notices', [\App\Http\Controllers\Student\NoticeController::class, 'index'])->name('notices.index')->middleware('module:notice');
        Route::get('/assignments', [\App\Http\Controllers\Student\AssignmentController::class, 'index'])->name('assignments.index');
        Route::post('/assignments/{id}/submit', [\App\Http\Controllers\Student\AssignmentController::class, 'submit'])->name('assignments.submit');
        Route::post('/assignments/{id}/resubmit', [\App\Http\Controllers\Student\AssignmentController::class, 'resubmit'])->name('assignments.resubmit');

        Route::prefix('library')->name('library.')->middleware('module:library')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Student\LibraryController::class, 'dashboard'])->name('dashboard');
            Route::get('/browse', [\App\Http\Controllers\Student\LibraryController::class, 'browse'])->name('browse');
            Route::get('/borrowed', [\App\Http\Controllers\Student\LibraryController::class, 'borrowed'])->name('borrowed');
            Route::get('/reservations', [\App\Http\Controllers\Student\LibraryController::class, 'reservations'])->name('reservations');
            Route::get('/requests', [\App\Http\Controllers\Student\LibraryController::class, 'requests'])->name('requests');
            Route::get('/fines', [\App\Http\Controllers\Student\LibraryController::class, 'fines'])->name('fines');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Accountant Routes
    |--------------------------------------------------------------------------
    |*/
    Route::prefix('accountant')->name('accountant.')->middleware('role:accountant')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Accountant\DashboardController::class, 'index'])->name('dashboard');

        Route::middleware('module:fees')->group(function () {
            Route::resource('fees', \App\Http\Controllers\Accountant\FeeController::class)->only(['index', 'update']);
            Route::get('/fees-history', [\App\Http\Controllers\Accountant\FeeController::class, 'history'])->name('fees.history');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Librarian Routes
    |--------------------------------------------------------------------------
    |*/
    Route::prefix('librarian')->name('librarian.')->middleware('role:librarian')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Librarian\DashboardController::class, 'index'])->name('dashboard');
        Route::middleware('module:library')->group(function () {
            Route::get('/books', [\App\Http\Controllers\Librarian\BooksController::class, 'index'])->name('books.index');
            Route::get('/issue', [\App\Http\Controllers\Librarian\IssueController::class, 'index'])->name('issues.index');
            Route::get('/return', [\App\Http\Controllers\Librarian\ReturnController::class, 'index'])->name('returns.index');
            Route::get('/requests', [\App\Http\Controllers\Librarian\RequestsController::class, 'index'])->name('requests.index');
            Route::get('/reservations', [\App\Http\Controllers\Librarian\ReservationsController::class, 'index'])->name('reservations.index');
            Route::get('/overdues', [\App\Http\Controllers\Librarian\OverduesController::class, 'index'])->name('overdues.index');
            Route::get('/fines', [\App\Http\Controllers\Librarian\FinesController::class, 'index'])->name('fines.index');
            Route::get('/history', [\App\Http\Controllers\Librarian\HistoryController::class, 'index'])->name('history.index');
            Route::get('/reports', [\App\Http\Controllers\Librarian\ReportsController::class, 'index'])->name('reports.index');
        });
    });

    // Shared Routes
    Route::get('/fees/receipt/{payment}', [FeeController::class, 'receipt'])->name('fees.receipt')->middleware('module:fees');
    Route::get('/results/view/{result}', [ResultController::class, 'show'])->name('results.show');
});

require __DIR__ . '/auth.php';

// Global Fallback Route for 404
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
