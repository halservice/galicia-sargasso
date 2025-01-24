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
use App\GeneratedCode;

new class extends \Livewire\Volt\Component {

    #[Locked]
    public string $result;

    public string $text;

    protected ?CodeGeneratorSettings $settings = null;
    protected ?GeneratedCode $generated_code = null;


    public function boot(): void
    {
        $id = session('last_generated_code_id');
        $this->settings = app(CodeGeneratorSettings::class);
        $this->generated_code = app(GeneratedCode::class)->find($id);
    }

    public function send(): void
    {
        $coder = match ($this->settings->llm_code) {
            LLM::Llama->value => new LLama(),
            default => new ChatGPT()
        };

        dd($this->generated_code);

        $system_message = match ($this->settings->llm_code){
            LLM::Llama->value => "You are Llama, an expert programmer. Generate clean and secure code based on user requirements using the following programming language {$this->settings->programming_language}. You must provide only the code in appropriate code blocks, explanations aren't required. Format your response using markdown.",
            default => "Generate a formal model in  {$this->settings->model_tool} for the following requirements:  {$this->generated_code->requirement} of the following code:{$this->generated_code->generated_code}"
        };

        $this->result = $coder
            ->systemMessage($system_message)
            ->send($this->generated_code->requirement);

        dd($this->generated_code->requirement);
        $text = $this->generated_code->requirement;

        GeneratedFormalModel::log(
            generated_code_id: $this->generated_code->id,
            system_message: $system_message,
            requirement: $this->generated_code->requirement,
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

