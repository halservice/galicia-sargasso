<?php

use Illuminate\Support\Arr;
use App\Enums\ProgrammingLanguage;
use App\Enums\LLM;
use App\Enums\LLMFormal;
use App\Enums\ModelTool;
use App\Settings\CodeGeneratorSettings;
use Livewire\Attributes\Validate;

new class extends \Livewire\Volt\Component {
    #[Validate('required|string')]
    public string $language;

    #[Validate('required|string')]
    public string $model;

    #[Validate('required|string')]
    public string $llm_code;

    #[Validate('required|string')]
    public string $llm_formal;

    #[Validate('required|string')]
    public string $llm_validation;


    protected ?CodeGeneratorSettings $settings = null;

    public function boot(): void
    {
        $this->settings = app(CodeGeneratorSettings::class);
    }

    public function mount(): void
    {
        $this->language = $this->settings->programming_language;
        $this->model = $this->settings->model_tool;
        $this->llm_code = $this->settings->llm_code;
        $this->llm_formal = $this->settings->llm_formal;
        $this->llm_validation = $this->settings->llm_validation;
    }

    public function with(): array
    {
        return [
            'languages' => ProgrammingLanguage::options(),
            'models' => ModelTool::options(),
            'llms' => LLM::options(),
//            'llmf' => LLMFormal::options(),
        ];
    }

    public function save(): void
    {
        $this->settings->programming_language = $this->language;
        $this->settings->model_tool = $this->model;
        $this->settings->llm_code = $this->llm_code;
        $this->settings->llm_formal = $this->llm_formal;
        $this->settings->llm_validation = $this->llm_validation;

        $this->settings->save();
    }
} ?>


<x-card title="Customization Options"
        subtitle="Allow users to customize the maximum number of iterations for the code refinement." shadow separator>

    <x-form wire:submit="save" no-separator>
        <x-select
            label="Select a programming language"
            wire:model="language"
            placeholder="Select a language..."
            :options="$languages"
            option-value="value"
            option-label="text"
        />

        <x-select
            label="Select a LLM for the code generation"
            wire:model="llm_code"
            placeholder="Select a LLM..."
            :options="$llms"
            option-value="value"
            option-label="text"
        />

        <x-select
            label="Select formal model tool"
            wire:model="model"
            placeholder="Select a model..."
            :options="$models"
            option-value="value"
            option-label="text"
        />

        <x-select
            label="Select a LLM for the formal model generation"
            wire:model="llm_formal"
            placeholder="Select a LLM..."
            :options="$llms"
            option-value="value"
            option-label="text"
        />

        <x-select
            label="Select a LLM for the validation"
            wire:model="llm_validation"
            placeholder="Select a LLM..."
            :options="$llms"
            option-value="value"
            option-label="text"
        />

        <x-slot:actions>
            <x-button label="Save" class="btn-primary" type="submit" wire:loading.attr="disabled"/>
        </x-slot:actions>
    </x-form>
</x-card>


{{--<div>--}}
{{--    <x-header--}}
{{--        description='Allow users to customize the maximum number of iterations for the code refinement.'>--}}
{{--        Customization Options--}}
{{--    </x-header>--}}

{{--    <div>--}}
{{--        <h1>{{ $count }}</h1>--}}
{{--        <button wire:click="increment">+</button>--}}
{{--    </div>--}}

{{--    <div class="bg-white w-[800px] w-min[400px] text-align-left py-3 px-3 rounded-[10px]">--}}
{{--        <x-select-info id="programming-language-tool-select" label="Select a programming language:" placeholder="Select a language.."--}}
{{--            :options="$languages"--}}
{{--        />--}}

{{--        <x-select-info id="model-tool-select" label="Select formal model tool:" placeholder="Select a tool..."--}}
{{--                       :options="$model"--}}
{{--        />--}}

{{--        <x-select-info id="number-iteration-tool-select" label="Select the number of iterations:" placeholder="Select a number"--}}
{{--                       :options="[--}}
{{--            '1' => '1',--}}
{{--            '2' => '2',--}}
{{--            '3' => '3',--}}
{{--            '4' => '4',--}}
{{--            '5' => '5',--}}
{{--        ]"--}}
{{--        />--}}

{{--        <x-select-info id="llm-code-tool-select" label="Select the LLM for generating the code:" placeholder="Select a LLM..."--}}
{{--                       :options="[--}}
{{--            'chatgpt' => 'ChatGPT',--}}
{{--            'llama'=> 'Llama-3.1',--}}
{{--        ]"--}}
{{--        />--}}

{{--        <x-select-info id="llm-formal-tool-select" label="Select the LLM for generating the formal model:" placeholder="Select a LLM..."--}}
{{--                       :options="[--}}
{{--            'chatgpt' => 'ChatGPT',--}}
{{--            'llama'=> 'Llama-3.1',--}}
{{--        ]"--}}
{{--        />--}}

{{--        <x-select-info id="llm-validation-tool-select" label="Select the LLM for validating the code:" placeholder="Select a LLM..."--}}
{{--                       :options="[--}}
{{--            'chatgpt' => 'ChatGPT',--}}
{{--            'llama'=> 'Llama-3.1',--}}
{{--        ]"--}}
{{--        />--}}

{{--    </div>--}}
{{--</div>--}}
