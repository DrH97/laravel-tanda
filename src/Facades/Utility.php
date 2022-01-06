<?php

namespace DrH\Tanda\Facades;

use Illuminate\Support\Facades\Facade;
use DrH\Tanda\Models\TandaRequest as TR;

/**
 * @method static TR airtimePurchase(int $phone, int $amount, int $relationId = null)
 *
 * @see \DrH\Tanda\Library\Utility
 */
class Utility extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \DrH\Tanda\Library\Utility::class;
    }
}
