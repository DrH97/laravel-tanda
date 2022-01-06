<?php

namespace DrH\Tanda\Tests\Unit;

use DrH\Tanda\Tests\MockServerTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionStatusCommandTest extends MockServerTestCase
{

    use RefreshDatabase;

    /** @test */
    function the_command_returns_nothing_text_when_no_unresolved_requests()
    {
        $this->artisan('tanda:query_status')
            ->expectsOutput('Nothing to query... all transactions seem to be ok.')
            ->assertExitCode(0);
    }

//    TODO: Would require us to inject a modded Kyanda repo to runtime
//    /** @test */
//    function the_command_echoes_successful_queries()
//    {
//        KyandaRequest::create([
//            'status_code' => '0000',
//            'status' => 'Success',
//            'merchant_reference' => 'KYAAPI677834',
//            'message' => 'Your request has been posted successfully!'
//        ]);
//
//        $this->mock->append(
//            new Response(200, ['Content_type' => 'application/json'],
//                json_encode($this->mockResponses['query_transaction_status'])));
//
//        $this->artisan('kyanda:query_status')
//            ->expectsOutput('Successful queries: ')
//            ->assertExitCode(0);
//    }

//    /** @test */
//    function the_command_echoes_failed_queries()
//    {
//        TandaRequest::create([
//            'status_code' => '0000',
//            'status' => 'Success',
//            'merchant_reference' => 'KYAAPI677834',
//            'provider' => Providers::SAFARICOM,
//            'message' => 'Your request has been posted successfully!'
//        ]);
//
//        $this->artisan('kyanda:query_status')
//            ->expectsOutput('Failed queries: ')
//            ->assertExitCode(0);
//    }
}
