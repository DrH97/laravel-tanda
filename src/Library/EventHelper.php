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
            event(new TandaRequestEvent($request));
            return;
        }

        $request->status == 000000
            ? TandaRequestSuccessEvent::dispatch($request)
            : TandaRequestFailedEvent::dispatch($request);
    }
}