<?php

namespace DrH\Tanda\Library;

use DrH\Tanda\Exceptions\TandaException;
use GuzzleHttp\Exception\GuzzleException;

class Account extends Core
{
    //  Check account balance
    /**
     * @return array
     * @throws GuzzleException|TandaException
     */
    public function balance(): array
    {
        $params = [
            'accountTypes' => '01,02'
        ];
        return $this->request(Endpoints::BALANCE, [], [], $params);
    }
}
