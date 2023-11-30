<?php

namespace App\Http\Controllers\Payments;

use App\Models\Payment;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Collection;

class PaymentsController extends Controller
{
    /**
     * Provision a new web server.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
//                $query->where('name', 'LIKE', "%{$value}%")->orWhere('email', 'LIKE', "%{$value}%");
                Collection::wrap($value)->each(function ($value) use ($query) {
                    $query
                        ->orWhere('name', 'LIKE', "%{$value}%")
                        ->orWhere('email', 'LIKE', "%{$value}%");
                });
            });
        });

        $payments = QueryBuilder::for(Payment::class)
            ->defaultSort('pay_date_oracle')
            ->allowedSorts([
                'id', 'pay_event_id', 'account_id', 'amount', 'tender_source', 'tender_source_descr', 'filen_name',
                'pay_date_oracle', 'fiscal_flag', 'file_name'
            ])
            ->allowedFilters([
                'id', 'pay_event_id', 'account_id', 'amount', 'tender_source', 'tender_source_descr', 'filen_name',
                'pay_date_oracle', 'fiscal_flag', 'file_name', $globalSearch])
            ->paginate()
            ->withQueryString();

        return Inertia::render('Dashboard', [
            'payments' => $payments,
        ])->table(function (InertiaTable $table) {
            $table
//            ->withGlobalSearch()
//            ->column('id', 'ID', searchable: true, sortable: true)
            ->column('pay_event_id', 'Payment Event CC&B', searchable: true, sortable: true)
            ->column('account_id', 'Лицевой счет', searchable: true, sortable: true)
            ->column('amount', 'Сумма', searchable: true, sortable: true)
            ->column('tender_source', 'Тендер в CC&B', searchable: true, sortable: true)
            ->column('tender_source_descr', 'Описание тендера', searchable: true, sortable: true)
            ->column('file_name', 'Файл реестра', searchable: true, sortable: true)
            ->column('pay_date_oracle', 'Дата платежа', searchable: true, sortable: true)
            ->column('fiscal_flag', 'Загружен в ФНС', searchable: true, sortable: true)
            ->column('fiscal_number', '№ Чека', searchable: true, sortable: true)
            ->column('shift_fiscal_number', 'Смена', searchable: true, sortable: true)
            ->column('receipt_date', 'Дата чека', searchable: true, sortable: true)
            ->column('fn_number', '№ ФН', searchable: true, sortable: true)
            ->column('kkt_registration_number', '№ ККТ', searchable: true, sortable: true)
            ->column('fiscal_attribute', 'ФПД', searchable: true, sortable: true)
            ->column('fiscal_doc_number', '№ ФД', searchable: true, sortable: true)
            ->selectFilter(key: 'tender_source', label: 'Тендер в CC&B', options: [
                'ZPLAT_L' => 'Зенит ЛЭСК',
                'VTB24_L' => 'ВТБ ЛЭСК',
            ]);

        });
    }
}

