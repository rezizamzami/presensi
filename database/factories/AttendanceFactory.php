<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        // Default: hadir tepat waktu, jadwal 08:00 sampai 17:00 WIB.
        return [
            'employee_id' => Employee::factory(),
            'work_date' => fake()->dateTimeBetween('2026-09-01', '2026-09-30')->format('Y-m-d'),
            'check_in_at' => fn (array $attrs) => self::wib($attrs['work_date'], '08:00'),
            'check_out_at' => fn (array $attrs) => self::wib($attrs['work_date'], '17:00'),
            'late_minutes' => 0,
            'early_leave_minutes' => 0,
            'status' => AttendanceStatus::Present,
        ];
    }

    // Menit terlambat dihitung dari jam masuk, bukan dari batas toleransi (PRD 4.1).
    public function late(int $minutes = 20): static
    {
        // Jam berupa fungsi agar dihitung setelah work_date final, termasuk bila ditimpa seeder.
        return $this->state([
            'status' => AttendanceStatus::Late,
            'late_minutes' => $minutes,
            'check_in_at' => fn (array $attrs) => self::wib($attrs['work_date'], '08:00')->addMinutes($minutes),
        ]);
    }

    public function earlyLeave(int $minutes = 30): static
    {
        return $this->state([
            'early_leave_minutes' => $minutes,
            'check_out_at' => fn (array $attrs) => self::wib($attrs['work_date'], '17:00')->subMinutes($minutes),
        ]);
    }

    // Lupa absen pulang: status tetap hadir atau terlambat (PRD 4.1).
    public function missingCheckOut(): static
    {
        return $this->state(['check_out_at' => null]);
    }

    public function absent(): static
    {
        return $this->state([
            'status' => AttendanceStatus::Absent,
            'check_in_at' => null,
            'check_out_at' => null,
        ]);
    }

    public function onLeave(AttendanceStatus $status = AttendanceStatus::Permission): static
    {
        return $this->state([
            'status' => $status,
            'check_in_at' => null,
            'check_out_at' => null,
        ]);
    }

    // Jam disimpan UTC, sedangkan jadwal ditulis dalam WIB.
    private static function wib(string $date, string $time): CarbonImmutable
    {
        return CarbonImmutable::parse("{$date} {$time}", 'Asia/Jakarta')->utc();
    }
}
