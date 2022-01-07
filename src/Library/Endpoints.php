<?php

namespace DrH\Tanda\Library;

use DrH\Tanda\Exceptions\TandaException;

class Endpoints
{
    public const AUTH = '/accounts/v1/oauth/token';
    public const REQUEST = '/io/v1/organizations/:organizationId/requests';
    public const STATUS = '/io/v1/organizations/:organizationId/requests/:organizationId';

    /**
     * @throws TandaException
     */
    private static function getEndpoint(string $section): string
    {
        $organizationId = config('tanda.organization_id', false);

        if (!$organizationId) {
            throw new TandaException("No Organization Id specified.");
        }

        $replaceItems = [
            ':organizationId' => $organizationId
        ];
        str_replace(
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
        $baseEndpoint = 'https://io-proxy-443.tanda.co.ke/';
        if (config('tanda.sandbox')) {
            $baseEndpoint .= 'sandbox/';
        }
        return $baseEndpoint . $suffix;
    }

    public static function build(string $endpoint): string
    {
        return self::getEndpoint($endpoint);
    }
}
