<?php

namespace DrH\Tanda\Library;

use Carbon\Carbon;
use DrH\Tanda\Events\TandaRequestFailedEvent;
use DrH\Tanda\Events\TandaRequestSuccessEvent;
use DrH\Tanda\Exceptions\TandaException;
use DrH\Tanda\Models\TandaRequest;
use GuzzleHttp\Exception\GuzzleException;
use JetBrains\PhpStorm\ArrayShape;

class Tanda
{
    private Utility $utility;

    public function __construct(BaseClient $baseClient)
    {
        $this->utility = new Utility($baseClient);
    }

    #[ArrayShape(['successful' => "array", 'errors' => "array"])]
    public function queryRequestStatus(): array
    {
        /** @var TandaRequest::[] $tandaRequests */
        $tandaRequests = TandaRequest::whereStatus(000001)->get();
        $success = $errors = [];

        foreach ($tandaRequests as $request) {
            try {
                $result = $this->utility->requestStatus($request->request_id);

                $success[$request->request_id] = $result['message'];

                $data = [
                    'status'         => $result['status'],
                    'message'        => $result['message'],
                    'receipt_number' => $result['receiptNumber'],
                    'result'         => $result['resultParameters'],
                    'last_modified'  => Carbon::parse($result['datetimeLastModified'])->utc(),
                ];

                $transaction = TandaRequest::updateOrCreate(
                    ['request_id' => $result['id']],
                    $data
                );

                $this->fireTandaEvent($transaction);
            } catch (TandaException | GuzzleException $e) {
                $errors[$request->merchant_reference] = $e->getMessage();
            }
        }

        return ['successful' => $success, 'errors' => $errors];
    }

    /**
     * @param TandaRequest $request
     * @return void
     */
    public static function fireTandaEvent(TandaRequest $request): void
    {
        if ($request->status == 000001) {
            return;
        }

        in_array($request->status, [000000, 000002])
            ? TandaRequestSuccessEvent::dispatch($request)
            : TandaRequestFailedEvent::dispatch($request);
    }
}
