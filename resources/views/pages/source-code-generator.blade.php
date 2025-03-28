<?php

use App\Actions\ResetGeneratorsAction;
use App\AI\ChatGPT;
use App\AI\LLama;
use App\Enums\LLM;
use App\Models\GeneratedCode;
use App\Models\UserSetting;
use App\Models\GeneratedFormalModel;
use App\Traits\ExtractCodeTrait;
use Carbon\Carbon;
use Livewire\Attributes\{Locked, Computed, Url, Validate};
use App\Models\GeneratedValidatedCode;

new class extends \Livewire\Volt\Component {
    use ExtractCodeTrait;
    use \App\Traits\ExtractRequestInfo;
    use \App\Traits\ExtractSamplePromptTrait;

    #[Validate('required|string')]
    public string $text = '';

    #[Locked]
    public ?string $result = null;

    #[Url]
    public ?int $formalId = null;

    protected ?GeneratedFormalModel $generatedFormal = null;
    protected ?UserSetting $settings = null;
//    protected ?CodeGeneratorSettings $settings = null;
    public bool $startFromCode = true;

    public string $code = '';

    protected ?GeneratedCode $lastCode = null;


    public function mount(): void
    {
        $this->lastCode = GeneratedCode::where('user_id', auth()->id())
            ->latest()
            ->first();

        // Per gestione caricamento codice tipo chat
        if ($this->lastCode?->is_active === true) {
            $this->text = $this->lastCode->requirement;
            $this->code = $this->lastCode->generated_code;
            $this->result = $this->code;
        }
    }

    public function boot(): void
    {
//        $this->settings = app(CodeGeneratorSettings::class);
        $this->settings = UserSetting::where('user_id',auth()->id())->first();

        $this->startFromCode = $this->settings->startFromGeneratedCode();

        // Se parto dal modello formale ho bisogno di recuperare info del modello formale, controllando che questo esista e sia attivo
        $formal = GeneratedFormalModel::where('user_id', auth()->id())
            ->latest()
            ->first();
        if (!$this->startFromCode && $formal?->is_active) {
            $this->generatedFormal = $formal;
        }

    }

    public function send($checkPrompt = false): void
    {
        // se parto dalla generazione del modello formale, ogni volta che creo un nuovo codice is_active si
        // disattiva solo su codice e validazione. mantengo ciò che ho prima ma posso modificare quello dopo.
        if (!$this->startFromCode) {
            if ($this->lastCode) {
                $this->lastCode->is_active = false;
                $this->lastCode->update();
            }
            $lastValidation = GeneratedValidatedCode::where('user_id', auth()->id())
                ->latest()
                ->first();
            if ($lastValidation) {
                $lastValidation->is_active = false;
                $lastValidation->update();
            }
        } else { //se parto dal codice con un nuovo codice resetto tutto, sto partendo da capo.
            app(ResetGeneratorsAction::class)();
        }

        $coder = match ($this->settings->llm_code) {
            LLM::Llama->value => new LLama(),
            default => new ChatGPT()
        };

        // due comandi di sistemi differenti a seconda del metodo che uso
        if (!$this->startFromCode) {
            $systemMessage = "You are an expert programmer.
            Generate clean and secure code based on user requirements and given formal model, using the following programming language: {$this->settings->programming_language}.
            - The formal model is a guideline, but the code should be as simple and direct as possible.
            - DO NOT introduce state variables, flags, or additional logic unless required.
            - If a requirement can be implemented with a direct function, prefer that approach.
            - You should only write the requested function(s), without a `main` function (unless explicitly required in the prompt)  or test cases.
            - You must provide the code within appropriate code blocks, with no explanations.
            - Format your response using markdown.";
            $this->text = "Generate a code in {$this->settings->programming_language} for the following requirements: {$this->generatedFormal->requirement} and the following formal model:{$this->generatedFormal->generated_formal_model}";
        } elseif ($checkPrompt === true) {
            $systemMessage = "You are an expert programmer.
            Generate clean and secure code based on user requirements, using the following programming language {$this->settings->programming_language}.
            - If a requirement can be implemented with a direct function, prefer that approach.
            - You should only write the requested function(s), without a `main` function (unless explicitly required in the prompt)  or test cases.
            - You must provide the code within appropriate code blocks, with no explanations.
            - Format your response using markdown.

            Handling unclear requests:
            If the user request is ambiguous or lacks necessary details, DO NOT generate ANY code section. Instead, ask for clarification by specifying what additional information is needed.
            When asking for clarification:
            - Use the same language as the user request.
            - Focus on practical aspects needed to implement the functionality
            - Avoid technical questions unless necessary. Keep your questions simple and relevant to the core functionality.
            - Your clarification requests should be based on general knowledge and should not assume the user has programming expertise.
            - Do not ask for details that can reasonably be assumed.
            Format your clarification request as follows:
            - Always start with '**Requesting new info**:'
            - [List the missing details]
            - Always end with '**Please, use the new revised prompt and modify it.**'
            If clarification is needed, provide a sample revised prompt for the user to follow. Format it as follows:
            - Start with 'Start sample prompt'
            - Include a modified prompt of the user request that incorporates all the missing details. Make sure to NOT modify the data already given by the user. If the user has to add something use [] to encapsulate the placeholders.
            - End with 'End sample prompt'
            This way, the user can easily adjust their request based on your suggestions.";
        } else {
            $systemMessage = "You are an expert programmer.
            Generate clean and secure code based on user requirements, using the following programming language {$this->settings->programming_language}.
            - If a requirement can be implemented with a direct function, prefer that approach.
            - You should only write the requested function(s), without a `main` function (unless explicitly required in the prompt)  or test cases.
            - You must provide the code within appropriate code blocks, with no explanations.
            - Format your response using markdown.";
        }
        if (trim($this->text) === '') {
            $this->result = "Error: the text field can't be empty.";
            return;
        }

        $model = \Auth::user()->settings->llm_code;
        $message = $coder->systemMessage($systemMessage, $this->text);
        $response = $coder->send($message, $model);
        $this->code = $this->extractCodeFromResponse($response);
        // salvo solo se code!=response, se è uguale è perché non ha trovato un codice.
        if ($this->code !== '') {
            $this->result = "This is your requested code:\n\n" . $this->code;

            GeneratedCode::log(
                $this->startFromCode ? null : $this->generatedFormal->id,
                $systemMessage,
                $this->text,
                $this->result,
            );
        } else {
            $this->result = $this->extractRequestNewInfo($response);
            $samplePrompt = $this->extractSamplePrompt($response);
            if ($samplePrompt != '') {
                $this->text = $samplePrompt;

            }
        }
    }

    public function clear(): void
    {
        app(ResetGeneratorsAction::class)();
        $this->reset('text', 'result');
    }
}
?>

