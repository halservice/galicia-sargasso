<?php

test('it belongs to generated code', function () {
    $gvc = \App\Models\GeneratedValidatedCode::factory()
        ->code()
        ->create();

    expect($gvc->generator)->toBeInstanceOf(\App\Models\GeneratedCode::class);
});

test('it belongs to formal model code', function () {
    $gvc = \App\Models\GeneratedValidatedCode::factory()
        ->formalModel()
        ->create();

    expect($gvc->generator)->toBeInstanceOf(\App\Models\GeneratedFormalModel::class);
});
