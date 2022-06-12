<?php

namespace DrH\Tanda\Library;

use DrH\Tanda\Events\TandaRequestEvent;
use DrH\Tanda\Events\TandaRequestFailedEvent;
use DrH\Tanda\Events\TandaRequestSuccessEvent;
use DrH\Tanda\Models\TandaRequest;

class EventHelper
{
    /**
     * @param TandaRequest $request
     * @return void
     */
    public static function fireTandaEvent(TandaRequest $request): void
    {
        if ($request->status == 000001) {
            tandaLogInfo("fireTandaEvent", [$request]);

            event(new TandaRequestEvent($request));
            return;
        }

        tandaLogInfo("fireTandaFinalEvent", [$request]);

        $request->status == 000000
            ? TandaRequestSuccessEvent::dispatch($request)
            : TandaRequestFailedEvent::dispatch($request);
    }
}