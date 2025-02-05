<?php

it('it deactivates models', function () {
    \App\Models\GeneratedCode::factory()->create(['is_active' => true]);
    \App\Models\GeneratedFormalModel::factory()->create(['is_active' => true]);
    \App\Models\GeneratedValidatedCode::factory()->create(['is_active' => true]);

    \App\Models\GeneratedCode::reset();
    \App\Models\GeneratedFormalModel::reset();
    \App\Models\GeneratedValidatedCode::reset();

    \Pest\Laravel\assertDatabaseHas('generated_codes', [
        'is_active' => false,
    ]);
    \Pest\Laravel\assertDatabaseHas('generated_formal_models', [
        'is_active' => false,
    ]);
    \Pest\Laravel\assertDatabaseHas('generated_validated_codes', [
        'is_active' => false,
    ]);


});
