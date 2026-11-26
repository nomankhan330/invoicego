<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
    ];

    /**
     * Per-request in-memory cache of lightweight system settings (excludes large base64 blobs).
     * Eliminates 6-12 repeated DB queries per page load on remote Cloud MySQL.
     */
    protected static ?array $requestCache = null;

    protected static array $blobKeys = [
        'brand_logo_light_b64',
        'brand_logo_dark_b64',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => static::clearRequestCache());
        static::deleted(fn () => static::clearRequestCache());
    }

    public static function clearRequestCache(): void
    {
        static::$requestCache = null;
    }

    protected static function castValue(?string $value, ?string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode((string) $value, true),
            default => $value,
        };
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            // Large binary blobs are fetched on-demand only so normal pages stay blazing fast
            if (in_array($key, static::$blobKeys, true)) {
                $setting = static::where('key', $key)->first(['value', 'type']);
                return $setting ? static::castValue($setting->value, $setting->type) : $default;
            }

            if (static::$requestCache === null) {
                static::$requestCache = [];
                $rows = static::whereNotIn('key', static::$blobKeys)
                    ->toBase()
                    ->get(['key', 'value', 'type']);

                foreach ($rows as $row) {
                    static::$requestCache[$row->key] = static::castValue($row->value, $row->type);
                }
            }

            return array_key_exists($key, static::$requestCache)
                ? static::$requestCache[$key]
                : $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        $stringValue = is_array($value) ? json_encode($value) : (string) $value;

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'group' => $group,
                'type' => $type,
            ]
        );

        static::clearRequestCache();
    }
}
