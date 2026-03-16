<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeFile;
use App\Models\EmploymentStatus;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('human resources data can be stored with its relationships', function () {
    $department = Department::create([
        'name' => 'Engineering',
        'code' => 'ENG',
    ]);

    $employmentStatus = EmploymentStatus::create([
        'name' => 'Permanent',
        'code' => 'PERM',
    ]);

    $position = Position::create([
        'department_id' => $department->id,
        'name' => 'Backend Developer',
        'level' => 'Senior',
    ]);

    $employee = Employee::create([
        'employee_code' => 'EMP-0001',
        'full_name' => 'Taksum Example',
        'email' => 'taksum@example.com',
        'phone' => '081234567890',
        'gender' => 'male',
        'birth_place' => 'Makassar',
        'birth_date' => '1998-06-12',
        'address' => 'Jl. Contoh No. 1',
        'department_id' => $department->id,
        'position_id' => $position->id,
        'employment_status_id' => $employmentStatus->id,
        'hire_date' => '2026-03-16',
        'is_active' => true,
    ]);

    $file = EmployeeFile::create([
        'employee_id' => $employee->id,
        'file_name' => 'contract.pdf',
        'file_path' => 'employee-files/contracts/contract.pdf',
        'category' => 'contract',
        'mime_type' => 'application/pdf',
        'file_size' => 102400,
    ]);

    expect($employee->department->is($department))->toBeTrue()
        ->and($employee->position->is($position))->toBeTrue()
        ->and($employee->employmentStatus->is($employmentStatus))->toBeTrue()
        ->and($employee->employeeFiles)->toHaveCount(1)
        ->and($employee->employeeFiles->first()->is($file))->toBeTrue()
        ->and($employee->is_active)->toBeTrue();
});
