<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'is_encrypted',
        'description',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    /**
     * Get a setting value by key
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        $value = $setting->value;

        // Decrypt if encrypted
        if ($setting->is_encrypted && $value) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, mixed $value, bool $encrypt = false, ?string $description = null): void
    {
        $storedValue = $value;

        // Encrypt if needed
        if ($encrypt && $value) {
            $storedValue = Crypt::encryptString($value);
        }

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'is_encrypted' => $encrypt,
                'description' => $description,
            ]
        );
    }

    /**
     * Check if a setting exists and has value
     */
    public static function hasValue(string $key): bool
    {
        $setting = static::where('key', $key)->first();
        return $setting && !empty($setting->value);
    }

    /**
     * Get Neo Feeder URL
     */
    public static function getNeoFeederUrl(): ?string
    {
        return static::getValue('neo_feeder_url');
    }

    /**
     * Get Neo Feeder Username
     */
    public static function getNeoFeederUsername(): ?string
    {
        return static::getValue('neo_feeder_username');
    }

    /**
     * Get Neo Feeder Password (decrypted)
     */
    public static function getNeoFeederPassword(): ?string
    {
        return static::getValue('neo_feeder_password');
    }
}
