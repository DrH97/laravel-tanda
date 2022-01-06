<?php

namespace DrH\Tanda\Events;

use DrH\Tanda\Models\TandaRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TandaRequestSuccessEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public TandaRequest $request;

    /**
     * @param TandaRequest $request
     */
    public function __construct(TandaRequest $request)
    {
        $this->request = $request;
    }
}
