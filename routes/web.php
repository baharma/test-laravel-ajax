<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmploymentStatusController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\SupportController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home');

Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
Route::patch('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');


Route::post('/upload-file', [SupportController::class, 'UploadFileLocal'])->name('upload.file');
Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
Route::get('/positions', [PositionController::class, 'index'])->name('positions.index');
Route::get('/employment-statuses', [EmploymentStatusController::class, 'index'])
    ->name('employment-statuses.index');
