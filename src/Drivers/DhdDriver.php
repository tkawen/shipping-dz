<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Drivers;

/** DHD Livraison — an Ecotrack courier with a preset host. Auth: `token` only. */
final class DhdDriver extends EcotrackDriver
{
    public function code(): string
    {
        return 'dhd';
    }

    public static function credentialFields(): array
    {
        return ['token'];
    }

    protected function domain(): string
    {
        return 'https://dhd.ecotrack.dz/';
    }
}
