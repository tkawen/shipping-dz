# Changelog

## [0.1.0] — 2026-06-26

Initial release. Extracted and generalised from TKAWEN/mystoq's production carrier engine.

### Added
- Unified `CarrierDriver` contract: `testConnection`, `createParcel`, `track`, `label`, `deleteParcel`.
- **Yalidine** + **Yalitec** drivers (`id` + `token`) — live-verified end-to-end.
- **ZR Express / Procolis** driver (`token` + `key`) — spec-verified create + testConnection.
- **Ecotrack** driver + **DHD** preset (`token`) — Ecotrack v1 field mappings.
- `Wilayas` — all **69** wilayas (post-2025 reform) in Arabic + Latin, with reverse lookup.
- `Phone` — DZ phone normalisation to 10-digit national form.
- `YalidineWebhook` — verified `X-Yalidine-Signature` HMAC + real `{type, events:[…]}` batch parser
  + French→canonical status mapping.
- DTOs: `ParcelRequest`, `ParcelResult`, `TrackingInfo`, `WebhookEvent`; `DeliveryType` enum.
- Pest unit tests for wilayas, phone, and webhook (signature + parsing).

### Verification tiers
- `yalidine` / `yalitec`: **live-verified** against a real merchant account.
- `zr_express` / `procolis`: spec-verified.
- `ecotrack` / `dhd`: Ecotrack v1 field-mapped (community verification welcome).
