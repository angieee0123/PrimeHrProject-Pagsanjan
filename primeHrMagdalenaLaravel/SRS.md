# Software Requirements Specification (SRS)
## PrimeHR — Human Resource Information System
### Municipal Government of Pagsanjan

---

**Document Version:** 1.0  
**System Name:** PrimeHR (NEW-PRIME-HRIS)  
**Platform:** Laravel (PHP), MySQL, Blade Templating  
**Prepared For:** Municipal Government of Pagsanjan  

---

## 1. Introduction

### 1.1 Purpose
This document defines the functional and non-functional requirements for PrimeHR, a web-based Human Resource Information System (HRIS) developed for the Municipal Government of Pagsanjan. It serves as the reference for development, testing, and validation of the system.

### 1.2 Scope
PrimeHR manages the full employee lifecycle including personnel records, attendance tracking, scheduling, payroll, leave and benefits, training, performance, recruitment, and departmental management. The system supports three user roles: Admin/HR, Permanent Employee, and Job Order Employee.

### 1.3 Definitions
| Term | Definition |
|------|-----------|
| DTR | Daily Time Record |
| QR Code | Quick Response code used for employee identification |
| Accredited Hours | Computed work hours based on schedule and grace period |
| Designation | Job title with associated salary grade and monthly rate |
| Employment Status | Permanent, Casual, Contractual, or Job Order |
| Grace Period | 15-minute allowance before marking an employee as late |

---

## 2. Overall Description

### 2.1 System Overview
PrimeHR is a multi-role web application. Administrators and HR officers manage all employee data and system operations. Employees (Permanent and Job Order) access their own dashboards to view attendance, payslips, leave, training, performance, and profile information.

### 2.2 User Roles

| Role | Access Level |
|------|-------------|
| Admin / HR | Full system access — manage employees, attendance, departments, payroll, reports |
| Permanent Employee | View own dashboard, attendance, payslip, leave, performance, training, profile |
| Job Order Employee | View own dashboard, attendance, payslip, performance, training, profile |

### 2.3 Operating Environment
- **Backend:** PHP 8.x, Laravel 11.x
- **Frontend:** Blade templates, CSS (Poppins font), Vanilla JS
- **Database:** MySQL
- **Authentication:** Laravel Auth with session-based login
- **Storage:** Laravel filesystem (public disk) for photos and attachments

---

## 3. Functional Requirements

### 3.1 Authentication Module

| ID | Requirement |
|----|-------------|
| AUTH-01 | The system shall provide a login page accepting email and password. |
| AUTH-02 | Upon successful login, the system shall redirect users based on role: Admin/HR → Admin Dashboard, Permanent → Permanent Dashboard, Job Order → Job Order Dashboard. |
| AUTH-03 | The system shall display an error message for invalid credentials. |
| AUTH-04 | The system shall support a "Remember Me" option. |
| AUTH-05 | The system shall provide a logout function that invalidates the session. |
| AUTH-06 | The system shall provide a Forgot Password page. |
| AUTH-07 | All protected routes shall require authentication via middleware. |

---

### 3.2 Personnel Management Module (Admin)

#### 3.2.1 Employee Registration

| ID | Requirement |
|----|-------------|
| PERS-01 | The system shall allow admin to register a new employee using a multi-step wizard form. |
| PERS-02 | The registration form shall capture: Employee ID, full name (first, middle, last, suffix), photo, birth date, place of birth, sex, civil status, height, weight, blood type, citizenship, and email. |
| PERS-03 | The system shall create a linked user account with email, username, password (hashed), and role during registration. |
| PERS-04 | The system shall save employment details: designation, department, employment status, appointment date, salary grade, and step increment. |
| PERS-05 | The system shall save the residential address: house number, street, barangay, city, province, and zip code. |
| PERS-06 | The system shall save contact information: mobile number, landline number, emergency contact person, and emergency contact number. |
| PERS-07 | The system shall save government IDs: GSIS No., PhilHealth No., Pag-IBIG No., TIN No., and Driver's License No. |
| PERS-08 | Employee registration shall be wrapped in a database transaction; on failure, all changes shall be rolled back. |
| PERS-09 | The system shall support photo upload for employee profile pictures stored in public storage. |

#### 3.2.2 Employee Listing and Filtering

