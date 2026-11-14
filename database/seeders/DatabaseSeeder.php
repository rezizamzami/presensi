<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CriterionType;
use App\Models\Criterion;
use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;

/** Data wajib agar aplikasi bisa jalan. Aman dijalankan ulang dan tidak menimpa ubahan admin. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCriteria();
        $this->seedDefaultSchedule();
    }

    private function seedCriteria(): void
    {
        // Bobot sementara sampai ada hasil AHP (PRD 5.2).
        $criteria = [
            ['code' => 'C1', 'name' => 'Kehadiran', 'source_metric' => 'attendance_rate', 'weight' => 50],
            ['code' => 'C2', 'name' => 'Ketepatan masuk', 'source_metric' => 'on_time_rate', 'weight' => 30],
            ['code' => 'C3', 'name' => 'Ketepatan pulang', 'source_metric' => 'no_early_leave_rate', 'weight' => 20],
        ];

        foreach ($criteria as $index => $criterion) {
            // firstOrCreate agar bobot yang sudah diubah admin tidak kembali ke awal.
            Criterion::firstOrCreate(
                ['code' => $criterion['code']],
                [
                    ...$criterion,
                    'type' => CriterionType::Benefit,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }

    private function seedDefaultSchedule(): void
    {
        // Dicek lewat is_default, bukan nama, karena admin boleh mengganti nama jadwal.
        if (WorkSchedule::where('is_default', true)->exists()) {
            return;
        }

        WorkSchedule::create([
            'name' => 'Reguler',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'late_tolerance_minutes' => 15,
            'work_days' => [1, 2, 3, 4, 5],
            'is_default' => true,
        ]);
    }
}