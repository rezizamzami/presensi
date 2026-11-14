<?php

declare(strict_types=1);

namespace App\Enums;

/** Nilai string harus sama dengan isi kolom leave_requests.status di database. */
enum LeaveStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
}