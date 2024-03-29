<?php

namespace DrH\Tanda\Tests\Library;

use DrH\Tanda\Exceptions\TandaException;
use DrH\Tanda\Library\Endpoints;
use DrH\Tanda\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class EndpointsTest extends TestCase
{
    /** @test */
    function get_url_from_valid_endpoint()
    {
        Config::set('tanda.organization_id', 'org');
        $testUrl = Endpoints::build(Endpoints::REQUEST);

        $this->assertStringContainsString(Config::get('tanda.urls.base'), $testUrl);
    }

    /** @test */
    function replaces_organization_id_correctly()
    {
        Config::set('tanda.organization_id', 'org');

        $testUrl = Endpoints::build(Endpoints::REQUEST);
        $actualUrl = Config::get('tanda.urls.base') . '/io/v1/organizations/org/requests';

        $this->assertSame($actualUrl, $testUrl);
    }

    /** @test */
    function throws_on_invalid_endpoint()
    {
        Config::set('tanda.organization_id', 'org');

        $this->expectException(TandaException::class);

        Endpoints::build("test_invalid");
    }

    /** @test */
    function throws_on_unset_organization_id()
    {
        Config::set('tanda.organization_id');

        $this->expectException(TandaException::class);

        Endpoints::build(Endpoints::REQUEST);
    }

    /** @test */
    function get_correct_url_when_base_url_not_set()
    {
        Config::set('tanda.base.url');
        Config::set('tanda.organization_id', 'org');

        $testUrl = Endpoints::build(Endpoints::REQUEST);

        $this->assertStringContainsString("https://io-proxy-443.tanda.co.ke", $testUrl);

        Config::set('tanda.sandbox', true);

        $testUrl = Endpoints::build(Endpoints::REQUEST);

        $this->assertStringContainsString("https://tandaio-api-uats.tanda.co.ke", $testUrl);
    }
}
