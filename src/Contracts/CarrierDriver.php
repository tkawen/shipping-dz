<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Contracts;

use Tkawen\ShippingDz\Dto\ParcelRequest;
use Tkawen\ShippingDz\Dto\ParcelResult;
use Tkawen\ShippingDz\Dto\TrackingInfo;

/**
 * One uniform contract for every Algerian carrier. Implementations own the
 * (verified) field mappings for their provider's API.
 */
interface CarrierDriver
{
    /** Stable lowercase code, e.g. "yalidine", "zr_express", "dhd". */
    public function code(): string;

    /** Which credential keys this driver needs (drives merchant UIs). @return string[] */
    public static function credentialFields(): array;

    /** Validate credentials against the live API (read-only). */
    public function testConnection(): bool;

    /** Create a parcel; throws CreateParcelException on rejection. */
    public function createParcel(ParcelRequest $parcel): ParcelResult;

    /** Latest status for a tracking number. */
    public function track(string $tracking): TrackingInfo;

    /** A printable label/waybill URL (or raw PDF bytes) for a tracking number. */
    public function label(string $tracking): string;

    /** Delete/cancel a parcel at the carrier. Returns true on success. */
    public function deleteParcel(string $tracking): bool;
}
