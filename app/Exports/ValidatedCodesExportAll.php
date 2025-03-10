<?php

namespace App\Exports;

use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ValidatedCodesExportAll implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return Collection
    */
    public function collection(): Collection
    {
        return GeneratedValidatedCode::with(['generator' => function ($query) {
            if ($query->getModel() instanceof GeneratedCode) {
                $query->with('formalModel'); // Carichiamo solo se è un GeneratedCode
            }
            if ($query->getModel() instanceof GeneratedFormalModel) {
                $query->with('generatedCode'); // Carichiamo solo se è un GeneratedFormalModel
            }
        }])
            ->latest()
            ->get()
            ->map(function ($item) {

                //parte da formal model
                if ($item->generator instanceof GeneratedCode) {
                    $formalModel = $item->generator->formalModel ?? null;
                    $item->user_input = $formalModel->requirement ?? '';
                    $item->formal_model = $formalModel->generated_formal_model ?? '';
                    $item->system_formal = $formalModel->system_message ?? '';
                    $item->formal_LLM = $formalModel->llm_used ?? '';
                    $item->formal_model_tool = $formalModel->model_tool ?? '';
                    $item->test_cases = $formalModel->test_case ?? '';

                    $item->programming_language = $item->generator->programming_language->value ?? '';
                    $item->first_code = $item->generator->generated_code ?? '';
                    $item->code_LLM = $item->generator->llm_used ?? '';
                    $item->system_code = $item->generator->system_message ?? '';
                }

                //parte da code gen
                if ($item->generator instanceof GeneratedFormalModel) {
                    $generatedCode = $item->generator->generatedCode ?? null;
                    $item->programming_language = $generatedCode->programming_language->value ?? '';
                    $item->user_input = $generatedCode->requirement ?? '';
                    $item->first_code = $generatedCode->generated_code ?? '';
                    $item->code_LLM = $generatedCode->llm_used ?? '';
                    $item->system_code = $generatedCode->system_message ?? '';

                    $item->formal_model = $item->generator->generated_formal_model ?? '';
                    $item->formal_LLM = $item->generator->llm_used ?? '';
                    $item->formal_model_tool = $item->generator->model_tool ?? '';
                    $item->test_cases = $item->generator->test_case ?? '';
                    $item->system_formal = $item->generator->system_message ?? '';

                }

                $item->generator_type = $item->generator_type === 'App\Models\GeneratedFormalModel' ? 'Code generation' : 'Formal Model generation';
                $item->validation_process = json_encode($item->validation_process);
                $item->test_cases = json_encode($item->test_cases);
                return $item;
            });
    }

    public function map($row): array
    {
        return [
            $row->generator_type,
            $row->user_input,
            $row->first_code,
            $row->system_code,
            $row->programming_language,
            $row->code_LLM,
            $row->formal_model,
            $row->system_formal,
            $row->formal_model_tool,
            $row->formal_LLM,
            $row->validated_code,
            $row->system_message,
            $row->validation_process,
            $row->iteration,
            $row->llm_used->value,
            $row->test_cases,
            $row->test_result,
        ];
    }

    public function headings(): array
    {
        return [
            'Process start from',
            'User request',
            'First generated code',
            'Code system message',
            'Language',
            'LMM Code',
            'Formal model',
            'Formal system message',
            'Model tool',
            'LMM Formal',
            'Final validated code',
            'Validation system message',
            'Process',
            'Iteration',
            'LLM Valid.',
            'Generated test',
            'Test result',
        ];
    }
}
