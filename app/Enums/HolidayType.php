<?php

declare(strict_types=1);

namespace App\Enums;

/** Nilai string harus sama dengan isi kolom holidays.type di database. */
enum HolidayType: string
{
    case National = 'national';
    case Collective = 'collective';
    case Custom = 'custom';
}