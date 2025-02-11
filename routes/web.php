<?php

use Livewire\Volt\Volt;

Volt::route('/','home')
    ->name('home')
    ->middleware('auth');

Volt::route('/source-code-generator/', 'source-code-generator')
    ->name('source-code-generator')
    ->middleware('auth');

Volt::route('/formal-model-generator/', 'formal-model-generator')
    ->name('formal-model-generator')
    ->middleware('auth');

Volt::route('/code-validation/', 'code-validation')
    ->name('code-validation')
    ->middleware('auth');

Volt::route('/feedback', 'feedback')
    ->name('feedback')
    ->middleware('auth');

Volt::route('/settings', 'settings')
    ->name('settings')
    ->middleware('auth');

Volt::route('/logs', 'logs')
    ->name('logs')
    ->middleware('auth');

Volt::route('/login', 'auth.login')
    ->name('login');

Route::get('/logout', function (){
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
    });
