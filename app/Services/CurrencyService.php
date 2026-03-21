<?php

namespace App\Services;

class CurrencyService
{
    private const RATES = [
        'COP' => 1.0,          // Base currency
        'USD' => 0.00024,      // 1 COP ≈ 0.00024 USD
        'MXN' => 0.0042,       // 1 COP ≈ 0.0042 MXN
        'CLP' => 0.23,         // 1 COP ≈ 0.23 CLP
        'PEN' => 0.00090,      // 1 COP ≈ 0.00090 PEN
        'ARS' => 0.21,         // 1 COP ≈ 0.21 ARS
    ];

    private const SYMBOLS = [
        'COP' => '$',
        'USD' => '$',
        'MXN' => '$',
        'CLP' => '$',
        'PEN' => 'S/',
        'ARS' => '$',
    ];

    private const NAMES = [
        'COP' => 'Peso Colombiano',
        'USD' => 'Dólar Estadounidense',
        'MXN' => 'Peso Mexicano',
        'CLP' => 'Peso Chileno',
        'PEN' => 'Sol Peruano',
        'ARS' => 'Peso Argentino',
    ];

    private const COUNTRY_CURRENCY = [
        'CO' => 'COP',
        'MX' => 'MXN',
        'CL' => 'CLP',
        'PE' => 'PEN',
        'AR' => 'ARS',
        'US' => 'USD',
        'EC' => 'USD',
    ];

    public static function convert(float $amountCOP, string $toCurrency): float
    {
        $rate = self::RATES[strtoupper($toCurrency)] ?? 1.0;
        return round($amountCOP * $rate, 2);
    }

    public static function format(float $amount, string $currency = 'COP'): string
    {
        $symbol = self::SYMBOLS[$currency] ?? '$';

        if ($currency === 'COP' || $currency === 'CLP' || $currency === 'ARS') {
            return $symbol . number_format($amount, 0, ',', '.');
        }

        return $symbol . number_format($amount, 2, '.', ',');
    }

    public static function formatWithCode(float $amount, string $currency = 'COP'): string
    {
        return self::format($amount, $currency) . ' ' . $currency;
    }

    public static function getSymbol(string $currency): string
    {
        return self::SYMBOLS[strtoupper($currency)] ?? '$';
    }

    public static function getName(string $currency): string
    {
        return self::NAMES[strtoupper($currency)] ?? $currency;
    }

    public static function getCurrencyForCountry(string $countryCode): string
    {
        return self::COUNTRY_CURRENCY[strtoupper($countryCode)] ?? 'USD';
    }

    public static function getSupportedCurrencies(): array
    {
        return array_keys(self::RATES);
    }

    public static function getPlanPrices(string $currency = 'COP'): array
    {
        $basePrices = [
            'esencial' => 299000,
            'metodo' => 399000,
            'elite' => 549000,
            'rise' => 99900,
        ];

        $converted = [];
        foreach ($basePrices as $plan => $priceCOP) {
            $converted[$plan] = [
                'amount' => self::convert($priceCOP, $currency),
                'formatted' => self::format(self::convert($priceCOP, $currency), $currency),
                'currency' => $currency,
            ];
        }

        return $converted;
    }
}
