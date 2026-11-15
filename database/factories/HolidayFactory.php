<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\HolidayType;
use App\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Holiday>
 */
class HolidayFactory extends Factory
{
    public function definition(): array
    {
        // Tanggal unik di database, jadi dibuat unik juga di sini.
        return [
            'date' => fake()->unique()->dateTimeBetween('2026-01-01', '2026-12-31')->format('Y-m-d'),
            'name' => ucfirst(fake()->words(3, true)),
            'type' => HolidayType::National,
        ];
    }

    public function custom(): static
    {
        return $this->state(['type' => HolidayType::Custom]);
    }
}
