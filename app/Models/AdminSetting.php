<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type'];

    public static function get($key, $default = null)
    {
        $setting = self::where('key', '=', $key)->first();
        if (!$setting) return $default;

        if ($setting->type === 'boolean') return (bool)$setting->value;
        if ($setting->type === 'integer') return (int)$setting->value;
        if ($setting->type === 'json') return json_decode($setting->value, true);
        
        return $setting->value;
    }

    public static function set($key, $value, $type = 'string')
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : $value, 'type' => $type]
        );
    }
}
