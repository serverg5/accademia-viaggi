<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TravelYear extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'year',
        'status',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'is_locked' => 'boolean',
        ];
    }

    public function records(): HasMany
    {
        return $this->hasMany(TravelRecord::class);
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }
}
