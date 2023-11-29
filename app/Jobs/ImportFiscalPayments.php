<?php

namespace App\Jobs;

use App\Models\Payment;
use Carbon\Carbon;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Log;
use PDO;
use Throwable;

class ImportFiscalPayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200;

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
    public function handle(): void
    {
        $dateFrom = Carbon::now()->subDays(5)->format('d.m.Y');
        $dateTo = Carbon::now()->format('d.m.Y');

        $sql = "
                select pe.pay_event_id,
       pe.pay_dt,
       pe.cre_dttm,
       pay.acct_id,
       pay.pay_amt,
       ac.cis_division,
       (select tc.tndr_source_cd
          from rusadm.ci_pay_tndr tn, rusadm.ci_tndr_ctl tc
         where tn.pay_event_id = pe.pay_event_id
           and tc.tndr_ctl_id = tn.tndr_ctl_id) tndr_source_cd,
       (select ptc.adhoc_char_val
          from rusadm.ci_pay_tndr pt, rusadm.ci_pay_tndr_char ptc
         where pt.pay_event_id = pe.pay_event_id
           and ptc.pay_tender_id = pt.pay_tender_id
           and trim(ptc.char_type_cd) = 'FALENAME') file_name
  from rusadm.ci_pay_event        pe,
       rusadm.ci_pay              pay,
       rusadm.ci_acct             ac,
       rusadm.f1_ext_lookup_val   elv,
       rusadm.f1_ext_lookup_val_l elvl,
       rusadm.ci_acct_per         ap,
       rusadm.ci_per              p
 where pe.pay_event_id = pay.pay_event_id
   and pe.pay_dt between to_date('" . $dateFrom . "', 'dd.mm.yyyy')
                     and to_date('" . $dateTo . "', 'dd.mm.yyyy')
   and ac.acct_id = pay.acct_id
   and ap.acct_id = ac.acct_id
   and ap.main_cust_sw = 'Y'
   and p.per_id = ap.per_id
   and trim(elv.bus_obj_cd) = (case
         when trim(ac.cis_division) = 'LESK' then
          'CM_EL_ORG'
         when trim(ac.cis_division) = 'GESK' then
          'CM_EL_ORG_G'
       end)
   and elv.bus_obj_cd = elvl.bus_obj_cd
   and elv.f1_ext_lookup_value = elvl.f1_ext_lookup_value
   and elvl.language_cd = 'RUS'
   and ((trim(elv.f1_ext_lookup_value) = '01' and ac.cis_division = 'GESK') or
       (trim(extractValue(XMLType('<root>' || elv.bo_data_area ||
                                   '</root>'),
                           '//root/tarrifTable/kod_bd/text()')) =
       trim(p.state) and ac.cis_division = 'LESK'))
   and trim(extractValue(XMLType('<root>' || elv.bo_data_area || '</root>'),
                         '//root/tarrifTable/cisDivision/text()')) =
       trim(ac.cis_division)
   and exists
 (select null
          from rusadm.ci_pay_tndr pt, rusadm.ci_pay_tndr_char ptc
         where pt.pay_event_id = pe.pay_event_id
           and ptc.pay_tender_id = pt.pay_tender_id
           and ptc.char_type_cd = 'KASSA'
           and ptc.srch_char_val = 'KASSA')
            ";
        $pdo = DB::connection('oracle')->getPDO();
        $query = $pdo->prepare($sql);
        $query->execute();
        $i = 0;
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $payment = Payment::firstOrCreate([
                'pay_event_id' => trim($row['pay_event_id']),
                'operation_id' => Str::uuid()->toString(),
                'account_id' => trim($row['acct_id']),
                'amount' => trim($row['pay_amt']),
                'tender_source' => trim($row['tndr_source_cd']),
                'file_name' => trim($row['file_name']),
                'cis_division' => trim($row['cis_division']),
                'pay_date_oracle' => trim($row['pay_dt']),
                'create_date_oracle' => trim($row['cre_dttm']),
            ]);
            if ($payment->wasRecentlyCreated) {
                /*
                * Ставим в очередь на отправку
                */
                $payment = Payment::where('pay_event_id', $row['pay_event_id'])->first();
                if ($payment) {
                    SendToKKT::dispatch($payment);
                }
                $i++;
            }
        }
        Log::channel('single')->info('Job success. ' . $i . ' rows worked');
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::channel('single')->error('Job Failed', ['exception' => $exception]);
    }
}
