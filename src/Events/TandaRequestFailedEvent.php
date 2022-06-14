<?php

namespace DrH\Tanda\Events;

use DrH\Tanda\Models\TandaRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TandaRequestFailedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public TandaRequest $request;

//    TODO: Change this when error returned is confirmed

    /**
     * @param TandaRequest $request
     */
    public function __construct(TandaRequest $request)
    {
        tandaLogInfo("TandaRequestFailedEvent: ", [$request]);

        $this->request = $request;
    }
}
