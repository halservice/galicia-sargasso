<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Enums\ProgrammingLanguage;
use App\Enums\ModelTool;
use App\Enums\LLM;
use App\Traits\ExtractCodeTrait;
use App\Settings\CodeGeneratorSettings;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Locked;
use App\AI\LLama;
use App\AI\ChatGPT;
use App\GeneratedCode;

new class extends \Livewire\Volt\Component {
    use ExtractCodeTrait;

    #[Validate('required|string')]
    public string $text;

    #[Locked]
    public string $result;

    public string $req;

    protected ?CodeGeneratorSettings $settings = null;

    public function mount(): void
    {
        if ($lastCodeId = session('last_generated_code_id')) {
            $lastCode = GeneratedCode::find($lastCodeId);
            if ($lastCode) {
                $this->req = $lastCode->requirement;
                $this->result = $lastCode->generated_code;
            }
        }
    }

    public function boot(): void
    {
        $this->settings = app(CodeGeneratorSettings::class);
    }

    public function clearSession(): void
    {
        session()->forget('last_generated_code_id');
        session()->forget('last_formal_model_id');
        session()->forget('last_validation_id');
    }

    public function send(): void
    {
        session()->forget('last_formal_model_id');
        session()->forget('last_validation_id');

        $this->req = $this->text;
        $this->text = "";


        $coder = match ($this->settings->llm_code) {
            LLM::Llama->value => new LLama(),
            default => new ChatGPT()
        };

        $system_message = "You are an expert programmer. Generate clean and secure code based on user requirements using the following programming language {$this->settings->programming_language}. You must provide only the code in appropriate code blocks, explanations aren't required. Format your response using markdown.";

        $message = $coder->systemMessage($system_message, $this->req);
        $response = $coder->send($message);

        $code = $this->extractCodeFromResponse($response);
        if ($code != $response) {
            $this->result = $code;
            $codeId = GeneratedCode::log(
                system_message: $system_message,
                requirement: $this->req,
                generatedCode: $this->result,
            );
            session(['last_generated_code_id' => $codeId]);
        } else {
            $this->result = "Error in generating the code.<br>Please try again.";
        }

    }

}
?>

<x-card title="Source Code Generator"
        subtitle="Input functional requirements in natural language through a user-friendly interface.">

    @if(!session('last_generated_code_id'))
        <x-form wire:submit="send" no-separator>
            <x-textarea
                wire:model="text"
                placeholder="Type your natural language input here..."
                rows="4"
                wire:keydown.enter="send"
                inline
            />
            <x-slot:actions>
                <x-button label="Send" class="btn-secondary" type="submit" wire:loading.attr="disabled"
                          wire:keydown.ctrl.enter="send"/>
            </x-slot:actions>
        </x-form>
    @else
        <x-form>
            <h2 class="text-center font-bold text-2xl ">Source Code Generated.</h2>
            <p class="text-center">Please, continue with the generation of the formal model or generate a
                new code.</p>
            <div class="flex justify-center w-full">
                <x-button
                    label="Generate New Code"
                    class="btn-secondary"
                    wire:click="clearSession"
                    wire:loading.attr="disabled"/>
            </div>
        </x-form>
    @endif


    <div class="py-3 px-3 rounded">
        @if(isset($req))
            <div class="chat-message user-message">
                {{ $req }}
            </div>
        @endif
        @if(isset($result))
            <div class="chat-message assistant-message">
                <pre><code>{{ $result }}</code></pre>
            </div>
        @endif
    </div>



</x-card>

