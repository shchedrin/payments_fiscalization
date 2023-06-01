<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $fillable = [
        'id',
        'pay_event_id',
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
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'pay_date_oracle',
        'cre_dttm',
    ];
}
