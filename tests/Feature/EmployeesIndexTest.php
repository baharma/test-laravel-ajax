<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmploymentStatus;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('employees endpoint supports keyword search and exact filters', function () {
    $engineering = Department::create([
        'name' => 'Engineering',
        'code' => 'ENG',
    ]);

    $finance = Department::create([
        'name' => 'Finance',
        'code' => 'FIN',
    ]);

    $permanent = EmploymentStatus::create([
        'name' => 'Permanent',
        'code' => 'PERM',
    ]);

    $contract = EmploymentStatus::create([
        'name' => 'Contract',
        'code' => 'CONT',
    ]);

    $backend = Position::create([
        'department_id' => $engineering->id,
        'name' => 'Backend Developer',
        'level' => 'Senior',
    ]);

    $accountant = Position::create([
        'department_id' => $finance->id,
        'name' => 'Accountant',
        'level' => 'Junior',
    ]);

    Employee::create([
        'employee_code' => 'EMP-0001',
        'full_name' => 'Raka Pratama',
        'email' => 'raka@example.com',
        'phone' => '081234567890',
        'department_id' => $engineering->id,
        'position_id' => $backend->id,
        'employment_status_id' => $permanent->id,
        'hire_date' => '2026-03-10',
        'is_active' => true,
    ]);

    Employee::create([
        'employee_code' => 'EMP-0002',
        'full_name' => 'Nadia Finance',
        'email' => 'nadia@example.com',
        'phone' => '089999999999',
        'department_id' => $finance->id,
        'position_id' => $accountant->id,
        'employment_status_id' => $contract->id,
        'hire_date' => '2026-03-11',
        'is_active' => false,
    ]);

    $response = $this->getJson(route('employees.index', [
        'search' => 'raka',
        'department_id' => $engineering->id,
        'employment_status_id' => $permanent->id,
        'is_active' => '1',
        'orderBy' => 'full_name',
        'orderDirection' => 'asc',
        'per_page' => 'all',
    ]));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.full_name', 'Raka Pratama')
        ->assertJsonPath('data.0.department.name', 'Engineering')
        ->assertJsonPath('data.0.employment_status.name', 'Permanent');
});
