<?php

namespace DrH\Tanda\Library;

use Carbon\Carbon;
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
        int $relationId = null,
        bool $save = true
    ): array | TandaRequest {
        $provider = strtoupper($provider);

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

        if (!in_array($provider, $allowedProviders)) {
            throw new TandaException("Provider does not seem to be valid or supported");
        }

        $this->provider = $provider;
        $this->amount = $amount;
        $this->destination = $accountNo;

        $this->setCommand($this->provider);

        if ($provider == Providers::KPLC_PREPAID) {
            $accountNo = sprintf('%011d', $accountNo);
        }

        if (in_array($provider, [Providers::KPLC_PREPAID, Providers::KPLC_POSTPAID])) {
            $provider = 'KPLC';
        } else {
            $provider = $this->provider;
        }

//        TODO: Check whether customerContact is necessary or what it is used for.
//              $phone = $this->formatPhoneNumber($phone);
//        NOTE: KPLC accounts are supposedly 11 digits. pad with zeroes on the left
//        TODO: Should we change accountNo to string? int may be removing initial zeroes
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
            'serviceProviderId' => $provider,
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
        $response = $this->request(Endpoints::STATUS, [], [':requestId' => $reference]);

        $request = TandaRequest::whereRequestId($response['id'])->first();

//        TODO: Create update function separately to avoid redundancies with controller
        if ($request && $request->status !== $response['status']) {
            $request->update([
                'status' => $response['status'],
                'message' => $response['message'],
                'receipt_number' => $response['receiptNumber'],
                'result' => $response['resultParameters'],
                'last_modified' => Carbon::parse($response['datetimeLastModified'])->utc(),
            ]);

            EventHelper::fireTandaEvent($request);
        }

        return $response;
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
                'command_id' => $this->command,
                'provider' => $this->provider,

//                TODO: Would it be better to decode the requestParameters and get below values??
                'destination' => $this->destination,
                'amount' => $this->amount,

                'result' => $response['resultParameters'],

                'last_modified' => $response['datetimeLastModified'],

                'relation_id' => $relationId
            ]);

            EventHelper::fireTandaEvent($request);

            return $request;
        } catch (\Exception $e) {
            throw new TandaException($e->getMessage());
        }
    }

    /**
     * @throws TandaException
     */
    private function validate(string $validationType, int $amount): void
    {

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
