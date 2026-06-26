<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Webhook;

/** One normalised parcel event parsed out of a carrier webhook batch. */
final class WebhookEvent
{
    /**
     * @param  array<string,mixed>  $data
     */
    public function __construct(
        public readonly string $type,        // parcel_status_updated, parcel_created, ...
        public readonly string $tracking,
        public readonly string $status = '', // raw carrier status (often French)
        public readonly ?string $eventId = null,
        public readonly ?string $occurredAt = null,
        public readonly array $data = [],
    ) {
    }

    public function isStatusUpdate(): bool
    {
        return $this->type === 'parcel_status_updated';
    }

    public function isDeleted(): bool
    {
        return $this->type === 'parcel_deleted';
    }

    public function isPaymentUpdate(): bool
    {
        return $this->type === 'parcel_payment_updated';
    }
}
