<?php

use Livewire\Volt\Volt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;


Volt::route('/','home')
    ->name('home')
    ->middleware('auth');

Volt::route('/source-code-generator', 'source-code-generator')
    ->name('source-code-generator')
    ->middleware('auth');

Volt::route('/formal-model-generator', 'formal-model-generator')
    ->name('formal-model-generator')
    ->middleware('auth');

Volt::route('/code-validation', 'code-validation')
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

Volt::route('/statistics', 'statistics')
    ->name('statistics')
    ->middleware('auth');

Volt::route('/login', 'auth.login')
    ->name('login')
    ->middleware('guest');

Volt::route('/register', 'auth.register')
    ->name('register')
    ->middleware('guest');

Volt::route('/forgot-password', 'auth.forgot-password')
    ->name('forgot-password')
    ->middleware('guest');


Volt::route('/reset-password/{token}', 'auth.reset-password')
    ->name('password.reset')
    ->middleware('guest');

Route::post('/logout', function (){
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login');
    })->name('logout');


