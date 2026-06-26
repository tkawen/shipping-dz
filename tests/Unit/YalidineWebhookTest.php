<?php

declare(strict_types=1);

use Tkawen\ShippingDz\Webhook\YalidineWebhook;

// A REAL Yalidine webhook body shape (captured live 2026-06-26). Note `é` is the
// literal escaped sequence Yalidine sends — the HMAC is over these exact bytes.
const REAL_BODY = '{"type":"parcel_status_updated","events":[{"event_id":"gZIRSToKmdQWqJzLpi4uUtMD5evyb018","occurred_at":"2026-06-26 21:14:13","data":{"tracking":"yal-US32VK","status":"En préparation","reason":null}}]}';

it('verifies the HMAC signature over the raw body', function () {
    $secret = 'test-signing-secret';
    $sig = hash_hmac('sha256', REAL_BODY, $secret);
    $wh = new YalidineWebhook($secret);

    expect($wh->verify(REAL_BODY, $sig))->toBeTrue()
        ->and($wh->verify(REAL_BODY, 'sha256=' . $sig))->toBeTrue()   // tolerates prefix
        ->and($wh->verify(REAL_BODY, 'deadbeef'))->toBeFalse()
        ->and($wh->verify(REAL_BODY, ''))->toBeFalse();
});

it('parses the real batch payload shape', function () {
    $wh = new YalidineWebhook('x');
    $events = $wh->parse(REAL_BODY);

    expect($events)->toHaveCount(1);
    $e = $events[0];
    expect($e->type)->toBe('parcel_status_updated')
        ->and($e->tracking)->toBe('yal-US32VK')
        ->and($e->status)->toBe('En préparation')
        ->and($e->eventId)->toBe('gZIRSToKmdQWqJzLpi4uUtMD5evyb018')
        ->and($e->isStatusUpdate())->toBeTrue();
});

it('still parses the legacy single-event shape', function () {
    $wh = new YalidineWebhook('x');
    $events = $wh->parse('{"event":"parcel_deleted","data":{"tracking":"yal-XYZ"}}');

    expect($events)->toHaveCount(1)
        ->and($events[0]->tracking)->toBe('yal-XYZ')
        ->and($events[0]->isDeleted())->toBeTrue();
});

it('maps French statuses to canonical lifecycle states', function () {
    expect(YalidineWebhook::canonicalStatus('Livré'))->toBe('delivered')
        ->and(YalidineWebhook::canonicalStatus('Retour'))->toBe('returned')
        ->and(YalidineWebhook::canonicalStatus('Sorti en livraison'))->toBe('shipped')
        ->and(YalidineWebhook::canonicalStatus('En préparation'))->toBeNull();
});
