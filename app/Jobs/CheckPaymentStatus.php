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
use Illuminate\Support\Facades\Log;

class CheckPaymentStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3500;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $payments = Payment::where('fiscal_flag', false)
            ->where('created_at', '<=', Carbon::now()->subHours(1)->toDateTimeString())
            ->orderBy('created_at', 'desc')
            ->get();

        foreach($payments as $payment) {
            $cashbox = new Cashbox($payment->cis_division);
            $response = $cashbox->checkStatus($payment);

            if ($response->ok()) {
                $status = $response->json();
                Log::info('CheckPaymentStatus', ['response' => $status]);

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

                    Log::info('CheckPaymentStatus', ['payment' => $payment]);

                    $payment->save();
                }
            } else {
                Log::error('CheckPaymentStatus.error', [
                    'response' => $response,
                    'payment' => $payment
                ]);
            }
        }
    }
}
