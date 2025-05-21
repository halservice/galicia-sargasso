<?php

namespace App\Traits;

use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;

trait DataPreparation
{
    protected function prepareData(GeneratedValidatedCode $item): GeneratedValidatedCode
    {
        // Get the data if the process starts from the formal model generation.
        if ($item->generator instanceof GeneratedCode) {
            $this->prepareFromFormalModel($item);
        }

        // Get the data if the process starts from the code generation.
        if ($item->generator instanceof GeneratedFormalModel) {
            $this->prepareFromCodeGeneration($item);
        }

        $item->generator_type = $item->generator_type === 'App\Models\GeneratedFormalModel'
            ? 'Code generation'
            : 'Formal Model generation';

        return $item;
    }

    protected function prepareFromFormalModel(GeneratedValidatedCode $item): void
    {
        $formalModel = $item->generator->formalModel ?? null;

        $item->user_input = $formalModel->requirement ?? '';
        $item->formal_model = $formalModel->generated_formal_model ?? '';
        $item->formal_LLM = $formalModel->llm_used ?? '';
        $item->formal_model_tool = $formalModel->model_tool ?? '';
        $item->test_cases = $item->test_case ?? '';
        $item->programming_language = $item->generator->programming_language->value ?? '';
        $item->first_code = $item->generator->generated_code ?? '';
        $item->code_LLM = $item->generator->llm_used ?? '';
    }

    protected function prepareFromCodeGeneration(GeneratedValidatedCode $item): void
    {
        $generatedCode = $item->generator->generatedCode ?? null;

        $item->programming_language = $generatedCode->programming_language->value ?? '';
        $item->user_input = $generatedCode->requirement ?? '';
        $item->first_code = $generatedCode->generated_code ?? '';
        $item->code_LLM = $generatedCode->llm_used ?? '';
        $item->formal_model = $item->generator->generated_formal_model ?? '';
        $item->formal_LLM = $item->generator->llm_used ?? '';
        $item->formal_model_tool = $item->generator->model_tool ?? '';
        $item->test_cases = $item->test_case ?? '';
    }

    public static function getHeadings(): array
    {
        return [
            'Process start from',
            'User request',
            'First generated code',
            'Language',
            'LMM Code',
            'Formal model',
            'Model tool',
            'LMM Formal',
            'Final validated code',
            'Iteration',
            'LLM Valid.',
            'Generated test',
            'Test result',
        ];
    }

    public static function getMap($row): array
    {
        return [
            $row->generator_type,
            $row->user_input,
            $row->first_code,
            $row->programming_language,
            $row->code_LLM,
            $row->formal_model,
            $row->formal_model_tool,
            $row->formal_LLM,
            $row->validated_code,
            $row->iteration,
            $row->llm_used->value,
            $row->test_cases,
            $row->test_result,
        ];
    }
}
