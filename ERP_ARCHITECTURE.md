# College ERP Architecture

## 1. Architecture Overview
- Stack: Laravel + MySQL + Blade + service-layer business logic.
- Roles: `admin`, `hod`, `teacher`, `student` via `role` middleware.
- Design: thin controllers, domain services (`FeeService`, `ResultService`, `PromotionService`, `ScheduleService`, `LeaveWorkflowService`), model observers for automation.

## 2. Database Schema
- Core identity: `users`, `departments`, `teachers`, `students`.
- Academic hierarchy: `academic_sessions -> courses -> semesters -> subjects -> semester_subjects`.
- Operations: `teacher_subject_assignments`, `schedules`, `classrooms`, `teacher_availabilities`.
- Academic performance: `attendance_sessions`, `attendance_records`, `results`, `result_subjects`, `assignments`, `assignment_submissions`.
- Finance: `fee_structures`, `student_fees`, `payments`.
- Workflow/communication: `leaves`, `notices`.

## 3. Ordered Migrations
- Base Laravel migrations then ERP schema migrations.
- Alignment migrations:
  - `2027_02_27_000016_align_required_erp_schema.php`
  - `2027_02_27_000017_add_leave_workflow_columns.php`

## 4. Models and Relationships
- Updated models: `User`, `Department`, `Student`, `Teacher`, `Subject`, `Schedule`, `Notice`, `Leave`, `TeacherSubjectAssignment`, `Attendance`.
- Added HOD linkage (`departments.hod_id`) and status-aware users.

## 5. Controllers
- Added:
  - `App\Http\Controllers\Hod\DashboardController`
  - `App\Http\Controllers\Hod\LeaveController`
  - `App\Http\Controllers\Hod\NoticeController`
  - `App\Http\Controllers\Admin\ScheduleController`
- Updated existing controllers for schema alignment and workflow routing.

## 6. Service Classes
- Existing: `SemesterService`, `FeeService`, `AttendanceService`, `ResultService`.
- Added:
  - `PromotionService`: pass/backlog/graduate transitions.
  - `ScheduleService`: teacher/classroom overlap prevention.
  - `LeaveWorkflowService`: Student/Teacher -> HOD -> Admin flow.

## 7. Middleware
- `RoleMiddleware` now validates role and active account status.

## 8. Routes
- Protected groups:
  - `/admin/*` with `role:admin`
  - `/hod/*` with `role:hod`
  - `/teacher/*` with `role:teacher`
  - `/student/*` with `role:student`
- Added HOD and schedule routes.

## 9. Blade Layout Structure
- Added HOD module views:
  - `resources/views/hod/dashboard.blade.php`
  - `resources/views/hod/leaves/index.blade.php`
  - `resources/views/hod/notices/index.blade.php`
  - `resources/views/hod/notices/create.blade.php`
- Added schedule views:
  - `resources/views/admin/schedules/index.blade.php`
  - `resources/views/admin/schedules/create.blade.php`

## 10. Seeders and Factories
- Scaled data:
  - 6 departments
  - 23 courses
  - 1500 students
  - 45 teachers
- Added seeders:
  - `HodSeeder`
  - `StudentFeeSeeder`
  - `PaymentSeeder`
- Updated `DatabaseSeeder` orchestration for realistic end-to-end data population.

## 11. Run Instructions
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Default admin:
- Email: `admin@college.edu`
- Password: `password`
