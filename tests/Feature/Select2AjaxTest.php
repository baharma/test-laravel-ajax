<?php

test('select2 ajax endpoint returns select2-compatible results', function () {
    $response = $this->getJson(route('demo.select2.employees', [
        'q' => 'engineering',
        'page' => 1,
    ]));

    $response
        ->assertOk()
        ->assertJsonStructure([
            'results' => [
                ['id', 'text'],
            ],
            'pagination' => ['more'],
        ]);
});
