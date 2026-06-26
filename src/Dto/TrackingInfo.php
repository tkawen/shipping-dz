<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Dto;

/** Normalised tracking snapshot. `status` is the provider's raw (often French) label. */
final class TrackingInfo
{
    /**
     * @param  array<string,mixed>  $raw
     */
    public function __construct(
        public readonly string $tracking,
        public readonly string $status,
        public readonly array $raw = [],
    ) {
    }
}
