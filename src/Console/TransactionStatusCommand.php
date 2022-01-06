<?php

namespace DrH\Tanda\Console;

use Illuminate\Console\Command;

class TransactionStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tanda:query_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the status of all missing transactions.';

//    private Tanda $tanda;
//
    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
//        $this->tanda = $repository;
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
//        $results = $this->tanda->queryTransactionStatus();
//
//        if (count($results['successful'])) {
//            $this->info("Successful queries: ");
//
//            foreach ($results['successful'] as $reference => $message) {
//                $this->comment(" * $reference ---> $message");
//            }
//        }
//
//        if (count($results['errors'])) {
//            $this->info("Failed queries: ");
//
//            foreach ($results['errors'] as $reference => $message) {
//                $this->comment(" * $reference ---> $message");
//            }
//        }
//
//        if (empty($results['successful']) && empty($results['errors'])) {
//            $this->comment("Nothing to query... all transactions seem to be ok.");
//        }
    }
}
