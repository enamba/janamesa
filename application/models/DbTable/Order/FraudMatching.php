<?php

/**
 * Description of FRaudMatching
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 12.01.2011
 */
class Yourdelivery_Model_DbTable_Order_FraudMatching extends Default_Model_DbTable_Base {

    protected $_name = "order_fraud_matching";

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 16.01.2010
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public static function getAllData() {
        $db = Zend_Registry::get('dbAdapter');

        return $db->fetchAll('select ofm.*,o.state from order_fraud_matching ofm inner join orders o on o.id=ofm.orderId where o.state>=0');
    }

}

?>
