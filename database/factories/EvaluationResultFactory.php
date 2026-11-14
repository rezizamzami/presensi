<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EvaluationPeriod;
use App\Models\EvaluationResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EvaluationResult>
 */
class EvaluationResultFactory extends Factory
{
    public function definition(): array
    {
        // Default: data tidak cukup (skor, ranking, kategori kosong). Nilai asli dihitung modul SAW.
        return [
            'evaluation_period_id' => EvaluationPeriod::factory(),
            'employee_id' => Employee::factory(),
            'raw_values' => [],
            'normalized_values' => [],
            'final_score' => null,
            'ranking' => null,
            'category' => null,
        ];
    }
}