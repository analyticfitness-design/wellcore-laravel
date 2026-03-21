<?php

namespace App\Services;

use Carbon\Carbon;

class LocaleService
{
    private const SUPPORTED_LOCALES = ['es', 'en'];

    private const TIMEZONE_MAP = [
        'CO' => 'America/Bogota',
        'MX' => 'America/Mexico_City',
        'CL' => 'America/Santiago',
        'PE' => 'America/Lima',
        'AR' => 'America/Argentina/Buenos_Aires',
        'EC' => 'America/Guayaquil',
        'US' => 'America/New_York',
    ];

    public static function formatDate(string|\DateTimeInterface $date, string $locale = 'es'): string
    {
        $carbon = $date instanceof \DateTimeInterface ? Carbon::instance($date) : Carbon::parse($date);

        if ($locale === 'en') {
            return $carbon->format('M d, Y');
        }

        return $carbon->translatedFormat('d M Y');
    }

    public static function formatDateTime(string|\DateTimeInterface $date, string $locale = 'es'): string
    {
        $carbon = $date instanceof \DateTimeInterface ? Carbon::instance($date) : Carbon::parse($date);

        if ($locale === 'en') {
            return $carbon->format('M d, Y h:i A');
        }

        return $carbon->translatedFormat('d M Y H:i');
    }

    public static function formatNumber(float $number, string $locale = 'es', int $decimals = 0): string
    {
        if ($locale === 'en') {
            return number_format($number, $decimals, '.', ',');
        }

        return number_format($number, $decimals, ',', '.');
    }

    public static function getTimezone(string $countryCode): string
    {
        return self::TIMEZONE_MAP[strtoupper($countryCode)] ?? 'America/Bogota';
    }

    public static function getSupportedLocales(): array
    {
        return self::SUPPORTED_LOCALES;
    }

    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::SUPPORTED_LOCALES);
    }
}
