<?php

namespace App\Http\Controllers;

use App\Models\Department;

class DepartmentController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: Department::class,
            relations: ['positions'],
            searchable: ['name', 'code', 'description'],
        );
    }
}
