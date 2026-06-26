<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Drivers;

use Tkawen\ShippingDz\Dto\ParcelRequest;
use Tkawen\ShippingDz\Dto\ParcelResult;
use Tkawen\ShippingDz\Dto\TrackingInfo;
use Tkawen\ShippingDz\Exceptions\CreateParcelException;
use Tkawen\ShippingDz\Exceptions\HttpException;
use Tkawen\ShippingDz\Support\Phone;
use Tkawen\ShippingDz\Support\Wilayas;

/**
 * Yalidine — Algeria's largest COD courier. Field mappings + response parsing
 * VERIFIED live against a real merchant account (2026-06-26): create, label,
 * track, delete all confirmed end-to-end.
 *
 * Auth: X-API-ID / X-API-TOKEN. Wilaya/commune are keyed by NAME (Latin).
 */
class YalidineDriver extends AbstractDriver
{
    protected const API_BASE = 'https://api.yalidine.app';

    public function code(): string
    {
        return 'yalidine';
    }

    /** `from_wilaya` is optional origin config; id+token are the required secrets. */
    public static function credentialFields(): array
    {
        return ['id', 'token', 'from_wilaya'];
    }

    protected static function optionalFields(): array
    {
        return ['from_wilaya'];
    }

    protected function headers(): array
    {
        return [
            'X-API-ID' => $this->cred('id'),
            'X-API-TOKEN' => $this->cred('token'),
            'Content-Type' => 'application/json',
        ];
    }

    public function testConnection(): bool
    {
        $res = $this->http->request('GET', static::API_BASE . '/v1/wilayas/?page_size=1', [
            'headers' => $this->headers(),
        ]);

        return $res->getStatusCode() === 200;
    }

    public function createParcel(ParcelRequest $p): ParcelResult
    {
        $payload = $this->mapPayload($p);

        $res = $this->http->request('POST', static::API_BASE . '/v1/parcels/', [
            'headers' => $this->headers(),
            'body' => json_encode([$payload], JSON_UNESCAPED_UNICODE),
        ]);
        $data = json_decode((string) $res->getBody(), true);

        // Yalidine returns: { "<order_id>": { success, tracking, label, message } }
        $entry = is_array($data) ? ($data[$payload['order_id']] ?? (is_array(reset($data)) ? reset($data) : null)) : null;
        if (! is_array($entry) || empty($entry['success'])) {
            $msg = is_array($entry) ? ($entry['message'] ?? 'unknown') : 'unexpected response';
            throw new CreateParcelException("Yalidine rejected the parcel: {$msg}");
        }

        return new ParcelResult(
            tracking: (string) ($entry['tracking'] ?? ''),
            labelUrl: (string) ($entry['label'] ?? $entry['label_url'] ?? '') ?: null,
            raw: $entry,
        );
    }

    public function track(string $tracking): TrackingInfo
    {
        $row = $this->getParcel($tracking);

        return new TrackingInfo(
            tracking: $tracking,
            status: (string) ($row['last_status'] ?? $row['status'] ?? ''),
            raw: $row,
        );
    }

    public function label(string $tracking): string
    {
        $row = $this->getParcel($tracking);

        return (string) ($row['label'] ?? '');
    }

    public function deleteParcel(string $tracking): bool
    {
        $res = $this->http->request('DELETE', static::API_BASE . '/v1/parcels/' . $tracking, [
            'headers' => $this->headers(),
        ]);

        return $res->getStatusCode() === 200;
    }

    /** Build Yalidine's exact create-order payload from a carrier-agnostic request. */
    protected function mapPayload(ParcelRequest $p): array
    {
        $toWilaya = Wilayas::latin($p->wilayaId);
        if ($toWilaya === null) {
            throw new CreateParcelException("Unknown wilaya id {$p->wilayaId}.");
        }
        [$first, $family] = $this->splitName($p->customerName);
        $from = $p->fromWilayaName ?: ($this->cred('from_wilaya') ?: 'Alger');

        return [
            'order_id' => $p->orderId,
            'from_wilaya_name' => $from,
            'firstname' => $first,
            'familyname' => $family,
            'contact_phone' => Phone::national($p->phone),
            'address' => $p->address !== '' ? $p->address : $p->communeName,
            'to_commune_name' => $p->communeName,
            'to_wilaya_name' => $toWilaya,
            'product_list' => $p->productList,
            'price' => $p->total,
            'do_insurance' => false,
            'declared_value' => $p->total,
            'length' => 10,
            'width' => 10,
            'height' => 10,
            'weight' => $p->weight,
            'freeshipping' => $p->freeShipping,
            'is_stopdesk' => $p->isStopDesk() && $p->stopdeskId !== null,
            'stopdesk_id' => $p->stopdeskId,
            'has_exchange' => $p->hasExchange,
            'product_to_collect' => null,
        ];
    }

    /** @return array{0:string,1:string} firstname, familyname */
    protected function splitName(string $name): array
    {
        $name = trim($name) ?: 'عميل';
        $parts = preg_split('/\s+/', $name, 2) ?: [$name];

        return [$parts[0], $parts[1] ?? '.'];
    }

    /** @return array<string,mixed> */
    protected function getParcel(string $tracking): array
    {
        $res = $this->http->request('GET', static::API_BASE . '/v1/parcels/' . $tracking, [
            'headers' => $this->headers(),
        ]);
        $data = json_decode((string) $res->getBody(), true);
        if (! is_array($data) || (int) ($data['total_data'] ?? 0) === 0) {
            throw new HttpException("Tracking not found at Yalidine: {$tracking}");
        }

        return $data['data'][0] ?? [];
    }
}
