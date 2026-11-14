<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Hasil SAW satu pegawai. Skor, ranking, dan kategori kosong berarti data tidak cukup. */
class EvaluationResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_period_id',
        'employee_id',
        'raw_values',
        'normalized_values',
        'final_score',
        'ranking',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'raw_values' => 'array',
            'normalized_values' => 'array',
            'final_score' => 'decimal:6',
            'ranking' => 'integer',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(EvaluationPeriod::class, 'evaluation_period_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}