<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeFile;
use App\Models\EmploymentStatus;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HumanResourcesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $departments = $this->seedDepartments();
            $statuses = $this->seedEmploymentStatuses();
            $positions = $this->seedPositions($departments);

            $employees = $this->seedEmployees($departments, $statuses, $positions);
            $this->seedEmployeeFiles($employees);
        });
    }

    protected function seedDepartments(): array
    {
        $departments = [
            [
                'code' => 'ENG',
                'name' => 'Engineering',
                'description' => 'Software engineering, QA, DevOps, and platform delivery.',
            ],
            [
                'code' => 'FIN',
                'name' => 'Finance',
                'description' => 'Financial planning, accounting, budgeting, and reporting.',
            ],
            [
                'code' => 'HR',
                'name' => 'Human Resource',
                'description' => 'Recruitment, people operations, and employee engagement.',
            ],
            [
                'code' => 'OPS',
                'name' => 'Operations',
                'description' => 'Daily business operations, process execution, and support.',
            ],
            [
                'code' => 'MKT',
                'name' => 'Marketing',
                'description' => 'Campaign planning, content, brand, and growth initiatives.',
            ],
        ];

        $records = [];

        foreach ($departments as $department) {
            $records[$department['code']] = Department::updateOrCreate(
                ['code' => $department['code']],
                $department,
            );
        }

        return $records;
    }

    protected function seedEmploymentStatuses(): array
    {
        $statuses = [
            ['code' => 'PERM', 'name' => 'Permanent'],
            ['code' => 'CONT', 'name' => 'Contract'],
            ['code' => 'PROB', 'name' => 'Probation'],
            ['code' => 'INT', 'name' => 'Internship'],
        ];

        $records = [];

        foreach ($statuses as $status) {
            $records[$status['code']] = EmploymentStatus::updateOrCreate(
                ['code' => $status['code']],
                $status,
            );
        }

        return $records;
    }

    protected function seedPositions(array $departments): array
    {
        $positions = [
            ['department' => 'ENG', 'name' => 'Backend Developer', 'level' => 'Senior'],
            ['department' => 'ENG', 'name' => 'Frontend Developer', 'level' => 'Mid'],
            ['department' => 'ENG', 'name' => 'QA Engineer', 'level' => 'Mid'],
            ['department' => 'FIN', 'name' => 'Finance Analyst', 'level' => 'Senior'],
            ['department' => 'FIN', 'name' => 'Accountant', 'level' => 'Junior'],
            ['department' => 'HR', 'name' => 'HR Officer', 'level' => 'Mid'],
            ['department' => 'HR', 'name' => 'Talent Acquisition', 'level' => 'Senior'],
            ['department' => 'OPS', 'name' => 'Operations Specialist', 'level' => 'Mid'],
            ['department' => 'OPS', 'name' => 'Project Coordinator', 'level' => 'Senior'],
            ['department' => 'MKT', 'name' => 'Digital Marketer', 'level' => 'Mid'],
        ];

        $records = [];

        foreach ($positions as $position) {
            $department = $departments[$position['department']];

            $record = Position::updateOrCreate(
                [
                    'department_id' => $department->id,
                    'name' => $position['name'],
                    'level' => $position['level'],
                ],
                [
                    'description' => "{$position['name']} for {$department->name}.",
                ],
            );

            $records[$position['department'] . '::' . $position['name'] . '::' . $position['level']] = $record;
        }

        return $records;
    }

    protected function seedEmployees(array $departments, array $statuses, array $positions): array
    {
        $employees = [
            [
                'employee_code' => 'EMP-0001',
                'full_name' => 'Raka Pratama',
                'email' => 'raka.pratama@example.com',
                'phone' => '081234560001',
                'gender' => 'male',
                'birth_place' => 'Makassar',
                'birth_date' => '1995-02-11',
                'address' => 'Jl. Andi Pangeran Pettarani No. 12, Makassar',
                'department' => 'ENG',
                'position' => 'ENG::Backend Developer::Senior',
                'status' => 'PERM',
                'hire_date' => '2022-01-17',
                'is_active' => true,
                'notes' => 'Lead backend engineer for core employee services.',
            ],
            [
                'employee_code' => 'EMP-0002',
                'full_name' => 'Anisa Ramadhani',
                'email' => 'anisa.ramadhani@example.com',
                'phone' => '081234560002',
                'gender' => 'female',
                'birth_place' => 'Makassar',
                'birth_date' => '1997-08-21',
                'address' => 'Jl. Perintis Kemerdekaan No. 8, Makassar',
                'department' => 'ENG',
                'position' => 'ENG::Frontend Developer::Mid',
                'status' => 'PERM',
                'hire_date' => '2023-03-01',
                'is_active' => true,
                'notes' => 'Responsible for dashboard and internal UI systems.',
            ],
            [
                'employee_code' => 'EMP-0003',
                'full_name' => 'Nadia Putri',
                'email' => 'nadia.putri@example.com',
                'phone' => '081234560003',
                'gender' => 'female',
                'birth_place' => 'Parepare',
                'birth_date' => '1998-01-14',
                'address' => 'Jl. Urip Sumoharjo No. 21, Makassar',
                'department' => 'FIN',
                'position' => 'FIN::Finance Analyst::Senior',
                'status' => 'PERM',
                'hire_date' => '2021-07-15',
                'is_active' => true,
                'notes' => 'Handles monthly reporting and financial reconciliation.',
            ],
            [
                'employee_code' => 'EMP-0004',
                'full_name' => 'Dimas Saputra',
                'email' => 'dimas.saputra@example.com',
                'phone' => '081234560004',
                'gender' => 'male',
                'birth_place' => 'Gowa',
                'birth_date' => '1999-12-03',
                'address' => 'Jl. Sultan Alauddin No. 50, Makassar',
                'department' => 'OPS',
                'position' => 'OPS::Operations Specialist::Mid',
                'status' => 'CONT',
                'hire_date' => '2024-02-12',
                'is_active' => true,
                'notes' => 'Focuses on internal operations workflow and vendor coordination.',
            ],
            [
                'employee_code' => 'EMP-0005',
                'full_name' => 'Salma Nurfadillah',
                'email' => 'salma.nurfadillah@example.com',
                'phone' => '081234560005',
                'gender' => 'female',
                'birth_place' => 'Bone',
                'birth_date' => '1996-05-09',
                'address' => 'Jl. AP Pettarani No. 45, Makassar',
                'department' => 'HR',
                'position' => 'HR::HR Officer::Mid',
                'status' => 'PERM',
                'hire_date' => '2022-10-01',
                'is_active' => true,
                'notes' => 'Manages onboarding and employee relations.',
            ],
            [
                'employee_code' => 'EMP-0006',
                'full_name' => 'Bagas Wibowo',
                'email' => 'bagas.wibowo@example.com',
                'phone' => '081234560006',
                'gender' => 'male',
                'birth_place' => 'Palopo',
                'birth_date' => '1994-09-18',
                'address' => 'Jl. Veteran Selatan No. 9, Makassar',
                'department' => 'MKT',
                'position' => 'MKT::Digital Marketer::Mid',
                'status' => 'CONT',
                'hire_date' => '2023-11-11',
                'is_active' => true,
                'notes' => 'Owns digital campaign strategy and paid acquisition.',
            ],
            [
                'employee_code' => 'EMP-0007',
                'full_name' => 'Farhan Akbar',
                'email' => 'farhan.akbar@example.com',
                'phone' => '081234560007',
                'gender' => 'male',
                'birth_place' => 'Makassar',
                'birth_date' => '2000-04-28',
                'address' => 'Jl. Bonto Langkasa No. 14, Makassar',
                'department' => 'ENG',
                'position' => 'ENG::QA Engineer::Mid',
                'status' => 'PROB',
                'hire_date' => '2026-01-06',
                'is_active' => true,
                'notes' => 'In probation period for QA automation team.',
            ],
            [
                'employee_code' => 'EMP-0008',
                'full_name' => 'Citra Lestari',
                'email' => 'citra.lestari@example.com',
                'phone' => '081234560008',
                'gender' => 'female',
                'birth_place' => 'Soppeng',
                'birth_date' => '1993-06-16',
                'address' => 'Jl. Boulevard No. 77, Makassar',
                'department' => 'OPS',
                'position' => 'OPS::Project Coordinator::Senior',
                'status' => 'PERM',
                'hire_date' => '2020-09-14',
                'is_active' => true,
                'notes' => 'Coordinates cross-team operational projects.',
            ],
            [
                'employee_code' => 'EMP-0009',
                'full_name' => 'Yoga Prakoso',
                'email' => 'yoga.prakoso@example.com',
                'phone' => '081234560009',
                'gender' => 'male',
                'birth_place' => 'Bulukumba',
                'birth_date' => '1998-11-02',
                'address' => 'Jl. Cendrawasih No. 33, Makassar',
                'department' => 'FIN',
                'position' => 'FIN::Accountant::Junior',
                'status' => 'CONT',
                'hire_date' => '2024-06-03',
                'is_active' => false,
                'end_date' => '2026-01-31',
                'notes' => 'Contract completed at end of January 2026.',
            ],
            [
                'employee_code' => 'EMP-0010',
                'full_name' => 'Aulia Maharani',
                'email' => 'aulia.maharani@example.com',
                'phone' => '081234560010',
                'gender' => 'female',
                'birth_place' => 'Maros',
                'birth_date' => '2002-03-20',
                'address' => 'Jl. Pengayoman No. 18, Makassar',
                'department' => 'HR',
                'position' => 'HR::Talent Acquisition::Senior',
                'status' => 'INT',
                'hire_date' => '2026-02-10',
                'is_active' => true,
                'notes' => 'Campus hiring and internship coordination.',
            ],
        ];

        $records = [];

        foreach ($employees as $employee) {
            $record = Employee::updateOrCreate(
                ['employee_code' => $employee['employee_code']],
                [
                    'full_name' => $employee['full_name'],
                    'email' => $employee['email'],
                    'phone' => $employee['phone'],
                    'gender' => $employee['gender'],
                    'birth_place' => $employee['birth_place'],
                    'birth_date' => $employee['birth_date'],
                    'address' => $employee['address'],
                    'department_id' => $departments[$employee['department']]->id,
                    'position_id' => $positions[$employee['position']]->id,
                    'employment_status_id' => $statuses[$employee['status']]->id,
                    'hire_date' => $employee['hire_date'],
                    'end_date' => $employee['end_date'] ?? null,
                    'photo' => 'employees/photos/' . Str::slug($employee['full_name']) . '.jpg',
                    'notes' => $employee['notes'],
                    'is_active' => $employee['is_active'],
                ],
            );

            $records[$employee['employee_code']] = $record;
        }

        return $records;
    }

    protected function seedEmployeeFiles(array $employees): void
    {
        $now = Carbon::now();

        $files = [
            [
                'employee_code' => 'EMP-0001',
                'file_name' => 'employment-contract-raka-pratama.pdf',
                'category' => 'contract',
                'mime_type' => 'application/pdf',
                'file_size' => 240512,
            ],
            [
                'employee_code' => 'EMP-0001',
                'file_name' => 'cv-raka-pratama.pdf',
                'category' => 'cv',
                'mime_type' => 'application/pdf',
                'file_size' => 112640,
            ],
            [
                'employee_code' => 'EMP-0003',
                'file_name' => 'tax-document-nadia-putri.pdf',
                'category' => 'tax',
                'mime_type' => 'application/pdf',
                'file_size' => 180224,
            ],
            [
                'employee_code' => 'EMP-0005',
                'file_name' => 'hr-certificate-salma-nurfadillah.pdf',
                'category' => 'certificate',
                'mime_type' => 'application/pdf',
                'file_size' => 95232,
            ],
            [
                'employee_code' => 'EMP-0008',
                'file_name' => 'project-assignment-citra-lestari.pdf',
                'category' => 'assignment',
                'mime_type' => 'application/pdf',
                'file_size' => 144384,
            ],
        ];

        foreach ($files as $file) {
            $employee = $employees[$file['employee_code']];

            EmployeeFile::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'file_name' => $file['file_name'],
                ],
                [
                    'file_path' => 'employee-files/' . $file['category'] . '/' . $file['file_name'],
                    'category' => $file['category'],
                    'mime_type' => $file['mime_type'],
                    'file_size' => $file['file_size'],
                    'uploaded_at' => $now,
                ],
            );
        }
    }
}
