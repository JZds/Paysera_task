<?php

namespace JZds\Paysera_task;


class ATM {

    private $freeCommissionLimit = 1000.00;
    private $freeCommissionCountLimit = 3;
    private $importDataLimit = 2000;

    public function Run($filename){

        $file = new CSVImporter();

        //Load data from input
        $data = $file->importData($filename, $this->importDataLimit);

        foreach($data as $operation){

            $user = $operation['user_id'];

            //Initialize natural cashOut array
            isset($naturalCashOut[$user]) ?: $naturalCashOut[$user] = [];

            $op = new Operation();
            //Pre conversion to EUR
            $operation['amount'] = $op->conversionToEur($operation['amount'], $operation['currency']);

            if($operation['operation'] == "cash_out" && $operation['user_type'] == "natural"){

                // Fill in natural cashOut array with data
                $naturalCashOut[$user] = $op->collectData($operation, $naturalCashOut[$user]);

                // Count free commission
                $operation['amount'] = $op->freeCommission(
                    $naturalCashOut[$user],
                    $operation,
                    $this->freeCommissionLimit,
                    $this->freeCommissionCountLimit
                );
            }

            $com = new Commission();
            // Count commission
            $commissionFee = $com->getCommissionFee(
                $operation['operation'],
                $operation['user_type'],
                $operation['amount'],
                $operation['currency']);

            echo $this->roundUp($commissionFee, 2) . "\n";
        }
    }

    private function roundUp($value, $places=0) {
        if ($places < 0) { $places = 0; }
        $mult = pow(10, $places);
        return number_format(ceil($value * $mult) / $mult, $places);
    }
}
?>