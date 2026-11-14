<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\HolidayType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'name',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'type' => HolidayType::class,
        ];
    }
}