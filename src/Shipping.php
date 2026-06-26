<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz;

use GuzzleHttp\ClientInterface;
use Tkawen\ShippingDz\Contracts\CarrierDriver;
use Tkawen\ShippingDz\Drivers\DhdDriver;
use Tkawen\ShippingDz\Drivers\EcotrackDriver;
use Tkawen\ShippingDz\Drivers\ProcolisDriver;
use Tkawen\ShippingDz\Drivers\YalidineDriver;
use Tkawen\ShippingDz\Drivers\YalitecDriver;
use Tkawen\ShippingDz\Exceptions\ShippingException;

/**
 * Entry point. Resolve a carrier driver by its code.
 *
 *   $driver = Shipping::driver('yalidine', ['id' => '…', 'token' => '…', 'from_wilaya' => 'Alger']);
 *   $result = $driver->createParcel($parcelRequest);
 *
 * Verification tiers (be honest about what's proven):
 *   • yalidine / yalitec — LIVE-verified end-to-end (create, label, track, delete, webhook).
 *   • zr_express (procolis) — spec-verified create + testConnection.
 *   • ecotrack / dhd — field-mapped to the Ecotrack v1 spec.
 */
final class Shipping
{
    /** code => driver class. Extend with register() for custom Ecotrack couriers. */
    private const DRIVERS = [
        'yalidine' => YalidineDriver::class,
        'yalitec' => YalitecDriver::class,
        'zr_express' => ProcolisDriver::class,
        'procolis' => ProcolisDriver::class,
        'ecotrack' => EcotrackDriver::class,
        'dhd' => DhdDriver::class,
    ];

    /** @var array<string,class-string<CarrierDriver>> */
    private static array $custom = [];

    /**
     * @param  array<string,string>  $credentials
     */
    public static function driver(string $code, array $credentials, ?ClientInterface $http = null): CarrierDriver
    {
        $class = self::$custom[$code] ?? self::DRIVERS[$code] ?? null;
        if ($class === null) {
            throw new ShippingException("Unknown carrier code: {$code}. Supported: " . implode(', ', self::supported()));
        }

        return new $class($credentials, $http);
    }

    /**
     * Register a custom Ecotrack courier (or any CarrierDriver) under a code.
     *
     * @param  class-string<CarrierDriver>  $driverClass
     */
    public static function register(string $code, string $driverClass): void
    {
        self::$custom[$code] = $driverClass;
    }

    /** @return string[] */
    public static function supported(): array
    {
        return array_values(array_unique([...array_keys(self::DRIVERS), ...array_keys(self::$custom)]));
    }
}
