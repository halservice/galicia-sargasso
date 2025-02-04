<?php

use App\AI\ChatGPT;
use App\AI\LLama;
use App\Enums\LLM;
use App\Models\GeneratedCode;
use App\Settings\CodeGeneratorSettings;
use App\Traits\ExtractCodeTrait;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;

new class extends \Livewire\Volt\Component {
    use ExtractCodeTrait;

    #[Validate('required|string')]
    public string $text = '';

    #[Locked]
    public ?string $result = null;

    protected ?CodeGeneratorSettings $settings = null;

    public ?GeneratedCode $code = null;

    public function mount(): void
    {
        if ($lastCodeId = session('last_generated_code_id')) {
            $lastCode = GeneratedCode::find($lastCodeId);
            if ($lastCode) {
                $this->text = $lastCode->requirement;
                $this->result = $lastCode->generated_code;
            }
        }
    }

    public function boot(): void
    {
        $this->settings = app(CodeGeneratorSettings::class);
    }

    public function send(): void
    {
        session()->forget('last_formal_model_id');

        $coder = match ($this->settings->llm_code) {
            LLM::Llama->value => new LLama(),
            default => new ChatGPT()
        };

        $systemMessage = "You are an expert programmer. Generate clean and secure code based on user requirements using the following programming language {$this->settings->programming_language}. You must provide only the code in appropriate code blocks, explanations aren't required. Format your response using markdown.";
        $message = $coder->systemMessage($systemMessage, $this->text);
        $response = $coder->send($message);

        $code = $this->extractCodeFromResponse($response);

        if ($code !== $response) {
            $this->result = $code;

            $this->code = GeneratedCode::log(
                generatedFormalId: $this->FormalId ?? null,
                systemMessage: $systemMessage,
                requirement: $this->text,
                generatedCode: $this->result,
            );

            session(['last_generated_code_id' => $this->code->id]);
        } else {
            $this->result = "Error in generating the code.<br>Please try again.";
        }

    }

    public function clear(): void
    {
        session()->forget('last_formal_model_id');
        session()->forget('last_generated_code_id');
        $this->reset('text', 'result');
    }
}
?>

<x-card title="Source Code Generator"
        subtitle="Input functional requirements in natural language through a user-friendly interface.">

    {{--    @if(!session('last_generated_code_id'))--}}
    <x-form wire:submit="send" no-separator>
        <x-textarea
            wire:model="text"
            placeholder="Type your natural language input here..."
            rows="4"
            wire:keydown.enter="send"
            inline
        />
        <x-slot:actions>
            <x-button label="Send" class="btn-primary" type="submit" wire:loading.attr="disabled"
                      wire:keydown.ctrl.enter="send"/>

            <x-button label="Reset" class="btn-danger" wire:loading.attr="disabled"
                      wire:click="clear"/>
        </x-slot:actions>
    </x-form>
    {{--    @else--}}
    {{--        <x-form>--}}
    {{--            <h2 class="text-center font-bold text-2xl ">Source Code Generated.</h2>--}}
    {{--            <p class="text-center">Please, continue with the generation of the formal model or generate a--}}
    {{--                new code.</p>--}}
    {{--            <div class="flex justify-center w-full">--}}
    {{--                <x-button--}}
    {{--                    label="Generate New Code"--}}
    {{--                    class="btn-secondary"--}}
    {{--                    wire:click="clearSession"--}}
    {{--                    wire:loading.attr="disabled"/>--}}
    {{--            </div>--}}
    {{--        </x-form>--}}
    {{--    @endif--}}


    @if(isset($result))
        <div class="chat-message assistant-message">
            <code>
                <pre>{{ $result }}</pre>
            </code>
        </div>

        <div class="mt-2 flex">
            <x-button label="Generate the formal model" class="btn-primary ml-auto"
                      :link="route('formal-model-generator', $code?->id)"></x-button>
        </div>
    @endif

</x-card>

