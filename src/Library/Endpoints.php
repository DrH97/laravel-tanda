<?php

namespace DrH\Tanda\Library;

use DrH\Tanda\Exceptions\TandaException;

class Endpoints
{
    public const AUTH = '/accounts/v1/oauth/token';
    public const REQUEST = '/io/v1/organizations/:organizationId/requests';
    public const STATUS = self::REQUEST . '/:requestId';
    public const BALANCE = '/wallets/v1/orgs/:organizationId/balances';

    public const ENDPOINT_REQUEST_TYPES = [
        self::AUTH => 'POST',
        self::REQUEST => 'POST',
        self::STATUS => 'GET',
        self::BALANCE => 'GET',
    ];

    /**
     * @throws TandaException
     */
    private static function getEndpoint(string $section, array $replace = [], array|null $params = null): string
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
            ] + $replace;

        $section = str_replace(
            array_keys($replaceItems),
            array_values($replaceItems),
            $section
        );
        return self::getUrl($section, $params);
    }

    /**
     * @param string $suffix
     * @return string
     */
    private static function getUrl(string $suffix, array|null $params = null): string
    {
        $defaultProductionUrl = 'https://io-proxy-443.tanda.co.ke/';
        $defaultSandboxUrl = 'https://tandaio-api-uats.tanda.co.ke/';

        $defaultUrl = config('tanda.sandbox') ? $defaultSandboxUrl : $defaultProductionUrl;

        $baseEndpoint = rtrim(
            config('tanda.urls.base') ?? $defaultUrl,
            '/'
        );

        $url = $baseEndpoint . $suffix;
        if ($params) {
            $url .= '?';
            foreach ($params as $key => $value) {
                $url .= $key . '=' . $value . '&';
            }
        }

        return $url;
    }

    public static function build(string $endpoint, array $replace = [], array $params = null): string
    {
        return self::getEndpoint($endpoint, $replace, $params);
    }
}
