<?php

use App\Models\Department;
use App\Models\EmploymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('department lookup returns select2-compatible results', function () {
    Department::create([
        'name' => 'Engineering',
        'code' => 'ENG',
    ]);

    $response = $this->getJson(route('lookups.departments', [
        'q' => 'eng',
    ]));

    $response->assertOk()
        ->assertJsonStructure([
            'results' => [
                ['id', 'text'],
            ],
        ])
        ->assertJsonPath('results.0.text', 'ENG - Engineering');
});

test('employment status lookup returns select2-compatible results', function () {
    EmploymentStatus::create([
        'name' => 'Permanent',
        'code' => 'PERM',
    ]);

    $response = $this->getJson(route('lookups.employment-statuses', [
        'q' => 'perm',
    ]));

    $response->assertOk()
        ->assertJsonStructure([
            'results' => [
                ['id', 'text'],
            ],
        ])
        ->assertJsonPath('results.0.text', 'PERM - Permanent');
});
