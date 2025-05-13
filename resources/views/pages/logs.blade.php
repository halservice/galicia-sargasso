<?php

use App\Exports\ValidatedCodesExport;
use App\Exports\ValidatedCodesExportAll;
use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use App\Settings\CodeGeneratorSettings;
use App\Traits\DataPreparation;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
//use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

// @phpstan-ignore-next-line
new class extends \Livewire\Volt\Component {
    use DataPreparation;
//    use withPagination;

    public function validated(): Collection
    {
        // For the log table get the 10 recent validated codes of the current user.
        return GeneratedValidatedCode::with(['generator' => function ($query) {
            $query->when($query->getModel() instanceof GeneratedCode, fn($q) => $q->with('formalModel'))
                ->when($query->getModel() instanceof GeneratedFormalModel, fn($q) => $q->with('generatedCode'));
        }])
            ->where('user_id', auth()->id())
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($item) => $this->prepareData($item));
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('pages.logs', [
            'validated' => $this->validated(),
        ]);
    }

    // Export all the current user' data.
    public function export()
    {
        return Excel::download(new ValidatedCodesExport, 'validated-codes-' . now()->format('Y-m-d') . '.xlsx');
    }

    // Export all users' data. To view this option the user must have 'is_admin === true'
    public function exportAll()
    {
        return Excel::download(new ValidatedCodesExportAll, 'validated-codes-' . now()->format('Y-m-d') . '.xlsx');
    }


    #[Computed]
    public function headers(): array
    {
        return [
            ['key' => 'generator_type', 'label' => 'Process start from'],
            ['key' => 'user_input', 'label' => 'User request'],
            ['key' => 'first_code', 'label' => 'First generated code'],
            ['key' => 'programming_language', 'label' => 'Language'],
            ['key' => 'code_LLM', 'label' => 'LMM Code'],
            ['key' => 'formal_model', 'label' => 'Formal model'],
            ['key' => 'formal_model_tool', 'label' => 'Model tool'],
            ['key' => 'formal_LLM', 'label' => 'LMM Formal'],
            ['key' => 'validated_code', 'label' => 'Final validated code'],
            ['key' => 'iteration', 'label' => 'Iteration'],
            ['key' => 'llm_used', 'label' => 'LLM Valid.'],
            ['key' => 'test_cases', 'label' => 'Generated test'],
            ['key' => 'test_result', 'label' => 'Test result'],
        ];
    }


}
?>


<x-card title="Logs"
        subtitle="Here you can see and download your latest results." shadow separator>

    <x-form>
        <div class="overflow-x-auto shadow sm:rounded-lg">
            <table class="min-w-full table-auto">
                <thead class="bg-base-300">
                <tr>
                    @foreach ($this->headers as $header)
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase whitespace-nowrap">
                            {{ $header['label'] }}
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody class="divide-y">
                @foreach ($validated as $code)
                    <tr>
                        @foreach ($this->headers as $header)
                            @php
                                $value = $code->{$header['key']};
                                $formattedValue = is_string($value) ? Str::limit($value, 50) : $value;
                            @endphp
                            <td class="px-6 py-4 text-sm truncate whitespace-nowrap"
                                title="{{ is_string($value) ? $value : '' }}">
                                @if (in_array($header['key'], ['first_code', 'formal_model', 'validated_code']))
                                    <code>{{ $formattedValue }}</code>
                                @else
                                    {{ $formattedValue }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </x-form>

    <div class="flex gap-2">
        <div class="mt-5">
            <x-button class="btn-primary" wire:click="export">Export</x-button>
        </div>
        @can('viewAdminContent', Auth::user())
            <div class="mt-5">
                <x-button class="btn-primary" wire:click="exportAll">Export All</x-button>
            </div>
        @endcan
    </div>

</x-card>
