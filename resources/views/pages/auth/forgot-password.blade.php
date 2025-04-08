<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Validate;

// @phpstan-ignore-next-line
new class extends \Livewire\Volt\Component {
    #[Validate('required|email')]
    public string $email = "";

    public function sendResetLink()
    {
        $this->validate();

        session()->forget(['success']);

        $user = User::where('email', $this->email)->first();
        if (!$user) {
        $this->addError('email','The provided credentials do not match our records.');
            return;
        }

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('success', 'Password reset link sent to your email.');
        } else {
            $this->addError('email','Please try again later.');
        }
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

        @if(session('success'))
            <div class="alert alert-success mb-4">
                {{ session('success') }}
            </div>
        @endif

        <x-form wire:submit="sendResetLink" no-separator>
            <x-input label="Email" wire:model="email" icon="o-user" inline/>

            <x-slot:actions>
                <x-button label="Send Reset Link" type="submit" icon="o-paper-airplane" class="btn-primary"/>
            </x-slot:actions>
        </x-form>

        <p class="text-xs text-right mt-3">
            <a class="text-primary underline" href="/login">Back to Login</a>
        </p>
    </div>
</div>
