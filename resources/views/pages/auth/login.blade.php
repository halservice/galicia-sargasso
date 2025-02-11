<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;

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
        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();

            $this->redirectIntended(default: route('home', absolute: false), navigate: true);
        }

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

            <x-slot:actions>
                <x-button label="Login" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="login"/>
            </x-slot:actions>
        </x-form>

    </div>
</div>
