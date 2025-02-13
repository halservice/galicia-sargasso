<?php

use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;

//use App\Settings\CodeGeneratorSettings;
use App\Models\UserSetting;
use Livewire\Attributes\Computed;

new class extends \Livewire\Volt\Component {

    public array $iterations = [];

    public bool $showDrawer = false;
    public string $activeContent = '';
    public ?int $selectedIterationIndex = null;
    public int $iterationNumber = 0;


//    protected ?CodeGeneratorSettings $settings = null;
    protected ?UserSetting $settings = null;

    public string $req = '';
    public ?GeneratedCode $generatedCode = null;
    public ?GeneratedFormalModel $generatedFormal = null;
    public ?GeneratedValidatedCode $generatedValidation = null;

    public function boot(): void
    {
        $this->settings = UserSetting::where('user_id', auth()->id())->first();
//        $this->settings = app(CodeGeneratorSettings::class);

        $this->generatedValidation = (GeneratedValidatedCode::where('user_id', auth()->id())
            ->latest()
            ->first());
        if ($this->generatedValidation?->is_active) {

            if ($this->settings->startFromGeneratedCode()) {
                $this->generatedFormal = GeneratedFormalModel::findOrFail($this->generatedValidation->generator_id);
                $this->generatedCode = $this->generatedFormal->generatedCode;
                $this->req = $this->generatedCode->requirement;
            } else {
                $this->generatedCode = GeneratedCode::findOrFail($this->generatedValidation->generator_id);
                $this->generatedFormal = $this->generatedCode->formalModel;
                $this->req = $this->generatedFormal->requirement;
            }

            $iterationCount = 0;
            $this->iterations = collect($this->generatedValidation->validation_process)
                ->where('role', 'assistant')
                ->map(function ($entry, $index) use (&$iterationCount) {
                    preg_match('/```(?:\w+)?\s*(.+?)```/s', $entry['content'], $validatedCodes);
                    preg_match('/### Changes Made:\n(.*?)\n### Number of changes made:/s', $entry['content'], $changes);
                    preg_match('/### Number of changes made:\s*(\d+)/', $entry['content'], $numChanges);

                    $iterationCount++;

                    return [
                        'iteration' => $iterationCount,
                        'validated_codes' => !empty($validatedCodes[1]) ? trim($validatedCodes[1]) : '',
                        'modifications' => !empty($changes[1]) ? explode("\n", trim($changes[1])) : [],
                        'num_changes' => $numChanges[1] ?? '0',
                    ];
                })
                ->toArray();
        }

    }

    public function showIteration(int $index, string $content): void
    {
        $this->iterationNumber = $index;
        $this->activeContent = $content;
        $this->showDrawer = true;
    }

}
?>


<x-card title="Feedback"
        subtitle="Provide detailed feedback on the correctness and compliance of the generated code." shadow separator>

    <x-drawer wire:model="showDrawer" class="w-11/12 lg:w-1/3" right>
        <div>
            @if($activeContent === 'firstCode')
                <pre><code id="copyContent">{{ $this->generatedCode->generated_code }}</code></pre>
            @elseif($activeContent === 'formal')
                <pre><code id="copyContent">{{ $this->generatedFormal->generated_formal_model }}</code></pre>
            @elseif($activeContent === 'test')
                <div id="copyContent">{!! Str::markdown($this->generatedFormal->test_case) !!}</div>
            @elseif($activeContent === 'testResult')
                <div id="copyContent">{!! Str::markdown($this->generatedValidation->test_result) !!}</div>
            @elseif($activeContent === 'iteration')
                <pre><code id="copyContent">{{ $this->iterations[$this->iterationNumber]['validated_codes'] }}</code></pre>
            @endif
        </div>
        <br>
{{--        <div x-data="{ showMessage: false }">--}}
            <x-button label="Close" class="btn-primary" @click="$wire.showDrawer = false"/>
            <x-button label="Copy" class="btn-secondary" @click="copy()"/>
{{--            <x-button label="Copy" class="btn-secondary"--}}
{{--                      @click="copy(); showMessage = true; setTimeout(() => showMessage = false, 2000)"/>--}}

{{--            <span x-show="showMessage" x-transition.opacity.duration.500ms class="text-green-500 font-bold mt-2 font">Copied!</span>--}}
{{--        </div>--}}
        </x-drawer>

    <x-form>
        @if($this->generatedValidation?->is_active)
            <h1 class="text-primary text-2xl font-bold">Summarization:</h1>
            <p>The user request was the following:</p>
            <i><b>
                    "{{ $this->req }}"
                </b></i>

            <div class="flex justify-left w-full gap-5">
                <x-button label="Show First Generated Code" class="btn-primary"
                          wire:click="$set('activeContent', 'firstCode'); $wire.showDrawer = true"/>
                <x-button label="Show Formal Model" class="btn-primary"
                          wire:click="$set('activeContent', 'formal'); $wire.showDrawer = true"/>
            </div>

            <h2 class="font-bold text-primary text-xl mt-4">Validation Process:</h2>
            @foreach($this->iterations as $index => $iteration)
                <p class="font-bold text-secondary">Iteration {{ $iteration['iteration'] }}:</p>
                <p>Number of main changes: {{ $iteration['num_changes'] }}</p>
                <p class="italic">Overview of the iteration:</p>
                <ul class="list-disc ml-5">
                    @foreach($iteration['modifications'] as $mod)
                        {{ $mod }}<br>
                    @endforeach
                </ul>
                <div class="flex justify-left w-full gap-5">
                    <x-button label="Show Validated Code" class="btn-secondary"
                              wire:click="showIteration({{ $index }}, 'iteration')"/>
                </div>
            @endforeach

            <h2 class="font-bold text-primary text-xl mt-4">Test:</h2>
            <p>The platform produces a few test cases, here you can find more about it.</p>
            <div class="flex justify-left w-full gap-5">
                <x-button label="Show Generated Test Cases" class="btn-primary"
                          wire:click="$set('activeContent', 'test'); $wire.showDrawer = true"/>
                <x-button label="Show If The Test Cases Passed" class="btn-primary"
                          wire:click="$set('activeContent', 'testResult'); $wire.showDrawer = true"/>
            </div>
        @else
            <p>You must complete the process first.</p>
        @endif


    </x-form>

</x-card>

<script>
    function copy() {
        let text = document.getElementById('copyContent').innerText;
        navigator.clipboard.writeText(text);
    }
</script>
