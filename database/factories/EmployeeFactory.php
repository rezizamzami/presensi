<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EmployeeStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Default belum punya akun, sesuai PRD 4.1. Pakai withAccount() bila perlu akun.
            'user_id' => null,
            'employee_number' => 'P-'.fake()->unique()->numerify('####'),
            'full_name' => fake()->name(),
            'department_id' => Department::factory(),
            // Jabatan diambil dari departemen yang sama, karena jabatan terikat ke satu departemen.
            'position_id' => fn (array $attributes) => Position::factory()
                ->create(['department_id' => $attributes['department_id']])->id,
            'work_schedule_id' => WorkSchedule::factory(),
            'join_date' => fake()->dateTimeBetween('2024-01-01', '2026-06-30')->format('Y-m-d'),
            'end_date' => null,
            'status' => EmployeeStatus::Active,
        ];
    }

    public function withAccount(): static
    {
        return $this->state(fn () => ['user_id' => User::factory()]);
    }

    public function resigned(string $endDate): static
    {
        return $this->state([
            'end_date' => $endDate,
            'status' => EmployeeStatus::Inactive,
        ]);
    }
}
