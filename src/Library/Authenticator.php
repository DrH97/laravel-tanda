<?php

namespace DrH\Tanda\Library;

use DrH\Tanda\Exceptions\TandaException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\ResponseInterface;

class Authenticator
{
    private string $endpoint;

    private ?string $credentials = null;

    /**
     * Authenticator constructor.
     *
     * @param BaseClient $client
     */
    public function __construct(private BaseClient $client)
    {
    }


    /**
     * @return string
     * @throws TandaException
     * @throws GuzzleException
     */
    public function authenticate(): string
    {
        $this->generateCredentials();

        if (config('tanda.cache_credentials', false) && !empty($key = $this->getFromCache())) {
            return $key;
        }

        try {
            $response = $this->makeRequest();

            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody());
                $this->saveCredentials($body);
                return $body->access_token;
            }

            throw new TandaException($response->getReasonPhrase());
        } catch (RequestException $exception) {
            $message = $exception->getResponse() ?
                $exception->getResponse()->getReasonPhrase() :
                $exception->getMessage();

            throw new TandaException($message);
        }
    }

    /**
     * @return void
     * @throws TandaException
     */
    private function generateCredentials(): void
    {
        $clientId = config('tanda.client_id', false);
        $clientSecret = config('tanda.client_secret', false);

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
        $clientSecret = config('tanda.client_secret', false);

        $this->endpoint = Endpoints::build(Endpoints::AUTH);

        return $this->client->clientInterface->request(
            'POST',
            $this->endpoint,
            [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
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
