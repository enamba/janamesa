<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));

$db = Zend_Registry::get('dbAdapterReadOnly');

$from = '2012-05-01 00:00:00';
$until = '2012-05-31 23:59:59';

$select = $db->select()
        ->from('billing')
        ->where('`until` = ?', $until)
        ->where('`from` = ?', $from);
$select->where('mode="rest"')
        ->where('status=1')
        ->where('refId not in (
12041,
12084,
12370,
12451,
12479,
12643,
12779,
12808,
12931,
13106,
13315,
13488,
13666,
14312,
14443,
14757,
14825,
15085,
15137,
15257,
15378,
15776,
15984,
16213,
16324,
16458,
16568,
16605,
16708,
16709,
16710,
16711,
16720,
16801,
17121,
17126,
17207,
17257,
17362,
17782,
17996,
18478,
18490,
18541,
19078,
19479,
19559,
19679,
20313,
20360
)')
        ->where('voucher > 0');
$rows = $db->query($select);

$filename = 'DTAUS01';
$file = tempnam('/tmp', 'yd');
$fp = fopen($file, 'w');

$dta = new Default_Banking_DTA($postData['type']);
$dta->setAccountFileSender(array(
    'name' => 'yd. Yourdelivery GmbH',
    'bank_code' => '10070124',
    'account_number' => '112132600'
));
foreach ($rows as $row) {
    try {
        $billing = new Yourdelivery_Model_Billing($row['id']);
    } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        continue;
    }

    if (!$billing->getObject() instanceof Yourdelivery_Model_Servicetype_Abstract) {
        continue;
    }

    $rev = $billing->getCustomized();
    $kto = $billing->getObject()->getKtoBlz();
    $blz = $billing->getObject()->getKtoNr();


    if (intval($kto) == 0 || intval($blz) == 0) {
        continue;
    }

    $inhaber = $billing->getObject()->getKtoName();
    if ($inhaber == "") {
        $inhaber = $billing->getObject()->getName();
    }

    $receiver = array(
        'name' => $inhaber,
        'account_number' => str_replace(' ', '', $blz),
        'bank_code' => str_replace(' ', '', $kto)
    );

    $amount = intToPrice(round($billing->getVoucher()), 2, '.');

    $dta->addExchange(
            $receiver, $amount, array(
        'first' => $billing->getNumber(),
        'second' => $billing->getObject()->getName()
            )
    );
}

fwrite($fp, $dta->getFileContent());