<?php

namespace DrH\Tanda\Library;

use GuzzleHttp\ClientInterface;

class BaseClient
{
    public ClientInterface $clientInterface;

    public Authenticator $authenticator;

    /**
     *
     * @param ClientInterface $clientInterface
     *
     */
    public function __construct(ClientInterface $clientInterface)
    {
        $this->clientInterface = $clientInterface;
        $this->authenticator = new Authenticator($this);
    }
}
