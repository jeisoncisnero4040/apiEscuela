<?php

namespace App\constans;

class Currencies{
    static function getCurrencieslist(){
        $currencies = [
            'USD',
            'EUR',
            'GBP',
            'AUD',
            'CAD',
            'JPY',
            'CHF',
            'CNY',
            'SEK',
            'NZD',
            'SGD',
            'HKD',
            'NOK',
            'MXN',
            'BRL',
            'INR',
            'RUB',
            'ZAR',
            'DKK',
            'PLN',
        ];
        return $currencies;
    }
}