<?php

use App\Actions\ResetGeneratorsAction;
use App\AI\ChatGPT;
use App\AI\LLama;
use App\Enums\LLM;
use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use App\Models\UserSetting;
use App\SystemMessages\FormalModelMessages;
use App\Traits\ExtractCodeTrait;
use App\Traits\ExtractRequestInfo;
use App\Traits\ExtractSamplePromptTrait;
use App\Traits\GenerationTrait;
use Carbon\Carbon;
use Livewire\Attributes\{Locked, Computed, Url, Validate};

// @phpstan-ignore-next-line
new class extends \Livewire\Volt\Component {
    use ExtractCodeTrait;
    use GenerationTrait;
    use ExtractRequestInfo;
    use ExtractSamplePromptTrait;

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

    public bool $skipCheck = false;
    public bool $startFromCode = true;

    public array $conversationThread = [];

    public function mount(): void
    {
        // If the session is active, upload previous test case parameters.
        if ($this->lastFormal?->is_active) {
            $this->text = $this->lastFormal->requirement;
            $this->formal = $this->lastFormal->generated_formal_model;
            $this->result = $this->formal;
        }
    }

    public function boot(): void
    {
        $this->settings = UserSetting::where('user_id', auth()->id())->first();
        $this->startFromCode = $this->settings->startFromGeneratedCode();
        $this->lastFormal = GeneratedFormalModel::where('user_id', auth()->id())
            ->latest()
            ->first();
        // If process starts from code generation, then upload the current active code.
        // Needed for the formal model generation.
        $code = GeneratedCode::where('user_id', auth()->id())
            ->latest()
            ->first();
        if ($this->startFromCode && $code?->is_active) {
            $this->generatedCode = $code;
        }
    }

    public function sendWithCheckbox(): void
    {
        $this->send(!$this->skipCheck);
    }

    public function deactivatePreviousFormalModel(): void
    {
        $this->lastFormal?->update(['is_active' => false]);
        GeneratedValidatedCode::where('user_id', auth()->id())->latest()->update(['is_active' => false]);
    }

    public function send(bool $checkPrompt): void
    {
        // User's prompt info:
        // 1. If the process start from the formal model then use the user direct input
        // 2. If the process start from the code generation then create a specified prompt with the code's info
        // 3. The user prompt can't be empty. If empty then an error message is displayed.
        $language = $this->settings->model_tool;
        $formalTool = ($language === 'Let the LLM choose the most suitable model')
            ? "the optimal formal model tool for this specific request between NuSMV and Event-B"
            : $language;
        if ($this->startFromCode) {
            $this->text = "Generate a formal model in $formalTool for the following requirements: {$this->generatedCode->requirement} and the following code:{$this->generatedCode->generated_code}";
        }
        if (trim($this->text) === '') {
            $this->result = "Error: the text field can't be empty.";
            return;
        }

        // When generating new formal model:
        // - If starting from code generation (startFromCode=true), deactivate only previous active formal model and validated code.
        // - If starting from formal model (startFromCode=false), deactivate ALL previous code, formal models, validation codes.
        if ($this->startFromCode) {
            $this->deactivatePreviousFormalModel();
        } else {
            app(ResetGeneratorsAction::class)();
        }

        $systemMessage = app(FormalModelMessages::class)->systemMessage(
            $formalTool,
            $this->startFromCode,
            $checkPrompt,
        );

        $data = $this->chat([
            'checkPrompt' => $checkPrompt,
            'startFromCode' => $this->startFromCode,
            'text' => $this->text,
            'settings' => $this->settings,
            'conversationThread' => $this->conversationThread,
            'model' => $this->settings->llm_formal,
            'systemMessage' => $systemMessage
        ]);

        $this->handleResponse($data, $systemMessage, $checkPrompt);
    }

    protected function handleResponse(array $data, string $systemMessage, bool $checkPrompt): void
    {
        $response = $data['response'];
        $this->conversationThread = $data['conversationThread'];

        $this->formal = $this->extractCodeFromResponse($response);

        if ($this->formal !== '') {
            // If code is found, save the new generated code.
            $this->result = $this->formal;
            $this->conversationThread = [];
            GeneratedFormalModel::log(
                !$this->startFromCode ? null : $this->generatedCode?->id,
                $systemMessage,
                $this->text,
                $this->formal
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
        app(ResetGeneratorsAction::class)();
        $this->reset('text', 'result');
        $this->conversationThread = [];
    }
}
?>


<x-card title="Formal Model Generator"
        subtitle="Generate a formal model of the source code using formal verification tools like NuSMV or PyModel.">

    @if($this->startFromCode)
        <x-form wire:submit="sendWithCheckbox" no-separator class="flex flex-col items-center justify-center">
            @if($this->generatedCode?->is_active)
                <h2 class="text-center font-bold text-2xl">Would you like to generate the formal model?</h2>
                <x-slot:actions>
                    <div class="flex justify-center w-full">
                        <x-button
                            class="btn-secondary"
                            type="submit" wire:loading.attr="disabled"
                            wire:keydown.ctrl.enter="sendWithCheckbox">
                            <span wire:loading.remove wire:target="sendWithCheckbox">Generate the formal model</span>
                            <span wire:loading wire:target="sendWithCheckbox" class="flex items-center">
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
        <x-send-first-step/>
    @endif

        <x-display-result :result="$result" :is-code="$formal != ''"/>

</x-card>

