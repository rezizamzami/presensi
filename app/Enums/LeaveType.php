<?php

declare(strict_types=1);

namespace App\Enums;

/** Nilai string harus sama dengan isi kolom leave_requests.type di database. */
enum LeaveType: string
{
    case Permission = 'permission';
    case Sick = 'sick';

    /** Izin yang disetujui menjadi baris absensi dengan status yang sama (PRD 4.1). */
    public function toAttendanceStatus(): AttendanceStatus
    {
        return match ($this) {
            self::Permission => AttendanceStatus::Permission,
            self::Sick => AttendanceStatus::Sick,
        };
    }
}
