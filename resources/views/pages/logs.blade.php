<?php

use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use App\Settings\CodeGeneratorSettings;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

new class extends \Livewire\Volt\Component {
    use withPagination;

//    #[Computed]
    public function validated(): Collection
    {
        return GeneratedValidatedCode::where('user_id', auth()->id())
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($item) {
            $item->generator_type = $item->generator_type === 'App\Models\GeneratedFormalModel' ? 'Code generation' : 'Formal Model generation';
            $item->validation_process = json_encode($item->validation_process, JSON_PRETTY_PRINT);
            return $item;
        });
    }

    public function render():  \Illuminate\Contracts\View\View
    {
        return view('pages.logs',[
            'validated' => $this->validated(),
        ]);
    }


    #[Computed]
    public function headers(): array
    {
        return [
            ['key' => 'generator_type', 'label' => 'Process start from'],
            ['key' => 'system_message', 'label' => 'Validation system message'],
            ['key' => 'validated_code', 'label' => 'Final validated code'],
            ['key' => 'validation_process', 'label' => 'Process'],
            ['key' => 'iteration', 'label' => 'Iteration'],
            ['key' => 'test_result', 'label' => 'Test result'],
            ['key' => 'llm_used', 'label' => 'LLM'],
        ];
    }

    public function exportAll()
    {
        $data = GeneratedValidatedCode::latest()->get();
        $filename = 'validated_codes_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $handle = fopen(storage_path('app/' . $filename), 'w');
        fputcsv($handle, ['Start from', 'System Message', 'Validated Code', 'Validation Process', 'Iteration', 'Test Result', 'LLM Used']);

        foreach ($data as $code) {
            $generatorType = $code->generator_type === 'App\Models\GeneratedFormalModel'
                ? 'Code generation'
                : 'Formal Model generation';

            fputcsv($handle, [
                $generatorType,
                $code->system_message,
                $code->validated_code,
//                $code->validation_process,
                $code->iteration,
                $code->test_result,
                $code->llm_used->value,
            ]);
        }

        fclose($handle);

        return response()->download(storage_path('app/' . $filename))->deleteFileAfterSend();
    }

}
?>


<x-card title="Logs"
        subtitle="Here you can see your latest results and download all the data by all users." shadow separator>

    <x-form>
    <div class="overflow-x-auto  shadow sm:rounded-lg">
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
            <tbody class=" divide-y">
            @foreach($validated as $code)
                <tr>
                    <td class="px-6 py-4 text-sm truncate whitespace-nowrap" title="{{ $code->generator_type }}">{{ $code->generator_type }}</td>
                    <td class="px-6 py-4 text-sm overflow-x-auto whitespace-nowrap" title="{{ $code->system_message }}">{{ Str::limit($code->system_message, 50) }}</td>
                    <td class="px-6 py-4 text-sm truncate whitespace-nowrap" title="{{ $code->validated_code }}">
                        <code>{{ Str::limit($code->validated_code, 50) }}</code>
                    </td>
                    <td class="px-6 py-4 text-sm truncate whitespace-nowrap">
                        <pre>{{ Str::limit($code->validation_process, 50) }}</pre>
                    </td>
                    <td class="px-6 py-4 text-sm whitespace-nowrap">{{ $code->iteration }}</td>
                    <td class="px-6 py-4 text-sm truncate whitespace-nowrap" title="{{ $code->test_result }}">{{ Str::limit($code->test_result, 50) }}</td>
                    <td class="px-6 py-4 text-sm whitespace-nowrap">{{ $code->llm_used }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    </x-form>

    <div class="mt-5">
        <x-button class="btn-primary" wire:click="exportAll" disabled>Export</x-button>
    </div>

</x-card>
