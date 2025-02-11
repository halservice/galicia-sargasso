<?php

use App\Actions\ResetGeneratorsAction;
use App\AI\ChatGPT;
use App\AI\LLama;
use App\Enums\LLM;
use App\Models\GeneratedCode;
use App\Settings\CodeGeneratorSettings;
use App\Models\GeneratedFormalModel;
use App\Traits\ExtractCodeTrait;
use Carbon\Carbon;
use Livewire\Attributes\{Locked, Computed, Url, Validate};
use App\Models\GeneratedValidatedCode;

new class extends \Livewire\Volt\Component {
    use ExtractCodeTrait;

    #[Validate('required|string')]
    public string $text = '';

    #[Locked]
    public ?string $result = null;

    #[Url]
    public ?int $formalId = null;

    protected ?GeneratedFormalModel $generatedFormal = null;
    protected ?CodeGeneratorSettings $settings = null;
    public bool $startFromCode = true;

    protected ?GeneratedCode $lastCode = null;


    public function mount(): void
    {
        $this->lastCode = GeneratedCode::where('user_id', auth()->id())
                            ->latest()
                            ->first();

        // Per gestione caricamento codice tipo chat
        if ($this->lastCode?->is_active === true) {
            $this->text = $this->lastCode->requirement;
            $this->result = $this->lastCode->generated_code;
        }
    }

    public function boot(): void
    {
        $this->settings = app(CodeGeneratorSettings::class);
        $this->startFromCode = $this->settings->startFromGeneratedCode();
//        devo aggiungere il controllo su is_active se lo voglio tenere, altrimeni non funziona
//        $lastCode = GeneratedCode::orderBy('created_at', 'desc')->first();
//        if ($lastCode?->created_at->lt(Carbon::now()->subMinutes(30))) {
//            $this->resetAll();
//        }

        // Se parto dal modello formale ho bisogno di recuperare info del modello formale, controllando che questo esista e sia attivo
        $formal = GeneratedFormalModel::where('user_id', auth()->id())
                    ->latest()
                    ->first();
        if (! $this->startFromCode  && $formal?->is_active) {
            $this->generatedFormal = $formal;
        }

    }

    public function send(): void
    {
        // se parto dalla generazione del modello formale, ogni volta che creo un nuovo codice is_active si
        // disattiva solo su codice e validazione. mantengo ciò che ho prima ma posso modificare quello dopo.
        if (! $this->startFromCode ) {
            if ($this->lastCode) {
                $this->lastCode->is_active = false;
                $this->$lastCode->update();
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
        if (! $this->startFromCode) {
            $systemMessage = "You are an expert programmer. Generate clean and secure code based on user requirements and a formal model, using the following programming language {$this->settings->programming_language}. You should only write the requested code or function, don't write a main and test cases. You must provide the code within appropriate code blocks, with no explanations. Format your response using markdown.";
            $this->text = "Generate a code in {$this->settings->programming_language} for the following requirements: {$this->generatedFormal->requirement} of the following formal model:{$this->generatedFormal->generated_formal_model}";
        } else {
            $systemMessage = "You are an expert programmer. Generate clean and secure code based on user requirements, using the following programming language {$this->settings->programming_language}. You should only write the requested code or function, don't write a main and test cases. You must provide only the code within appropriate code blocks, with no explanation. Format your response using markdown.";
            if(trim($this->text) === ''){
                $this->result="Error: the text field can't be empty.";
                return;
            }
        }

        $message = $coder->systemMessage($systemMessage, $this->text);
        $response = $coder->send($message);

        $code = $this->extractCodeFromResponse($response);

        // salvo solo se code!=respose, se è uguale è perché non ha trovato un codice.
        if ($code !== $response) {
            $this->result = $code;

            GeneratedCode::log(
                $this->startFromCode  ?  null : $this->generatedFormal->id,
                $systemMessage,
                $this->text,
                $this->result,
            );
        } else {
            $this->result = "Error in generating the code.<br>Please try again.";
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
        subtitle="Input functional requirements in natural language through a user-friendly interface.">

    @if(! $this->startFromCode)
        <x-form wire:submit="send" no-separator class="flex flex-col items-center justify-center">
            @if($this->generatedFormal?->is_active)
                <h2 class="text-center font-bold text-2xl">Would you like to generate the source code?</h2>
                <x-slot:actions>
                    <div class="flex justify-center w-full">
                        <x-button
                                  class="btn-secondary"
                                  type="submit" wire:loading.attr="disabled"
                                  wire:keydown.ctrl.enter="send">
                        <span wire:loading.remove wire:target="send">Generate the code</span>
                        <span wire:loading wire:target="send" class="flex items-center">
                         <x-icon name="o-arrow-path" class="animate-spin mr-2" />
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
                wire:keydown.enter="send"
                inline
            />
            <x-slot:actions>
                <x-button class="btn-primary" type="submit" wire:loading.attr="disabled"
                          wire:keydown.ctrl.enter="send">
                <span wire:loading.remove wire:target="send">Send</span>
                <span wire:loading wire:target="send" class="flex items-center">
                 <x-icon name="o-arrow-path" class="animate-spin mr-2" />
                 Sending...
                </span>
                </x-button>
                <x-button label="Reset" class="btn-secondary" wire:loading.attr="disabled"
                          wire:click="clear"/>
            </x-slot:actions>
        </x-form>
    @endif

    @if(isset($result))
        <div class="rounded-[10px] p-[15px] gap-[5px] w-fit break-words mr-auto mb-5 bg-[#3864fc] text-white mt-5">
            <code>
                <pre>{{ $result }}</pre>
            </code>
        </div>
    @endif

</x-card>

