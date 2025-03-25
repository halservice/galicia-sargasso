<?php

use App\Actions\ResetGeneratorsAction;
use App\AI\ChatGPT;
use App\AI\LLama;
use App\Enums\LLM;
use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;

//use App\Settings\CodeGeneratorSettings;
use App\Models\UserSetting;
use App\Traits\ExtractCodeTrait;
use Carbon\Carbon;
use Livewire\Attributes\{Locked, Computed, Url, Validate};

new class extends \Livewire\Volt\Component {
    use ExtractCodeTrait;
    use \App\Traits\ExtractRequestInfo;

    #[Locked]
    public string $result;

    #[Validate('required|string')]
    public string $text = '';

    #[Url]
    public ?int $codeId = null;

    public ?GeneratedFormalModel $lastFormal = null;
    public ?GeneratedCode $generatedCode = null;
    protected ?UserSetting $settings = null;
    public string $formal = '';
//    protected ?CodeGeneratorSettings $settings = null;

    public bool $startFromCode = true;

//    protected ?GeneratedCode $generated_code = null;

    public function mount(): void
    {
        $this->lastFormal = GeneratedFormalModel::where('user_id', auth()->id())
            ->latest()
            ->first();

        if ($this->lastFormal?->is_active) {
            $this->text = $this->lastFormal->requirement;
            $this->formal = $this->lastFormal->generated_formal_model;
            $this->result = $this->formal;
        }
    }

    public function boot(): void
    {
//        $this->settings = app(CodeGeneratorSettings::class);
        $this->settings = UserSetting::where('user_id',auth()->id())->first();
        $this->startFromCode = $this->settings->startFromGeneratedCode();

        // Se parto dal codice ho bisogno di recuperare info del codice, controllando che questo esista e sia attivo
        $code = GeneratedCode::where('user_id', auth()->id())
            ->latest()
            ->first();
        if ($this->startFromCode && $code?->is_active) {
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
            $lastValidation = GeneratedValidatedCode::where('user_id', auth()->id())
                ->latest()
                ->first();
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
            ? "the optimal formal model for this specific request"
            : $this->settings->model_tool;

        // due comandi di sistemi differenti a seconda del metodo che uso
        if ($this->startFromCode) {
            $system_message = "You are an expert in formal verification using $formalTool.
            Generate a formal model based on the user-provided requirements and a given program source code.
            **Rules:**
            - The model must represent only the core logic of the requirement—DO NOT introduce unnecessary constraints, states, transitions, or conditions unless required.
            - If the requirement is straightforward (e.g., printing a message), use the simplest representation without unnecessary states.
            - DO NOT impose **any** value constraints (e.g., variable ranges like `0..255`) unless they are **explicitly** stated in the requirement. Use unconstrained types instead.              - DO NOT enforce additional control variables (e.g., state flags) unless required.
            - Output only the formal model in a correctly formatted code block, with the appropriate language specification.
            - No explanations or comments—only the formal model itself.";
            $this->text = "Generate a formal model in $formalTool for the following requirements: {$this->generatedCode->requirement} and the following code:{$this->generatedCode->generated_code}";
        } else {
            $system_message = "You are an expert in formal verification using $formalTool.
            Generate a formal model based on the user-provided requirements.
            **Rules:**
            - The model must represent only the core logic of the requirement—DO NOT introduce unnecessary constraints, states, transitions, or conditions unless required.
            - If the requirement is straightforward (e.g., printing a message), use the simplest representation without unnecessary states.
            - DO NOT impose **any** value constraints (e.g., variable ranges like `0..255`) unless they are **explicitly** stated in the requirement. Use unconstrained types instead.              - DO NOT enforce additional control variables (e.g., state flags) unless required.
            - Output only the formal model in a correctly formatted code block, with the appropriate language specification.
            - No explanations or comments—only the formal model itself.

            Handling unclear requests:
            If the user request is ambiguous or lacks necessary details, do not generate a formal model. Instead, ask for clarification by specifying what additional information is needed.
            Format your clarification request as follows:
            You must always start with this: '**Requesting new info**:' then
            - [List the missing details]
            You must always end your request with '**Please add these specifications to your existing request.**'";
            if (trim($this->text) === '') {
                $this->result = "Error: the text field can't be empty.";
                return;
            }
        }

        $model = \Auth::user()->settings->llm_formal;
        $message = $coder->systemMessage($system_message, $this->text);
        $response = $coder->send($message, $model);

        $this->formal = $this->extractCodeFromResponse($response);

        if ($this->formal != '') {
            $this->result = "This is your requested formal model:\n\n" . $this->formal;

            GeneratedFormalModel::log(
                $this->startFromCode ? $this->generatedCode->id : null,
                $system_message,
                $this->text,
                $this->result,
            );

        } else {
            $this->result = $this->extractRequestNewInfo($response);
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
                        <x-button
                            class="btn-secondary"
                            type="submit" wire:loading.attr="disabled"
                            wire:keydown.ctrl.enter="send">
                            <span wire:loading.remove wire:target="send">Generate the formal model</span>
                            <span wire:loading wire:target="send" class="flex items-center">
                        <x-icon name="o-arrow-path" class="animate-spin mr-2"/>
                        Generating the formal model...
                        </span>
                        </x-button>
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
                <x-button class="btn-primary" type="submit" wire:loading.attr="disabled"
                          wire:keydown.ctrl.enter="send">
                    <span wire:loading.remove wire:target="send">Send</span>
                    <span wire:loading wire:target="send" class="flex items-center">
                 <x-icon name="o-arrow-path" class="animate-spin mr-2"/>
                 Sending...
                </span>
                </x-button>
                <x-button label="Reset" class="btn-secondary" wire:loading.attr="disabled"
                          wire:click="clear"/>
            </x-slot:actions>
        </x-form>
    @endif


    @if(isset($result))
        @if($formal != '')
            <div class="rounded-[10px] p-[15px] gap-[5px] w-fit break-words mr-auto mb-5 bg-[#3864fc] text-white mt-5 max-w-4xl">
                <code>
                    <pre class="whitespace-pre-wrap">{{ $result }}</pre>
                </code>
            </div>
        @else
            <div class="rounded-[10px] p-[15px] gap-[5px] w-fit break-words mr-auto mb-5 bg-orange-400 text-white mt-5 max-w-4xl">
                <p class="whitespace-pre-wrap">{!! Str::markdown($result) !!}</p>
            </div>
        @endif
    @endif

</x-card>

