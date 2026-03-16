<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmploymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function departmentsLookup(Request $request): JsonResponse
    {
        return $this->lookupResponse(
            query: Department::query(),
            request: $request,
            searchColumns: ['name', 'code'],
            formatter: fn (Department $department) => [
                'id' => $department->id,
                'text' => trim("{$department->code} - {$department->name}", ' -'),
            ],
        );
    }

    public function employmentStatusesLookup(Request $request): JsonResponse
    {
        return $this->lookupResponse(
            query: EmploymentStatus::query(),
            request: $request,
            searchColumns: ['name', 'code'],
            formatter: fn (EmploymentStatus $status) => [
                'id' => $status->id,
                'text' => trim("{$status->code} - {$status->name}", ' -'),
            ],
        );
    }

    protected function lookupResponse(
        Builder $query,
        Request $request,
        array $searchColumns,
        callable $formatter,
    ): JsonResponse {
        try {
            $keyword = trim((string) $request->input('q', ''));

            $results = $query
                ->when($keyword !== '', function ($builder) use ($keyword, $searchColumns) {
                    $builder->where(function ($nestedQuery) use ($keyword, $searchColumns) {
                        foreach ($searchColumns as $index => $column) {
                            $method = $index === 0 ? 'where' : 'orWhere';
                            $nestedQuery->{$method}($column, 'like', "%{$keyword}%");
                        }
                    });
                })
                ->orderBy('name')
                ->limit(20)
                ->get()
                ->map($formatter);

            return response()->json([
                'results' => $results,
            ]);
        } catch (\Throwable) {
            return response()->json([
                'results' => [],
            ]);
        }
    }
}
