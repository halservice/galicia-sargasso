<?php

use App\Actions\ResetGeneratorsAction;
use App\AI\ChatGPT;
use App\AI\LLama;
use App\Enums\LLM;
use App\Models\GeneratedCode;
use App\Models\UserSetting;
use App\Models\GeneratedFormalModel;
use App\SystemMessages\CodeGenerationMessages;
use App\Traits\ExtractCodeTrait;
use App\Traits\ExtractRequestInfo;
use App\Traits\ExtractSamplePromptTrait;
use App\Traits\GenerationTrait;
use Carbon\Carbon;
use Livewire\Attributes\{Locked, Computed, Url, Validate};
use App\Models\GeneratedValidatedCode;

// @phpstan-ignore-next-line
new class extends \Livewire\Volt\Component {
    use ExtractCodeTrait;
    use ExtractRequestInfo;
    use ExtractSamplePromptTrait;
    use GenerationTrait;

    #[Validate('required|string')]
    public string $text = '';

    #[Locked]
    public ?string $result = null;

    #[Url]
    public ?int $formalId = null;

    public bool $skipCheck = false;
    public bool $startFromCode = true;

    protected ?GeneratedFormalModel $generatedFormal = null;
    protected ?UserSetting $settings = null;
    protected ?GeneratedCode $lastCode = null;

    public string $code = '';

    public array $conversationThread = [];

    public function mount(): void
    {
        // If the session is active, upload previous test case parameters.
        $this->lastCode = GeneratedCode::where('user_id', auth()->id())
            ->latest()
            ->first();
        if ($this->lastCode?->is_active) {
            $this->text = $this->lastCode->requirement;
            $this->code = $this->lastCode->generated_code;
            $this->result = $this->code;
        }

    }

    public function boot(): void
    {
        $this->settings = UserSetting::where('user_id', auth()->id())->first();
        $this->startFromCode = $this->settings->startFromGeneratedCode();
        $this->lastCode = GeneratedCode::where('user_id', auth()->id())
            ->latest()
            ->first();

        // If process starts from formal model, then upload the current active formal model.
        // Needed for the code generation.
        $formal = GeneratedFormalModel::where('user_id', auth()->id())
            ->latest()
            ->first();
        if (!$this->startFromCode && $formal?->is_active) {
            $this->generatedFormal = $formal;
        }
//
    }

    public function sendWithCheckbox(): void
    {
        $this->send(!$this->skipCheck);
    }

    public function deactivatePreviousCode(): void
    {
        $this->lastCode?->update(['is_active' => false]);
        GeneratedValidatedCode::where('user_id', auth()->id())->latest()->update(['is_active' => false]);
    }

    public function send(bool $checkPrompt): void
    {
        // User's prompt info:
        // 1. If the process start from the code then use the user direct input
        // 2. If the process start from the formal model then create a specified prompt with the formal model's info
        // 3. The user prompt can't be empty. If empty then an error message is displayed.
        if (!$this->startFromCode) {
            $this->text = "Generate a code in {$this->settings->programming_language} for the following requirements: {$this->generatedFormal->requirement} and the following formal model:{$this->generatedFormal->generated_formal_model}";
        }
        if (trim($this->text) === '') {
            $this->result = "Error: the text field can't be empty.";
            return;
        }

        // When generating new code:
        // - If starting from formal model (startFromCode=false), deactivate only previous active generated code and validated code.
        // - If starting from code generation (startFromCode=true), deactivate ALL previous code, formal models, validation codes.
        if (!$this->startFromCode) {
            $this->deactivatePreviousCode();
        } else {
            app(ResetGeneratorsAction::class)();
        }

        // Get the system message.
        $systemMessage = app(CodeGenerationMessages::class)->systemMessage(
            $this->settings->programming_language,
            $this->startFromCode,
            $checkPrompt
        );

        $data = $this->chat([
            'checkPrompt' => $checkPrompt,
            'startFromCode' => $this->startFromCode,
            'text' => $this->text,
            'settings' => $this->settings,
            'conversationThread' => $this->conversationThread,
            'model' => $this->settings->llm_code,
            'systemMessage' => $systemMessage
        ]);

        $this->handleResponse($data, $systemMessage, $checkPrompt);

    }

    protected function handleResponse(array $data, string $systemMessage, bool $checkPrompt): void
    {
        $response = $data['response'];
        $this->conversationThread = $data['conversationThread'];

        $this->code = $this->extractCodeFromResponse($response);

        if ($this->code !== '') {
            // If code is found, save the new generated code.
            $this->result = $this->code;
            $this->conversationThread = [];
            GeneratedCode::log(
                $this->startFromCode ? null : $this->generatedFormal?->id,
                $systemMessage,
                $this->text,
                $this->code
            );
        } elseif ($checkPrompt) {
            // If code is not found and the process is also checking the prompt, it means the process requires additional information:
            // 1. Extract the new requested info to show the user
            // 2. Extract the sample prompt to give the user
            // 3. To keep track of the previous request from the system write them in $conversationThread
            $this->result = $this->extractRequestNewInfo($response);
            $samplePrompt = $this->extractSamplePrompt($response);
            $this->conversationThread[] = [
                'role' => 'assistant',
                'content' => $this->result,
            ];
            if ($samplePrompt !== '') {
                $this->text = $samplePrompt;
            }
        } else {
            // If there is no code found && the process is not checking the prompt then print an error.
            $this->result = "Error: please try again.";
        }
    }

    public function clear(): void
    {
        // Reset all info.user_settings
        app(ResetGeneratorsAction::class)();
        $this->reset('text', 'result');
        $this->conversationThread = [];
    }

}
?>

<x-card title="Source Code Generator"
        subtitle="Input functional requirements in natural language through a user-friendly interface."
>

    @if(! $this->startFromCode)
        {{--        Template if the process starts from the formal model generation, first the formal model must be generated. --}}
        <x-form wire:submit="sendWithCheckbox" no-separator class="flex flex-col items-center justify-center">
            @if($this->generatedFormal?->is_active)
                <h2 class="text-center font-bold text-2xl">Would you like to generate the source code?</h2>
                <x-slot:actions>
                    <div class="flex justify-center w-full">
                        <x-button
                            class="btn-secondary"
                            type="submit" wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="sendWithCheckbox">Generate the code</span>
                            <span wire:loading wire:target="sendWithCheckbox" class="flex items-center">
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
        <x-send-first-step/>
    @endif

    <x-display-result :result="$result" :is-code="$code != ''"/>

</x-card>

