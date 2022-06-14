<?php

namespace DrH\Tanda\Repositories;

use DrH\Tanda\Exceptions\TandaException;
use DrH\Tanda\Library\BaseClient;
use DrH\Tanda\Library\Utility;
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
        /** @var TandaRequest[] $tandaRequests */
        $tandaRequests = TandaRequest::whereStatus(000001)->get();
        $success = $errors = [];

        foreach ($tandaRequests as $request) {
            try {
                $result = $this->utility->requestStatus($request->request_id);

                $success[$request->request_id] = $result['message'];

            } catch (TandaException | GuzzleException $e) {
                $errors[$request->request_id] = $e->getMessage();
            }
        }

        return ['successful' => $success, 'errors' => $errors];
    }
}
