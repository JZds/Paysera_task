<?php

use JZds\Paysera_task\Commission;
use JZds\Paysera_task\CSVImporter;
use JZds\Paysera_task\Operation;

class ScriptTest extends PHPUnit_Framework_TestCase {

    public function testCSVImporterStoresData(){
        $obj = new CSVImporter();

        $data = $obj->importData("input.csv", 2000);

        $this->assertInternalType('array',$data);
        $this->assertEquals(9,count($data));

        $header = array("date","user_id","user_type","operation","amount","currency");

        foreach($data as $key => $operation){
            for($i = 0; $i < count($header); $i++){
                $this->assertArrayHasKey($header[$i], $operation);
            }
            $this->assertNotNull(array_values($operation));
        }
    }

    public function testOperationConversionFunctions(){
        $op = new Operation();

        $currency = array(
            "USD" => 1.1497,
            "JPY" => 129.53,
            "EUR" => 1
        );

        // To Eur Conversion test
        for($i = 0; $i < 1000; $i++) {
            $random = rand();
            $cur = array_rand($currency);
            $ans = $op->conversionToEur($random, $cur);

            $this->assertEquals(round($random * (1 / $currency[$cur]),4), $ans);

//            echo "\n\nAmount[". $cur ."]: " . $random . "\n Converted[EUR]:" . $ans;
        }

        // From Eur Conversion test
        for($i = 0; $i < 1000; $i++) {
            $random = (float)rand()/(float)getrandmax();
            $cur = array_rand($currency);
            $ans = $op->conversionBackFromEur($random, $cur);

            $this->assertEquals(($random * $currency[$cur]), $ans);

//            echo "\n\nAmount[EUR]: " . $random . "\n Converted[". $cur ."]: " . $ans;
        }
    }

    public function testCommissionLimitFunctions(){
        $com = new Commission();

        $cashInLimit = array(
            "USD" => 5.7485,
            "JPY" => 647.65,
            "EUR" => 5
        );

        $cashOutLimit = array(
            "USD" => 0.57485,
            "JPY" => 64.765,
            "EUR" => 0.5
        );

        // Cash out limit test
        for($i = 0; $i < 1000; $i++) {
            $CashOut = (float)rand()/(float)getrandmax();
            $cashOutTest = $com->cashOutLimit($CashOut, "EUR");
            $this->assertGreaterThanOrEqual($cashOutLimit['EUR'], $cashOutTest);

//            echo "Random number: " . $CashOut . "\n Answer: " . $cashOutTest . "\n limit: " . $cashOutLimit['EUR'] . "\n";
        }

        // Cash in limit test
        for($i = 0; $i < 1000; $i++) {
            $CashIn = rand();
            $cashInTest = $com->cashInLimit($CashIn, "EUR");
            $this->assertLessThanOrEqual($cashInLimit['EUR'], $cashInTest);

//            echo "Random number: " . $CashIn . "\n Answer: " . $cashInTest . "\n limit: " . $cashInLimit['EUR'] . "\n";
        }
    }

    public function testCommissionFeeFunction(){
        $com = new Commission();
        $op = new Operation();

        $cashInLimit = array(
            "USD" => 5.7485,
            "JPY" => 647.65,
            "EUR" => 5
        );

        $cashOutLimit = array(
            "USD" => 0.57485,
            "JPY" => 64.765,
            "EUR" => 0.5
        );

        // Cash in commission fee
        for($i = 0; $i < 1000; $i++){
            $currencyCashIn = array_rand($cashInLimit);
            $random = rand();
            $ans = $com->getCommissionFee("cash_in", "natural", $op->conversionToEur($random, $currencyCashIn), $currencyCashIn);
            $this->assertLessThanOrEqual($cashInLimit[$currencyCashIn], $ans);

//            echo "\n\nCurrency: " . $currencyCashIn . "\n Amount: " . $random . "\n Commission Fee: " . $ans ;
        }

        // Cash out commission fee by legal and natural
        for($i = 0; $i < 1000; $i++){
            $currencyCashOut = array_rand($cashOutLimit);
            $random = rand();
            $ans = $com->getCommissionFee("cash_out", "legal", $op->conversionToEur( $random, $currencyCashOut), $currencyCashOut);
            $this->assertGreaterThanOrEqual($cashOutLimit[$currencyCashOut], $ans);

//            echo "\n\nCurrency: " . $currencyCashOut . "\n Amount: " . $random . "\n Commission Fee: " . $ans ;

            $ans2 = $com->getCommissionFee("cash_in", "natural", $op->conversionToEur(rand(), $currencyCashOut), $currencyCashOut);
            $this->assertNotNull($cashOutLimit[$currencyCashOut], $ans2);
            $this->assertGreaterThan(0, $ans2);
        }
    }
}