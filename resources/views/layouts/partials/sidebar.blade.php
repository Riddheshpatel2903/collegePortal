<aside
    class="fixed inset-y-0 left-0 z-50 w-[260px] bg-gradient-to-b from-violet-950 via-slate-900 to-slate-950 text-white transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    <div class="flex h-full flex-col">

        <!-- Logo -->
        <div class="flex items-center gap-3 border-b border-white/10 px-6 py-5">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <div
                    class="h-10 w-10 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-500 flex items-center justify-center shadow-lg shadow-violet-500/30 group-hover:scale-110 transition-transform duration-300">
                    <i class="bi bi-mortarboard-fill text-white text-lg"></i>
                </div>
                <div>
                    <h2
                        class="text-sm font-black tracking-widest uppercase text-white/90 group-hover:text-white transition-colors">
                        EDU<span class="text-violet-400">PORTAL</span></h2>
                    <p class="text-[9px] font-bold text-violet-400/50 uppercase tracking-widest leading-none">Management
                    </p>
                </div>
            </a>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 overflow-y-auto space-y-1 custom-scrollbar">
            @auth
            @php $role = auth()->user()->role; @endphp

            <p class="px-4 mb-4 text-[10px] font-bold text-violet-400/50 uppercase tracking-[0.2em]">Main Menu</p>

            @if(auth()->user()->isAdmin())
            @canPage('admin.dashboard')
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-grid-1x2-fill text-base"></i> Dashboard
            </a>
            @endcanPage
            @canPage('admin.users.index')
            <a href="{{ route('admin.users.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-people-fill text-base"></i> Users
            </a>
            @endcanPage
            @canPage('admin.teachers.index')
            <a href="{{ route('admin.teachers.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.teachers.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-person-workspace text-base"></i> Teachers
            </a>
            @endcanPage
            @canPage('admin.students.index')
            <a href="{{ route('admin.students.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.students.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-person-badge-fill text-base"></i> Students
            </a>
            @endcanPage

            <p class="px-4 mt-5 mb-3 text-[10px] font-bold text-violet-400/50 uppercase tracking-[0.2em]">Academics</p>

            @canPage('admin.departments.index')
            <a href="{{ route('admin.departments.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.departments.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-building text-base"></i> Departments
            </a>
            @endcanPage
            @canPage('admin.courses.index')
            <a href="{{ route('admin.courses.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.courses.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-journal-bookmark-fill text-base"></i> Courses
            </a>
            @endcanPage
            @canPage('admin.semesters.index')
            <a href="{{ route('admin.semesters.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.semesters.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-calendar-week-fill text-base"></i> Semesters
            </a>
            @endcanPage
            @canPage('admin.subjects.index')
            <a href="{{ route('admin.subjects.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.subjects.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-book-fill text-base"></i> Subjects
            </a>
            @endcanPage
            @canPage('admin.timetable-auto.index')
            <a href="{{ route('admin.timetable-auto.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.timetable-auto.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-magic text-base"></i> Timetable
            </a>
            @endcanPage
            @canPage('admin.academic-phase.index')
            <a href="{{ route('admin.academic-phase.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.academic-phase.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-calendar3 text-base"></i> Academic Phase
            </a>
            @endcanPage

            <p class="px-4 mt-5 mb-3 text-[10px] font-bold text-violet-400/50 uppercase tracking-[0.2em]">Finance & Info
            </p>

            @canPage('admin.fees.index')
            <a href="{{ route('admin.fees.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.fees.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-credit-card-fill text-base"></i> Fees
            </a>
            @endcanPage
            @canPage('admin.results.index')
            <a href="{{ route('admin.results.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.results.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-bar-chart-fill text-base"></i> Results
            </a>
            @endcanPage
            @canPage('admin.notices.index')
            <a href="{{ route('admin.notices.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.notices.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-megaphone-fill text-base"></i> Notices
            </a>
            @endcanPage
            @canPage('admin.events.index')
            <a href="{{ route('admin.events.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.events.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-calendar-event-fill text-base"></i> Events
            </a>
            @endcanPage
            @canPage('admin.assignments.index')
            <a href="{{ route('admin.assignments.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.assignments.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-journal-text text-base"></i> Assignments
            </a>
            @endcanPage
            @canPage('admin.leaves.index')
                <a href="{{ route('admin.leaves.index') }}"
                    class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('admin.leaves.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                    <i class="bi bi-calendar2-check-fill text-base"></i> Leaves
                </a>
                @endcanPage

            @elseif($role == 'accountant')
            @canPage('accountant.dashboard')
            <a href="{{ route('accountant.dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('accountant.dashboard') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-grid-1x2-fill text-base"></i> Dashboard
            </a>
            @endcanPage
            @canPage('accountant.fees.index')
            <a href="{{ route('accountant.fees.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('accountant.fees.*') && !request()->routeIs('accountant.fees.history') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-safe-fill text-base"></i> Cashier Desk
            </a>
            @endcanPage
            @canPage('accountant.fees.history')
                <a href="{{ route('accountant.fees.history') }}"
                    class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('accountant.fees.history') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                    <i class="bi bi-clock-history text-base"></i> History
                </a>
                @endcanPage

            @elseif($role == 'hod')
            @canPage('hod.dashboard')
            <a href="{{ route('hod.dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('hod.dashboard') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-grid-1x2-fill text-base"></i> Dashboard
            </a>
            @endcanPage
            @canPage('hod.timetable.index')
            <a href="{{ route('hod.timetable.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('hod.timetable.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-clock-history text-base"></i> Timetable
            </a>
            @endcanPage
            @canPage('hod.teacher-assignments.index')
            <a href="{{ route('hod.teacher-assignments.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('hod.teacher-assignments.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-diagram-3-fill text-base"></i> Teacher Assignments
            </a>
            @endcanPage
            @canPage('hod.leaves.index')
            <a href="{{ route('hod.leaves.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('hod.leaves.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-calendar2-check-fill text-base"></i> Leave Approval
            </a>
            @endcanPage
            @canPage('hod.internal-marks.index')
            <a href="{{ route('hod.internal-marks.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('hod.internal-marks.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-journal-check text-base"></i> Internal Marks
            </a>
            @endcanPage
            @canPage('hod.notices.index')
                <a href="{{ route('hod.notices.index') }}"
                    class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('hod.notices.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                    <i class="bi bi-megaphone-fill text-base"></i> Notices
                </a>
                @endcanPage

            @elseif($role == 'teacher')
            @canPage('teacher.dashboard')
            <a href="{{ route('teacher.dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('teacher.dashboard') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-grid-1x2-fill text-base"></i> Dashboard
            </a>
            @endcanPage
            @canPage('teacher.attendance.index')
            <a href="{{ route('teacher.attendance.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('teacher.attendance.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-calendar-check-fill text-base"></i> Attendance
            </a>
            @endcanPage
            @canPage('teacher.assignments.index')
            <a href="{{ route('teacher.assignments.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('teacher.assignments.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-journal-text text-base"></i> Assignments
            </a>
            @endcanPage
            @canPage('teacher.results.index')
            <a href="{{ route('teacher.results.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs(['teacher.results.index', 'teacher.results.create', 'teacher.results.load', 'teacher.results.store']) ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-journal-check text-base"></i> Internal Marks
            </a>
            @endcanPage
            @canPage('teacher.results.search')
            <a href="{{ route('teacher.results.search') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('teacher.results.search') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-search text-base"></i> Result Search
            </a>
            @endcanPage
            @canPage('teacher.schedule.index')
            <a href="{{ route('teacher.schedule.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('teacher.schedule.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-clock-fill text-base"></i> Schedule
            </a>
            @endcanPage
            @canPage('teacher.notices.index')
            <a href="{{ route('teacher.notices.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('teacher.notices.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-megaphone-fill text-base"></i> Notices
            </a>
            @endcanPage
            @canPage('teacher.leaves.index')
                <a href="{{ route('teacher.leaves.index') }}"
                    class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('teacher.leaves.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                    <i class="bi bi-calendar2-check-fill text-base"></i> Leaves
                </a>
                @endcanPage

            @elseif($role == 'student')
            @canPage('student.dashboard')
            <a href="{{ route('student.dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('student.dashboard') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-grid-1x2-fill text-base"></i> Dashboard
            </a>
            @endcanPage
            @canPage('student.schedule.index')
            <a href="{{ route('student.schedule.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('student.schedule.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-clock-fill text-base"></i> Timetable
            </a>
            @endcanPage
            @canPage('student.results.index')
            <a href="{{ route('student.results.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('student.results.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-bar-chart-fill text-base"></i> Results
            </a>
            @endcanPage
            @canPage('student.attendance.index')
            <a href="{{ route('student.attendance.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('student.attendance.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-calendar-check-fill text-base"></i> Attendance
            </a>
            @endcanPage
            @canPage('student.assignments.index')
            <a href="{{ route('student.assignments.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('student.assignments.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-journal-text text-base"></i> Assignments
            </a>
            @endcanPage
            @canPage('student.fees.index')
            <a href="{{ route('student.fees.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('student.fees.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-credit-card-fill text-base"></i> Fees
            </a>
            @endcanPage
            @canPage('student.notices.index')
            <a href="{{ route('student.notices.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('student.notices.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-megaphone-fill text-base"></i> Notices
            </a>
            @endcanPage
            @canPage('student.leaves.index')
            <a href="{{ route('student.leaves.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('student.leaves.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-calendar2-check-fill text-base"></i> Apply Leave
            </a>
            @endcanPage
            @canPage('student.library.dashboard')
            <a href="{{ route('student.library.dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('student.library.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-book-half text-base"></i> Library
            </a>
            @endcanPage

            @elseif($role == 'librarian')
            @canPage('librarian.dashboard')
            <a href="{{ route('librarian.dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('librarian.dashboard') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-grid-1x2-fill text-base"></i> Dashboard
            </a>
            @endcanPage
            @canPage('librarian.books.index')
            <a href="{{ route('librarian.books.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('librarian.books.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-journal-bookmark-fill text-base"></i> Manage Books
            </a>
            @endcanPage
            @canPage('librarian.issues.index')
            <a href="{{ route('librarian.issues.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('librarian.issues.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-box-arrow-up-right text-base"></i> Issue Book
            </a>
            @endcanPage
            @canPage('librarian.returns.index')
            <a href="{{ route('librarian.returns.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('librarian.returns.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-box-arrow-in-down-left text-base"></i> Return Book
            </a>
            @endcanPage
            @canPage('librarian.requests.index')
            <a href="{{ route('librarian.requests.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('librarian.requests.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-inboxes-fill text-base"></i> Book Requests
            </a>
            @endcanPage
            @canPage('librarian.reservations.index')
            <a href="{{ route('librarian.reservations.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('librarian.reservations.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-bookmark-heart-fill text-base"></i> Reservations
            </a>
            @endcanPage
            @canPage('librarian.overdues.index')
            <a href="{{ route('librarian.overdues.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('librarian.overdues.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-alarm-fill text-base"></i> Overdue Books
            </a>
            @endcanPage
            @canPage('librarian.fines.index')
            <a href="{{ route('librarian.fines.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('librarian.fines.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-receipt-cutoff text-base"></i> Fine Management
            </a>
            @endcanPage
            @canPage('librarian.history.index')
            <a href="{{ route('librarian.history.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('librarian.history.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-clock-history text-base"></i> Borrow History
            </a>
            @endcanPage
            @canPage('librarian.reports.index')
            <a href="{{ route('librarian.reports.index') }}"
                class="flex items-center gap-3 px-4 py-3 text-[13px] font-semibold rounded-xl transition-all duration-200 {{ request()->routeIs('librarian.reports.*') ? 'nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <i class="bi bi-bar-chart-fill text-base"></i> Reports & Analytics
            </a>
            @endcanPage
            @endif
            @endauth
        </nav>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-white/10">
            <div class="flex items-center gap-3 px-3 py-3 rounded-xl bg-white/5">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=7c3aed&color=fff"
                    class="h-9 w-9 rounded-lg ring-2 ring-violet-500/30" alt="User">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-violet-300/50 font-semibold truncate">
                        {{ ucfirst(auth()->user()->role) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</aside>
