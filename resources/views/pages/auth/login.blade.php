<div class="flex items-center justify-center min-h-screen">
    <div class="md:w-96 mx-auto">
        <img
            src="{{ asset('images/GALICIA_LOGO.png') }}"
            alt="Galicia"
            class="mx-auto my-auto max-w-[400px]"
        >

        <x-form wire:submit="login" no-separator>
            <x-input label="Username" wire:model="username" icon="o-user" inline />
            <x-input label="Password" wire:model="password" type="password" icon="o-key" inline />

            <x-slot:actions>
                <x-button label="Login" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="login" />
            </x-slot:actions>
        </x-form>

    </div>
</div>
