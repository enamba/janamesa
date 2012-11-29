<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

$db = Zend_Registry::get('dbAdapter');
$sql = 'select imei,password from gprs_printer';
$result = $db->query($sql);

//ping to them all
foreach($result as $printer){
    $ret = Yourdelivery_Sender_Sms_Printer::ping($printer['imei'], $printer['password']);
    /**
     * @todo: what shall we do if the printer is offline?
     */
}
