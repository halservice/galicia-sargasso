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
use App\GeneratedFormalModel;
use App\GeneratedCode;

new class extends \Livewire\Volt\Component {
    use ExtractCodeTrait;

    #[Locked]
    public string $result;

    public string $req;

    protected ?CodeGeneratorSettings $settings = null;
    protected ?GeneratedCode $generated_code = null;

    public function mount(): void
    {
        if ($lastFormalId = session('last_formal_model_id')) {
            $lastFormal = GeneratedFormalModel::find($lastFormalId);
            if ($lastFormal) {
                $this->req = $lastFormal->requirement;
                $this->result = $lastFormal->generated_formal_model;
            }
        }
    }

    public function boot(): void
    {
        $this->generatedCodeId = session('last_generated_code_id');
        $this->settings = app(CodeGeneratorSettings::class);
        $this->generated_code = app(GeneratedCode::class)->find($this->generatedCodeId);
    }

    public function send(): void
    {
        session()->forget('last_validation_id');

        $this->req = $this->generated_code->requirement;

        $coder = match ($this->settings->llm_formal) {
            LLM::Llama->value => new LLama(),
            default => new ChatGPT()
        };

        $system_message = "You are an expert in formal verification using {$this->settings->model_tool}. Generate a {$this->settings->model_tool} formal model based on the provided requirements by the user and a generated code. Include all necessary requirements to verify the system's properties. Always output the model in a code block with '{$this->settings->model_tool}' as the language specification. You must provide only the formal model, explanations aren't required.";
        $user_message =  "Generate a formal model in {$this->settings->model_tool} for the following requirements: {$this->generated_code->requirement} of the following code:{$this->generated_code->generated_code}";

        $message = $coder->systemMessage($system_message, $user_message);
        $response = $coder->send($message);
        $code = $this->extractCodeFromResponse($response);

        if ($code != $response){
            $this->result = $code;

            $test_case_message = "Given the generated formal model, could you generate a few test cases that the code should execute if written correctly?";
            $message=[
                ...$message,
                [
                    'role'=>'assistant',
                    'content'=>$this->result,
                ],[
                    'role'=>'user',
                    'content'=> $test_case_message
                ]
            ];
            $response = $coder->send($message);

            $formalId = GeneratedFormalModel::log(
                generated_code_id: $this->generated_code->id,
                test_case:$response,
                system_message: $system_message,
                requirement: $this->generated_code->requirement,
                generatedFormalModel: $this->result,
            );
            session(['last_formal_model_id' => $formalId]);
        }else{
            $this->result = "Error in generating formal model.<br>Please try again or change the formal model.";
        }
    }

}
?>


<x-card title="Formal Model Generator"
        subtitle="Generate a formal model of the source code using formal verification tools like NuSMV or PyModel.">

    @if(session('last_generated_code_id'))
        <x-form wire:submit="send" no-separator class="flex flex-col items-center justify-center">
            @if(!session('last_formal_model_id'))
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
            @else
                <h2 class="text-center font-bold text-2xl ">Formal Model Generated.</h2>
                <p class="text-center ">Please, continue with the generation of the validated code.</p>
            @endif
        </x-form>
    @else
        <x-form>
            <p class="text-center ">Please, generate a code first.</p>
        </x-form>
    @endif


    <div class="p-2 rounded">
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

