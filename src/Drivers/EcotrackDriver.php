<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Drivers;

use Tkawen\ShippingDz\Dto\ParcelRequest;
use Tkawen\ShippingDz\Dto\ParcelResult;
use Tkawen\ShippingDz\Dto\TrackingInfo;
use Tkawen\ShippingDz\Exceptions\CreateParcelException;
use Tkawen\ShippingDz\Exceptions\CredentialsException;
use Tkawen\ShippingDz\Support\Phone;
use Tkawen\ShippingDz\Support\Wilayas;

/**
 * Ecotrack — a shared SaaS platform behind ~60 Algerian couriers (DHD, Conexlog/UPS,
 * GoLivri, World Express, Packers, Anderson…). One adapter, per-courier `token` + `domain`.
 * Auth: Authorization: Bearer <token>. code_wilaya is limited to 1–58 by the Ecotrack v1 API.
 *
 * Field mappings follow the Ecotrack v1 spec. testConnection + createParcel implemented;
 * read endpoints vary per deployment (see track()/label()).
 */
class EcotrackDriver extends AbstractDriver
{
    public function code(): string
    {
        return 'ecotrack';
    }

    /** `token` is the secret; `domain` is the courier's Ecotrack host (e.g. https://dhd.ecotrack.dz). */
    public static function credentialFields(): array
    {
        return ['token', 'domain'];
    }

    protected static function optionalFields(): array
    {
        return ['domain']; // a concrete subclass may hardcode the domain instead
    }

    /** The courier's Ecotrack base host, normalised with a trailing slash. */
    protected function domain(): string
    {
        $domain = $this->cred('domain');
        if ($domain === '') {
            throw new CredentialsException($this->code() . ' requires a "domain" (e.g. https://dhd.ecotrack.dz).');
        }

        return rtrim($domain, '/') . '/';
    }

    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->cred('token'),
            'Content-Type' => 'application/json',
        ];
    }

    public function testConnection(): bool
    {
        $res = $this->http->request('GET', $this->domain() . 'api/v1/get/wilayas', ['headers' => $this->headers()]);

        return $res->getStatusCode() === 200;
    }

    public function createParcel(ParcelRequest $p): ParcelResult
    {
        if ($p->wilayaId < 1 || $p->wilayaId > 58) {
            throw new CreateParcelException(sprintf(
                'Wilaya %d (%s) is not served by the Ecotrack API (1–58 only).',
                $p->wilayaId,
                Wilayas::ar($p->wilayaId) ?? '?',
            ));
        }

        $payload = [
            'reference' => $p->orderId,
            'nom_client' => $p->customerName !== '' ? $p->customerName : 'عميل',
            'telephone' => Phone::national($p->phone),
            'adresse' => $p->address !== '' ? $p->address : $p->communeName,
            'commune' => $p->communeName,
            'code_wilaya' => $p->wilayaId,
            'montant' => $p->total,
            'remarque' => (string) ($p->note ?? ''),
            'produit' => $p->productList,
            'type' => 1, // 1 = Livraison
            'stop_desk' => $p->isStopDesk() ? 1 : 0,
        ];

        $res = $this->http->request('POST', $this->domain() . 'api/v1/create/order', [
            'headers' => $this->headers(),
            'body' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
        $data = json_decode((string) $res->getBody(), true);

        $tracking = (string) ($data['tracking'] ?? $data['data']['tracking'] ?? $data['tracking_id'] ?? '');
        if ($res->getStatusCode() >= 300 || $tracking === '') {
            $msg = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : (string) $res->getBody();
            throw new CreateParcelException('Ecotrack rejected the parcel: ' . $msg);
        }

        return new ParcelResult(tracking: $tracking, labelUrl: null, raw: is_array($data) ? $data : []);
    }

    public function track(string $tracking): TrackingInfo
    {
        $res = $this->http->request('GET', $this->domain() . 'api/v1/get/tracking/' . $tracking, [
            'headers' => $this->headers(),
        ]);
        $data = json_decode((string) $res->getBody(), true);
        $row = is_array($data) ? ($data['data'] ?? $data) : [];

        return new TrackingInfo(
            tracking: $tracking,
            status: (string) ($row['status'] ?? $row['situation'] ?? ''),
            raw: is_array($row) ? $row : [],
        );
    }

    public function label(string $tracking): string
    {
        return $this->domain() . 'api/v1/get/order/label?tracking=' . rawurlencode($tracking);
    }

    public function deleteParcel(string $tracking): bool
    {
        $res = $this->http->request('POST', $this->domain() . 'api/v1/delete/order', [
            'headers' => $this->headers(),
            'body' => json_encode(['tracking' => $tracking], JSON_UNESCAPED_UNICODE),
        ]);

        return $res->getStatusCode() < 300;
    }
}
