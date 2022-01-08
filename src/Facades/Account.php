<?php

namespace DrH\Tanda\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array balance()
 *
 * @see \DrH\Tanda\Library\Utility
 */
class Account extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \DrH\Tanda\Library\Account::class;
    }
}