| ID | Requirement |
|----|-------------|
| PERS-10 | The system shall display a paginated list of all employees with name, employee ID, department, designation, employment status, and account status. |
| PERS-11 | The system shall display summary statistics: total employees, active, inactive, and permanent count. |
| PERS-12 | The system shall support tab-based navigation: Employees tab and Schedules tab. |
| PERS-13 | The system shall allow admin to view full employee details in a modal. |
| PERS-14 | The system shall allow admin to generate and display a QR code for each employee, with download and print options. |

#### 3.2.3 Employee Update and Status Management

| ID | Requirement |
|----|-------------|
| PERS-15 | The system shall allow admin to edit employee personal information, employment details, contacts, address, and government IDs. |
| PERS-16 | The system shall allow admin to activate or deactivate an employee's user account (Active / Inactive). |
| PERS-17 | The system shall display a confirmation modal before performing destructive actions. |

---

### 3.3 Schedule Management Module (Admin)

| ID | Requirement |
|----|-------------|
| SCHED-01 | The system shall allow admin to assign a work schedule to an employee with: start date, end date, AM In, AM Out, PM In, and PM Out times. |
| SCHED-02 | The system shall prevent overlapping schedules for the same employee and display the conflicting date range. |
| SCHED-03 | The system shall support bulk schedule assignment to multiple employees simultaneously. |
| SCHED-04 | When a schedule is created or updated, the system shall automatically recalculate accredited hours for all attendance records within that date range. |
| SCHED-05 | The system shall allow admin to view all schedules for a specific employee. |
| SCHED-06 | The system shall allow admin to edit an existing schedule. |
| SCHED-07 | The system shall allow admin to delete a schedule. |
| SCHED-08 | The system shall provide a real-time overlap check via AJAX before submitting the schedule form. |
| SCHED-09 | The system shall allow export of all employee schedules to a CSV file. |

---

### 3.4 Attendance Management Module (Admin)

#### 3.4.1 Attendance Overview

| ID | Requirement |
|----|-------------|
| ATT-01 | The system shall display an attendance summary for all employees within a selected date range. |
| ATT-02 | The summary shall include: present days, absent days, late count, half-day count, overtime hours, attendance rate (%), and status (Complete / Incomplete). |
| ATT-03 | The system shall calculate working days excluding weekends (Saturday and Sunday). |
| ATT-04 | The system shall support filtering by department and attendance status. |
| ATT-05 | The system shall display aggregate totals: total present, total absent, total late, total overtime, complete count, and incomplete count. |

#### 3.4.2 Attendance Rules

| ID | Requirement |
|----|-------------|
| ATT-06 | The system shall apply a 15-minute grace period to AM In and PM In times when computing late minutes. |
| ATT-07 | If an employee clocks in within the grace period, the system shall credit attendance from the scheduled start time (not the actual clock-in time). |
| ATT-08 | The system shall compute late minutes as: actual AM In − scheduled AM In (when beyond grace period). |
| ATT-09 | The system shall compute undertime minutes as: scheduled PM Out − actual PM Out (when employee leaves early). |
| ATT-10 | The system shall compute accredited hours as: AM accredited minutes + PM accredited minutes. |
| ATT-11 | The system shall compute total hours as: actual time logged across AM, PM, and OT sessions. |
| ATT-12 | An employee is marked as having a half-day if only AM or only PM attendance is recorded. |
| ATT-13 | An attendance status is "Complete" if absent = 0 and late ≤ 2 for the period; otherwise "Incomplete". |

#### 3.4.3 Detailed DTR

| ID | Requirement |
|----|-------------|
| ATT-14 | The system shall provide a detailed Daily Time Record (DTR) view per employee for a selected date range. |
| ATT-15 | Each DTR row shall show: date, day of week, AM In, AM Out, PM In, PM Out, OT In, OT Out, late minutes, undertime minutes, and total hours. |
| ATT-16 | The system shall display accredited hours breakdown (AM and PM) with grace period indicators. |
| ATT-17 | The system shall allow export of the detailed DTR to a CSV file with employee and department header information. |

#### 3.4.4 Attendance Correction

