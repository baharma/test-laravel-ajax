<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: Employee::class,
            relations: ['department', 'position', 'employmentStatus', 'employeeFiles'],
            searchable: ['employee_code', 'full_name', 'email', 'phone', 'birth_place'],
        );
    }

    public function store(Request $request)
    {
        $payload = $this->validateEmployee($request);

        $employee = DB::transaction(function () use ($payload) {
            return Employee::query()->create($payload);
        });

        return $this->apiSuccess(
            $employee->load($this->relations),
            'Employee created successfully',
        );
    }

    public function update(Request $request, int|string $id)
    {
        $employee = $this->findRecord($id);
        $payload = $this->validateEmployee($request, $employee);

        $employee = DB::transaction(function () use ($employee, $payload) {
            $employee->fill($payload);
            $employee->save();

            return $employee->fresh();
        });

        return $this->apiSuccess(
            $employee->load($this->relations),
            'Employee updated successfully',
        );
    }

    protected function validateEmployee(Request $request, ?Employee $employee = null): array
    {
        $employeeId = $employee?->id;

        return $request->validate([
            'employee_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'employee_code')->ignore($employeeId),
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employeeId),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'position_id' => [
                'required',
                'integer',
                Rule::exists('positions', 'id')->where(function ($query) use ($request) {
                    $departmentId = $request->input('department_id');

                    if (filled($departmentId)) {
                        $query->where('department_id', $departmentId);
                    }

                    return $query;
                }),
            ],
            'employment_status_id' => ['required', 'integer', 'exists:employment_statuses,id'],
            'hire_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:hire_date'],
            'photo' => ['nullable', 'string', 'max:2048'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
