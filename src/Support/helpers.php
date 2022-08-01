<?php

use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

//TODO: Add tests for all these helpers
//    e.g. if logging channels doesn't exist, we shouldn't throw error
if (!function_exists('shouldLog')) {
    function shouldLog(): bool
    {
        return config('tanda.logging.enabled') == true;
    }
}

if (!function_exists('getLogger')) {
    function getLogger(): LoggerInterface
    {
        if (shouldLog()) {
            $channels = [];
            foreach (config('tanda.logging.channels') as $rawChannel) {
                if (is_string($rawChannel)) {
                    $channels[] = $rawChannel;
                } elseif (is_array($rawChannel)) {
                    $channels[] = Log::build($rawChannel);
                }
            }
            return Log::stack($channels);
        }

        return Log::build([
            'driver' => 'single',
            'path' => '/dev/null',
        ]);
    }
}

if (!function_exists('tandaLog')) {
    function tandaLog(string $level, string $message, array $context = []): void
    {
        $message = '[LIB - TANDA]: ' . $message;
        getLogger()->log($level, $message, $context);
    }
}

if (!function_exists('tandaLogError')) {
    function tandaLogError(string $message, array $context = []): void
    {
        $message = '[LIB - TANDA]: ' . $message;
        getLogger()->error($message, $context);
    }
}

if (!function_exists('tandaLogInfo')) {
    function tandaLogInfo(string $message, array $context = []): void
    {
        $message = '[LIB - TANDA]: ' . $message;
        getLogger()->info($message, $context);
    }
}



if (!function_exists('parseData')) {
    function parseGuzzleResponse(ResponseInterface $response, bool $includeBody): array
    {
        $headers = [];
        $excludeHeaders = ['set-cookie'];
        foreach ($response->getHeaders() as $name => $value) {
            if (in_array(strtolower($name), $excludeHeaders)) {
                continue;
            }

            $headers[$name] = $value;
        }


        // response is cloned to avoid any accidental data damage
        $body = (clone $response)->getBody();
        if (!$body->isReadable()) {
            $content = "unreadable";

            return [
                'protocol' => $response->getProtocolVersion(),
                'reason_phrase' => $response->getReasonPhrase(),
                'status_code' => $response->getStatusCode(),
                'headers' => $headers,
                'size' => $response->getBody()->getSize(),
                'body' => $content,
            ];
        }

        if ($body->isSeekable()) {
            $previousPosition = $body->tell();
            $body->rewind();
        }

        $content = $body->getContents();

        if ($body->isSeekable()) {
            $body->seek($previousPosition);
        }

        return $includeBody ?
            [
                'protocol' => $response->getProtocolVersion(),
                'reason_phrase' => $response->getReasonPhrase(),
                'status_code' => $response->getStatusCode(),
                'headers' => $headers,
                'size' => $response->getBody()->getSize(),
                'body' => $content,
            ] :
            [
                'protocol' => $response->getProtocolVersion(),
                'reason_phrase' => $response->getReasonPhrase(),
                'status_code' => $response->getStatusCode(),
                'headers' => $headers,
                'size' => $response->getBody()->getSize(),
            ];
    }
}