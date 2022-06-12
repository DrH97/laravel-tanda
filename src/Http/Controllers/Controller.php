<?php

namespace DrH\Tanda\Http\Controllers;

use Carbon\Carbon;
use DrH\Tanda\Exceptions\TandaException;
use DrH\Tanda\Facades\Account;
use DrH\Tanda\Facades\Utility;
use DrH\Tanda\Library\EventHelper;
use DrH\Tanda\Models\TandaRequest;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * -----------------------------------------------------------------------------    ACCOUNT
     *
     * @return array
     */
    public function accountBalance(): array
    {
        return Account::balance();
    }

    /**
     * @param Request $request
     * @return array
     * @throws TandaException
     */
    public function requestStatus(Request $request): array
    {
        if (!$request->has('reference')) {
            throw new TandaException("Transaction reference is missing.");
        }

        return Utility::requestStatus($request->input('reference'));
    }

    /**
     * -----------------------------------------------------------------------------------------------    UTILITY
     *
     * @throws TandaException
     */
    public function airtimePurchase(Request $request): TandaRequest
    {
        $this->validateRequest([
            'phone' => 'required|regex:/[0-9]+/|digits_between:9,12',
            'amount' => 'required|regex:/[0-9]+/'
        ], $request, [
            'phone.required' => 'Phone number is required.',
            'phone.integer' => 'Invalid phone number. Must not start with zero.',
            'phone.digits_between' => 'The phone number must be between 9 and 12 digits long.',
            'amount.integer' => 'Invalid amount. Must not start with zero.',
        ]);

        return Utility::airtimePurchase($request->input('phone'), $request->input('amount'));
    }


    /**
     * @throws TandaException
     */
    public function validateRequest(array $rules, Request $request, $messages = [])
    {
        $validation = Validator::make($request->all(), $rules, $messages);

        if ($validation->fails()) {
            throw new TandaException($validation->errors()->first());
        }
    }

    public function instantPaymentNotification(Request $request)
    {
        tandaLogInfo("ipn: ", [$request]);

        try {
            $tandaRequest = TandaRequest::whereRequestId($request->input('transactionId'))->first();
            if (isset($tandaRequest)) {
                $tandaRequest->update([
                    'status' => $request->input('status'),
                    'message' => $request->input('message'),
                    'receipt_number' => $request->input('receiptNumber'),
                    'last_modified' => Carbon::parse($request->input('timestamp'))->utc(),
                    'result' => $request->input('resultParameters'),
                ]);
            } else {
                $tandaRequest = TandaRequest::create([
                    'request_id' => $request->input('transactionId'),
                    'provider' => $request->input('provider', ""),
                    'destination' => $request->input('destination', ""),
                    'status' => $request->input('status'),
                    'message' => $request->input('message'),
                    'receipt_number' => $request->input('receiptNumber'),
                    'last_modified' => Carbon::parse($request->input('timestamp'))->utc(),
                    'result' => $request->input('resultParameters'),
                ]);
            }

            EventHelper::fireTandaEvent($tandaRequest);
        } catch (QueryException $e) {
            Log::error('Error updating instant payment notification. - ' . $e->getMessage());
        }
    }
}
