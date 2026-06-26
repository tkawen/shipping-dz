<?php

declare(strict_types=1);

namespace Tkawen\ShippingDz\Drivers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Tkawen\ShippingDz\Contracts\CarrierDriver;
use Tkawen\ShippingDz\Exceptions\CredentialsException;

abstract class AbstractDriver implements CarrierDriver
{
    protected ClientInterface $http;

    /**
     * @param  array<string,string>  $credentials
     */
    public function __construct(
        protected array $credentials,
        ?ClientInterface $http = null,
    ) {
        $this->assertCredentials();
        // Allow injecting a client (tests / custom middleware); default Guzzle, http_errors off
        // so we read non-2xx bodies ourselves instead of catching exceptions everywhere.
        $this->http = $http ?? new Client(['http_errors' => false, 'timeout' => 20]);
    }

    /** Throw if any required credential field is missing/empty. */
    protected function assertCredentials(): void
    {
        foreach (static::credentialFields() as $field) {
            // origin/config fields (e.g. from_wilaya) are optional — only secrets are required
            if (in_array($field, static::optionalFields(), true)) {
                continue;
            }
            if (empty($this->credentials[$field])) {
                throw new CredentialsException(sprintf(
                    '%s credentials must include "%s".',
                    $this->code(),
                    $field,
                ));
            }
        }
    }

    /** Fields in credentialFields() that are config, not required secrets. @return string[] */
    protected static function optionalFields(): array
    {
        return [];
    }

    protected function cred(string $key, string $default = ''): string
    {
        return (string) ($this->credentials[$key] ?? $default);
    }
}
