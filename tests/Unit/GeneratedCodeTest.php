<?php

test('it belongs to formal model', function () {
    $gc = \App\Models\GeneratedCode::factory()
        ->for(\App\Models\GeneratedFormalModel::factory(), 'formalModel')
        ->create();

    expect($gc->formalModel)->toBeInstanceOf(\App\Models\GeneratedFormalModel::class);
});

//test('it has one validated code', function () {
//    $gc = \App\Models\GeneratedCode::factory()
//        ->has(\App\Models\GeneratedValidatedCode::factory(), 'validatedCode')
//        ->create();
//
//    expect($gc->validatedCode)->toBeInstanceOf(\App\Models\GeneratedValidatedCode::class);
//});

test('it has validated code', function () {
    $gc = \App\Models\GeneratedCode::factory()
        ->has(\App\Models\GeneratedValidatedCode::factory(),'validated')
        ->create();

    expect($gc->validated)->toBeInstanceOf(\App\Models\GeneratedValidatedCode::class);
});

