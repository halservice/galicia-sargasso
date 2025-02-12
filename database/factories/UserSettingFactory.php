<?php

namespace Database\Factories;

use App\Enums\LLM;
use App\Enums\ModelTool;
use App\Enums\ProgrammingLanguage;
use App\Enums\Sequence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GeneratedCode>
 */
class UserSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'programming_language' => ProgrammingLanguage::random(),
            'model_tool' => ModelTool::random(),
            'llm_code' => LLM::random(),
            'llm_formal' => LLM::random(),
            'llm_validation' => LLM::random(),
            'iteration' => fake()->numberBetween(1, 5),
            'sequence' => Sequence::random(),
        ];
    }
}
