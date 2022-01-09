<?php

namespace DrH\Tanda\Library;

use Carbon\Carbon;
use DrH\Tanda\Events\TandaRequestFailedEvent;
use DrH\Tanda\Events\TandaRequestSuccessEvent;
use DrH\Tanda\Exceptions\TandaException;
use DrH\Tanda\Models\TandaRequest;
use GuzzleHttp\Exception\GuzzleException;

class Tanda
{
    public function __construct(BaseClient $baseClient)
    {
        $this->utility = new Utility($baseClient);
    }

    public function queryRequestStatus(): array
    {
        /** @var TandaRequest::[] $tandaRequests */
        $tandaRequests = TandaRequest::whereStatus(000001)->get();
        $success = $errors = [];

        foreach($tandaRequests as $request) {
            try {
                $result = $this->utility->requestStatus($request->request_id);

                $success[$request->request_id] = $result->message;

                $data = [
                    'status'         => $result['status'],
                    'message'        => $result['message'],
                    'receipt_number' => $result['receiptNumber'],
                    'result'         => $result['resultParameters'],
                    'last_modified'  => Carbon::parse($result['datetimeLastModified'])->utc(),
                ];

                $transaction = TandaRequest::updateOrCreate(
                    ['request_id' => $result['id']], $data
                );

                $this->fireKyandaEvent($transaction);
            } catch (TandaException | GuzzleException $e) {
                $errors[$request->merchant_reference] = $e->getMessage();
            }
        }
        return ['successful' => $success, 'errors' => $errors];
    }

    /**
     * @param TandaRequest $tandaCallback
     * @return void
     */
    private function fireKyandaEvent(TandaRequest $tandaCallback): void
    {
//        TODO: Check on proper status codes
        in_array($tandaCallback['status_code'], [000001, 000002])
            ? TandaRequestSuccessEvent::dispatch($tandaCallback)
            : TandaRequestFailedEvent::dispatch($tandaCallback);
    }
}