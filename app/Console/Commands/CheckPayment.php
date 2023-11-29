<?php

namespace App\Console\Commands;

use App\Models\Cashbox;
use App\Models\Payment;
use Illuminate\Console\Command;

class CheckPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:check {id}';

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
        $id = $this->argument('id');
        $payment = Payment::find($id);

        $cashbox = new Cashbox($payment->cis_division);
        dd($cashbox->checkStatus($payment));
    }
}
