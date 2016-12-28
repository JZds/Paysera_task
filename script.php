<?php

use JZds\Paysera_task\ATM;

require __DIR__ . '/vendor/autoload.php';

if(isset($argv[1])) {
    $filename = $argv[1];
    $ATM = new ATM();
    $ATM->Run($filename);
} else {
    echo "No csv file supplied.\n";
}
?>