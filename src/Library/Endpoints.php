<?php

namespace DrH\Tanda\Library;

use DrH\Tanda\Exceptions\TandaException;

class Endpoints
{
    public const AUTH = '/accounts/v1/oauth/token';

    private static function getEndpoint(string $section): string
    {
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
