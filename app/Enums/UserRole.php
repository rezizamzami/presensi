<?php

declare(strict_types=1);

namespace App\Enums;

/** Nilai string harus sama dengan isi kolom users.role di database. */
enum UserRole: string
{
    case Admin = 'admin';
    case Employee = 'employee';
}
