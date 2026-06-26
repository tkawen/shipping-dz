<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Webhook;

/**
 * Yalidine push-webhook verifier + parser. Both the signature scheme AND the payload
 * shape were captured from a REAL Yalidine webhook (2026-06-26) — public guides get
 * both wrong, which silently drops every event.
 *
 *  Signature: header `X-Yalidine-Signature` = hash_hmac('sha256', RAW_BODY, signingSecret)
 *             (the "Clé secrète" from the Yalidine webhook screen).
 *  Payload:   { "type": "...", "events": [ { "event_id", "occurred_at",
 *               "data": { "tracking", "status", "reason", ... } } ] }
 */
final class YalidineWebhook
{
    public function __construct(
        private readonly string $signingSecret,
    ) {
    }

    /**
     * Validate the HMAC signature over the EXACT raw request body.
     * Pass the value of the `X-Yalidine-Signature` header (with or without `sha256=`).
     */
    public function verify(string $rawBody, string $signatureHeader): bool
    {
        if ($this->signingSecret === '' || $signatureHeader === '') {
            return false;
        }
        $sig = str_starts_with($signatureHeader, 'sha256=') ? substr($signatureHeader, 7) : $signatureHeader;
        $expected = hash_hmac('sha256', $rawBody, $this->signingSecret);

        return hash_equals($expected, $sig);
    }

    /**
     * Parse the raw JSON body into a flat list of events.
     *
     * @return WebhookEvent[]
     */
    public function parse(string $rawBody): array
    {
        $payload = json_decode($rawBody, true);
        if (! is_array($payload)) {
            return [];
        }
        $topType = (string) ($payload['type'] ?? $payload['event'] ?? $payload['event_type'] ?? '');
        $rows = is_array($payload['events'] ?? null)
            ? $payload['events']
            : [['data' => (is_array($payload['data'] ?? null) ? $payload['data'] : $payload)]];

        $events = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $data = is_array($row['data'] ?? null) ? $row['data'] : $row;
            $tracking = (string) ($data['tracking'] ?? $data['tracking_number'] ?? '');
            if ($tracking === '') {
                continue;
            }
            $events[] = new WebhookEvent(
                type: (string) ($row['type'] ?? $topType),
                tracking: $tracking,
                status: (string) ($data['status'] ?? $data['last_status'] ?? ''),
                eventId: isset($row['event_id']) ? (string) $row['event_id'] : null,
                occurredAt: isset($row['occurred_at']) ? (string) $row['occurred_at'] : null,
                data: $data,
            );
        }

        return $events;
    }

    /**
     * Map a raw Yalidine (French) status to a canonical lifecycle state, or null if it
     * doesn't change the order state.
     */
    public static function canonicalStatus(string $status): ?string
    {
        $s = mb_strtolower(trim($status));

        return match (true) {
            $s === '' => null,
            str_contains($s, 'livré') || str_contains($s, 'delivered') => 'delivered',
            str_contains($s, 'retour') || str_contains($s, 'returned') => 'returned',
            str_contains($s, 'annul') || str_contains($s, 'cancel') => 'cancelled',
            str_contains($s, 'expéd') || str_contains($s, 'transit') || str_contains($s, 'sorti') => 'shipped',
            default => null,
        };
    }
}
