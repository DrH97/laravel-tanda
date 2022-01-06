<?php

namespace DrH\Tanda\Library;

class Core
{
    private BaseClient $baseClient;

    /**
     *
     * @param BaseClient $baseClient
     */
    public function __construct(BaseClient $baseClient)
    {
        $this->baseClient = $baseClient;
    }



}