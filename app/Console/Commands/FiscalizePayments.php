<?php

namespace App\Console\Commands;

use App\Models\Cashbox;
use App\Models\Payment;
use Illuminate\Console\Command;

class FiscalizePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:fiscalize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $payment = Payment::find(1);

        $cashbox = new Cashbox('TEST');
        $result = $cashbox->sendDocument($payment);
        dd($result);
        return $result;
    }
}
