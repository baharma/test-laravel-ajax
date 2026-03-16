<?php

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::post('/demo/dropzone-upload', function (Request $request) {
    $request->validate([
        'file' => ['required', 'file', 'max:5120'],
    ]);

    $file = $request->file('file');
    $storedPath = $file->store('demo-uploads');

    return response()->json([
        'name' => $file->getClientOriginalName(),
        'path' => $storedPath,
        'size' => $file->getSize(),
        'type' => $file->getMimeType(),
    ]);
})->name('demo.dropzone-upload');

Route::get('/demo/select2/employees', function (Request $request) {
    $employees = collect([
        ['id' => 1, 'code' => 'EMP-0001', 'name' => 'Anisa Ramadhani', 'department' => 'Finance'],
        ['id' => 2, 'code' => 'EMP-0002', 'name' => 'Raka Pratama', 'department' => 'Engineering'],
        ['id' => 3, 'code' => 'EMP-0003', 'name' => 'Nabila Putri', 'department' => 'Human Resource'],
        ['id' => 4, 'code' => 'EMP-0004', 'name' => 'Farhan Akbar', 'department' => 'Engineering'],
        ['id' => 5, 'code' => 'EMP-0005', 'name' => 'Salma Nurfadillah', 'department' => 'Sales'],
        ['id' => 6, 'code' => 'EMP-0006', 'name' => 'Dimas Saputra', 'department' => 'Operations'],
        ['id' => 7, 'code' => 'EMP-0007', 'name' => 'Aulia Maharani', 'department' => 'Marketing'],
        ['id' => 8, 'code' => 'EMP-0008', 'name' => 'Bagas Wibowo', 'department' => 'Engineering'],
        ['id' => 9, 'code' => 'EMP-0009', 'name' => 'Citra Lestari', 'department' => 'Finance'],
        ['id' => 10, 'code' => 'EMP-0010', 'name' => 'Yoga Prakoso', 'department' => 'Operations'],
    ]);

    $keyword = Str::lower((string) $request->input('q', ''));
    $page = max((int) $request->input('page', 1), 1);
    $perPage = 5;

    $filtered = $employees
        ->filter(function (array $employee) use ($keyword) {
            if ($keyword === '') {
                return true;
            }

            return Str::contains(
                Str::lower($employee['code'].' '.$employee['name'].' '.$employee['department']),
                $keyword,
            );
        })
        ->values();

    $results = $filtered
        ->forPage($page, $perPage)
        ->values()
        ->map(fn (array $employee) => [
            'id' => $employee['id'],
            'text' => "{$employee['code']} - {$employee['name']} ({$employee['department']})",
        ]);

    return response()->json([
        'results' => $results,
        'pagination' => [
            'more' => $filtered->count() > ($page * $perPage),
        ],
    ]);
})->name('demo.select2.employees');
