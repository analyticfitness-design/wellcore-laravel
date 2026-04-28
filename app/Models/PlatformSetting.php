<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $table = 'platform_settings';

    protected $fillable = ['section', 'key', 'value', 'is_secret'];

    protected $casts = ['is_secret' => 'boolean'];

    public static function getSectionMap(string $section): array
    {
        return static::where('section', $section)
            ->pluck('value', 'key')
            ->toArray();
    }

    public static function setSectionValues(string $section, array $values, array $secrets = []): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(
                ['section' => $section, 'key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value, 'is_secret' => in_array($key, $secrets)]
            );
        }
    }

    public static function maskSecret(?string $value): string
    {
        if (empty($value)) {
            return '';
        }
        $last4 = substr($value, -4);
        return str_repeat('•', 8) . $last4;
    }
}
