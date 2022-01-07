<?php

namespace DrH\Tanda\Tests\Library;

use DrH\Tanda\Exceptions\TandaException;
use DrH\Tanda\Tests\MockServerTestCase;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;

class AuthenticatorTest extends MockServerTestCase
{
    /** @test */
    function can_authenticate_successfully()
    {
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth_success'])));

        $accessToken = (new \DrH\Tanda\Library\Authenticator($this->_client))->authenticate();

        $this->assertEquals("token", $accessToken);
    }

    /** @test */
    function can_store_key_in_cache()
    {
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth_success'])));

        (new \DrH\Tanda\Library\Authenticator($this->_client))->authenticate();

        $accessToken = (new \DrH\Tanda\Library\Authenticator($this->_client))->authenticate();

        $this->assertEquals("token", $accessToken);
    }

    /** @test */
    function throws_on_unset_client_credentials()
    {
        $this->expectException(TandaException::class);

        Config::set('tanda.client_id');

        (new \DrH\Tanda\Library\Authenticator($this->_client))->authenticate();
    }

    /** @test */
    function throws_on_incorrect_client_credentials()
    {
        $this->mock->append(
            new Response(401, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth_failure'])));

        $this->expectException(TandaException::class);

        Config::set('tanda.client_secret', 'somethingWRONGgoeshere');

        (new \DrH\Tanda\Library\Authenticator($this->_client))->authenticate();
    }

    /** @test */
    function throws_on_authentication_failure()
    {
        $this->mock->append(
            new Response(401, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth_failure'])));

        $this->expectException(TandaException::class);

        (new \DrH\Tanda\Library\Authenticator($this->_client))->authenticate();
    }

    /** @test */
    function throws_on_unexpected_status_code()
    {
        $this->mock->append(
            new Response(301, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth_failure'])));

        $this->expectException(TandaException::class);

        (new \DrH\Tanda\Library\Authenticator($this->_client))->authenticate();
    }
}