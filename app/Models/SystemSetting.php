<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("system_setting.{$key}", 3600, function () use ($key) {
            $row = static::query()->where('key', $key)->first();

            if (! $row) {
                return null;
            }

            // Ne jamais mettre un modèle Eloquent en cache (évite __PHP_Incomplete_Class).
            return [
                'type' => $row->type,
                'value' => $row->value,
            ];
        });

        if ($setting === null) {
            return $default;
        }

        return match ($setting['type'] ?? 'string') {
            'integer' => (int) $setting['value'],
            'boolean' => filter_var($setting['value'], FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting['value'] ?? 'null', true),
            default => $setting['value'] ?? $default,
        };
    }

    public static function forgetCache(string $key): void
    {
        Cache::forget("system_setting.{$key}");
    }
}
