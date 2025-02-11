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

test('it belongs to user', function () {
    $gc = \App\Models\GeneratedCode::factory()
        ->for(\App\Models\User::factory(), 'user')
        ->create();

    expect($gc->user)->toBeInstanceOf(\App\Models\User::class);
});
