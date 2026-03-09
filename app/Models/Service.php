<?php

namespace App\Models;

use App\Models\Scopes\ShopScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'name',
        'price',
        'duration_minutes',
        'is_extra',
        'has_special_commission',
        'commission_type',
        'commission_value',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_extra' => 'boolean',
        'is_active' => 'boolean',
        'has_special_commission' => 'boolean',
        'commission_value' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ShopScope);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
