<?php

use Illuminate\Support\Arr;
use App\Enums\ProgrammingLanguage;
use App\Enums\LLM;
use App\Enums\LLMFormal;
use App\Enums\ModelTool;
use App\Settings\CodeGeneratorSettings;
use Livewire\Attributes\Validate;
use App\GeneratedCode;
use App\GeneratedFormalModel;
use App\GeneratedValidatedCode;

new class extends \Livewire\Volt\Component {

    public string $req;
    public string $first_code;
    public string $formal_model;

    public bool $showDrawer = false;
    public bool $showDrawer2 = false;

    protected ?CodeGeneratorSettings $settings = null;
    protected ?GeneratedCode $generatedCode = null;
    protected ?GeneratedFormalModel $generatedFormal = null;
    protected ?GeneratedValidatedCode $generatedValidation = null;

    public function boot(): void
    {
        if (session('feedback_validation_id')) {
            $this->generatedValidation = session('feedback_validation_id');
            $this->generatedValidation = app(GeneratedValidatedCode::class)->find($this->validationId);

            $this->generated_code = app(GeneratedCode::class)->find($this->generatedValidation->generated_code_id);
            $this->req = $this->generated_code->requirement;
            $this->first_code = $this->generated_code->generated_code;

            $this->generated_formal = app(GeneratedFormalModel::class)->find($this->generatedValidation->generated_formal_id);
            $this->formal_model = $this->generated_formal->generated_formal_model;

            $this->settings = app(CodeGeneratorSettings::class);
        }

    }

    public function mount(): void
    {
//        $this->request = $this->settings->programming_language;
    }

}
?>


<x-card title="Feedback"
        subtitle="Provide detailed feedback on the correctness and compliance of the generated code." shadow separator>

<x-form>
    @if(session('feedback_validation_id'))
    <h1 class="text-primary text-2xl font-bold">Summarization:</h1>
    <p>The user request was the following:</p>
    <i><b>
       {{ $req }}
        </b></i>
    <x-drawer wire:model="showDrawer" class="w-11/12 lg:w-1/3" right>
        <div><pre><code>{{ $first_code }}</code></pre><br></div>
        <x-button label="Close" @click="$wire.showDrawer = false" />
    </x-drawer>
    <x-drawer wire:model="showDrawer2" class="w-11/12 lg:w-1/3" right>
        <div><pre><code>{{ $formal_model }}</code></pre><br></div>
        <x-button label="Close" @click="$wire.showDrawer2 = false" />
    </x-drawer>
    <div class="flex justify-left w-full gap-5">
        <x-button label="Show First Generated Code" wire:click="$toggle('showDrawer')" />
        <x-button label="Show Formal Model" wire:click="$toggle('showDrawer2')" />
    </div>
    @else
        <p>You must progress with the process first.</p>
    @endif



</x-form>

</x-card>
