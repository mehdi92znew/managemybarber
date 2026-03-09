<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shop;

class CashDrawer extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'date',
        'starting_cash',
        'closed_at',
        'closing_cash',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'closed_at' => 'datetime',
        'starting_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
