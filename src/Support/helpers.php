<?php

use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

if (!function_exists('getLogChannel')) {
    function getLogChannel(): LoggerInterface
    {
        //TODO: Add config to determine where to log
        if (!shouldLog())
            $path = "/dev/null";
        else
            $path = storage_path('logs/tanda.log');

        return Log::build([
            'driver' => 'single',
            'path' => $path,
        ]);
    }
}

if (!function_exists('tandaLog')) {
    function tandaLog(string|array $level, string $message, array $context = []): void
    {
        getLogChannel()->log($level, $message, $context);
    }
}

if (!function_exists('tandaLogError')) {
    function tandaLogError(string|array $message, array $context = []): void
    {
        getLogChannel()->error($message, $context);
    }
}

if (!function_exists('tandaLogInfo')) {
    function tandaLogInfo(string|array $message, array $context = []): void
    {
        getLogChannel()->info($message, $context);
    }
}

if (!function_exists('shouldLog')) {
    function shouldLog(): bool
    {
        return config('tanda.enable_logging') == true;
    }
}

if (!function_exists('parseData')) {
    function parseGuzzleResponse(ResponseInterface $response): array
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

        return [
            'protocol' => $response->getProtocolVersion(),
            'reason_phrase' => $response->getReasonPhrase(),
            'status_code' => $response->getStatusCode(),
            'headers' => $headers,
            'size' => $response->getBody()->getSize(),
            'body' => $content,
        ];
    }
}