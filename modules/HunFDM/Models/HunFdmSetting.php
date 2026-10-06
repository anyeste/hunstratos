<?php

namespace Modules\HunFDM\Models;

use Illuminate\Database\Eloquent\Model;

class HunFdmSetting extends Model
{
    protected $table = 'hun_fdm_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'label',
        'group',
    ];

    /**
     * Retrieve a setting value, cast to its declared type.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'float'   => (float) $setting->value,
            'json'    => json_decode($setting->value, true),
            default   => $setting->value,
        };
    }

    /**
     * Set a setting value (persists immediately).
     */
    public static function set(string $key, mixed $value): void
    {
        $encoded = is_array($value) ? json_encode($value) : (string) $value;
        static::where('key', $key)->update(['value' => $encoded]);
    }

    /**
     * Return all settings as a flat key => (cast value) array.
     */
    public static function allCast(): array
    {
        return static::all()->mapWithKeys(function ($s) {
            return [$s->key => static::get($s->key)];
        })->all();
    }
}
