<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeFile;
use App\Models\EmploymentStatus;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('human resources seeder populates master and employee data', function () {
    $this->seed(\Database\Seeders\HumanResourcesSeeder::class);

    expect(Department::count())->toBeGreaterThanOrEqual(5)
        ->and(EmploymentStatus::count())->toBeGreaterThanOrEqual(4)
        ->and(Position::count())->toBeGreaterThanOrEqual(10)
        ->and(Employee::count())->toBeGreaterThanOrEqual(10)
        ->and(EmployeeFile::count())->toBeGreaterThanOrEqual(5)
        ->and(Employee::where('employee_code', 'EMP-0001')->exists())->toBeTrue();
});
