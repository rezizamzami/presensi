<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Jadwal jam kerja yang dipakai pegawai untuk menentukan terlambat dan pulang cepat. */
class WorkSchedule extends Model
{
    use HasFactory;

    // default_marker kolom virtual yang dihitung database, jadi tidak fillable.
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'late_tolerance_minutes',
        'work_days',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'late_tolerance_minutes' => 'integer',
            'work_days' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}