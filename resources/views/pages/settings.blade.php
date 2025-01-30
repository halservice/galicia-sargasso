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

    #[Validate('required|int')]
    public int $iteration;

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
        $this->iteration = $this->settings->iteration;
    }

    public function with(): array
    {
        return [
            'languages' => ProgrammingLanguage::options(),
            'models' => ModelTool::options(),
            'llms' => LLM::options(),
        ];
    }

    public function save(): void
    {
        $this->settings->programming_language = $this->language;
        $this->settings->model_tool = $this->model;
        $this->settings->llm_code = $this->llm_code;
        $this->settings->llm_formal = $this->llm_formal;
        $this->settings->llm_validation = $this->llm_validation;
        $this->settings->iteration = $this->iteration;

        $this->settings->save();
    }
} ?>


<x-card title="Customization Options"
        subtitle="Allow users to customize the maximum number of iterations for the code refinement." shadow separator>

{{--    <input wire:model.live.debounce.500ms="test" />--}}

{{--    {{ $test }}--}}

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

        <x-input
            label="Insert the number of iteration for the validation process"
            wire:model="iteration"
            placeholder="Write a number..."
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



