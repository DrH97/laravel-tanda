<?php

namespace DrH\Tanda\Tests;

use DrH\Tanda\Library\BaseClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

abstract class MockServerTestCase extends TestCase
{
    use RefreshDatabase;

    protected BaseClient $_client;

    protected MockHandler $mock;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('tanda.client_id', 'somethinggoeshere');
        Config::set('tanda.client_secret', 'somethinggoeshere');
        Config::set('tanda.organization_id', 'somethinggoeshere');

        $this->mock = new MockHandler();

        $handlerStack = HandlerStack::create($this->mock);
        $this->_client = new BaseClient(new Client(['handler' => $handlerStack]));
    }

    protected array $mockResponses = [
        'auth_success' => [
            'access_token' => 'token',
            'token_type' => 'bearer',
            'expires_in' => 3599,
            'scope' => 'apiclient'
        ],
        'auth_failure' => [
            'error' => 'invalid_client',
            'error_description' => 'Bad client credentials',
        ],
        'balance' => [
            'Account_Bal' => 399930,
            'Earnings_Bal' => 1586.6
        ],
        'request_success' => [
            "id" => "6c8fed6c-6548-4947-9aa6-19d70c85c332",
            "status" => "000001",
            "message" => "Request received successfully.",
            "receiptNumber" => null,
            "commandId" => "TopupFlexi",
            "serviceProviderId" => "SAFARICOM",
            "datetimeCreated" => "2022-01-06 12:50:27.878 +0100",
            "datetimeLastModified" => "2022-01-06 12:50:28.034 +0100",
            "datetimeCompleted" => "2022-01-06 12:50:28.034 +0100",
            "requestParameters" => [
                [
                    "id" => "accountNumber",
                    "value" => "254714611696",
                    "label" => "Customer's phone number"
                ],
                [
                    "id" => "amount",
                    "value" => "10.00",
                    "label" => "Amount"
                ]
            ],
            "referenceParameters" => [
                [
                    "id" => "resultUrl",
                    "value" => "https://my-test.free.beeceptor.com/results",
                    "label" => "Callback URL"
                ]
            ],
            "resultParameters" => null
        ],
        'request_failed' => [
            "id" => "ca522017-6083-4857-b221-4798bce082c7",
            "status" => "000002",
            "message" => "Payment authorization failed. KES 10.00 exceeds the available Working Float balance",
            "receiptNumber" => null,
            "commandId" => "TopupFlexi",
            "serviceProviderId" => "SAFARICOM",
            "datetimeCreated" => "2022-01-06 12:50:27.878 +0100",
            "datetimeLastModified" => "2022-01-06 12:50:28.034 +0100",
            "datetimeCompleted" => "2022-01-06 12:50:28.034 +0100",
            "requestParameters" => [
                [
                    "id" => "accountNumber",
                    "value" => "254714611696",
                    "label" => "Customer's phone number"
                ],
                [
                    "id" => "amount",
                    "value" => "10.00",
                    "label" => "Amount"
                ]
            ],
            "referenceParameters" => [
                [
                    "id" => "resultUrl",
                    "value" => "https://my-test.free.beeceptor.com/results",
                    "label" => "Callback URL"
                ]
            ],
            "resultParameters" => null
        ],
    ];
}
