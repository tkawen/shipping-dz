<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Support;

/**
 * Algerian phone normalisation. DZ mobiles are `0[5-7]xxxxxxxx` (10 digits).
 * Verified against Yalidine (wants leading 0) and Ecotrack/Maystro (digits_between 9–10).
 */
final class Phone
{
    /** National 10-digit form `0XXXXXXXXX` (strips 00 / +213 / spaces / dashes). '' if unusable. */
    public static function national(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);   // 00 international access code → drop
        }
        if (str_starts_with($digits, '213')) {
            $digits = substr($digits, 3);   // Algeria country code → drop
        }
        $core = ltrim($digits, '0');

        return $core === '' ? '' : '0' . $core;
    }

    /** Is this a plausible DZ mobile (0[5-7] + 8 digits)? */
    public static function isValidMobile(?string $phone): bool
    {
        return (bool) preg_match('/^0[5-7]\d{8}$/', self::national($phone));
    }
}
