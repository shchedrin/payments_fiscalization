<?php

namespace App\Jobs;

use App\Models\Cashbox;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class CheckPaymentStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 10;

    protected $payment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $cashbox = new Cashbox($this->payment->cis_division);
        $response = $cashbox->checkStatus($this->payment);

        if ($response->ok()) {
            $status = $response->json();

            if ($status['operation']['status'] == 'complete') {
                $fiscalization = $status['fiscalization'];

                $this->payment->fiscal_flag = true;

                $this->payment->fiscal_number = $fiscalization['fiscal_number'];
                $this->payment->shift_fiscal_number = $fiscalization['shift_fiscal_number'];
                $this->payment->receipt_date = Carbon::createFromTimestampUTC(
                    $fiscalization['receipt_date']
                )->setTimezone('Europe/Moscow');
                $this->payment->fn_number = $fiscalization['fn_number'];
                $this->payment->kkt_registration_number = $fiscalization['kkt_registration_number'];
                $this->payment->fiscal_attribute = $fiscalization['fiscal_attribute'];
                $this->payment->fiscal_doc_number = $fiscalization['fiscal_doc_number'];

                $this->payment->save();
            }
        }
    }
}
