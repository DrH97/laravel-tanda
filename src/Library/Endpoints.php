<?php

namespace DrH\Tanda\Library;

use DrH\Tanda\Exceptions\TandaException;

class Endpoints
{
    public const AUTH = '/accounts/v1/oauth/token';
    public const REQUEST = '/io/v1/organizations/:organizationId/requests';
    public const STATUS = '/io/v1/organizations/:organizationId/requests/:organizationId';
    public const BALANCE = '/wallets/v1/orgs/:organizationId/balances';

    public const ENDPOINT_REQUEST_TYPES = [
        self::AUTH => 'POST',
        self::REQUEST => 'POST',
        self::STATUS => 'GET'
    ];

    /**
     * @throws TandaException
     */
    private static function getEndpoint(string $section): string
    {
        if (!in_array($section, array_keys(self::ENDPOINT_REQUEST_TYPES))) {
            throw new TandaException("Endpoint is invalid or does not exist.");
        }

        $organizationId = config('tanda.organization_id', false);

        if (!$organizationId) {
            throw new TandaException("No Organization Id specified.");
        }

        $replaceItems = [
            ':organizationId' => $organizationId
        ];

        $section = str_replace(
            array_keys($replaceItems),
            array_values($replaceItems),
            $section
        );
        return self::getUrl($section);
    }

    /**
     * @param string $suffix
     * @return string
     */
    private static function getUrl(string $suffix): string
    {
        $baseEndpoint = rtrim(
            config('tanda.base.url') ?? 'https://io-proxy-443.tanda.co.ke/',
            '/'
        );

        if (config('tanda.sandbox')) {
            $baseEndpoint .= '/sandbox';
        }
        return $baseEndpoint . $suffix;
    }

    public static function build(string $endpoint): string
    {
        return self::getEndpoint($endpoint);
    }
}
