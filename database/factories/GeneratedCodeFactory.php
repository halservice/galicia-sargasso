<?php

namespace Database\Factories;

use App\Enums\LLM;
use App\Enums\ProgrammingLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GeneratedCode>
 */
class GeneratedCodeFactory extends Factory
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
            'generated_code' => fake()->paragraph(),
            'programming_language' => ProgrammingLanguage::random(),
            'llm_used' => LLM::random(),
        ];
    }
}
