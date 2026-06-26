<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Drivers;

use Tkawen\ShippingDz\Dto\ParcelRequest;
use Tkawen\ShippingDz\Dto\ParcelResult;
use Tkawen\ShippingDz\Dto\TrackingInfo;
use Tkawen\ShippingDz\Exceptions\CreateParcelException;
use Tkawen\ShippingDz\Exceptions\ShippingException;
use Tkawen\ShippingDz\Support\Phone;

/**
 * Procolis backend — powers ZR Express (and NOEST). Auth: `token` + `key` headers
 * (VERIFIED against the Procolis API spec). TypeLivraison: 0 = domicile, 1 = stop-desk.
 */
class ProcolisDriver extends AbstractDriver
{
    protected const API_BASE = 'https://procolis.com/api_v1';

    public function code(): string
    {
        return 'zr_express';
    }

    public static function credentialFields(): array
    {
        return ['token', 'key'];
    }

    protected function headers(): array
    {
        return [
            'token' => $this->cred('token'),
            'key' => $this->cred('key'),
            'Content-Type' => 'application/json',
        ];
    }

    public function testConnection(): bool
    {
        $res = $this->http->request('GET', static::API_BASE . '/token', ['headers' => $this->headers()]);
        $data = json_decode((string) $res->getBody(), true);

        return $res->getStatusCode() === 200 && ($data['Statut'] ?? null) === 'Accès activé';
    }

    public function createParcel(ParcelRequest $p): ParcelResult
    {
        $payload = [
            'Tracking' => '',
            'TypeLivraison' => $p->isStopDesk() ? 1 : 0, // 0=domicile, 1=stopdesk
            'TypeColis' => 0,
            'Confrimee' => 1,
            'Client' => $p->customerName !== '' ? $p->customerName : 'عميل',
            'MobileA' => Phone::national($p->phone),
            'MobileB' => $p->phoneAlt ? Phone::national($p->phoneAlt) : '',
            'Adresse' => $p->address !== '' ? $p->address : $p->communeName,
            'IDWilaya' => $p->wilayaId,
            'Commune' => $p->communeName,
            'Total' => $p->total,
            'Note' => (string) ($p->note ?? ''),
            'TProduit' => $p->productList,
            'id_Externe' => $p->orderId,
            'Source' => '',
        ];

        $res = $this->http->request('POST', static::API_BASE . '/add_colis', [
            'headers' => $this->headers(),
            'body' => json_encode(['Colis' => [$payload]], JSON_UNESCAPED_UNICODE),
        ]);
        $data = json_decode((string) $res->getBody(), true);
        $colis = $data['Colis'][0] ?? null;
        $msg = is_array($colis) ? (string) ($colis['MessageRetour'] ?? '') : '';

        if ($msg !== 'Good') {
            throw new CreateParcelException('ZR/Procolis rejected the parcel: ' . ($msg ?: 'unexpected response'));
        }

        return new ParcelResult(
            tracking: (string) ($colis['Tracking'] ?? ''),
            labelUrl: null,
            raw: $colis,
        );
    }

    public function track(string $tracking): TrackingInfo
    {
        $res = $this->http->request('POST', static::API_BASE . '/lire', [
            'headers' => $this->headers(),
            'body' => json_encode(['Colis' => [['Tracking' => $tracking]]], JSON_UNESCAPED_UNICODE),
        ]);
        $data = json_decode((string) $res->getBody(), true);
        $row = $data['Colis'][0] ?? [];

        return new TrackingInfo(
            tracking: $tracking,
            status: (string) ($row['Situation'] ?? $row['status'] ?? ''),
            raw: is_array($row) ? $row : [],
        );
    }

    public function label(string $tracking): string
    {
        // ZR/Procolis generates labels in its own dashboard — no public label endpoint.
        throw new ShippingException('ZR/Procolis does not expose a label API; print from the ZR dashboard.');
    }

    public function deleteParcel(string $tracking): bool
    {
        throw new ShippingException('ZR/Procolis does not expose a delete API.');
    }
}
