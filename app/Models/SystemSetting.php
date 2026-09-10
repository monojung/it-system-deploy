<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class SystemSetting extends Model
{
    use HasFactory, Auditable;

    protected $table = 'it_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true) ?: $default,
            default => $setting->value,
        };
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'text', ?string $description = null): static
    {
        $valToStore = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $valToStore,
                'group' => $group,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    public static function getGroup(string $group): array
    {
        $settings = static::where('group', $group)->get();
        $result = [];
        foreach ($settings as $s) {
            $result[$s->key] = static::get($s->key);
        }
        return $result;
    }
}
