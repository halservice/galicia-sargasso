<?php

test('it belongs to code', function () {
    $gfm = \App\Models\UserSetting::factory()
        ->for(\App\Models\User::factory())
        ->create();

    expect($gfm->user)->toBeInstanceOf(\App\Models\User::class);
});
