<?php

namespace App\Helpers;

class CurrencyHelper
{
    const RATES = [
        'EUR' => 1.0,
        'SEK' => 0.087,    // Zweden
        'NOK' => 0.086,    // Noorwegen
        'PLN' => 0.233,    // Polen
        'HUF' => 0.0025,   // Hongarije
        'GEL' => 0.336,    // Georgië
        'RSD' => 0.0085,   // Servië
        'MKD' => 0.016,    // Noord-Macedonië
        'HRK' => 0.133,    // Kroatië
        'JPY' => 0.0062,   // Japan
        'ARS' => 0.00083,  // Argentinië
        'PYG' => 0.00012,  // Paraguay
        'USD' => 0.92,     // US Dollar (ref)
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
