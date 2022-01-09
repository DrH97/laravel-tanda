<?php

namespace DrH\Tanda\Tests\Library;

use DrH\Tanda\Exceptions\TandaException;
use DrH\Tanda\Library\Commands;
use DrH\Tanda\Library\Core;
use DrH\Tanda\Library\Endpoints;
use DrH\Tanda\Library\Providers;
use DrH\Tanda\Tests\MockServerTestCase;
use GuzzleHttp\Psr7\Response;

class CoreTest extends MockServerTestCase
{

    /** @test */
    function send_request_successfully()
    {
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth_success'])));
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['request_pending'])));

        $req = (new Core($this->_client))->request(Endpoints::REQUEST, []);

        $this->assertIsArray($req);
        $this->assertEquals(00001, $req['status']);
    }

    /** @test */
    function throws_on_authentication_failure()
    {
        $this->mock->append(
            new Response(401, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth_failure'])));

        $this->expectException(TandaException::class);

        (new Core($this->_client))->request(Endpoints::REQUEST, []);
    }

    /** @test */
    function throws_on_request_failure()
    {
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth_success'])));
        $this->mock->append(
            new Response(400, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['request_unsuccessful'])));

        $this->expectException(TandaException::class);

        (new Core($this->_client))->request(Endpoints::REQUEST, []);
    }


    /** @test */
    function creates_correct_request_body()
    {
        $requestParameters = [
            [
                "id" => "accountNumber",
                "value" => '000000',
                "label" => "Customer's phone number"
            ],
            [
                "id" => "amount",
                "value" => 10,
                "label" => "Amount"
            ]
        ];
        $body = [
            'commandId' => Commands::AIRTIME_COMMAND,
            'serviceProviderId' => Providers::SAFARICOM,
            'requestParameters' => $requestParameters
        ];

        $body = (new Core($this->_client))->getBody($body);

        $this->assertIsArray($body);
        $this->assertArrayHasKey('referenceParameters', $body);
    }

}