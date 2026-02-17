<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarberPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'barber_id',
        'amount',
        'date',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function barber()
    {
        return $this->belongsTo(User::class, 'barber_id');
    }
}
