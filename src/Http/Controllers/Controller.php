<?php

namespace DrH\Tanda\Http\Controllers;

use DrH\Tanda\Exceptions\TandaException;
use DrH\Tanda\Facades\Utility;
use DrH\Tanda\Models\TandaRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * -----------------------------------------------------------------------------------------------    UTILITY
     *
     * @throws TandaException
     */
    public function airtimePurchase(Request $request): TandaRequest {
        $this->validateRequest([
            'phone' => 'required|integer|digits_between:9,12',
            'amount' => 'required|integer'
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
}
