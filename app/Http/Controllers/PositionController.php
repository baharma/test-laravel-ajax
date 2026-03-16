<?php

namespace App\Http\Controllers;

use App\Models\Position;

class PositionController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: Position::class,
            relations: ['department'],
            searchable: ['name', 'level', 'description'],
        );
    }
}
