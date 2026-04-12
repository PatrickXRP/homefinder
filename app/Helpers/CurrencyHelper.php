<?php

namespace App\Helpers;

class CurrencyHelper
{
    const RATES = [
        'SEK' => 0.087,
        'RON' => 0.201,
        'BGN' => 0.511,
        'HRK' => 0.133,
        'GEL' => 0.336,
        'EUR' => 1.0,
        'ALL' => 0.010,
        'MKD' => 0.016,
    ];

    public static function toEur(int $amount, string $currency): int
    {
        $rate = self::RATES[strtoupper($currency)] ?? 1.0;
        return (int) round($amount * $rate);
    }

    public static function format(int $amount, string $currency): string
    {
        return strtoupper($currency) . ' ' . number_format($amount, 0, ',', '.');
    }

    public static function formatEur(int $amount): string
    {
        return '€ ' . number_format($amount, 0, ',', '.');
    }
}
