<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $pay_event_id;
    protected $amount;
    protected $cis_division;

    /**
     * @var
     * Номер операции в кассе
     */
    protected $operation_id;

    protected $table = 'payments';
    protected $fillable = [
        'id',
        'pay_event_id',
        'operation_id',
        'cis_division',
        'account_id',
        'amount',
        'tender_source',
        'file_name',
        'pay_date_oracle',
        'create_date_oracle',
        'fiscal_flag',
        'fiscal_status',
        'created_at',
        'updated_at',
        'fiscal_number',
        'shift_fiscal_number',
        'receipt_date',
        'fn_number',
        'kkt_registration_number',
        'fiscal_attribute',
        'fiscal_doc_number'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'pay_date_oracle',
        'cre_dttm',
        'receipt_date'
    ];

    /**
     * @return mixed
     */
    public function getOperationId()
    {
        return $this->operation_id;
    }

    /**
     * @param mixed $operation_id
     */
    public function setOperationId($operation_id): void
    {
        $this->operation_id = $operation_id;
    }
}
