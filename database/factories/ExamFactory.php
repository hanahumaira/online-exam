<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'created_by' => User::factory()->lecturer(),
            'title' => fake()->sentence(4),
            'instructions' => fake()->paragraph(),
            'duration_minutes' => fake()->numberBetween(15, 180),
            'published_at' => null,
            'results_released_at' => null,
        ];
    }
}
