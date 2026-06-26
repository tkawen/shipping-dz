<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Drivers;

/**
 * Yalitec — same API shape as Yalidine, different base host.
 * Auth: X-API-ID / X-API-TOKEN.
 */
final class YalitecDriver extends YalidineDriver
{
    protected const API_BASE = 'https://api.yalitec.me';

    public function code(): string
    {
        return 'yalitec';
    }
}
