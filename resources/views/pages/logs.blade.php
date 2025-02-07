<?php

use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

new class extends \Livewire\Volt\Component {
    use withPagination;

    #[Computed]
    public function validated(): Collection
    {
        return GeneratedValidatedCode::all()->map(function ($item){
            $item->generator_type = $item->generator_type === 'App\Models\GeneratedFormalModel' ? 'Code generation' : 'Formal Model generation';
            $item->validation_process = json_encode($item->validation_process, JSON_PRETTY_PRINT);
            return $item;
        });
    }

    #[Computed]
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#'],
            ['key' => 'generator_type', 'label' => 'Process start from'],
            ['key' => 'system_message', 'label' => 'Validation system message'],
            ['key' => 'validated_code', 'label' => 'Final validated code'],
            ['key' => 'validation_process', 'label' => 'Process'],
            ['key' => 'test_result', 'label' => 'Test result'],
            ['key' => 'iteration', 'label' => 'Iteration'],
            ['key' => 'llm_used', 'label' => 'LLM'],
        ];
    }

}
?>


<x-card title="Logs"
        subtitle="Here you can find and download all the data by all users." shadow separator>
    <x-form>
        <div class="overflow-x-auto">
            <x-table :headers="$this->headers()"
                     :rows="$this->validated()"
                     class="whitespace-nowrap"
                     striped
            />
        </div>

    </x-form>
    <div class="mt-5">
        <x-button class="btn-primary">Export</x-button>
    </div>

</x-card>
