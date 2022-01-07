<?php

namespace DrH\Tanda\Library;

use DrH\Tanda\Exceptions\TandaException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

class Core
{
    private BaseClient $baseClient;

    private string $bearer;

    /**
     *
     * @param BaseClient $baseClient
     */
    public function __construct(BaseClient $baseClient)
    {
        $this->baseClient = $baseClient;
    }


    /**
     * @throws TandaException|GuzzleException
     */
    public function request(string $endpointSuffix, array $body): array
    {
        $endpoint = Endpoints::build($endpointSuffix);
        $method = Endpoints::ENDPOINT_REQUEST_TYPES[$endpointSuffix];

        try {
            $response = $this->sendRequest($method, $endpoint, $body);
            $_body = json_decode($response->getBody());

            return (array)$_body;
        } catch (ClientException | ServerException $exception) {
            throw new TandaException($exception->getResponse()->getBody());
        }
    }

    /**
     * @throws TandaException|GuzzleException
     */
    public function sendRequest(string $method, string $endpoint, array $body): ResponseInterface
    {
        $this->bearer = $this->baseClient->authenticator->authenticate();

//        Added these to reduce redundancy in child classes
        $body += [
            'referenceParameters' => $this->getReferenceParameters()
        ];

        return $this->baseClient->clientInterface->request(
            $method,
            $endpoint,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->bearer,
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
            ]
        );
    }

    /**
     * @throws TandaException
     */
    public function getTelcoFromPhone(int $phone): string
    {
        $safReg = '/^(?:254|\+254|0)?((?:7(?:[0129][0-9]|4[0123568]|5[789]|6[89])|(1([1][0-5])))[0-9]{6})$/';
        $airReg = '/^(?:254|\+254|0)?((?:(7(?:(3[0-9])|(5[0-6])|(6[27])|(8[0-9])))|(1([0][0-6])))[0-9]{6})$/';
        $telReg = '/^(?:254|\+254|0)?(7(7[0-9])[0-9]{6})$/';
        $equReg = '/^(?:254|\+254|0)?(7(6[3-6])[0-9]{6})$/';
        $faibaReg = '/^(?:254|\+254|0)?(747[0-9]{6})$/';

        switch (1) {
            case preg_match($safReg, $phone):
                $result = Providers::SAFARICOM;
                break;
            case preg_match($airReg, $phone):
                $result = Providers::AIRTEL;
                break;
            case preg_match($telReg, $phone):
                $result = Providers::TELKOM;
                break;
//            case preg_match($equReg, $phone):
//                $result = Providers::EQUITEL;
//                break;
            case preg_match($faibaReg, $phone):
                $result = Providers::FAIBA;
                break;
            default:
                $result = null;
                break;
        }

        if (!$result) {
            throw new TandaException("Phone does not seem to be valid or supported");
        }

        return $result;
    }

    /**
     * @throws TandaException
     */
    public function formatPhoneNumber(string $number, bool $strip_plus = true): string
    {
        $number = preg_replace('/\s+/', '', $number);

        $possibleStartingChars = ['+254', '0', '254', '7', '1'];

        if (!Str::startsWith($number, $possibleStartingChars)) {
            //            Number doesn't have valid starting digits e.g. -0254110000000
            throw new TandaException("Number does not seem to be a valid phone");
        }

        $replace = static function ($needle, $replacement) use (&$number) {
            if (Str::startsWith($number, $needle)) {
                $pos = strpos($number, $needle);
                $length = strlen($needle);
                $number = substr_replace($number, $replacement, $pos, $length);
            }
        };

        $replace('7', '2547');
        $replace('1', '2541');

        if ($strip_plus) {
            $replace('+254', '254');
        }

        if (!Str::startsWith($number, "254")) {
            //  Means the number started with correct digits but after replacing,
            //  found invalid digit e.g. 254256000000
            //  254 isn't found and so 254 does not replace it, which means false number
            throw new TandaException("Number does not seem to be a valid phone");
        }

        return $number;
    }

    private function getReferenceParameters(): array
    {
        return [
            "id" => "resultUrl",
            "value" => config('tanda.urls.callback'),
            "label" => "Hook"
        ];
    }
}
