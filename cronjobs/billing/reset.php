<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));


$sql = "select id from billing where mode in ('rest') and (`from`='2011-03-01 00:00:00' or `from`='2011-03-16 00:00:00')";
$db = Zend_Registry::get('dbAdapter');
$count = 0;

$reset = $db->fetchAll($sql);

foreach ($reset as $key => $value) {
    $count++;
    try {
        $bill = new Yourdelivery_Model_Billing($value['id']);
        unset($reset[$key]);
        $status = Yourdelivery_Model_Billing::rebuild($value['id']);
        if (!$status) {
            echo "E: " . $bill->getId() . "\n";
        } else {
            echo "S: " . $bill->getId() . "\n";
        }
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        echo "\nF: " . $value['id'] . '\n';
    }
}
    
