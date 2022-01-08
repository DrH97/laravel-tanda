<?php

namespace DrH\Tanda\Tests\Library;

use DrH\Tanda\Tests\MockServerTestCase;
use GuzzleHttp\Psr7\Response;

class AccountTest extends MockServerTestCase
{
    /** @test */
    function balance()
    {
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['auth_success'])));
        $this->mock->append(
            new Response(200, ['Content_type' => 'application/json'],
                json_encode($this->mockResponses['balance'])));

        $bal = (new \DrH\Tanda\Library\Account($this->_client))->balance();

        $this->assertIsArray($bal);
        $this->assertEquals(399930, $bal['Account_Bal']);
    }
}