| ID | Requirement |
|----|-------------|
| ATT-18 | The system shall allow admin to correct any attendance record (AM In, AM Out, PM In, PM Out, OT In, OT Out). |
| ATT-19 | The system shall allow creation of a new attendance record for a date with no existing record. |
| ATT-20 | Every correction shall create an AttendanceCorrection log capturing old values, new values, reason, corrected-by user, and optional file attachments (PDF, JPG, PNG, max 5MB each). |
| ATT-21 | After correction, the system shall automatically recompute accredited hours and total hours and update the AccreditedHoursLog. |

#### 3.4.5 Accredited Hours Log

| ID | Requirement |
|----|-------------|
| ATT-22 | The system shall maintain one AccreditedHoursLog record per attendance entry. |
| ATT-23 | The log shall store: schedule used, AM/PM accredited minutes, OT minutes, late minutes, undertime minutes, total accredited minutes, total actual minutes, grace period flags, and computation notes. |
| ATT-24 | The system shall provide an API endpoint to retrieve the accredited hours log for a specific attendance record. |

---

### 3.5 Department Management Module (Admin)

| ID | Requirement |
|----|-------------|
| DEPT-01 | The system shall allow admin to add a new department with: code, name, department head, personnel count, status (Active/Inactive), and description. |
| DEPT-02 | Department codes shall be unique. |
| DEPT-03 | The system shall display all departments in a list with their details. |
| DEPT-04 | The system shall allow bulk import of departments via CSV file. |
| DEPT-05 | The system shall provide a downloadable CSV template for department import. |
| DEPT-06 | The system shall allow export of all departments to a CSV file. |
| DEPT-07 | The system shall allow admin to add designations under a department with: title, salary grade, monthly rate, employment type, and description. |
| DEPT-08 | The system shall allow bulk import of designations via CSV file. |
| DEPT-09 | The system shall provide a downloadable CSV template for designation import. |
| DEPT-10 | The system shall allow export of all designations to a CSV file. |
| DEPT-11 | The system shall provide an API endpoint to retrieve designations by department (used in employee registration). |
| DEPT-12 | Duplicate designations (same title, department, and monthly rate) shall be skipped during import. |

---

### 3.6 Chatbot Module

| ID | Requirement |
|----|-------------|
| CHAT-01 | The system shall provide an AI-powered chatbot accessible to Admin, Permanent, and Job Order users. |
| CHAT-02 | The chatbot shall be powered by the Groq API (external AI service). |
| CHAT-03 | The system shall provide an API endpoint (`POST /api/chatbot`) for chatbot interactions. |
| CHAT-04 | The system shall provide an API endpoint to retrieve the authenticated user's ID and name for chatbot context. |

---

### 3.7 Admin Module Pages

| ID | Requirement |
|----|-------------|
| ADM-01 | Admin Dashboard — overview of HR statistics and quick navigation. |
| ADM-02 | Recruitment — manage job postings and applicants. |
| ADM-03 | Training — manage employee training records. |
| ADM-04 | Performance — manage employee performance evaluations. |
| ADM-05 | Leave and Benefits — manage employee leave requests and benefits. |
| ADM-06 | Payroll — manage payroll processing. |
| ADM-07 | Reports — generate and view HR reports. |
| ADM-08 | Notifications — view system notifications. |
| ADM-09 | Theme Settings — customize system appearance. |

---

### 3.8 Permanent Employee Module

| ID | Requirement |
|----|-------------|
| PERM-01 | Permanent employees shall access a dedicated dashboard. |
| PERM-02 | Permanent employees shall view their own attendance records. |
| PERM-03 | Permanent employees shall view their payslips. |
| PERM-04 | Permanent employees shall view and manage leave and benefits. |
| PERM-05 | Permanent employees shall view their performance evaluations. |
| PERM-06 | Permanent employees shall view their training records. |
| PERM-07 | Permanent employees shall view and update their profile. |
| PERM-08 | Permanent employees shall access system settings. |
| PERM-09 | Permanent employees shall receive and view notifications. |
| PERM-10 | Permanent employees shall access the AI chatbot. |

---

### 3.9 Job Order Employee Module

| ID | Requirement |
|----|-------------|
| JO-01 | Job Order employees shall access a dedicated dashboard. |
| JO-02 | Job Order employees shall view their own attendance records. |
| JO-03 | Job Order employees shall view their payslips. |
| JO-04 | Job Order employees shall view their performance evaluations. |
| JO-05 | Job Order employees shall view their training records. |
| JO-06 | Job Order employees shall view and update their profile. |
| JO-07 | Job Order employees shall access system settings. |
| JO-08 | Job Order employees shall receive and view notifications. |
| JO-09 | Job Order employees shall access the AI chatbot. |

