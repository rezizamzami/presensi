<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('2026-09-01', '2026-09-30')->format('Y-m-d');

        return [
            'employee_id' => Employee::factory(),
            'type' => LeaveType::Permission,
            'start_date' => $date,
            'end_date' => $date,
            'reason' => fake()->sentence(),
            'status' => LeaveStatus::Pending,
        ];
    }

    public function sick(): static
    {
        return $this->state(['type' => LeaveType::Sick]);
    }

    public function approved(): static
    {
        return $this->state([
            'status' => LeaveStatus::Approved,
            'reviewed_by' => User::factory()->admin(),
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => LeaveStatus::Rejected,
            'reviewed_by' => User::factory()->admin(),
            'reviewed_at' => now(),
            'reject_reason' => fake()->sentence(),
        ]);
    }
}
