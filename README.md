# College Management Portal (ERP)

A comprehensive, multi-role Enterprise Resource Planning (ERP) system designed for colleges and universities. Built with **Laravel 11**, this platform streamlines academic administration, financial tracking, and faculty-student collaboration.

---

## 🚀 Key Modules & Features

### 🏛️ Admin Panel (Super Administration)
*   **Academic Lifecycle**: Manage Sessions, Departments, Courses, and Semesters.
*   **Infrastructure**: Classroom inventory and auto-assignment.
*   **User Management**: Full CRUD for Students, Teachers, HODs, Accountants, and Librarians.
*   **Automated Timetables**: Smart engine for generating clash-free schedules.
*   **System Controls**: Toggle modules (Fees, Library, Leave) and manage granular role-based permissions.
*   **Fees & Metrics**: Financial overview with real-time collection statistics.

### 🎓 HOD Module (Departmental Head)
*   **Faculty Oversight**: Assign teachers to specific subjects/semesters.
*   **Workflow Approval**: Manage leave requests for department staff and students.
*   **Departmental Notices**: Broadcast critical updates to specific courses.
*   **Internal Marks**: Validation and monitoring of departmental academic performance.

### 👨‍🏫 Teacher Module
*   **Attendance Tracking**: Advanced session-based marking with historical audits.
*   **Result Management**: Grading engine with result lock/unlock security features.
*   **Assignment Hub**: Create, distribute, and grade student submissions.
*   **Leave Management**: Dedicated workspace to request and track personal leaves.

### 🧑‍🎓 Student Module
*   **Smart Dashboard**: Real-time overview of current attendance and upcoming assignments.
*   **Financial Workspace**: Track fee due dates, paid history, and balance.
*   **Academic Portal**: View semester results, download notices, and access class schedules.
*   **Leave Application**: Digital submission of leave requests to HODs.

### 💰 Accountant & 📚 Librarian
*   **Accountant**: Dedicated fee collection ledger and payment history verification.
*   **Librarian**: Book inventory, automated fine calculation, and issue/return tracking.

---

## 🛠️ Technology Stack
*   **Backend**: PHP 8.2+ (Laravel 11)
*   **Database**: MySQL 8.0 / MariaDB
*   **Frontend**: Tailwind CSS, Vanilla JS, Blade Templates
*   **Architecture**: Service-Layer pattern with thin controllers and dedicated domain logic.

---

## 📦 Installation & Setup

1.  **Clone & Install Dependencies**:
    ```bash
    composer install
    npm install && npm run build
    ```
2.  **Configuration**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
3.  **Database Migration**:
    ```bash
    php artisan migrate:fresh --seed
    ```
4.  **Launch**:
    ```bash
    php artisan serve
    ```

### 🔑 Default Credentials
*   **Admin**: `admin@college.edu` / `password`
*   **Standard Password**: All seeded accounts use `password`.

---

## 📂 Project Structure
*   `app/Services`: Contains complex business logic (Promotion, Fee, Schedule, Leave).
*   `app/Models/Observers`: Handles automated tasks like student registration side-effects.
*   `resources/views`: Clean, modern UI partitioned by user role.

---
*Created as a comprehensive ERP solution for institutional efficiency.*
