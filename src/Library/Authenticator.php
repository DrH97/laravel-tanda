<?php

namespace DrH\Tanda\Library;

use DrH\Tanda\Exceptions\TandaException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\ResponseInterface;

class Authenticator
{
    protected string $endpoint;

    protected BaseClient $client;

    protected static Authenticator $instance;

    private ?string $credentials = null;

    /**
     * Authenticator constructor.
     *
     * @param BaseClient $baseClient
     */
    public function __construct(BaseClient $baseClient)
    {
        $this->client = $baseClient;
        $this->endpoint = Endpoints::build(Endpoints::AUTH);
        self::$instance = $this;

    }


    /**
     * @return string
     * @throws TandaException
     * @throws GuzzleException
     */
    public function authenticate(): string
    {
        if (config('tanda.cache_credentials', false) && !empty($key = $this->getFromCache())) {
            return $key;
        }

        $this->generateCredentials();

        $response = $this->makeRequest();
        if ($response->getStatusCode() === 200) {
            $body = json_decode($response->getBody());
            $this->saveCredentials($body);
            return $body->access_token;
        }
        throw new TandaException($response->getReasonPhrase());
    }

    /**
     * @return void
     * @throws TandaException
     */
    private function generateCredentials(): void
    {
        $clientId = config('tanda.client_id', false);
        $clientSecret = config('tanda.client_id', false);

        if (!$clientId || !$clientSecret) {
            throw new TandaException("No Client Id/Secret specified.");
        }

        $this->credentials = base64_encode($clientId . ':' . $clientSecret);
    }


    /**
     * @return ResponseInterface
     * @throws GuzzleException|TandaException
     */
    private function makeRequest(): ResponseInterface
    {
        $clientId = config('tanda.client_id', false);
        $clientSecret = config('tanda.client_id', false);

        if (!$clientId || !$clientSecret) {
            throw new TandaException("No Client Id/Secret specified.");
        }

        return $this->client->clientInterface->request(
            'POST',
            $this->endpoint,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'scope' => ''
                ]
            ]
        );
    }


    private function getFromCache(): mixed
    {
        return Cache::get($this->credentials);
    }

    /**
     * Store the credentials in the cache.
     *
     * @param object $credentials
     */
    private function saveCredentials(object $credentials): void
    {
//        Use the returned expiry time with 10 seconds leeway for latency etc...
        Cache::put($this->credentials, $credentials->access_token, $credentials->expires_in - 10);
    }

}