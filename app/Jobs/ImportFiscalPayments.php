<?php

namespace App\Jobs;

use App\Models\Payment;
use Carbon\Carbon;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

class ImportFiscalPayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200;
    private DateTime $dateFrom;
    private DateTime $dateTo;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->dateFrom = Carbon::createFromFormat('d.m.Y', '22.05.2023');
        $this->dateTo = Carbon::createFromFormat('d.m.Y', '28.05.2023');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $memoryStart = memory_get_usage();
        try {
            $sql = "
                select pe.pay_event_id,
       pe.pay_dt,
       pe.cre_dttm,
       pay.acct_id,
       pay.pay_amt,
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
   and pe.pay_dt between to_date('" . $this->dateFrom->format('d.m.Y') . "', 'dd.mm.yyyy')
                     and to_date('" . $this->dateTo->format('d.m.Y') . "', 'dd.mm.yyyy')
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
                DB::table('payments')->insertOrIgnore([
                    'pay_event_id' => $row['pay_event_id'],
                    'account_id' => $row['acct_id'],
                    'amount' => $row['pay_amt'],
                    'tender_source' => $row['tndr_source_cd'],
                    'file_name' => $row['file_name'],
                    'pay_date_oracle' => $row['pay_dt'],
                    'create_date_oracle' => $row['cre_dttm'],
                ]);
                $i++;
            }
            print('Получено строк: ' . $i);
        } catch (Throwable $exception) {
            dd($exception);
        }
    }
}
