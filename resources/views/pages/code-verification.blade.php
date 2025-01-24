<?php

use Illuminate\Support\Arr;
use App\Enums\ProgrammingLanguage;
use App\Enums\ModelTool;
use App\Enums\LLM;
use App\Settings\CodeGeneratorSettings;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Locked;
use App\AI\LLama;
use App\AI\ChatGPT;
use App\GeneratedFormalModel;

new class extends \Livewire\Volt\Component {
    #[Validate('required|string')]
    public string $text = '';

    #[Locked]
    public string $result;

    protected ?CodeGeneratorSettings $settings = null;

    public function boot(): void
    {
        $this->settings = app(CodeGeneratorSettings::class);
    }

    public function send(): void
    {
        $coder = match ($this->settings->llm_code) {
            LLM::Llama->value => new LLama(),
            default => new ChatGPT()
        };

        $system_message = match ($this->settings->llm_code){
            LLM::Llama->value => "You are Llama, an expert programmer. Generate clean and secure code based on user requirements using the following programming language {$this->settings->programming_language}. You must provide only the code in appropriate code blocks, explanations aren't required. Format your response using markdown.",
            default => "You are an expert programmer. Generate clean and secure code based on user requirements using the following programming language {$this->settings->programming_language}. You must provide only the code in appropriate code blocks, explanations aren't required. Format your response using markdown."
        };

        $this->result = $coder
            ->systemMessage($system_message)
            ->send($this->text);


        GeneratedFormalModel::log(
            system_message: $system_message,
            requirement: $this->text,
            generatedFormalModel: $this->result,
        );

    }

}
?>


<x-card title="Formal Model Generator"
        subtitle="Generate a formal model of the source code using formal verification tools like NuSMV or PyModel.">

    <div class="p-2 rounded border">
        <div class="chat-message user-message">
            {{ $text }}
        </div>
        <div class="chat-message assistant-message">
            <p wire:stream="answer"> {{ $result }} </p>
        </div>
    </div>

    <x-form wire:submit="send" no-separator>

        <x-slot:actions>
            <x-button label="Send" class="btn-primary" type="submit" wire:loading.attr="disabled"
                      wire:keydown.ctrl.enter="send"/>
        </x-slot:actions>
    </x-form>
</x-card>

