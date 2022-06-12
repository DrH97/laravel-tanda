<?php

use Illuminate\Support\Facades\Log;
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