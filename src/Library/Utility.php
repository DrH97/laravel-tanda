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

    private string $phone;

    /**
     * @param int $phone
     * @param int $amount
     * @param int|null $relationId
     * @param bool $save
     * @return array|TandaRequest
     * @throws GuzzleException
     * @throws TandaException
     */
    public function airtimePurchase(int $phone, int $amount, int $relationId = null, bool $save = true): array
    {
        $this->validate("AIRTIME", $amount);

        $this->provider = $this->getTelcoFromPhone($phone);
        $this->phone = $this->formatPhoneNumber($phone);
        $this->amount = $amount;

        $body = [
            'amount' => $amount,
            'phone' => $this->phone,
            'telco' => $this->provider,
            'initiatorPhone' => $phone,
        ];

        $response = $this->request('airtime', $body);

        if ($save) {
            return (array)$this->saveRequest($response, $relationId);
        }

        return $response;
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
                'destination' => $this->phone,
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
        if ($request->status == 0001) {
            return;
        }
        if ($request->status == 0000) {
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

            case Providers::KPLC:
                $min = config('tanda.limits.bills.KPLC.min', 100);
                $max = config('tanda.limits.bills.KPLC.max', 35000);

                break;

            default:
                return;
        }

        if ($amount < $min || $amount > $max) {
            throw new TandaException("Amount needs to be between $min and $max.");
        }

    }
}