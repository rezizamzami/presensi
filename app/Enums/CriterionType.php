<?php

declare(strict_types=1);

namespace App\Enums;

/** Nilai string harus sama dengan isi kolom criteria.type di database. */
enum CriterionType: string
{
    case Benefit = 'benefit';
    case Cost = 'cost';
}