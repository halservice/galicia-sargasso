<?php

use App\Exports\ValidatedCodesExport;
use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use App\Settings\CodeGeneratorSettings;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

new class extends \Livewire\Volt\Component {
    use withPagination;

//    #[Computed]
    public function validated(): Collection
    {
        return GeneratedValidatedCode::with(['generator' => function ($query) {
            if ($query->getModel() instanceof GeneratedCode) {
                $query->with('formalModel'); // Carichiamo solo se è un GeneratedCode
            }
            if ($query->getModel() instanceof GeneratedFormalModel) {
                $query->with('generatedCode'); // Carichiamo solo se è un GeneratedFormalModel
            }
        }])
            ->where('user_id', auth()->id())
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($item) {

                //parte da formal model
                if ($item->generator instanceof GeneratedCode) {
                    $item->user_input = $item->generator->formalModel->requirement;
                    $item->formal_model = $item->generator->formalModel->generated_formal_model;
                    $item->system_formal = $item->generator->formalModel->system_message;
                    $item->formal_LLM = $item->generator->formalModel->llm_used;
                    $item->formal_model_tool = $item->generator->formalModel->model_tool;
                    $item->test_cases = $item->generator->formalModel->test_case;

                    $item->programming_language = $item->generator->programming_language->name;
                    $item->first_code = $item->generator->generated_code;
                    $item->code_LLM = $item->generator->llm_used;
                    $item->system_code = $item->generator->system_message;
                }

                //parte da code gen
                if ($item->generator instanceof GeneratedFormalModel) {
                    $item->programming_language = $item->generator->generatedCode->programming_language->name;
                    $item->user_input = $item->generator->generatedCode->requirement;
                    $item->first_code = $item->generator->generatedCode->generated_code;
                    $item->code_LLM = $item->generator->generatedCode->llm_used;
                    $item->system_code = $item->generator->generatedCode->system_message;

                    $item->formal_model = $item->generator->generated_formal_model;
                    $item->formal_LLM = $item->generator->llm_used;
                    $item->formal_model_tool = $item->generator->model_tool;
                    $item->test_cases = $item->generator->test_case;
                    $item->system_formal = $item->generator->system_message;

                }

                $item->generator_type = $item->generator_type === 'App\Models\GeneratedFormalModel' ? 'Code generation' : 'Formal Model generation';
                $item->validation_process = json_encode($item->validation_process, JSON_PRETTY_PRINT);
                return $item;
            });
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('pages.logs', [
            'validated' => $this->validated(),
        ]);
    }

    public function export()
    {
        return Excel::download(new ValidatedCodesExport, 'validated-codes-' . now()->format('Y-m-d') . '.xlsx');
    }


    #[Computed]
    public function headers(): array
    {
        return [
            ['key' => 'generator_type', 'label' => 'Process start from'],
            ['key' => 'user_input', 'label' => 'User request'],
            ['key' => 'first_code', 'label' => 'First generated code'],
            ['key' => 'system_code', 'label' => 'Code system message'],
            ['key' => 'programming_language', 'label' => 'Language'],
            ['key' => 'code_LLM', 'label' => 'LMM Code'],
            ['key' => 'formal_model', 'label' => 'Formal model'],
            ['key' => 'system_formal', 'label' => 'Formal system message'],
            ['key' => 'formal_model_tool', 'label' => 'Model tool'],
            ['key' => 'formal_LLM', 'label' => 'LMM Formal'],
            ['key' => 'validated_code', 'label' => 'Final validated code'],
            ['key' => 'system_message', 'label' => 'Validation system message'],
            ['key' => 'validation_process', 'label' => 'Process'],
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
                                @elseif ($header['key'] === 'validation_process')
                                    <pre>{{ $formattedValue }}</pre>
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
            <x-button class="btn-primary" wire:click="export" disabled>Export</x-button>
        </div>
        @can('viewAdminContent', Auth::user())
        <div class="mt-5">
            <x-button class="btn-primary" wire:click="exportAll">Export All</x-button>
        </div>
        @endcan
    </div>

</x-card>
