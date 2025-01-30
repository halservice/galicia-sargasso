<?php

use App\Traits\ExtractCodeTrait;
use Illuminate\Support\Arr;
use App\Enums\ProgrammingLanguage;
use App\Enums\ModelTool;
use App\Enums\LLM;
use App\Settings\CodeGeneratorSettings;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Locked;
use App\AI\LLama;
use App\AI\ChatGPT;
use App\GeneratedValidatedCode;
use App\GeneratedFormalModel;
use App\GeneratedCode;

new class extends \Livewire\Volt\Component {
    use ExtractCodeTrait;

    #[Locked]
    public string $result;

    #[Locked]
    public string $req;

    protected ?CodeGeneratorSettings $settings = null;
    protected ?GeneratedCode $generated_code = null;
    protected ?GeneratedFormalModel $generated_formal = null;

    public function mount(): void
    {
        if ($lastValidationId = session('last_validation_id')) {
            $lastValidation = GeneratedValidatedCode::find($lastValidationId);
            if ($lastValidation) {
                $this->req = "Validating the code...";
                $this->result = $lastValidation->generated_validated_code;
            }
        }
    }

    public function boot(): void
    {
        $this->generatedCodeId = session('last_generated_code_id');
        $this->generated_code = app(GeneratedCode::class)->find($this->generatedCodeId);
        $this->generatedFormalId = session('last_formal_model_id');
        $this->generated_formal = app(GeneratedFormalModel::class)->find($this->generatedFormalId);
        $this->settings = app(CodeGeneratorSettings::class);
    }

    public function checkChanges(string $response): bool
    {
        if (preg_match('/Number of changes made:\s*(\d+)/i', $response, $matches)) {
            $number = (int)trim($matches[1]);
            if ($number === 0)
                return true;
        }
        return false;
    }

    public function send(): void
    {
        session()->forget('feedback_validation_id');

        $iterations = $this->settings->iteration;

        $coder = match ($this->settings->llm_code) {
            LLM::Llama->value => new LLama(),
            default => new ChatGPT()
        };

        $system_message = "Your job is to validate a source code given the {$this->settings->model_tool} formal model, you must show a better code following the specification of the formal model. First you have to generate the new validated code after you should briefly summon the changes you have done in '### Changes Made:'. Lastly you must print '### Number of changes made:' and specify an integer that could be 0 if there are no changes.";
        $user_message = "Validate this code {$this->generated_code->generated_code} following the formal model {$this->generated_formal->generated_formal_model}";

        $currentCode = $this->generated_code->generated_code;
        $message = $coder->systemMessage($system_message, $user_message);
        $messages = $message;
        $flag = false;

        for ($i = 1; $i <= $iterations && $flag === false; $i++) {
            $this->req = "Validating the code... Iteration: $i/$iterations";
            $this->stream(to: 'req', content: $this->req);

            $response = $coder->send($message);

            $messages[] = [
                'role' => 'assistant',
                'content' => $response,
            ];

            $flag = $this->checkChanges($response);
            if ($flag === false && $i + 1 <= $iterations) {
                $currentCode = $this->extractCodeFromResponse($response);
                $messages[] = [
                    'role' => 'user',
                    'content' => "Here is the updated code after iteration $i: $currentCode. Please, validate the code following the formal model {$this->generated_formal->generated_formal_model}."
                ];
                $message = [
                    [
                    'role' => 'system',
                    'content' => $system_message,
                    ], [
                    'role' => 'user',
                    'content' => end($messages)['content'],
                    ]
                ];

            }

        }

        $this->result= $currentCode;

        $check_system_message = "Your job is to check if a few test, generated from a formal model, are resolved correctly in the code. I know it's not possible to execute them, but try to understand if they could pass or not.";
        $check_user_message = "Here is the code $currentCode and here are the test {$this->generated_formal->test_case}.";
        $check_test = $coder->systemMessage($check_system_message, $check_user_message);
        $check_test = $coder->send($check_test);

        $validatedId = GeneratedValidatedCode::log(
            generated_code_id: $this->generated_code->id,
            generated_formal_id: $this->generated_formal->id,
            test_result: $check_test,
            validationProcess: $messages,
            system_message: $system_message,
            generatedValidatedCode: $currentCode,
        );

        session(['last_validation_id' => $validatedId]);
        session(['feedback_validation_id' => $validatedId]);
        session()->forget('last_formal_model_id');
        session()->forget('last_generated_code_id');
    }

}
?>


<x-card title="Code Validation"
        subtitle="Automatically checks the generated code against the formal model and refines it based on the errors detected.">

    @if(session('last_generated_code_id') && session('last_formal_model_id'))
        <x-form no-separator class="flex flex-col items-center justify-center">
            <h2 class="text-center font-bold text-2xl text-gray-400">Would you like to validate the code?</h2>
            <div class="flex justify-center w-full">
                <x-button
                    label="Validate the code"
                    class="btn-secondary"
                    wire:click="send"
                    wire:loading.attr="disabled"/>
            </div>
        </x-form>
    @else
        <x-form>
            <p class="text-center text-gray-300">Please, generate the code and the formal model first.</p>
        </x-form>
    @endif


    <div class="p-2 rounded">
        @if(isset($req))
            <div class="chat-message user-message">
                <p wire:stream="req">{{ $req }}</p>
            </div>
        @endif
        @if(isset($result))
            <div class="chat-message assistant-message">
                <pre><code>{{ $result }} </code></pre>
            </div>
        @endif
    </div>


</x-card>

