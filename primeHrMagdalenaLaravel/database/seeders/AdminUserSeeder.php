<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Contact;
use App\Models\User;
use App\Models\Employee;
use App\Models\EmployeeSupportingDocument;
use App\Models\EmploymentDetail;
use App\Models\GovernmentId;
use App\Models\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employee = Employee::create([
            'employee_id' => 'EMP-2025-0001',
            'first_name' => 'Jade',
            'middle_name' => 'Alvarado',
            'last_name' => 'Flores',
            'photo' => '/storage/employees/photos/1782459335_1.png',
            'birth_date' => '1990-01-01',
            'place_of_birth' => 'Pagsanjan, Laguna',
            'sex' => 'Male',
            'civil_status' => 'Single',
            'height' => 170.00,
            'weight' => 70.00,
            'blood_type' => 'O+',
            'citizenship' => 'Filipino',
            'email' => 'admin@gmail.com',
        ]);

        $employeeDetails = EmploymentDetail::create([
            'employee_id' => $employee->id,
            'designation_id' => 138,
            'department_id' => 3,
            'employment_status' => 'Permanent',
            'appointment_date' => '2026-01-01',
            'salary_grade' => NULL,
            'step_increment' => '1',
        ]);

        $employeeGovernmentIDs = GovernmentId::create([
            'employee_id' => $employee->id,
            'gsis_no' => null,
            'gsis_file_path' => null,
            'philhealth_no' => null,
            'philhealth_file_path' => null,
            'pagibig_no' => null,
            'pagibig_file_path' => null,
            'tin_no' => null,
            'tin_file_path' => null,
            'license_no' => null,
            'license_file_path' => null,
        ]);

        $employeeSupportingDocuments = EmployeeSupportingDocument::create([
            'employee_id' => $employee->id,
            'pds_file_path' => null,
            'appointment_form_file_path' => null,
            'position_description_file_path' => null,
            'medical_certificate_file_path' => null,
            'nbi_clearance_file_path' => null,
            'financial_clearance_file_path' => null,
            'neuro_exam_file_path' => null,
            'licenses_file_path' => null,
            'performance_eval_file_path' => null,
            'commendation_file_path' => null,
            'disciplinary_file_path' => null,
            'other_records_file_path' => null,
        ]);

        $employeeAddress = Address::create([
            'employee_id' => $employee->id,
            'type' => 'residential',
            'house_no' => null,
            'street' => '123 Admin Street',
            'barangay' => 'Barangay 1',
            'city' => 'Pagsanjan',
            'province' => 'Laguna',
            'zip_code' => '4008',
        ]);

        $employeeContact = Contact::create([
            'employee_id' => $employee->id,
            'type' => 'mobile',
            'number' => '09123456789',
            'contact_person' => NULL,
        ]);

        $employeeSchedule = Schedule::create([
            'employee_id' => $employee->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'am_in' => '08:00:00',
            'am_out' => '12:00:00',
            'pm_in' => '13:00:00',
            'pm_out' => '17:00:00',
        ]);

        // `employee_id` is the link every screen that names the signed-in person
        // resolves through — the rails (`$user->employee`), the AI assistant's
        // self-service scope, the notification audiences. It was missing here,
        // so a freshly seeded admin signed in to a rail that could not find its
        // own name and fell back to an invented "Admin User".
        User::factory()->create([
            'name'        => 'Jade Flores',
            'username'    => 'floresjade',
            'email'       => 'admin@gmail.com',
            'password'    => bcrypt('asdf'),
            'roles'       => ['employee', 'admin'],
            'status'      => 'Active',
            'employee_id' => $employee->id,
        ]);
    }
}
