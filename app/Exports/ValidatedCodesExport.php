<?php

namespace App\Exports;

use App\Enums\LLM;
use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ValidatedCodesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection(): \Illuminate\Support\Collection
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
                $item->test_cases = json_encode($item->test_cases, JSON_PRETTY_PRINT);
                return $item;
            });
    }

    public function map($item): array
    {
        return [
            $item->generator_type,
            $item->user_input,
            $item->first_code,
            $item->system_code,
            $item->programming_language,
            $item->code_LLM,
            $item->formal_model,
            $item->system_formal,
            $item->formal_model_tool,
            $item->formal_LLM,
            $item->validated_code,
            $item->system_message,
            $item->validation_process,
            $item->iteration,
            $item->llm_used->value,
            $item->test_cases,
            $item->test_result,
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
