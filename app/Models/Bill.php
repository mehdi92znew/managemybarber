<?php

namespace App\Models;

use App\Models\Scopes\ShopScope;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new ShopScope);
    }
    protected $fillable = [
        'shop_id',
        'amount',
        'date',
        'type',
        'note',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
