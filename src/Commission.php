<?php

namespace JZds\Paysera_task;


class Commission {

    private $minCommissionFee = 0.50;
    private $maxCommissionFee = 5.00;
    private $cashInCommissionRate = 0.03;
    private $cashOutCommissionRate = 0.3;

    public function getCommissionFee($operationType, $user_type, $amount, $currency){

        if($operationType == "cash_in"){
            $commissionRate = $this->cashInCommissionRate;
        } elseif($operationType == "cash_out"){
            $commissionRate = $this->cashOutCommissionRate;
        } else {
            echo "Bad operation: $operationType \n";
            die();
        }

        $commissionAmount = ($commissionRate / 100) * $amount;

        if($operationType == "cash_out" && $user_type == "legal"){
            $commissionAmount = $this->cashOutLimit($commissionAmount, $currency);
        } elseif($operationType == "cash_in"){
            $commissionAmount = $this->cashInLimit($commissionAmount, $currency);
        } else {
            $op = new Operation();
            return $op->ConversionBackFromEur($commissionAmount, $currency);
        }
        return $commissionAmount;
    }

    public function cashOutLimit($amount, $currency){

        $op = new Operation();
        if ($this->minCommissionFee > $amount) {

            return $op->ConversionBackFromEur($this->minCommissionFee, $currency);
        }
        return $op->ConversionBackFromEur($amount, $currency);
    }

    public function cashInLimit($amount, $currency){

        $op = new Operation();
        if ($this->maxCommissionFee < $amount) {

            return $op->ConversionBackFromEur($this->maxCommissionFee, $currency);
        }
        return $op->ConversionBackFromEur($amount, $currency);
    }
}
