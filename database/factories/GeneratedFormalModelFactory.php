<?php

namespace Database\Factories;

use App\Enums\LLM;
use App\Enums\ModelTool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GeneratedCode>
 */
class GeneratedFormalModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'system_message' => fake()->sentence,
            'requirement' => fake()->sentence,
            'model_tool' => ModelTool::random(),
            'generated_formal_model' => fake()->sentence,
            'llm_used' => LLM::random(),
            'test_case' => fake()->sentence,
        ];
    }
}
