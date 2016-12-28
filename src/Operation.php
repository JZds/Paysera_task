<?php namespace JZds\Paysera_task;

use Carbon\Carbon;

class Operation {

    private $exchangeRates = array(
        "USD" => 1.1497,
        "JPY" => 129.53
    );

    public function collectData($data, $collection)
    {
        $operationDate = Carbon::createFromFormat('Y-m-d', $data['date']);

        if ( ! empty($collection)
            && $collection['year'] == $operationDate->year
            && $collection['week'] == $operationDate->weekOfYear) {

            $collection['amount'] += $data['amount'];
            $collection['count'] += 1;

            return $collection;
        }

        $collection = array(
            'year'  =>  $operationDate->year,
            'week'  =>  $operationDate->weekOfYear,
            'amount' => $data['amount'],
            'count' => 1
        );

        return $collection;
    }

    public function freeCommission($collection, $data, $amountLimit, $countLimit)
    {
        if ($collection['count'] > $countLimit) {
            return $data['amount'];
        }

        $diff = $collection['amount'] - $amountLimit;

        if ($diff < 0) {
            return 0;
        } elseif ($diff > $data['amount']) {
            return $data['amount'];
        } else {
            return $this->conversionBackFromEur($diff, $data['currency']);
        }
    }

    public function conversionToEur($amount, $currency){
        foreach($this->exchangeRates as $i => $rate){
            if($currency == $i){
                return round($amount * (1 / $rate),4);
            }
        }
        return $amount;
    }

    public function conversionBackFromEur($amount, $currency){
        foreach($this->exchangeRates as $i => $rate){
            if($currency == $i){
                return $amount * $rate;
            }
        }
        return $amount;
    }
}