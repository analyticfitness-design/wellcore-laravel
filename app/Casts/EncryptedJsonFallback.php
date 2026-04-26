<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;

/**
 * Encrypts on write; on read tries decrypt, falls back to plain JSON for legacy rows.
 * Needed for PaymentLog.payload which existed as plaintext before this hardening pass.
 */
class EncryptedJsonFallback implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }

        try {
            return json_decode(Crypt::decryptString($value), true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException) {
            return json_decode($value, true);
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }

        return Crypt::encryptString(json_encode($value));
    }
}
