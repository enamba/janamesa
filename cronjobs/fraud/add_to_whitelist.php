<?php

require_once(realpath(dirname(__FILE__) . '/../base.php'));


$db = Zend_Registry::get('dbAdapter');

$sql = 'INSERT IGNORE INTO paypal_black_white_list (payerId, created,white, comment, count)
            SELECT ppt.payerId, NOW(), 1 , "whitelist cron", COUNT(ppt.payerId) as count
            FROM `paypal_transactions` ppt LEFT JOIN `orders` o  ON o.id = ppt.orderId  
            WHERE o.state > 0 AND LENGTH(ppt.payerId) > 0 AND TIMESTAMPDIFF(MONTH,o.time, NOW()) < 3 GROUP BY ppt.payerId 
            HAVING count > 3;';


$db->query($sql);
