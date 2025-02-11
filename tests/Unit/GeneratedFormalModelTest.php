<?php

test('it belongs to generated code', function () {
    $gfm = \App\Models\GeneratedFormalModel::factory()
        ->for(\App\Models\GeneratedCode::factory())
        ->create();

    expect($gfm->generatedCode)->toBeInstanceOf(\App\Models\GeneratedCode::class);
});

test('it has validated code', function () {
    $gfm = \App\Models\GeneratedFormalModel::factory()
        ->has(\App\Models\GeneratedValidatedCode::factory(),'validated')
        ->create();

    expect($gfm->validated)->toBeInstanceOf(\App\Models\GeneratedValidatedCode::class);
});

test('it belongs to user', function () {
    $gc = \App\Models\GeneratedCode::factory()
        ->for(\App\Models\User::factory(), 'user')
        ->create();

    expect($gc->user)->toBeInstanceOf(\App\Models\User::class);
});
