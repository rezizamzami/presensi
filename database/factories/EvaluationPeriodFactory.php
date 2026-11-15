<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EvaluationStatus;
use App\Models\EvaluationPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EvaluationPeriod>
 */
class EvaluationPeriodFactory extends Factory
{
    public function definition(): array
    {
        // month harus tanggal 1 dan unik. Semua bulan 2024 sampai 2026 ada 36 pilihan.
        return [
            'month' => fake()->unique()->dateTimeBetween('2024-01-01', '2026-12-31')->format('Y-m-01'),
            'status' => EvaluationStatus::Draft,
        ];
    }

    public function final(): static
    {
        return $this->state([
            'status' => EvaluationStatus::Final,
            'finalized_at' => now(),
        ]);
    }
}
