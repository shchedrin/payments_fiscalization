<?php

namespace App\Http\Controllers\Payments;

use App\Models\Payments;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    /**
     * Provision a new web server.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showAll(Request $request)
    {
        return view('payments', [
            'payments' => Payments::all()
        ]);
    }
}

