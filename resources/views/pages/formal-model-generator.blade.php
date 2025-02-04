<?php

use App\AI\ChatGPT;
use App\AI\LLama;
use App\Enums\LLM;
use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Settings\CodeGeneratorSettings;
use App\Traits\ExtractCodeTrait;
use Livewire\Attributes\{Locked, Computed, Url, Validate};

new class extends \Livewire\Volt\Component {
    use ExtractCodeTrait;

    #[Locked]
    public string $result;

    #[Validate('required|string')]
    public string $text = '';

    #[Url]
    public ?int $codeId = null;

    public ?GeneratedCode $generatedCode = null;
    protected ?CodeGeneratorSettings $settings = null;
//    protected ?GeneratedCode $generated_code = null;

    public function mount(): void
    {
        if ($lastFormalId = session('last_formal_model_id')) {
            $lastFormal = GeneratedFormalModel::find($lastFormalId);

            if ($lastFormal) {
//                $this->text = $lastFormal->requirement;
                $this->result = $lastFormal->generated_formal_model;
            }
        }
    }

    public function boot(): void
    {
//        $this->generatedCodeId = session('last_generated_code_id');
        $this->settings = app(CodeGeneratorSettings::class);

        if ($this->isFromGeneratedCode) {
            $this->generatedCode = GeneratedCode::findOrFail($this->codeId);
        }
//        $this->generated_code = app(GeneratedCode::class)->find($this->generatedCodeId);
    }

    #[Computed]
    public function isFromGeneratedCode(): bool
    {
        return (bool) $this->codeId;
    }

    public function send(): void
    {

//        $formalModel = new GeneratedFormalModel();
//
//        $formalModel->forceFill([])
//            //->save();
//        $formalModel->generatedCode()->associate($this->generatedCode);
//        $formalModel->push();

        $coder = match ($this->settings->llm_formal) {
            LLM::Llama->value => new LLama(),
            default => new ChatGPT()
        };

        if ($this->isFromGeneratedCode) {
            $system_message = "You are an expert in formal verification using {$this->settings->model_tool}. Generate a {$this->settings->model_tool} formal model based on the provided requirements by the user and a generated code. Include all necessary requirements to verify the system's properties. Always output the model in a code block with '{$this->settings->model_tool}' as the language specification. You must provide only the formal model, explanations aren't required.";
            $user_message = "Generate a formal model in {$this->settings->model_tool} for the following requirements: {$this->generatedCode->requirement} of the following code:{$this->generatedCode->generated_code}";

        }else{
            $system_message = "You are an expert in formal verification using {$this->settings->model_tool}. Generate a {$this->settings->model_tool} formal model based on the provided requirements by the user. Include all necessary requirements to verify the system's properties. Always output the model in a code block with '{$this->settings->model_tool}' as the language specification. You must provide only the formal model, explanations aren't required.";
            $user_message = $this->text;
        }

        $message = $coder->systemMessage($system_message, $user_message);
        $response = $coder->send($message);

        $code = $this->extractCodeFromResponse($response);

        if ($code != $response) {
            $this->result = $code;

            $test_case_message = "Given the generated formal model, could you generate a few test cases that the code should execute if written correctly?";
            $message = [
                ...$message,
                [
                    'role' => 'assistant',
                    'content' => $this->result,
                ], [
                    'role' => 'user',
                    'content' => $test_case_message
                ]
            ];
            $response = $coder->send($message);

            $formal = GeneratedFormalModel::log(
                generatedCodeId: $this->codeId ?? null,
                testCase: $response,
                systemMessage: $system_message,
                requirement: $this->generatedCode->requirement,
                generatedFormalModel: $this->result,
            );
            session(['last_formal_model_id' => $formal->id]);
        } else {
            $this->result = "Error in generating formal model.<br>Please try again or change the formal model.";
        }
    }

}
?>


<x-card title="Formal Model Generator"
        subtitle="Generate a formal model of the source code using formal verification tools like NuSMV or PyModel.">

    @if($this->isFromGeneratedCode())
        <x-form wire:submit="send" no-separator class="flex flex-col items-center justify-center">
            <h2 class="text-center font-bold text-2xl">Would you like to generate the formal
                    model?</h2>
                <x-slot:actions>
                    <div class="flex justify-center w-full">
                        <x-button label="Generate Formal Model"
                                  class="btn-secondary"
                                  type="submit" wire:loading.attr="disabled"
                                  wire:keydown.ctrl.enter="send"/>
                    </div>
                </x-slot:actions>
        </x-form>
    @else
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

{{--                <x-button label="Reset" class="btn-danger" wire:loading.attr="disabled"--}}
{{--                          wire:click="clear"/>--}}
            </x-slot:actions>
        </x-form>
    @endif



    @if(isset($result))
            <div class="mt-5 chat-message assistant-message">
                <code>
                    <pre>{{ $result }}</pre>
                </code>
            </div>

            @if($this->isFromGeneratedCode())
            <div class="mt-2 flex">
                <x-button label="Validate the code" class="btn-primary ml-auto"
                          :link="route('code-validation', $code?->id)"></x-button>
            </div>
            @else
                <div class="mt-2 flex">
                    <x-button label="Generate the code" class="btn-primary ml-auto"
                              :link="route('source-code-generator', $code?->id)"></x-button>
                </div>
            @endif
    @endif

</x-card>

