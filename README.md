# 🇩🇿 shipping-dz — Sovereign Algerian COD Shipping SDK

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777bb4.svg)](composer.json)

One clean, framework-agnostic PHP API for Algeria's COD couriers — **Yalidine, Yalitec,
ZR Express (Procolis) and the Ecotrack family** (DHD, GoLivri, World Express, Packers,
Anderson, …). Built and **verified against real merchant accounts**, by [TKAWEN](https://tkawen.com).

> Why another package? Because the existing ones get the details wrong: swapped credential
> fields, a broken Yalidine `createOrder` response parser, wilaya lists frozen at 58, and
> webhook handlers that silently drop every event. This SDK fixes all of that — every mapping
> here was confirmed against a live courier account.

## ✨ Highlights

- **69 wilayas** — updated for the **16 Nov 2025** territorial reform (others still ship 58).
- **Correct credentials** — `yalidine`/`yalitec` = `id`+`token`, `zr_express` = `token`+`key`,
  Ecotrack = `token`. (Getting these wrong is the #1 integration bug.)
- **Per-carrier payload mapping** — four incompatible create-order schemas, each handled right.
- **Yalidine webhooks done right** — verified `X-Yalidine-Signature` HMAC **and** the real
  `{type, events:[…]}` batch shape. No more silently-dropped status updates.
- **Framework-agnostic** — plain PHP + Guzzle. Works in Laravel, Symfony, or no framework.
- **Honest verification tiers** — we tell you exactly what's been proven live vs. spec-mapped.

## Install

```bash
composer require tkawen/shipping-dz
```

## Quick start

```php
use Tkawen\ShippingDz\Shipping;
use Tkawen\ShippingDz\Dto\ParcelRequest;
use Tkawen\ShippingDz\Enums\DeliveryType;

$driver = Shipping::driver('yalidine', [
    'id'          => 'YOUR_API_ID',
    'token'       => 'YOUR_API_TOKEN',
    'from_wilaya' => 'Alger',          // origin wilaya (Latin name)
]);

$driver->testConnection();             // bool

$result = $driver->createParcel(new ParcelRequest(
    orderId:      'order-1001',
    customerName: 'Ahmed Benali',
    phone:        '0555 12 34 56',
    wilayaId:     16,                  // Alger
    communeName:  'Alger Centre',
    address:      'Rue Didouche Mourad',
    total:        4500,                // DZD
    productList:  'Sneakers ×1',
    deliveryType: DeliveryType::Home,
));

$result->tracking;   // "yal-XXXXXX"
$result->labelUrl;   // printable bordereau URL

$driver->track($result->tracking);    // TrackingInfo
$driver->label($result->tracking);    // label URL
$driver->deleteParcel($result->tracking);
```

## Webhooks (Yalidine real-time tracking)

```php
use Tkawen\ShippingDz\Webhook\YalidineWebhook;

$wh = new YalidineWebhook($signingSecret);            // the "Clé secrète"

// 1. Verify over the EXACT raw request body (not the parsed array!)
if (! $wh->verify($rawBody, $request->header('X-Yalidine-Signature'))) {
    abort(403);
}

// 2. Parse the batch → flat events
foreach ($wh->parse($rawBody) as $event) {
    $event->tracking;                                  // "yal-XXXXXX"
    $canonical = YalidineWebhook::canonicalStatus($event->status); // delivered|returned|shipped|cancelled|null
    // … update your order …
}
```

## Supported carriers & verification tier

| Code | Carrier | Credentials | Status |
|------|---------|-------------|--------|
| `yalidine` | Yalidine | `id`, `token`, `from_wilaya` | ✅ Live-verified (create · label · track · delete · webhook) |
| `yalitec` | Yalitec | `id`, `token`, `from_wilaya` | ◑ Same API as Yalidine |
| `zr_express` / `procolis` | ZR Express | `token`, `key` | ◑ Spec-verified create + testConnection |
| `dhd` | DHD Livraison | `token` | ◑ Ecotrack v1 field-mapped |
| `ecotrack` | any Ecotrack courier | `token`, `domain` | ◑ Ecotrack v1 field-mapped |

Register any other Ecotrack courier:

```php
Shipping::register('golivri', \Tkawen\ShippingDz\Drivers\EcotrackDriver::class);
$driver = Shipping::driver('golivri', ['token' => '…', 'domain' => 'https://golivri.ecotrack.dz']);
```

## Wilayas helper

```php
use Tkawen\ShippingDz\Support\Wilayas;

Wilayas::count();              // 69
Wilayas::latin(16);            // "Alger"
Wilayas::ar(16);               // "الجزائر"
Wilayas::idFromName('Béjaïa'); // 6  (accent-insensitive)
Wilayas::isPost2025(60);       // true  (created by the 2025 reform)
```

## Testing

```bash
composer install
vendor/bin/pest
```

## Contributing

PRs welcome — especially **live-verified** field mappings for carriers currently marked ◑.
If you confirm a mapping against a real account, say so in the PR so we can promote its tier.

## License

MIT © TKAWEN
