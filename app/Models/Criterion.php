<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CriterionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Kriteria SAW. Tipe terkunci mengikuti metrik sumber, admin hanya mengubah bobot dan status aktif. */
class Criterion extends Model
{
    use HasFactory;

    // Bentuk jamak "criteria" tidak bisa ditebak Laravel dari nama class.
    protected $table = 'criteria';

    protected $fillable = [
        'code',
        'name',
        'type',
        'source_metric',
        'weight',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => CriterionType::class,
            'weight' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
