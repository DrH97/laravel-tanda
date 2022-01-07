<?php

namespace DrH\Tanda\Tests\Models;

use DrH\Tanda\Models\TandaRequest;
use DrH\Tanda\Tests\TestCase;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TandaRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_request_has_unique_request_id()
    {
        $request = TandaRequest::create([
            'request_id' => '6c8fed6c-6548-4947-9aa6-19d70c85c332',
            "status" => "000001",
            "message" => "Request received successfully.",
            "command_id" => "TopupFlexi",
            "provider" => "SAFARICOM",
            'destination' => '0715330000',
            'amount' => '1500',
            'result' => [],
            'last_modified' => "2022-01-06 12:50:28.034 +0100",
            'relation_id' => null
        ]);


        try {
            TandaRequest::create([
                'request_id' => '6c8fed6c-6548-4947-9aa6-19d70c85c332',
                "status" => "000001",
                "message" => "Request received successfully.",
                "command_id" => "TopupFlexi",
                "provider" => "SAFARICOM",
                'destination' => '0715330000',
                'amount' => '1500',
                'result' => [],
                'last_modified' => "2022-01-06 12:50:28.034 +0100",
                'relation_id' => null
            ]);
        } catch (QueryException $e) {
            $this->assertStringContainsString("UNIQUE constraint failed", $e->getMessage());
        }

    }
}