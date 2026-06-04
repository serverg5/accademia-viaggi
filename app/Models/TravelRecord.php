<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'travel_year_id',
        'year',
        'month',
        'record_date',
        'practice_code',
        'values',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'record_date' => 'date',
            'values' => 'array',
        ];
    }

    public function travelYear(): BelongsTo
    {
        return $this->belongsTo(TravelYear::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
