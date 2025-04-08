<?php

use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;

// @phpstan-ignore-next-line
new class extends \Livewire\Volt\Component {
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $token = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    #[Validate('required|string')]
    public string $password_confirmation = '';

    public function mount(Request $request, string $token): void
    {
        $this->token = $token;
        $this->email = $request->query('email', '');
    }

    public function resetPassword(): void
    {
        $this->validate();

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            $this->redirectRoute('login');
        } else {
            $this->addError('email', __($status));
        }
    }
};
?>
<div>
    <div class="flex items-center justify-center min-h-screen">
        <div class="md:w-96 mx-auto">
            <img
                src="{{ asset('images/GALICIA_LOGO.png') }}"
                alt="Galicia"
                class="mx-auto my-auto max-w-[400px] mb-5"
            >

            <x-form wire:submit="resetPassword" no-separator>
                <x-input label="Email" wire:model="email" type="email" icon="o-envelope" inline/>
                <x-input label="New password" wire:model="password" type="password" icon="o-key" inline/>
                <x-input label="Confirm new password" wire:model="password_confirmation" type="password" icon="o-key"
                         inline/>

                <x-slot:actions>
                    <x-button label="Reset" type="submit" class="btn-primary" spinner="resetPassword"/>
                </x-slot:actions>
            </x-form>

        </div>
    </div>
</div>
