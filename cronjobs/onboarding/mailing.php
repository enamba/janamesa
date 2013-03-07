<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));
gc_enable();
$memLimit = 6096;
require_once('Mailchimp/MCAPI.class.php');
ini_set('memory_limit', $memLimit . 'M');
ini_set('max_execution_time', 0);

$db = Zend_Registry::get('dbAdapterReadOnly');
$config = Zend_Registry::get('configuration');

$select = $db->select()->
        from(array('nr' => 'customers'), array('FNAME'=>'name','LNAME'=> 'prename', 'EMAIL' => 'email'))->
        where('created >= DATE(NOW() - INTERVAL 1 DAY) and created < DATE(NOW())');
$rowsD_1 = $db->fetchAll($select);

$select = $db->select()->
        from(array('nr' => 'customers'), array('FNAME'=>'name', 'LNAME'=>'prename', 'EMAIL' => 'email'))->
        where('created >= DATE(NOW() - INTERVAL 10 DAY) and created < DATE(NOW() - INTERVAL 9 DAY)');
$rowsD_10 = $db->fetchAll($select);

$final = strtotime('2012-06-01');
$parcial_7A = time();
$parcial_7B = strtotime('7 days ago', $parcial_7A);
while ($parcial_7A > $final){
    $parcial_7A = strtotime('14 days ago', $parcial_7A);
    $parcial_7A_d = date('Y-m-d', $parcial_7A);
    $parcial_7A_d_1 = date('Y-m-d', strtotime('+1 day', $parcial_7A));
    
    $parcial_7B = strtotime('14 days ago', $parcial_7B);
    $parcial_7B_d = date('Y-m-d', $parcial_7B);
    $parcial_7B_d_1 = date('Y-m-d', strtotime('+1 days', $parcial_7B));
    
    $select = $db->select()->
            from(array('nr' => 'customers'), array('FNAME'=>'name', 'LNAME'=>'prename', 'EMAIL' => 'email'))->
            where('created >= "'.$parcial_7A_d.'" and created < "' . $parcial_7A_d_1 . '"');
    $rowsD_7_a[] = $db->fetchAll($select);
    
    $select = $db->select()->
            from(array('nr' => 'customers'), array('FNAME'=>'name', 'LNAME'=>'prename', 'EMAIL' => 'email'))->
            where('created >= "'.$parcial_7B_d.'" and created < "' . $parcial_7B_d_1 . '"');
    $rowsD_7_b[] = $db->fetchAll($select);
}

//echo "\n ListID:" . $config->mailchimp->listId->D1;
//echo "\n apikey:" . $config->mailchimp->apikey;

$api = new MCAPI($config->mailchimp->apikey);

// --- D+1  \/
//echo "D+1 \n";

$retval = $api->listMembers($config->mailchimp->listId->D1, 'subscribed', null, 0, 5000 );
$email_remove = array();
foreach($retval['data'] as $member){
    $email_remove[] = $member['email'];
}
$vals = $api->listBatchUnsubscribe($config->mailchimp->listId->D1, $email_remove, true, false, false);
//echo "success:".$vals['success_count']."\n";
//echo "errors:".$vals['error_count']."\n";

$vals_D_1 = $api->listBatchSubscribe($config->mailchimp->listId->D1,$rowsD_1,false, true, false);

//if ($api->errorCode){
//    echo "Batch Subscribe failed! D1 \n";
//	echo "code:".$api->errorCode."\n";
//	echo "msg :".$api->errorMessage."\n";
//} else {
//	echo "added:   ".$vals_D_1['add_count']."\n";
//	echo "updated: ".$vals_D_1['update_count']."\n";
//	echo "errors:  ".$vals_D_1['error_count']."\n";
//	foreach($vals_D_1['errors'] as $val){
//		echo $val['email_address']. " failed\n";
//		echo "code:".$val['code']."\n";
//		echo "msg :".$val['message']."\n";
//	}
//}
// --- D+1 /\
// 
// --- D+10  \/
//echo "D+10 \n";

