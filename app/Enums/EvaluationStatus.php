<?php

declare(strict_types=1);

namespace App\Enums;

/** Nilai string harus sama dengan isi kolom evaluation_periods.status di database. */
enum EvaluationStatus: string
{
    case Draft = 'draft';
    case Final = 'final';
}