<?php

namespace App\Http\Controllers;

use App\Models\EmploymentStatus;

class EmploymentStatusController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: EmploymentStatus::class,
            searchable: ['name', 'code'],
        );
    }
}