---

## 4. Database Design

### 4.1 Core Tables

| Table | Key Fields |
|-------|-----------|
| `employees` | id, employee_id, first_name, middle_name, last_name, suffix, photo, birth_date, place_of_birth, sex, civil_status, height, weight, blood_type, citizenship, email |
| `users` | id, employee_id (FK), email, username, password, role, status |
| `employment_details` | id, employee_id (FK), designation_id (FK), department_id (FK), employment_status, appointment_date, salary_grade, step_increment |
| `departments` | id, code, name, head, personnel_count, status, description |
| `designations` | id, title, department_id (FK), salary_grade, monthly_rate, employment_type, description |
| `addresses` | id, employee_id (FK), type, house_no, street, barangay, city, province, zip_code |
| `contacts` | id, employee_id (FK), type (mobile/landline/emergency), number, contact_person |
| `government_ids` | id, employee_id (FK), gsis_no, philhealth_no, pagibig_no, tin_no, license_no |
| `schedules` | id, employee_id (FK), start_date, end_date, am_in, am_out, pm_in, pm_out |
| `attendance` | id, employee_id (FK), date, am_in, am_out, pm_in, pm_out, ot_in, ot_out, accredited_hours, total_hours |
| `attendance_corrections` | id, attendance_id (FK), employee_id (FK), date, old/new time fields, reason, attachments, corrected_by |
| `accredited_hours_log` | id, attendance_id (FK), employee_id (FK), schedule_id (FK), am/pm accredited minutes, ot/late/undertime minutes, grace flags, notes |
| `educations` | id, employee_id (FK), level, school_name, degree, year_graduated, honors |
| `eligibilities` | id, employee_id (FK), type, rating, exam_date, exam_place, license_no, validity_date |
| `work_experiences` | id, employee_id (FK), company_name, position, from_date, to_date, salary, appointment_status, is_government |
| `trainings` | id, employee_id (FK), title, date_from, date_to, hours, conducted_by |
| `family_members` | id, employee_id (FK), name, relationship, birthdate, occupation |
| `documents` | id, employee_id (FK), document_type, file_path, status, uploaded_at |
| `legal_requirements` | id, employee_id (FK), saln_submitted, oath_of_office, assumption_date |

---

## 5. Non-Functional Requirements

| ID | Requirement |
|----|-------------|
| NFR-01 | All routes that access employee or HR data shall be protected by the `auth` middleware. |
| NFR-02 | Passwords shall be stored using Laravel's bcrypt hashing. |
| NFR-03 | File uploads (photos, attachments) shall be validated for type and size before storage. |
| NFR-04 | CSV exports shall include a UTF-8 BOM for proper encoding in Microsoft Excel. |
| NFR-05 | Database operations involving multiple table writes shall use transactions to ensure data integrity. |
| NFR-06 | The system shall use Poppins as the primary UI font for visual consistency. |
| NFR-07 | The system UI shall be responsive and support modal-based interactions for forms and confirmations. |
| NFR-08 | The system shall use Laravel's session-based CSRF protection on all POST forms. |
| NFR-09 | The system shall log attendance computation notes for auditability. |
| NFR-10 | The system shall support role-based redirection upon login (admin, hr, permanent, job order). |

---

## 6. System Constraints

- The system is designed for the Municipal Government of Pagsanjan and uses the municipal logo in the UI.
- Attendance records are entered/corrected manually by admin; there is no biometric device integration in the current scope.
- The chatbot feature requires an active Groq API key configured in the environment.
- The system does not currently implement automated payroll computation; the payroll module is a placeholder for future development.
- Leave, benefits, recruitment, performance, and reports modules are present as UI pages and are planned for full implementation in future iterations.

---

## 7. Future Enhancements

- Biometric or QR-based automated attendance logging
- Full payroll computation engine
- Leave request and approval workflow
- Performance evaluation forms and scoring
- Recruitment pipeline management
- Automated report generation (PDF)
- Email notifications for leave approvals, schedule changes, and corrections
- Mobile-responsive PWA version

---

*End of Software Requirements Specification*
