<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EvaluationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Satu periode evaluasi bulanan. Bobot dan pengaturan disalin ke snapshot agar hasil lama tidak berubah. */
class EvaluationPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'status',
        'weights_snapshot',
        'settings_snapshot',
        'run_at',
        'run_by',
        'finalized_at',
        'finalized_by',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'status' => EvaluationStatus::class,
            'weights_snapshot' => 'array',
            'settings_snapshot' => 'array',
            'run_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    public function results(): HasMany
    {
        return $this->hasMany(EvaluationResult::class);
    }

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'run_by');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }
}
