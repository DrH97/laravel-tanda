<?php

namespace DrH\Tanda\Tests\Library;

use DrH\Tanda\Exceptions\TandaException;
use DrH\Tanda\Library\Providers;
use DrH\Tanda\Library\Utility;
use DrH\Tanda\Models\TandaRequest;
use DrH\Tanda\Tests\MockServerTestCase;
use GuzzleHttp\Psr7\Response;

class UtilityTest extends MockServerTestCase
{
    /** @test */
    function airtime_purchase_is_successful()
    {
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth_success'])));
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['request_pending'])));

        $res = (new Utility($this->_client))->airtimePurchase(775432100, 10, null, false);

        $this->assertIsArray($res);
        $this->assertEquals('000001', $res['status']);
    }

    /** @test */
    function airtime_purchase_request_is_saved_to_db()
    {
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth_success'])));
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['request_success'])));

        $res = (new Utility($this->_client))->airtimePurchase("700000000", 10);

        $this->assertInstanceOf(TandaRequest::class, $res);
        $this->assertEquals(000000, $res->status);
    }

    /** @test */
    function airtime_purchase_fails_on_invalid_phone()
    {
        $this->expectException(TandaException::class);

//        Use facade class for testing coverage
        \DrH\Tanda\Facades\Utility::airtimePurchase("123765432100", 10);
    }


    /** @test */
    function bill_payment_is_successful()
    {
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth_success'])));
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['request_pending'])));

        $res = (new Utility($this->_client))->billPayment(765432100, 100, Providers::KPLC_POSTPAID, 765432100, null, false);

        $this->assertIsArray($res);
        $this->assertEquals('000001', $res['status']);
    }

    /** @test */
    function bill_payment_request_is_saved_to_db()
    {
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth_success'])));
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['request_failed'])));

//        TODO: Make tests cover all providers and commands
        $res = (new Utility($this->_client))->billPayment(765432100, 10, Providers::DSTV, 765432100);

        $this->assertInstanceOf(TandaRequest::class, $res);
        $this->assertEquals(000002, $res->status);
        $this->assertEquals(Providers::DSTV, $res->provider);
    }

    /** @test */
    function bill_payment_fails_on_invalid_provider()
    {
        $this->expectException(TandaException::class);

        (new Utility($this->_client))->billPayment(765432100, 10, Providers::SAFARICOM, 765432100);
    }

    /** @test */
    function bill_payment_fails_on_invalid_amount()
    {
        $this->expectException(TandaException::class);

        (new Utility($this->_client))->billPayment(765432100, 10, Providers::KPLC_POSTPAID, 765432100, null, false);
    }


    /** @test */
    function request_status_is_successful()
    {
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth_success'])));
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['request_success'])));

        $status = (new Utility($this->_client))->requestStatus("6c8fed6c-6548-4947-9aa6-19d70c85c332");

        $this->assertIsArray($status);
        $this->assertEquals(000000, $status['status']);
    }
}