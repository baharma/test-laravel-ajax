<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SupportController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home');

Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');


Route::post('/upload-file', [SupportController::class, 'UploadFileLocal'])->name('upload.file');
Route::get('/lookups/departments', [EmployeeController::class, 'departmentsLookup'])
    ->name('lookups.departments');
Route::get('/lookups/employment-statuses', [EmployeeController::class, 'employmentStatusesLookup'])
    ->name('lookups.employment-statuses');
