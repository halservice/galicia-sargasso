<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;

// @phpstan-ignore-next-line
new class extends \Livewire\Volt\Component {
    #[Validate('required|string|email')]
    public string $email = "";

    #[Validate('required|string')]
    public string $password = "";

    /**
     *
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $this->validate();

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
            $this->redirectIntended(default: route('home', absolute: false), navigate: true);
        }

        $this->addError('email', 'The provided credentials do not match our records.');

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
}

?>

<div class="flex items-center justify-center min-h-screen">
    <div class="md:w-96 mx-auto">
        <img
            src="{{ asset('images/GALICIA_LOGO.png') }}"
            alt="Galicia"
            class="mx-auto my-auto max-w-[400px] mb-5"
        >

        <x-form wire:submit="authenticate" no-separator>
            <x-input label="Email" wire:model="email" icon="o-user" inline/>
            <x-input label="Password" wire:model="password" type="password" icon="o-key" inline/>
            <a class="text-xs text-center underline" href="/forgot-password">
                Forgot your password?
            </a>

            <x-slot:actions>
                <x-button label="Login" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="login"/>
            </x-slot:actions>

        </x-form>
        <p class="text-xs text-right mt-3">
            Don't have an account? <a class="text-primary underline" href="/register">Sign up!</a>
        </p>

    </div>
</div>
