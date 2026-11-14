<?php

declare(strict_types=1);

namespace App\Enums;

/** Nilai string harus sama dengan isi kolom employees.status di database. */
enum EmployeeStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}