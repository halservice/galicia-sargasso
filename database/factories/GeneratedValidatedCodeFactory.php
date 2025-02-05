<?php

namespace Database\Factories;

use App\Enums\LLM;
use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GeneratedCode>
 */
class GeneratedValidatedCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $morphs = [
            GeneratedCode::class,
            GeneratedFormalModel::class,
        ];

        return [
            'generator_type' => Arr::random($morphs),
            'generator_id' => function (array $attrs) {
                return $attrs['generator_type']::factory()->create()->id;
            },
            'system_message' => fake()->sentence,
            'validation_process' => [
                'test' => 1,
            ],
            'test_result' => fake()->paragraph(),
            'llm_used' => LLM::random(),
            'iteration' => fake()->numberBetween(1, 5),
            'validated_code' => fake()->sentence,
        ];
    }

    public function code()
    {
        return $this->state([
            'generator_type' => GeneratedCode::class,
            'generator_id' => GeneratedCode::factory(),
        ]);
    }

    public function formalModel()
    {
        return $this->state([
            'generator_type' => GeneratedFormalModel::class,
            'generator_id' => GeneratedFormalModel::factory(),
        ]);
    }
}