$retval = $api->listMembers($config->mailchimp->listId->D10, 'subscribed', null, 0, 5000 );
$email_remove = array();
foreach($retval['data'] as $member){
    $email_remove[] = $member['email'];
}
$vals = $api->listBatchUnsubscribe($config->mailchimp->listId->D10, $email_remove, true, false, false);
//echo "success:".$vals['success_count']."\n";
//echo "errors:".$vals['error_count']."\n";

$vals_D_10 = $api->listBatchSubscribe($config->mailchimp->listId->D10,$rowsD_10,false, true, false);

//if ($api->errorCode){
//    echo "Batch Subscribe failed! D10 \n";
//	echo "code:".$api->errorCode."\n";
//	echo "msg :".$api->errorMessage."\n";
//} else {
//	echo "added:   ".$vals_D_10['add_count']."\n";
//	echo "updated: ".$vals_D_10['update_count']."\n";
//	echo "errors:  ".$vals_D_10['error_count']."\n";
//	foreach($vals_D_10['errors'] as $val){
//		echo $val['email_address']. " failed\n";
//		echo "code:".$val['code']."\n";
//		echo "msg :".$val['message']."\n";
//        }
//}
// --- D+10 /\
// 
// --- D+7_A  \/
//echo "D*7_A \n";
$retval = $api->listMembers($config->mailchimp->listId->D7A, 'subscribed', null, 0, 5000 );
$email_remove = array();
foreach($retval['data'] as $member){
    $email_remove[] = $member['email'];
}
$simple_rowD_7_a = array();
foreach ($rowsD_7_a as $rowD_7_a){
    $simple_rowD_7_a = array_merge($simple_rowD_7_a, $rowD_7_a);
}

$vals = $api->listBatchUnsubscribe($config->mailchimp->listId->D7A, $email_remove, true, false, false);
//echo "success:".$vals['success_count']."\n";
//echo "errors:".$vals['error_count']."\n";
$vals_D_7_a = $api->listBatchSubscribe($config->mailchimp->listId->D7A,$simple_rowD_7_a,false, true, false);

//if ($api->errorCode){
//    echo "Batch Subscribe failed! D*7 A \n";
//	echo "code:".$api->errorCode."\n";
//	echo "msg :".$api->errorMessage."\n";
//} else {
//	echo "added:   ".$vals_D_7_a['add_count']."\n";
//	echo "updated: ".$vals_D_7_a['update_count']."\n";
//	echo "errors:  ".$vals_D_7_a['error_count']."\n";
//	foreach($vals_D_7_a['errors'] as $val){
//		echo $val['email_address']. " failed\n";
//		echo "code:".$val['code']."\n";
//		echo "msg :".$val['message']."\n";
//	}
//}
// --- D+7_A /\
 
// 
// --- D+7_B  \/
//echo "D*7_B \n";
$retval = $api->listMembers($config->mailchimp->listId->D7B, 'subscribed', null, 0, 5000 );
$email_remove = array();
foreach($retval['data'] as $member){
    $email_remove[] = $member['email'];
}
$simple_rowD_7_b = array();
foreach ($rowsD_7_b as $rowD_7_b){
    $simple_rowD_7_b = array_merge($simple_rowD_7_b, $rowD_7_b);
}


$vals = $api->listBatchUnsubscribe($config->mailchimp->listId->D7B, $email_remove, true, false, false);
//echo "success:".$vals['success_count']."\n";
//echo "errors:".$vals['error_count']."\n";

$vals_D_7_b = $api->listBatchSubscribe($config->mailchimp->listId->D7B,$simple_rowD_7_b,false, true, false);

//if ($api->errorCode){
//    echo "Batch Subscribe failed! D*7 B \n";
//	echo "code:".$api->errorCode."\n";
//	echo "msg :".$api->errorMessage."\n";
//} else {
//	echo "added:   ".$vals_D_7_b['add_count']."\n";
//	echo "updated: ".$vals_D_7_b['update_count']."\n";
//	echo "errors:  ".$vals_D_7_b['error_count']."\n";
//	foreach($vals_D_7_b['errors'] as $val){
//		echo $val['email_address']. " failed\n";
//		echo "code:".$val['code']."\n";
//		echo "msg :".$val['message']."\n";
//        }
//}

?>
