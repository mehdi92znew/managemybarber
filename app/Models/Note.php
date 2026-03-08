<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        'shop_id',
        'author_id',
        'barber_id',
        'content',
        'type',
        'date',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function barber()
    {
        return $this->belongsTo(User::class, 'barber_id');
    }
}
