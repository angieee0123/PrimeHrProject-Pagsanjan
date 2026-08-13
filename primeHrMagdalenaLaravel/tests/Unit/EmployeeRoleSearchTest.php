<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmploymentDetail;
use App\Models\User;
use App\Services\AiAccessPolicy;
use App\Services\EmployeeSearchService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * "Who is the mayor" must resolve to the employee whose account holds the
 * `mayor` role — not to everyone assigned to the Mayor's Office department.
 *
 * This is the bug that made the chatbot answer office-holder questions with a
 * roster: "who is the mayor" matched the "Mayor's Office" department in
 * parseSearchQuery() and listed every employee in it. The role path runs
 * before the department filter and is re-scoped through AiAccessPolicy like
 * every other retrieval.
 */
class EmployeeRoleSearchTest extends TestCase
{
    private EmployeeSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->service = new EmployeeSearchService(new AiAccessPolicy());
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->json('roles')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix')->nullable();
            $table->string('email')->nullable();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->timestamps();
        });

        Schema::create('employment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();
            $table->string('employment_status')->nullable();
            $table->date('appointment_date')->nullable();
            $table->string('salary_grade')->nullable();
        });
    }

    private function employee(string $name): Employee
    {
        [$first, $last] = explode(' ', $name) + [null, $name];

        return Employee::create([
            'employee_id' => 'EMP-' . fake()->unique()->numberBetween(1000, 9999),
            'first_name' => $first,
            'last_name' => $last,
        ]);
    }

    private function assign(Employee $employee, Department $department): void
    {
        EmploymentDetail::create([
            'employee_id' => $employee->id,
            'department_id' => $department->id,
            'designation_id' => Designation::create(['title' => 'Staff'])->id,
            'employment_status' => 'Permanent',
        ]);
    }

    private function grantRole(Employee $employee, string $role): void
    {
        User::create([
            'name' => $employee->first_name . ' ' . $employee->last_name,
            'email' => fake()->unique()->safeEmail(),
            'password' => 'x',
            'employee_id' => $employee->id,
            'roles' => [$role],
            'status' => 'Active',
        ]);
    }

    private function user(array $roles, ?int $employeeId = null): User
    {
        $user = new User();
        $user->roles = $roles;
        $user->status = 'Active';
        $user->employee_id = $employeeId;

        return $user;
    }

    #[Test]
    public function asking_for_the_mayor_returns_the_role_holder_not_the_office_roster(): void
    {
        $mayorsOffice = Department::create(['code' => 'MO', 'name' => "Mayor's Office"]);
        $treasury = Department::create(['code' => 'TR', 'name' => 'Treasury Office']);

        $mayor = $this->employee('Juan Mayor');
        $this->assign($mayor, $mayorsOffice);
        $this->grantRole($mayor, 'mayor');

        $this->assign($this->employee('Ana Clerk'), $mayorsOffice);
        $this->assign($this->employee('Ben Staff'), $mayorsOffice);
        $this->assign($this->employee('Cora Treasurer'), $treasury);

        $result = $this->service->search($this->user(['admin']), 'who is the mayor');

        $this->assertSame(1, $result['count'], 'expected exactly the mayor, not the whole office');
        $this->assertStringContainsString('Juan Mayor', $result['data'][0]['name']);
        $this->assertStringContainsString('The Mayor is:', $result['answer']);
    }

    #[Test]
    public function the_role_is_read_from_the_account_even_when_department_also_matches(): void
    {
        $mayorsOffice = Department::create(['code' => 'MO', 'name' => "Mayor's Office"]);

        $mayor = $this->employee('Maria Ledesma');
        $this->assign($mayor, $mayorsOffice);
        $this->grantRole($mayor, 'mayor');

        // A staff member in the Mayor's Office who is NOT the mayor.
        $this->assign($this->employee('Nono Staff'), $mayorsOffice);

        $result = $this->service->search($this->user(['hr']), "who's the mayor");

        $this->assertSame(1, $result['count']);
        $this->assertStringContainsString('Maria Ledesma', $result['data'][0]['name']);
    }

    #[Test]
    public function an_hr_officer_question_resolves_through_the_hr_role(): void
    {
        $hrDept = Department::create(['code' => 'HR', 'name' => 'HR Office']);

        $hrHead = $this->employee('Rosa Santos');
        $this->assign($hrHead, $hrDept);
        $this->grantRole($hrHead, 'hr');

        $result = $this->service->search($this->user(['admin']), 'who is the hr officer');

        $this->assertSame(1, $result['count']);
        $this->assertStringContainsString('Rosa Santos', $result['data'][0]['name']);
        $this->assertStringContainsString('The HR officer is:', $result['answer']);
    }

    #[Test]
    public function a_self_scoped_caller_is_still_limited_to_their_own_record(): void
    {
        $mayorsOffice = Department::create(['code' => 'MO', 'name' => "Mayor's Office"]);

        $mayor = $this->employee('Juan Mayor');
        $this->assign($mayor, $mayorsOffice);
        $this->grantRole($mayor, 'mayor');

        $employee = $this->employee('Pedro Empleyado');
        $this->assign($employee, $mayorsOffice);

        $result = $this->service->search($this->user(['employee'], $employee->id), 'who is the mayor');

        $this->assertSame(0, $result['count']);
        $this->assertStringContainsString('linked to the Mayor role', $result['answer']);
        $this->assertStringContainsString('your own records', $result['answer']);
    }

    #[Test]
    public function when_no_account_holds_the_role_it_says_so_instead_of_listing_everyone(): void
    {
        $mayorsOffice = Department::create(['code' => 'MO', 'name' => "Mayor's Office"]);
        $this->assign($this->employee('Solo Staff'), $mayorsOffice);

        $result = $this->service->search($this->user(['admin']), 'who is the mayor');

        $this->assertSame(0, $result['count']);
        $this->assertStringContainsString('No employee account is currently linked to the Mayor role', $result['answer']);
    }
}
