<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Dto;

use Tkawen\ShippingDz\Enums\DeliveryType;

/**
 * A carrier-agnostic parcel order. Each driver translates this into its provider's
 * exact create-order payload (the verified field mappings live in the drivers).
 */
final class ParcelRequest
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $customerName,
        public readonly string $phone,
        public readonly int $wilayaId,
        public readonly string $communeName,
        public readonly string $address,
        public readonly int $total,
        public readonly string $productList = 'منتجات',
        public readonly DeliveryType $deliveryType = DeliveryType::Home,
        public readonly ?string $phoneAlt = null,
        public readonly ?string $note = null,
        public readonly int $weight = 1,
        public readonly bool $freeShipping = false,
        public readonly bool $hasExchange = false,
        /** Yalidine stop-desk centre id (required by Yalidine when delivering to a desk). */
        public readonly ?string $stopdeskId = null,
        /** Origin wilaya NAME (Latin) — Yalidine/Yalitec need it; defaults per driver config. */
        public readonly ?string $fromWilayaName = null,
    ) {
    }

    public function isStopDesk(): bool
    {
        return $this->deliveryType === DeliveryType::StopDesk;
    }
}
