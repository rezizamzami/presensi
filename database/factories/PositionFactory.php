<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    public function definition(): array
    {
        // Nama jabatan unik hanya per departemen, tapi dibuat unik global agar aman dipakai berulang.
        return [
            'department_id' => Department::factory(),
            'name' => 'Jabatan '.fake()->unique()->numerify('###'),
        ];
    }
}