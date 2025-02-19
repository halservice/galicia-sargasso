<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;

new class extends \Livewire\Volt\Component {
    #[Validate('required|string|email')]
    public string $email = "";

    #[Validate('required|string|min:8|confirmed')]
    public string $password = "";

    #[Validate('required|string')]
    public string $username = "";

    #[Validate('required|string')]
    public string $password_confirmation = "";

    /**
     *
     * Handle an authentication attempt.
     */
    public function mount()
    {
        // It is logged in
        if (auth()->user()) {
            return redirect('/');
        }
    }

    public function register()
    {
        $data = $this->validate();
        $user = User::where('email', $this->email)->first();
        if ($user) {
            $this->addError('email', 'The email address is already registered.');
            return;
        }

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        auth()->login($user);
        request()->session()->regenerate();

        return $this->redirect('/');
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

        <x-form wire:submit="register" no-separator>
            <x-input label="Name" wire:model="username" icon="o-user" inline/>
            <x-input label="E-mail" wire:model="email" icon="o-envelope" inline/>
            <x-input label="Password" wire:model="password" type="password" icon="o-key" inline/>
            <x-input label="Confirm Password" wire:model="password_confirmation" type="password" icon="o-key" inline/>

            <p class="text-xs text-center">
                Already registered? <a class="text-primary underline" href="/login">Sign in!</a>
            </p>
            <x-slot:actions>
                <x-button label="Register" type="submit" icon="o-paper-airplane" class="btn-primary"
                          spinner="register"/>
            </x-slot:actions>
        </x-form>

    </div>
</div>
