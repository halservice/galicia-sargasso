<?php

use App\Actions\CodeValidationAction;
use App\Actions\ResetGeneratorsAction;
use App\Actions\OldCodeValidationAction;
use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;

//use App\Settings\CodeGeneratorSettings;
use App\Models\UserSetting;
use App\Traits\ExtractCodeTrait;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

new class extends \Livewire\Volt\Component {
    use ExtractCodeTrait;

    #[Locked]
    public string $result;

    public string $req;

//    protected ?CodeGeneratorSettings $settings = null;

    protected ?UserSetting $settings = null;
    public ?GeneratedCode $generatedCode = null;
    public ?GeneratedFormalModel $generatedFormal = null;
    public ?GeneratedValidatedCode $lastValidation = null;

    public function mount(): void
    {
        $this->lastValidation = GeneratedValidatedCode::where('user_id', auth()->id())
            ->latest()
            ->first();

        if ($this->lastValidation?->is_active === true) {
            $this->result = $this->lastValidation->validated_code;
        }


    }

    public function boot(): void
    {
//        $this->settings = app(CodeGeneratorSettings::class);
        $this->settings = UserSetting::where('user_id', auth()->id())->first();

        if ($this->settings->startFromGeneratedCode() &&
            ($formal = GeneratedFormalModel::where('user_id', auth()->id())
                ->latest()
                ->first())?->is_active) {
            $this->generatedFormal = $formal;
            $this->generatedCode = $formal->generatedCode;
        } else if (($code = GeneratedCode::where('user_id', auth()->id())
            ->latest()
            ->first())?->is_active) {
            $this->generatedCode = $code;
            $this->generatedFormal = $code->formalModel;
        }
    }

    public function send(): void
    {
        if ($this->lastValidation) {
            $this->lastValidation->is_active = false;
            $this->lastValidation->update();
        }
        $validated = app(CodeValidationAction::class)(
            $this->generatedCode,
            $this->generatedFormal
        );
        $this->result = $validated->validated_code;

        redirect()->route('code-validation');

    }

    public function clear(): void
    {
        app(ResetGeneratorsAction::class)();
        $this->reset('result');
        redirect()->route('code-validation');
    }
}
?>


<x-card title="Code Validation"
        subtitle="Automatically checks the generated code and refines it based on the errors detected.">

    @if($this->generatedCode?->is_active && $this->generatedFormal?->is_active && $this->lastValidation?->is_active)
        <x-form no-separator class="flex flex-col items-center justify-center">
            <h2 class="text-center font-bold text-2xl">Code validated.</h2>
            <x-slot:actions>
                <div class="flex justify-center w-full">
                    <x-button label="Start Again" class="btn-secondary" wire:loading.attr="disabled"
                              wire:click="clear"/>
                </div>
            </x-slot:actions>
        </x-form>
    @elseif($this->generatedCode?->is_active && $this->generatedFormal?->is_active)
        <x-form no-separator class="flex flex-col items-center justify-center">
            <h2 class="text-center font-bold text-2xl">Would you like to validate the code?</h2>
            <x-slot:actions>
                <div class="flex justify-center w-full">
                    <x-button
                        class="btn-secondary"
                        wire:click="send"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="send">Validate the code</span>
                        <span wire:loading wire:target="send" class="flex items-center">
                 <x-icon name="o-arrow-path" class="animate-spin h-4 w-4 mr-2"/>
                 Validating the code...
                </span>
                    </x-button>
                </div>
            </x-slot:actions>
        </x-form>
    @else
        <x-form>
            <h2 class="text-center font-bold text-2xl">Please, generate the code and the formal model first.</h2>
        </x-form>
    @endif


    {{--    @if(isset($req))--}}
    {{--        <div class="chat-message user-message">--}}
    {{--            <p wire:stream="req">{{ $req }}</p>--}}
    {{--        </div>--}}
    {{--    @endif--}}
    @if(isset($result))
        <div
            class="rounded-[10px] p-[15px] gap-[5px] w-fit break-words mr-auto mb-5 bg-[#3864fc] text-white mt-5 max-w-4xl">
            <code>
                <pre class="whitespace-pre-wrap">{{ $result }}</pre>
            </code>
        </div>
    @endif

</x-card>

