<?php

namespace App\Console\Commands;

use App\Models\Cashbox;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

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
        $response = $cashbox->checkStatus($payment);

        if ($response->ok()) {
            $status = $response->json();

            if ($status['operation']['status'] == 'complete') {
                $fiscalization = $status['fiscalization'];

                $payment->fiscal_flag = true;

                $payment->fiscal_number = $fiscalization['fiscal_number'];
                $payment->shift_fiscal_number = $fiscalization['shift_fiscal_number'];
                $payment->receipt_date = Carbon::createFromTimestampUTC($fiscalization['receipt_date'])->format('Y-m-d H:i:s');
                $payment->fn_number = $fiscalization['fn_number'];
                $payment->kkt_registration_number = $fiscalization['kkt_registration_number'];
                $payment->fiscal_attribute = $fiscalization['fiscal_attribute'];
                $payment->fiscal_doc_number = $fiscalization['fiscal_doc_number'];

                $payment->save();
            }
        }
    }
}
