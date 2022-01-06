<?php

namespace DrH\Tanda\Tests;

use DrH\Tanda\TandaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            TandaServiceProvider::class,
        ];
    }
}
