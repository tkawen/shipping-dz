<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Dto;

/** The outcome of creating a parcel at a carrier. */
final class ParcelResult
{
    /**
     * @param  array<string,mixed>  $raw  the provider's raw response (for debugging)
     */
    public function __construct(
        public readonly string $tracking,
        public readonly ?string $labelUrl = null,
        public readonly array $raw = [],
    ) {
    }
}
