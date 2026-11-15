<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CriterionType;
use App\Models\Criterion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Criterion>
 */
class CriterionFactory extends Factory
{
    public function definition(): array
    {
        // Kode dan metrik sumber unik di database. Data asli lewat DatabaseSeeder, ini hanya untuk tes.
        return [
            'code' => 'T'.fake()->unique()->numerify('####'),
            'name' => 'Kriteria '.fake()->unique()->numerify('###'),
            'type' => CriterionType::Benefit,
            'source_metric' => 'metric_'.fake()->unique()->numerify('####'),
            'weight' => 0,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
