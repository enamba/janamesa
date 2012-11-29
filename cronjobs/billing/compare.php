<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

$inDir = APPLICATION_PATH . '/../storage/csvcompare/in';
$outDir = APPLICATION_PATH . '/../storage/csvcompare/out';

if (!is_dir($inDir)) {
    mkdir($inDir, 0777, true);
}

if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

$stack = glob($inDir . '/*.csv');

//loop through rows
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 0);

//start comparison                                
$unpayed = Yourdelivery_Model_Billing::allUnpayed();

error_reporting(E_ERROR);

foreach ($stack as $file) {

    $fp = fopen($file, 'r');

    $result = array();

    $compareData = array();
    while (($data = fgetcsv($fp, 1000, ";")) !== FALSE) {
        $compareData[] = $data;
    }

    foreach ($unpayed as $key => $u) {

        echo '.'; //. $key;
        $billNumberVals = explode('-', $u['number']);
        $billRegexString = "/R{0,1}(.*)(" . $billNumberVals[1] . ")(.*)(" . $billNumberVals[2] . ")(.*)(" . $billNumberVals[3] . ")/";


        $searchFor = array(
            "amount" => array(str_replace(".", ".", round(($u['amount'] / 100), 2)), null, false, null),
            "number" => array($u['number'], null, false, null),
            "name" => array(strtoupper($u['name']), null, false, null),
            "ktoNr" => array($u['ktoNr'], null, false, null),
            "ktoBlz" => array($u['ktoBlz'], null, false, null),
            "ktoName" => array(strtoupper($u['ktoName']), null, false, null),
            "customerNr" => array($u['customerNr'], null, false, null),
            "contactName" => array(strtoupper($u['contact_name']), null, false, null),
            "voucher" => array("-" . str_replace(".", ".", round(($u['voucher'] / 100), 2)), null, false, null),
        );

        $resultFor = array();


        $i = 0;
        foreach ($compareData as $csvRow => $data) {
            $i++;
            $score = 0;
            $tmpFor = array();
            foreach ($data as $dataKey => $d) {

                if (empty($d)) {
                    continue;
                }
                foreach ($searchFor as $key => $val) {

                    if (empty($val[0])) {
                        continue;
                    }
                    $sim = 0;
                    $testBill = false;


                    //test for bill Number
                    if ($dataKey > 28 && $key == "number") {
                        if (count($billNumberVals) == 4 && preg_match($billRegexString, $d)) {
                            $score += 102;
                            $tmpFor[$key] = array($val[0], $d, true, $data);
                        }
                        //match Kto Nr
                    } elseif ($dataKey > 28 && $key == "ktoNr") {

                        if (preg_match("/KTO(.*)(" . preg_quote($val[0]) . ")/", $d)) {
                            $score += 50;
                            $tmpFor[$key] = array($val[0], $d, true, $data);
                        }
                        //match BLZ     
                    } elseif ($dataKey > 28 && $key == "ktoBlz") {

                        if (preg_match("/BLZ(.*)(" . preg_quote($val[0]) . ")/", $d)) {
                            $score += 10;
                            $tmpFor[$key] = array($val[0], $d, true, $data);
                        }
                        //match amount        
                    } elseif ($dataKey === 2 && $key == "amount" && !empty($val[0]) && $val[0] != 0 && strcmp($val[0], trim($d)) == 0) {

                        $score += 60;
                        $tmpFor[$key] = array($val[0], $d, true, $data);
                        //match voucher    
                    } elseif ($dataKey === 2 && $key == "voucher" && !empty($val[0]) && $val[0] != 0 && strcmp($val[0], trim($d)) == 0) {

                        $score += 60;
                        $tmpFor[$key] = array($val[0], $d, true, $data);
                        //match Name , ktoName, Contact Name       
                    } elseif ($dataKey > 28 && ($key == "name" || $key == "ktoName" || $key == "contactName")) {
                        similar_text($d, $val[0], $sim);
                        if ($sim > 95) {
                            $score += 10;
                            $tmpFor[$key] = array($val[0], $d, true, $data);
                        }
                    }
                }
            }

            if ($score > 101) {
                $resultFor = array_merge($tmpFor, $resultFor);
                $resultFor['score'] = array("Score: " . $score, "Zeile: " . ($i), true, null);
                break;
            }
        }


        if (count($resultFor) > 0) {
            //create result list

            $amount = floatval($u['amount']);
            $billAmount = (empty($amount)) ? "-" . $u['voucher'] : $u['amount'];


            $result[] = array(
                array('number' => $u['number'], 'id' => $u['uid'], 'amount' => round($billAmount / 100, 2), 'score'),
                $resultFor
            );
        }
    }



    $view->resultLength = count($searchFor);
    $view->result = $result;

    $html = $view->fetch('administration/billing/_compare_result.htm');

    $out = fopen($outDir . sprintf('/vergleich-%s.html', date('y-d-m-H:i:s')), 'a+');
    fwrite($out, $html);
    fclose($out);

    unlink($file);
}
