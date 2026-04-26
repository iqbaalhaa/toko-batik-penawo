<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function all_assoc(): array
    {
        return Cache::remember('site_settings.all', 600, function () {
            return self::query()->pluck('value', 'key')->toArray();
        });
    }

    public static function get(string $key, $default = null)
    {
        $all = self::all_assoc();
        return $all[$key] ?? $default;
    }

    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            self::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('site_settings.all');
    }
}
