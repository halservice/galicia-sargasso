<?php

use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use Livewire\Attributes\Computed;

new class extends \Livewire\Volt\Component {

    public string $req;
    public string $first_code;
    public string $formal_model;
    public array $iterations = [];

    public bool $showDrawer = false;
    public bool $showDrawer2 = false;
//    public bool $showDrawer3 = false;

    protected ?GeneratedCode $generatedCode = null;
    protected ?GeneratedFormalModel $generatedFormal = null;
    protected ?GeneratedValidatedCode $generatedValidation = null;

    public function boot(): void
    {
        if ((GeneratedValidatedCode::latest('created_at')->first())?->is_active) {
            $this->generatedValidation = GeneratedValidatedCode::latest('created_at')->first();

            if($this->isFromGeneratedCode){
                $this->generatedFormal = GeneratedFormalModel::findOrFail($this->generatedValidation->generator_id);
                $this->generatedCode = GeneratedCode::findOrFail($this->generatedFormal->generated_code_id);
            }else{
                $this->generatedCode = GeneratedCode::findOrFail($this->generatedValidation->generator_id);
                $this->generatedFormal = GeneratedFormalModel::findOrFail($this->generatedCode->generated_formal_model_id);
            }

            $this->req = $this->generatedCode->requirement;
            $this->first_code = $this->generatedCode->generated_code;

//            $jsonString = $this->generatedValidation->validation_process ?? '[]';
//            $data = json_decode($this->generatedValidation->validation_process, true);

            $iterationCount = 0;
            $this->iterations = collect($data)
                ->where('role', 'assistant')
                ->map(function ($entry, $index) use (&$iterationCount) {
                    preg_match('/```(?:\w+)?\s*(.+?)```/s', $entry['content'], $validatedCodes);
                    preg_match('/### Changes Made:\n(.*?)\n### Number of changes made:/s', $entry['content'], $changes);
                    preg_match('/### Number of changes made:\s*(\d+)/', $entry['content'], $numChanges);

                    $iterationCount++;

                    return [
                        'iteration' => $iterationCount,
                        'validated_codes' => !empty($validatedCodes[1]) ? explode("\n", trim($validatedCodes[1])) : [],
                        'modifications' => !empty($changes[1]) ? explode("\n", trim($changes[1])) : [],
                        'num_changes' => $numChanges[1] ?? '0',
                    ];
                })
                ->toArray();
        }

    }

    #[Computed]
    public function isFromGeneratedCode(): bool
    {
        if((GeneratedValidatedCode::latest('created_at')->first())?->generator_type === 'App\Models\GeneratedFormalModel'){
            return true;
        }else{
            return false;
        }
    }


}
?>


<x-card title="Feedback"
        subtitle="Provide detailed feedback on the correctness and compliance of the generated code." shadow separator>

    <x-form>
        @if((GeneratedValidatedCode::latest('created_at')->first())?->is_active)
            <h1 class="text-primary text-2xl font-bold">Summarization:</h1>
            <p>The user request was the following:</p>
            <i><b>
                    {{ $req }}
                </b></i>

            <x-drawer wire:model="showDrawer" class="w-11/12 lg:w-1/3" right>
                <div>
                    <pre><code>{{ $first_code }}</code></pre>
                    <br></div>
                <x-button label="Close" @click="$wire.showDrawer = false"/>
            </x-drawer>
            <x-drawer wire:model="showDrawer2" class="w-11/12 lg:w-1/3" right>
                <div>
                    <pre><code>{{ $formal_model }}</code></pre>
                    <br></div>
                <x-button label="Close" @click="$wire.showDrawer2 = false"/>
            </x-drawer>
            {{--            <x-drawer wire:model="showDrawer3" class="w-11/12 lg:w-1/3" right>--}}
            {{--                <div><pre><code>{{ $iterations[$selectedIndex]['validated_code'] }}</code></pre><br></div>--}}
            {{--                <x-button label="Close" @click="$wire.showDrawer3 = false" />--}}
            {{--             </x-drawer>--}}


            <div class="flex justify-left w-full gap-5">
                <x-button label="Show First Generated Code" wire:click="$toggle('showDrawer')"/>
                <x-button label="Show Formal Model" wire:click="$toggle('showDrawer2')"/>
            </div>

            <h2 class="font-bold text-primary text-xl mt-4">Validation Process:</h2>
            @foreach($iterations as $iteration)
                <p class="font-bold text-secondary">Iteration {{ $iteration['iteration'] }}:</p>
                <p>Number of changes: {{ $iteration['num_changes'] }}</p>
                <p class="italic">Summary of the adjustments made during this iteration:</p>
                <ul class="list-disc ml-5">
                    @foreach($iteration['modifications'] as $mod)
                        <li>{{ $mod }}</li>
                    @endforeach
                </ul>
                {{--                <div class="flex justify-left w-full gap-5">--}}
                {{--                    <x-button label="Show Validated Code"--}}
                {{--                              wire:click="set('selectedIndex', {{ $index }}); $wire.showDrawer3 = true"/>--}}
                {{--                </div>--}}
            @endforeach

        @else
            <p>You must complete the process first.</p>
        @endif


    </x-form>

</x-card>