<x-card title="Source Code Generator"
        subtitle="Input functional requirements in natural language through a user-friendly interface."
    >

    @if(! $this->startFromCode)
        <x-form wire:submit="send" no-separator class="flex flex-col items-center justify-center">
            @if($this->generatedFormal?->is_active)
                <h2 class="text-center font-bold text-2xl">Would you like to generate the source code?</h2>
                <x-slot:actions>
                    <div class="flex justify-center w-full">
                        <x-button
                            class="btn-secondary"
                            type="submit" wire:loading.attr="disabled"
                            >
                            <span wire:loading.remove wire:target="send">Generate the code</span>
                            <span wire:loading wire:target="send" class="flex items-center">
                         <x-icon name="o-arrow-path" class="animate-spin mr-2"/>
                         Generating the code...
                        </span>
                        </x-button>
                    </div>
                </x-slot:actions>
            @else
                <h2 class="text-center font-bold text-2xl">Generate the formal model first or change the sequence of the
                    process.</h2>
            @endif
        </x-form>
    @else
        <x-form wire:submit="send" no-separator>
            <x-textarea
                wire:model="text"
                placeholder="Type your natural language input here..."
                rows="4"
                wire:keydown.enter="send(true)"
                inline
            />
            <x-slot:actions>
                <x-button class="btn-primary" type="button" wire:loading.attr="disabled"
{{--                          wire:keydown.ctrl.enter="send" --}}
                          wire:click="send(false)">
                    <span wire:loading.remove wire:target="send(false)">Send</span>
                    <span wire:loading wire:target="send(false)" class="flex items-center">
                 <x-icon name="o-arrow-path" class="animate-spin mr-2"/>
                 Sending...
                </span>
                </x-button>
                <x-button class="btn-primary" type="button" wire:loading.attr="disabled" wire:click="send(true)">
                    <span wire:loading.remove wire:target="send(true)">Check prompt & send</span>
                    <span wire:loading wire:target="send(true)" class="flex items-center">
                 <x-icon name="o-arrow-path" class="animate-spin mr-2"/>
                 Checking prompt...
                </span>
                </x-button>
                <x-button label="Reset" class="btn-secondary" wire:loading.attr="disabled"
                          wire:click="clear"/>
            </x-slot:actions>
        </x-form>
    @endif

    @if(isset($result))
        @if($code != '')
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

