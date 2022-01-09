<?php

namespace DrH\Tanda\Library;

use DrH\Tanda\Events\TandaRequestFailedEvent;
use DrH\Tanda\Events\TandaRequestSuccessEvent;
use DrH\Tanda\Exceptions\TandaException;
use DrH\Tanda\Models\TandaRequest;
use GuzzleHttp\Exception\GuzzleException;

class Utility extends Core
{
    private string $provider;

    private int $amount;

    private string $destination;

    private string $command;

    /**
     * @param int $phone
     * @param int $amount
     * @param int|null $relationId
     * @param bool $save
     * @return array|TandaRequest
     * @throws GuzzleException
     * @throws TandaException
     */
    public function airtimePurchase(
        int $phone,
        int $amount,
        int $relationId = null,
        bool $save = true
    ): array | TandaRequest {
        $this->validate("AIRTIME", $amount);

        $this->provider = $this->getTelcoFromPhone($phone);
        $this->destination = $this->formatPhoneNumber($phone);
        $this->amount = $amount;
        $this->setCommand($this->provider);

        $requestParameters = [
            [
                "id" => "amount",
                "value" => $this->amount,
                "label" => "Amount"
            ],
            [
                "id" => "accountNumber",
                "value" => $this->destination,
                "label" => "Customer's phone number"
            ]
        ];
        $body = [
            'commandId' => $this->command,
            'serviceProviderId' => $this->provider,
            'requestParameters' => $requestParameters
        ];

        $response = $this->request(Endpoints::REQUEST, $body);

        if ($save) {
            return $this->saveRequest($response, $relationId);
        }

        return $response;
    }


    /**
     * @param int $accountNo
     * @param int $amount
     * @param string $provider
     * @param int $phone
     * @param int|null $relationId
     * @param bool $save
     * @return array|TandaRequest
     * @throws GuzzleException
     * @throws TandaException
     */
    public function billPayment(
        int $accountNo,
        int $amount,
        string $provider,
        int $phone,
        int $relationId = null,
        bool $save = true
    ): array | TandaRequest {
        $allowedProviders = [
            Providers::KPLC_PREPAID,
            Providers::KPLC_POSTPAID,
            Providers::GOTV,
            Providers::DSTV,
            Providers::ZUKU,
            Providers::STARTIMES,
            Providers::NAIROBI_WTR
        ];

        $this->validate($provider, $amount);

        if (!in_array(strtoupper($provider), $allowedProviders)) {
            throw new TandaException("Provider does not seem to be valid or supported");
        }

        $this->provider = $provider;
        $this->amount = $amount;
        $this->destination = $phone;

        $this->setCommand($this->provider);

//        TODO: Check whether customerContact is necessary or what it is used for.
//              $phone = $this->formatPhoneNumber($phone);
        $requestParameters = [
            [
                "id" => "accountNumber",
                "value" => $accountNo,
                "label" => "A/c"
            ],
            [
                "id" => "amount",
                "value" => $this->amount,
                "label" => "Amount"
            ]
        ];

        $body = [
            'commandId' => $this->command,
            'serviceProviderId' => $this->provider,
            'requestParameters' => $requestParameters
        ];

        $response = $this->request(Endpoints::REQUEST, $body);

        if ($save) {
            return $this->saveRequest($response, $relationId);
        }

        return $response;
    }

    //  Check request status

    /**
     * @param string $reference
     * @return array
     * @throws GuzzleException|TandaException
     */
    public function requestStatus(string $reference): array
    {
        return $this->request(Endpoints::STATUS, [], [':requestId' => $reference]);
    }

    private function setCommand(string $provider)
    {
        $this->command = match ($provider) {
            Providers::DSTV, Providers::GOTV, Providers::STARTIMES, Providers::ZUKU => Commands::TV_COMMAND,
            Providers::SAFARICOM, Providers::AIRTEL, Providers::TELKOM => Commands::AIRTIME_COMMAND,
            Providers::KPLC_POSTPAID, Providers::NAIROBI_WTR => Commands::POSTPAID_BILL_COMMAND,
            Providers::KPLC_PREPAID => Commands::KPLC_PREPAID_COMMAND,
            Providers::FAIBA => Commands::FAIBA_COMMAND,
        };
    }

    /**
     * @throws TandaException
     */
    private function saveRequest(array $response, int $relationId = null): TandaRequest
    {
        try {
            $request = TandaRequest::create([
                'request_id' => $response['id'],
                'status' => $response['status'],
                'message' => $response['message'],
                'command_id' => $response['commandId'],
                'provider' => $this->provider,

//                TODO: Would it be better to decode the requestParameters and get below values??
                'destination' => $this->destination,
                'amount' => $this->amount,

                'result' => $response['resultParameters'],

                'last_modified' => $response['datetimeLastModified'],

                'relation_id' => $relationId
            ]);

//            TODO: Is it necessary? X Doubt
            $this->fireTandaEvent($request);

            return $request;
        } catch (\Exception $e) {
            throw new TandaException($e->getMessage());
        }
    }

    /**
     * @param TandaRequest $request
     * @return void
     */
    private function fireTandaEvent(TandaRequest $request): void
    {
//        TODO: Check on proper status codes
        if ($request->status == 000001) {
            return;
        }
        if ($request->status == 000000) {
            event(new TandaRequestSuccessEvent($request));
        } else {
            event(new TandaRequestFailedEvent($request));
        }
    }

    /**
     * @throws TandaException
     */
    private function validate(string $validationType, int $amount): void
    {
        $min = 0;
        $max = 0;

        switch ($validationType) {
            case "AIRTIME":
                $min = config('tanda.limits.AIRTIME.min', 10);
                $max = config('tanda.limits.AIRTIME.max', 10000);

                break;

            case Providers::KPLC_POSTPAID:
                $min = config('tanda.limits.bills.KPLC_POSTPAID.min', 100);
                $max = config('tanda.limits.bills.KPLC_POSTPAID.max', 35000);

                break;

            case Providers::KPLC_PREPAID:
                $min = config('tanda.limits.bills.KPLC_PREPAID.min', 100);
                $max = config('tanda.limits.bills.KPLC_PREPAID.max', 35000);

                break;

            default:
                return;
        }

        if ($amount < $min || $amount > $max) {
            throw new TandaException("Amount needs to be between $min and $max.");
        }
    }
}
