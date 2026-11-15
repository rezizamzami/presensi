<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\EmployeeStatus;
use App\Enums\HolidayType;
use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\AbsentRun;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Position;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/** Data contoh untuk demo dan pengembangan. Tidak dipanggil DatabaseSeeder dan menolak jalan di production. */
class DemoSeeder extends Seeder
{
    private const PERIOD_START = '2026-09-01';

    // Perintah alpa hanya sudah memproses sampai tanggal ini. November sengaja belum lengkap.
    private const ABSENT_RUN_END = '2026-10-31';

    private const PERIOD_END = '2026-11-13';

    private const RANDOM_EMPLOYEES = 47;

    private const DEPARTMENTS = [
        'TKN' => 'Teknis',
        'KEU' => 'Keuangan',
        'UMM' => 'Umum',
        'SDM' => 'SDM',
        'PRC' => 'Perencanaan',
    ];

    private const POSITIONS = ['Staf pelaksana', 'Analis', 'Kepala seksi'];

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('DemoSeeder tidak boleh dijalankan di production.');
        }

        if (User::where('email', 'admin@presensi.test')->exists()) {
            throw new RuntimeException('Data demo sudah ada. Kosongkan dulu dengan: php artisan migrate:fresh --seed');
        }

        $schedule = WorkSchedule::where('is_default', true)->first()
            ?? throw new RuntimeException('Jadwal default belum ada. Jalankan php artisan db:seed lebih dulu.');

        // Seed tetap supaya data sama setiap kali dijalankan. mt_rand ikut terkunci oleh seed ini.
        fake()->seed(2026);

        DB::transaction(function () use ($schedule): void {
            $admin = User::factory()->admin()->create(['name' => 'Admin utama', 'email' => 'admin@presensi.test']);
            $holidays = $this->seedHolidays();
            $workDays = $schedule->work_days;
            $workdayList = $this->workdaysBetween(self::PERIOD_START, '2026-10-30', $workDays, $holidays);

            $items = $this->seedEmployees($this->seedStructure(), $workdayList);
            $coverage = $this->seedLeaves($items, $admin, $workDays, $holidays);
            $rows = $this->seedAttendance($items, $coverage, $schedule, $workDays, $holidays);

            $this->command?->info(sprintf(
                'Demo siap: %d pegawai, %d baris absensi. Admin: admin@presensi.test (Admin#2026), pegawai: Pegawai#2026.',
                count($items),
                $rows,
            ));
        });
    }

    /** @return array<string, true> */
    private function seedHolidays(): array
    {
        $path = resource_path('data/holidays/2026.json');
        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        foreach ($data['holidays'] as $row) {
            Holiday::firstOrCreate(
                ['date' => $row['date']],
                ['name' => $row['name'], 'type' => HolidayType::from($row['type'])],
            );
        }

        Holiday::firstOrCreate(
            ['date' => '2026-09-15'],
            ['name' => 'Libur khusus contoh', 'type' => HolidayType::Custom],
        );

        return Holiday::pluck('date')
            ->map(fn ($date) => substr((string) $date, 0, 10))
            ->flip()
            ->map(fn () => true)
            ->all();
    }

    /** @return list<array{department: Department, positions: list<Position>}> */
    private function seedStructure(): array
    {
        $structure = [];

        // Lewat factory karena tidak bergantung pada daftar fillable model.
        foreach (self::DEPARTMENTS as $code => $name) {
            $department = Department::factory()->create(['name' => $name, 'code' => $code]);
            $positions = [];

            foreach (self::POSITIONS as $positionName) {
                $positions[] = Position::factory()->create([
                    'department_id' => $department->id,
                    'name' => $positionName,
                ]);
            }

            $structure[] = ['department' => $department, 'positions' => $positions];
        }

        return $structure;
    }

    /** @return list<array{employee: Employee, user: ?User, profile: array}> */
    private function seedEmployees(array $structure, array $workdayList): array
    {
        $schedule = WorkSchedule::where('is_default', true)->firstOrFail();

        $profiles = $this->specialProfiles();
        for ($i = 0; $i < self::RANDOM_EMPLOYEES; $i++) {
            $profiles[] = $this->randomProfile($i, $workdayList);
        }

        $items = [];
        $usedEmails = [];
        $mustChange = [];

        foreach ($profiles as $index => $profile) {
            $name = $profile['name'] ?? fake()->firstName().' '.fake()->lastName();
            $group = $structure[$index % count($structure)];
            $position = $group['positions'][mt_rand(0, count($group['positions']) - 1)];

            $user = null;
            if ($profile['account']) {
                $user = User::factory()->create([
                    'name' => $name,
                    'email' => $this->uniqueEmail($name, $usedEmails),
                ]);

                if ($profile['must']) {
                    $mustChange[] = $user->id;
                }
            }

            $employee = Employee::create([
                'user_id' => $user?->id,
                'employee_number' => sprintf('P-%04d', $index + 1),
                'full_name' => $name,
                'department_id' => $group['department']->id,
                'position_id' => $position->id,
                'work_schedule_id' => $schedule->id,
                'join_date' => $profile['join']
                    ?? CarbonImmutable::parse('2024-01-01')->addDays(mt_rand(0, 911))->format('Y-m-d'),
                'end_date' => $profile['end'],
                'status' => $profile['end'] !== null ? EmployeeStatus::Inactive : EmployeeStatus::Active,
            ]);

            $items[] = ['employee' => $employee, 'user' => $user, 'profile' => $profile];
        }

        // Akun demo langsung bisa dipakai. Hanya akun baru yang wajib ganti password.
        User::query()->update(['must_change_password' => false]);
        if ($mustChange !== []) {
            User::whereIn('id', $mustChange)->update(['must_change_password' => true]);
        }

        return $items;
    }

    /** Profil kasus tepi PRD 5.6. Sisanya acak. */
    private function specialProfiles(): array
    {
        return [
            // Akun demo utama, sama dengan prototipe.
            $this->profile(['name' => 'Rizky Fadhillah', 'late' => 0.05, 'early' => 0.03, 'absent' => 0.0, 'missing' => 0.0]),
            // Hadir 0 padahal hari kerja efektif lebih dari 0.
            $this->profile(['late' => 0.0, 'early' => 0.0, 'absent' => 1.0, 'missing' => 0.0]),
            // Baru bergabung di tengah Oktober, akun baru wajib ganti password.
            $this->profile(['join' => '2026-10-19', 'must' => true]),
            // Berhenti di tengah Oktober.
            $this->profile(['end' => '2026-10-14']),
            // Belum punya akun: tidak ikut alpa otomatis dan evaluasi.
            $this->profile(['account' => false, 'join' => '2026-10-12']),
            $this->profile(['account' => false, 'join' => '2025-03-03']),
            // Sering lupa absen pulang.
            $this->profile(['missing' => 0.35]),
            // Sering terlambat.
            $this->profile(['late' => 0.65]),
            // Sering pulang cepat.
            $this->profile(['early' => 0.5]),
            // Banyak alpa.
            $this->profile(['absent' => 0.2]),
            // Sempurna.
            $this->profile(['late' => 0.0, 'early' => 0.0, 'absent' => 0.0, 'missing' => 0.0]),
            // Izin sakit disetujui setelah pegawai sudah hadir di hari pertama (baris hadir dipertahankan).
            $this->profile(['leaves' => [[
                'type' => LeaveType::Sick, 'start' => '2026-09-22', 'end' => '2026-09-23',
                'status' => LeaveStatus::Approved, 'attended' => ['2026-09-22'],
            ]]]),
            // Satu pegawai dengan izin menunggu, ditolak, dan dibatalkan.
            $this->profile(['absent' => 0.0, 'leaves' => [
                ['type' => LeaveType::Permission, 'start' => '2026-11-16', 'end' => '2026-11-17', 'status' => LeaveStatus::Pending],
                ['type' => LeaveType::Permission, 'start' => '2026-10-06', 'end' => '2026-10-06', 'status' => LeaveStatus::Rejected],
                ['type' => LeaveType::Sick, 'start' => '2026-10-20', 'end' => '2026-10-21', 'status' => LeaveStatus::Cancelled],
            ]]),
        ];
    }

    private function randomProfile(int $i, array $workdayList): array
    {
        $profile = $this->profile([
            'late' => $this->between(0.02, 0.25),
            'early' => $this->between(0.0, 0.15),
            'absent' => $this->between(0.0, 0.06),
            'missing' => $this->between(0.0, 0.04),
        ]);

        if ($i % 8 === 0) {
            $date = $workdayList[mt_rand(0, count($workdayList) - 1)];
            $profile['leaves'][] = [
                'type' => mt_rand(0, 1) === 0 ? LeaveType::Permission : LeaveType::Sick,
                'start' => $date,
                'end' => $date,
                'status' => LeaveStatus::Approved,
            ];
        }

        return $profile;
    }

    private function profile(array $overrides = []): array
    {
        return $overrides + [
            'name' => null,
            'join' => null,
            'end' => null,
            'account' => true,
            'must' => false,
            'late' => 0.08,
            'early' => 0.05,
            'absent' => 0.02,
            'missing' => 0.02,
            'leaves' => [],
        ];
    }

    /**
     * Membuat pengajuan izin dan peta hari yang ditimpa izin.
     *
     * @return array<int, array<string, array>>
     */
    private function seedLeaves(array $items, User $admin, array $workDays, array $holidays): array
    {
        $coverage = [];

        foreach ($items as $item) {
            $employee = $item['employee'];

            foreach ($item['profile']['leaves'] as $leave) {
                $status = $leave['status'];
                $reviewed = $status !== LeaveStatus::Pending;

                $request = LeaveRequest::create([
                    'employee_id' => $employee->id,
                    'type' => $leave['type'],
                    'start_date' => $leave['start'],
                    'end_date' => $leave['end'],
                    'reason' => fake()->sentence(4),
                    'status' => $status,
                    'reviewed_by' => $reviewed ? $admin->id : null,
                    'reviewed_at' => $reviewed ? now() : null,
                    'reject_reason' => $status === LeaveStatus::Rejected ? fake()->sentence(4) : null,
                ]);

                if (! in_array($status, [LeaveStatus::Approved, LeaveStatus::Cancelled], true)) {
                    continue;
                }

                $end = CarbonImmutable::parse($leave['end']);
                for ($d = CarbonImmutable::parse($leave['start']); $d->lte($end); $d = $d->addDay()) {
                    if (! $this->isWorkday($d, $workDays, $holidays)) {
                        continue;
                    }

                    $date = $d->format('Y-m-d');
                    $coverage[$employee->id][$date] = match (true) {
                        // Izin dibatalkan: hari yang sudah lewat kembali menjadi alpa (PRD 4.1).
                        $status === LeaveStatus::Cancelled => ['mode' => 'absent'],
                        in_array($date, $leave['attended'] ?? [], true) => ['mode' => 'attended'],
                        default => [
                            'mode' => 'leave',
                            'status' => $leave['type']->toAttendanceStatus(),
                            'leave_id' => $request->id,
                        ],
                    };
                }
            }
        }

        return $coverage;
    }

    private function seedAttendance(array $items, array $coverage, WorkSchedule $schedule, array $workDays, array $holidays): int
    {
        $sched = [
            'start' => $this->minutes($schedule->start_time),
            'end' => $this->minutes($schedule->end_time),
            'tolerance' => (int) $schedule->late_tolerance_minutes,
        ];

        $now = now()->format('Y-m-d H:i:s');
        $rows = [];
        $absentPerDate = [];

        for ($d = CarbonImmutable::parse(self::PERIOD_START); $d->lte(CarbonImmutable::parse(self::PERIOD_END)); $d = $d->addDay()) {
            $date = $d->format('Y-m-d');
            $absentPerDate[$date] = 0;

            if (! $this->isWorkday($d, $workDays, $holidays)) {
                continue;
            }

            foreach ($items as $item) {
                $row = $this->attendanceRow($item, $date, $coverage[$item['employee']->id][$date] ?? null, $sched, $now);

                if ($row === null) {
                    continue;
                }

                if ($row['status'] === AttendanceStatus::Absent->value) {
                    $absentPerDate[$date]++;
                }

                $rows[] = $row;
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Attendance::insert($chunk);
        }

        // Penanda per tanggal, termasuk akhir pekan dan hari libur (PRD 4.1).
        foreach ($absentPerDate as $date => $count) {
            if ($date > self::ABSENT_RUN_END) {
                break;
            }

            AbsentRun::create([
                'work_date' => $date,
                'processed_at' => $this->utc($date, 23 * 60 + 55),
                'employee_count' => $count,
            ]);
        }

        return count($rows);
    }

    private function attendanceRow(array $item, string $date, ?array $cover, array $sched, string $now): ?array
    {
        $employee = $item['employee'];
        $profile = $item['profile'];

        if ($item['user'] === null) {
            return null;
        }

        if ($date < $employee->join_date->format('Y-m-d')) {
            return null;
        }

        if ($employee->end_date !== null && $date > $employee->end_date->format('Y-m-d')) {
            return null;
        }

        $row = [
            'employee_id' => $employee->id,
            'work_date' => $date,
            'check_in_at' => null,
            'check_out_at' => null,
            'late_minutes' => 0,
            'early_leave_minutes' => 0,
            'status' => null,
            'leave_request_id' => null,
            'is_manual' => false,
            'correction_reason' => null,
            'corrected_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $mode = $cover['mode'] ?? 'normal';

        if ($mode === 'leave') {
            return [...$row, 'status' => $cover['status']->value, 'leave_request_id' => $cover['leave_id']];
        }

        if ($mode === 'absent' || ($mode === 'normal' && $this->chance($profile['absent']))) {
            // Setelah tanggal ini perintah alpa belum berjalan, jadi barisnya memang belum ada.
            return $date > self::ABSENT_RUN_END ? null : [...$row, 'status' => AttendanceStatus::Absent->value];
        }

        if ($this->chance($profile['late'])) {
            $late = mt_rand($sched['tolerance'] + 1, 75);
            $status = AttendanceStatus::Late;
            $in = $sched['start'] + $late;
        } else {
            // Dalam toleransi tetap hadir dengan menit terlambat 0 (PRD 4.1).
            $late = 0;
            $status = AttendanceStatus::Present;
            $in = $sched['start'] + mt_rand(-25, $sched['tolerance']);
        }

        $early = 0;
        if ($this->chance($profile['missing'])) {
            $out = null;
        } elseif ($this->chance($profile['early'])) {
            $early = mt_rand(10, 90);
            $out = $sched['end'] - $early;
        } else {
            $out = $sched['end'] + mt_rand(0, 30);
        }

        return [
            ...$row,
            'check_in_at' => $this->utc($date, $in),
            'check_out_at' => $out === null ? null : $this->utc($date, $out),
            'late_minutes' => $late,
            'early_leave_minutes' => $early,
            'status' => $status->value,
        ];
    }

    /** @return list<string> */
    private function workdaysBetween(string $from, string $to, array $workDays, array $holidays): array
    {
        $days = [];
        $end = CarbonImmutable::parse($to);

        for ($d = CarbonImmutable::parse($from); $d->lte($end); $d = $d->addDay()) {
            if ($this->isWorkday($d, $workDays, $holidays)) {
                $days[] = $d->format('Y-m-d');
            }
        }

        return $days;
    }

    private function isWorkday(CarbonImmutable $date, array $workDays, array $holidays): bool
    {
        return in_array($date->dayOfWeekIso, $workDays, true) && ! isset($holidays[$date->format('Y-m-d')]);
    }

    private function uniqueEmail(string $name, array &$used): string
    {
        $base = Str::slug($name, '.');
        $email = $base.'@presensi.test';

        for ($n = 2; isset($used[$email]); $n++) {
            $email = $base.'.'.$n.'@presensi.test';
        }

        $used[$email] = true;

        return $email;
    }

    private function minutes(string $time): int
    {
        [$hour, $minute] = explode(':', $time);

        return (int) $hour * 60 + (int) $minute;
    }

    // Jam jadwal ditulis dalam WIB, database menyimpan UTC.
    private function utc(string $date, int $minutes): string
    {
        return CarbonImmutable::parse($date.' 00:00', 'Asia/Jakarta')
            ->addMinutes($minutes)
            ->utc()
            ->format('Y-m-d H:i:s');
    }

    private function chance(float $probability): bool
    {
        return mt_rand() / (mt_getrandmax() + 1) < $probability;
    }

    private function between(float $min, float $max): float
    {
        return round($min + ($max - $min) * (mt_rand() / (mt_getrandmax() + 1)), 3);
    }
}
