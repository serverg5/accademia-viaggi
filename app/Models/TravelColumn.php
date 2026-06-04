<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TravelColumn extends Model
{
    use HasFactory;

    public const TYPE_TEXT = 'text';
    public const TYPE_NUMBER = 'number';
    public const TYPE_SELECT = 'select';
    public const TYPE_DATE = 'date';
    public const TYPE_MONEY = 'money';
    public const TYPE_BOOLEAN = 'boolean';

    protected $fillable = [
        'key',
        'label',
        'type',
        'options',
        'is_required',
        'is_visible',
        'is_visible_in_billing',
        'is_system',
        'is_deletable',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
            'is_visible' => 'boolean',
            'is_visible_in_billing' => 'boolean',
            'is_system' => 'boolean',
            'is_deletable' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function selectOptions(): HasMany
    {
        return $this->hasMany(TravelSelectOption::class);
    }

    public function isSelect(): bool
    {
        return $this->type === self::TYPE_SELECT;
    }
}
