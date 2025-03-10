<?php

use App\Actions\ResetGeneratorsAction;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Arr;
use App\Enums\ProgrammingLanguage;
use App\Enums\LLM;
use App\Enums\ModelTool;
use App\Enums\Sequence;
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

    #[Validate('required|string')]
    public string $sequence;

    protected ?User $user = null;
    protected ?UserSetting $settings = null;
//    protected ?CodeGeneratorSettings $settings = null;

    public function boot(): void
    {
        $this->settings = UserSetting::where('user_id',auth()->id())->first();
//        $this->settings = app(CodeGeneratorSettings::class);
    }

    public function mount(): void
    {
        $this->language = $this->settings->programming_language;
        $this->model = $this->settings->model_tool;
        $this->llm_code = $this->settings->llm_code;
        $this->llm_formal = $this->settings->llm_formal;
        $this->llm_validation = $this->settings->llm_validation;
        $this->iteration = $this->settings->iteration;
        $this->sequence = $this->settings->sequence;
    }

    public function with(): array
    {
        return [
            'languages' => ProgrammingLanguage::options(),
            'models' => ModelTool::options(),
            'llms' => LLM::options(),
            'sequences' => \App\Enums\Sequence::options(),
        ];
    }

    public function save(): void
    {
        //imposto parametri definiti se sforo con i valori delle iterations
        if ($this->iteration < 1) {
            $this->iteration = 1;
        } elseif ($this->iteration > 5) {
            $this->iteration = 5;
        }

        $this->settings->programming_language = $this->language;
        $this->settings->model_tool = $this->model;
        $this->settings->llm_code = $this->llm_code;
        $this->settings->llm_formal = $this->llm_formal;
        $this->settings->llm_validation = $this->llm_validation;
        $this->settings->iteration = $this->iteration;
        $this->settings->sequence = $this->sequence;

        $this->settings->save();

        app(ResetGeneratorsAction::class)();
    }
} ?>


<x-card title="Customization Options"
        subtitle="Allow users to customize the maximum number of iterations for the code refinement." shadow separator>

    <x-form wire:submit="save" no-separator>
        <x-select
            class="w-80"
            label="Select the sequence of the process"
            wire:model="sequence"
            placeholder="Select a sequence..."
            :options="$sequences"
            option-value="value"
            option-label="value"
        />

        <x-select
            class="w-80"
            label="Select a programming language"
            wire:model="language"
            placeholder="Select a language..."
            :options="$languages"
            option-value="value"
            option-label="value"
        />

        <x-select
            class="w-80"
            label="Select a LLM for the code generation"
            wire:model="llm_code"
            placeholder="Select a LLM..."
            :options="$llms"
            option-value="value"
            option-label="value"
        />

        <x-select
            class="w-80"
            label="Select formal model tool"
            wire:model="model"
            placeholder="Select a model..."
            :options="$models"
            option-value="value"
            option-label="value"
        />

        <x-select
            class="w-80"
            label="Select a LLM for the formal model generation"
            wire:model="llm_formal"
            placeholder="Select a LLM..."
            :options="$llms"
            option-value="value"
            option-label="value"
        />

        <x-input
            class="w-80"
            label="Insert the number of iteration for the validation process (max 5)"
            wire:model="iteration"
            placeholder="Write a number between 1 and 5..."
        />

        <x-select
            class="w-80"
            label="Select a LLM for the validation"
            wire:model="llm_validation"
            placeholder="Select a LLM..."
            :options="$llms"
            option-value="value"
            option-label="value"
        />

        <x-slot:actions>
            <x-button label="Save" class="btn-primary" type="submit" wire:loading.attr="disabled"/>
        </x-slot:actions>
    </x-form>
</x-card>



