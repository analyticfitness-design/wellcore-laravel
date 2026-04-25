<?php

namespace App\Models;

use App\Enums\UserType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_type',
    'user_id',
    'type',
    'title',
    'body',
    'link',
    'read_at',
])]
class WellcoreNotification extends Model
{
    protected $table = 'notifications';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'user_type' => UserType::class,
            'read_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Decode \uXXXX literal escapes that may have been stored if the text was
     * serialized with json_encode() before being saved (double-encoding bug).
     * Fast-path: skips strings that do not contain the \u sequence at all.
     */
    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $this->decodeUnicodeEscapes($value),
        );
    }

    protected function body(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? $this->decodeUnicodeEscapes($value) : $value,
        );
    }

    private function decodeUnicodeEscapes(string $value): string
    {
        if (!str_contains($value, '\\u')) {
            return $value;
        }
        return (string) preg_replace_callback(
            '/\\\\u([0-9a-fA-F]{4})/i',
            fn ($m) => mb_chr(hexdec($m[1]), 'UTF-8'),
            $value,
        );
    }
}
