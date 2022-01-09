<?php

namespace DrH\Tanda\Facades;

use DrH\Tanda\Models\TandaRequest as TR;
use Illuminate\Support\Facades\Facade;

/**
 * @method static TR airtimePurchase(int $phone, int $amount, int $relationId = null)
 * @method static array requestStatus(string $reference)
 * @method static array | TR billPayment(int $accountNo, int $amount, string $provider, int $phone, int $relationId = null, bool $save = true)
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
