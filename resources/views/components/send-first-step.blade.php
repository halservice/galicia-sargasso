{{--        If the process starts from the code generation then show text input space template--}}
<x-form wire:submit="sendWithCheckbox" no-separator>
    <x-textarea
        wire:model="text"
        placeholder="Type your natural language input here..."
        rows="4"
        wire:keydown.enter="sendWithCheckbox"
        inline
    />
    <x-slot:actions>
        <div class="flex flex-col items-end gap-4 w-full">
            <div class="flex gap-4 w-full justify-end">
                <x-button class="btn-primary" type="button" wire:loading.attr="disabled"
                          wire:click="sendWithCheckbox">
                    <span wire:loading.remove wire:target="sendWithCheckbox">Send</span>
                    <span wire:loading wire:target="sendWithCheckbox" class="flex items-center">
                         <x-icon name="o-arrow-path" class="animate-spin mr-2"/>
                        Sending...
                        </span>
                </x-button>
                <x-button label="Reset" class="btn-secondary" wire:loading.attr="disabled"
                          wire:click="clear"/>
            </div>

            <div class="flex items-center space-x-2">
                <input type="checkbox" wire:model="skipCheck"/>
                <span>Disable prompt review</span>
            </div>
        </div>

    </x-slot:actions>
</x-form>
