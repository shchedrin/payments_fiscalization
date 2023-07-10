<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Cashbox
{
    public const API_VERSION = '/v1';
    public const BASE_URL = 'https://kkm.rarus-cloud.ru';

    public $company;

    private $client;
    private $inn;
    private $apiKey;

    public function __construct(string $company)
    {
        $apiKey = $inn = null;
        switch ($company) {
            case 'LESK':
                $this->apiKey = env('RARUS_API_KEY_LESK');
                $this->inn = env('RARUS_INN_LESK');
            case 'GESK':
                $this->apiKey = env('RARUS_API_KEY_GESK');
                $this->inn = env('RARUS_INN_GESK');
            case 'TEST':
                $this->apiKey = env('RARUS_API_KEY_TEST');
                $this->inn = env('RARUS_INN_TEST');
        }
        $this->client = Http::withHeaders([
            'API-KEY' => $this->apiKey
        ])->acceptJson()->baseUrl(self::BASE_URL);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function ping()
    {
        $response = $this->getClient()->get('/ping');
        return $response->json();
    }

    public function sendDocument(Payment $payment)
    {
        $response = $this->getClient()->post(self::API_VERSION . '/document', [
            'id' => $payment->operation_id,
            'doc_type' => 'sale',
            'tax_system' => 'OSN',
            'inn' => '6234117358',
            'items' => [
                [
                    'name' => 'Электроэнергия',
                    'price' => floatval($payment->amount),
                    'quantity' => 1,
                    'sum' => floatval($payment->amount),
                    'tax' => 'vat20',
                    'tax_sum' => round($payment->amount / 120 * 20, 2),
                    'sign_method_calculation' => 'full_payment',
                    'sign_calculation_object' => 'commodity',
                ]
            ],
            'total' => floatval($payment->amount)
        ]);
        return $response->json();
    }
}
