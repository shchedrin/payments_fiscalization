<?php

namespace App\Http\Controllers\Payments;

use App\Models\Payments;
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

        $payments = QueryBuilder::for(Payments::class)
            ->defaultSort('pay_date')
            ->allowedSorts([
                'id', 'pay_event_id', 'account', 'amount', 'tender_source', 'tender_source_descr', 'filen_name',
                'pay_date', 'fiscal_flag'
            ])
            ->allowedFilters([
                'id', 'pay_event_id', 'account', 'amount', 'tender_source', 'tender_source_descr', 'filen_name',
                'pay_date', 'fiscal_flag', $globalSearch])
            ->paginate()
            ->withQueryString();

        return Inertia::render('Dashboard', [
            'payments' => $payments,
        ])->table(function (InertiaTable $table) {
            $table
//            ->withGlobalSearch()
            ->column('id', 'ID', searchable: true, sortable: true)
            ->column('pay_event_id', 'Payment Event CC&B', searchable: true, sortable: true)
            ->column('account', 'Лицевой счет', searchable: true, sortable: true)
            ->column('amount', 'Сумма', searchable: true, sortable: true)
            ->column('tender_source', 'Тендер в CC&B', searchable: true, sortable: true)
            ->column('tender_source_descr', 'Описание тендера', searchable: true, sortable: true)
            ->column('filen_name', 'Файл реестра', searchable: true, sortable: true)
            ->column('pay_date', 'Дата платежа', searchable: true, sortable: true)
            ->column('fiscal_flag', 'Загружен в ФНС', searchable: true, sortable: true);
        });
    }
}

