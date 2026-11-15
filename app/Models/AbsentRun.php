<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Penanda bahwa perintah alpa otomatis sudah memproses satu tanggal, termasuk hari libur. */
class AbsentRun extends Model
{
    protected $fillable = [
        'work_date',
        'processed_at',
        'employee_count',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'processed_at' => 'datetime',
            'employee_count' => 'integer',
        ];
    }
}
