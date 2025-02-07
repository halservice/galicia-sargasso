<?php

use App\Actions\ResetGeneratorsAction;
use App\AI\ChatGPT;
use App\AI\LLama;
use App\Enums\LLM;
use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use App\Settings\CodeGeneratorSettings;
use App\Traits\ExtractCodeTrait;
use Carbon\Carbon;
use Livewire\Attributes\{Locked, Computed, Url, Validate};

new class extends \Livewire\Volt\Component {
    use ExtractCodeTrait;

    #[Locked]
    public string $result;

    #[Validate('required|string')]
    public string $text = '';

    #[Url]
    public ?int $codeId = null;

    public ?GeneratedFormalModel $lastFormal = null;
    public ?GeneratedCode $generatedCode = null;
    protected ?CodeGeneratorSettings $settings = null;

    public bool $startFromCode = true;
//    protected ?GeneratedCode $generated_code = null;

    public function mount(): void
    {
        $this->lastFormal = GeneratedFormalModel::latest()->first();

        if ($this->lastFormal?->is_active) {
            $this->text = $this->lastFormal->requirement;
            $this->result = $this->lastFormal->generated_formal_model;
        }
    }

    public function boot(): void
    {
        $this->settings = app(CodeGeneratorSettings::class);
        $this->startFromCode = $this->settings->startFromGeneratedCode();

//        devo aggiungere il controllo su is_active se lo voglio tenere, altrimeni non funziona
//        $lastFormal = GeneratedFormalModel::orderBy('created_at', 'desc')->first();
//        if($lastFormal?->created_at->lt(Carbon::now()->subMinutes(30))) {
//            $this->resetAll();
//        }

        // Se parto dal codice ho bisogno di recuperare info del codice, controllando che questo esista e sia attivo
        if ($this->startFromCode && ($code = GeneratedCode::latest()->first())?->is_active) {
            $this->generatedCode = $code;
        }
    }

    public function send(): void
    {

        // se parto dalla generazione del codice, ogni volta che creo un nuovo modello is_active si
        // disattiva solo su modello e validazione. mantengo ciò che ho prima, ma posso modificare quello dopo.
        if ($this->startFromCode) {
            if ($this->lastFormal) {
                $this->lastFormal->is_active = false;
                $this->lastFormal->update();
            }
            $lastValidation = GeneratedValidatedCode::latest()->first();
            if ($lastValidation) {
                $lastValidation->is_active = false;
                $lastValidation->update();
            }
        } else {  //se parto dal codice con un nuovo codice resetto tutto, sto partendo da capo.
            app(ResetGeneratorsAction::class)();
        }

        $coder = match ($this->settings->llm_formal) {
            LLM::Llama->value => new LLama(),
            default => new ChatGPT()
        };

        $formalTool = ($this->settings->model_tool === 'let-llm')
            ? "the best choice for this specific model, choosing between NuSMV and Event-B"
            : $this->settings->model_tool;

        // due comandi di sistemi differenti a seconda del metodo che uso
        if ($this->startFromCode) {
            $system_message = "You are an expert in formal verification using $formalTool. Generate a formal model based on the user-provided requirements and a generated code. Include all necessary requirements to verify the system's properties. Always output the model in a code block with the language specification. You must provide only the formal model, explanations aren't required. Format the answer in markdown.";
            $this->text = "Generate a formal model in $formalTool for the following requirements: {$this->generatedCode->requirement} of the following code:{$this->generatedCode->generated_code}";
        } else {
            $system_message = "You are an expert in formal verification using $formalTool. Generate a formal model based on the user-provided requirements. Include all necessary requirements to verify the system's properties. Always output the model in a code block with the language specification. You must provide only the formal model, explanations aren't required. Format the answer in markdown.";
        }

        $message = $coder->systemMessage($system_message, $this->text);
        $response = $coder->send($message);

        $code = $this->extractCodeFromResponse($response);

        if ($code != $response) {
            $this->result = $code;

            $test_case_message = "Given a formal model, could you generate a few test cases that the code should execute if written correctly?";
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

            GeneratedFormalModel::log(
                $this->startFromCode ? $this->generatedCode->id : null,
                $response,
                $system_message,
                $this->text,
                $this->result,
            );

        } else {
            $this->result = "Error in generating formal model.<br>Please try again or change the formal model.";
        }
    }

    public function clear(): void
    {
        app(ResetGeneratorsAction::class)();
        $this->reset('text', 'result');
    }
}
?>


<x-card title="Formal Model Generator"
        subtitle="Generate a formal model of the source code using formal verification tools like NuSMV or PyModel.">

    @if($this->startFromCode)
        <x-form wire:submit="send" no-separator class="flex flex-col items-center justify-center">
            @if($this->generatedCode?->is_active)
                <h2 class="text-center font-bold text-2xl">Would you like to generate the formal model?</h2>
                <x-slot:actions>
                    <div class="flex justify-center w-full">
                        <x-button label="Generate Formal Model"
                                  class="btn-secondary"
                                  type="submit" wire:loading.attr="disabled"
                                  wire:keydown.ctrl.enter="send"/>
                    </div>
                </x-slot:actions>
            @else
                <h2 class="text-center font-bold text-2xl">Generate the source code first or change the sequence of the
                    process.</h2>
            @endif
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

                <x-button label="Reset" class="btn-secondary" wire:loading.attr="disabled"
                          wire:click="clear"/>
            </x-slot:actions>
        </x-form>
    @endif


    @if(isset($result))
        <div class="mt-5 chat-message assistant-message">
            <code>
                <pre>{{ $result }}</pre>
            </code>
        </div>
    @endif

</x-card>

