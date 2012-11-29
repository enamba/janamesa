<?php

/**
 * Reset Orders where supporters are on the ticket for more than 5 Minutes
 * 
 * @author Daniel Hahn <hahn@lieferando.de>
 * @since 06.10.2011
 * 
 */
require_once(realpath(dirname(__FILE__) . '/../base.php'));
$lock = new ExclusiveLock(basename(__FILE__));
if ( $lock->lock() ){
    
    $dbRead = Zend_Registry::get('dbAdapterReadOnly');

    try {
        //Select to avoid locks
        $select = $dbRead->select()->from('orders', array('id'))->where("supporter IS NOT NULL and (ADDTIME(pulledOn, '00:05:00') <= NOW() OR pulledOn IS NULL OR pulledOn = 0)");


        $rows = $dbRead->fetchAll($select);        
        if(count($rows) > 0) {        
            $db = Zend_Registry::get('dbAdapter');        
            foreach($rows as $row) {
                $db->update('orders', array('supporter' => NULL) , array("id = ?" => $row['id']));
            }                
        }


    }catch (Zend_Db_Adapter_Exception $e) {
        clog("error", "Reset Tickets: ".$e->getMessage());
    }


    clog("info", "reset tickets: " . count($rows) . " Rows changed");
}
else{
    clog("warn", "could not get lock for " . __FILE__);
}