<?php

namespace DrH\Tanda\Library;

use DrH\Tanda\Exceptions\TandaException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Account
 *
 * @package Nabcellent\Kyanda\Library
 */
class Account extends Core
{
    //  Check account balance
    /**
     * @return array
     * @throws GuzzleException|TandaException
     */
    public function balance(): array {
        return $this->request(Endpoints::BALANCE, []);
    }

    //  Check transaction status

    /**
     * @param string $reference
     * @return array
     * @throws GuzzleException|TandaException
     */
    public function transactionStatus(string $reference): array {
        $this->attachMerchantStart = true;

        $body = [
            "transactionRef" => $reference,
        ];

        return $this->request('transaction_status', $body);
    }
}
